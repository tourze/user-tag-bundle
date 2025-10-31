<?php

namespace UserTagBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\CatalogBundle\Service\CatalogService;
use Tourze\Symfony\AopLockBundle\Attribute\Lockable;
use Tourze\UserTagContracts\TagLoaderInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Event\BeforeAddTagEvent;
use UserTagBundle\Repository\AssignLogRepository;
use UserTagBundle\Repository\TagRepository;

/**
 * 通用的CRM服务，未完整
 * 第三方Bundle如果想覆盖这里的行为，可以使用Decorate功能
 *
 * @see https://symfony.com/doc/current/service_container/service_decoration.html
 * @see https://symfony.com/blog/new-in-symfony-6-1-service-decoration-attributes
 */
readonly class LocalUserTagLoader implements TagLoaderInterface
{
    public function __construct(
        private AssignLogRepository $assignLogRepository,
        private EntityManagerInterface $entityManager,
        private TagRepository $tagRepository,
        private CatalogService $catalogService,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * 为指定消费者打标
     * 不考虑并发
     */
    #[Lockable(key: 'crm_tag_user_{{ user.id }}')]
    public function assignTag(UserInterface $user, Tag $tag): AssignLog
    {
        $this->dispatchBeforeAddTagEvent($user, $tag);

        $removeTags = $this->getMutexTags($tag);
        $assignLog = $this->processExistingLogs($user, $tag, $removeTags);

        if (null === $assignLog) {
            $assignLog = new AssignLog();
        }

        $this->setupAssignLog($assignLog, $user, $tag);
        $this->entityManager->persist($assignLog);
        $this->entityManager->flush();

        return $assignLog;
    }

    private function dispatchBeforeAddTagEvent(UserInterface $user, Tag $tag): void
    {
        $event = new BeforeAddTagEvent();
        $event->setUser($user);
        $event->setTag($tag);
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * @return array<Tag>
     */
    private function getMutexTags(Tag $tag): array
    {
        $catalog = $tag->getCatalog();
        if (null === $catalog) {
            return [];
        }

        $metadata = $catalog->getMetadata();
        if (!is_array($metadata) || !((bool) ($metadata['mutex'] ?? false))) {
            return [];
        }

        return $this->findSiblingTags($catalog, $tag->getId());
    }

    /**
     * @return array<Tag>
     */
    private function findSiblingTags(Catalog $catalog, int $excludeTagId): array
    {
        $siblingTags = $this->tagRepository->findBy(['catalog' => $catalog]);
        $removeTags = [];

        foreach ($siblingTags as $item) {
            if ($item->getId() !== $excludeTagId) {
                $removeTags[] = $item;
            }
        }

        return $removeTags;
    }

    /**
     * @param array<Tag> $removeTags
     */
    private function processExistingLogs(UserInterface $user, Tag $tag, array $removeTags): ?AssignLog
    {
        $items = $this->assignLogRepository->findBy(['userId' => $user->getUserIdentifier()]);
        $assignLog = null;

        foreach ($items as $item) {
            $itemTag = $item->getTag();
            if (null !== $itemTag && $itemTag->getId() === $tag->getId()) {
                $assignLog = $item;
                continue;
            }
            if (null !== $itemTag && in_array($itemTag, $removeTags, true)) {
                $this->entityManager->remove($item);
            }
        }

        $this->entityManager->flush();

        return $assignLog;
    }

    /**
     * 设置分配日志
     * 不考虑并发
     */
    private function setupAssignLog(AssignLog $assignLog, UserInterface $user, Tag $tag): void
    {
        $assignLog->setUserId($user->getUserIdentifier());
        $assignLog->setTag($tag);
        $assignLog->setValid(true);
        $assignLog->setAssignTime(CarbonImmutable::now());
    }

    /**
     * 解绑标签
     * 不考虑并发
     */
    #[Lockable(key: 'crm_tag_user_{{ user.getUserIdentifier() }}')]
    public function unassignTag(UserInterface $user, Tag $tag): AssignLog
    {
        $assignLog = $this->assignLogRepository->findOneBy([
            'userId' => $user->getUserIdentifier(),
            'tag' => $tag,
        ]);

        if (null === $assignLog) {
            $assignLog = new AssignLog();
        }

        $this->setupUnassignLog($assignLog, $user, $tag);
        $this->entityManager->persist($assignLog);
        $this->entityManager->flush();

        return $assignLog;
    }

    /**
     * 设置解绑日志
     * 不考虑并发
     */
    private function setupUnassignLog(AssignLog $assignLog, UserInterface $user, Tag $tag): void
    {
        $assignLog->setUserId($user->getUserIdentifier());
        $assignLog->setTag($tag);
        $assignLog->setValid(false);
        $assignLog->setUnassignTime(CarbonImmutable::now());
    }

    /**
     * 拉取标签
     */
    public function getTagByName(string $name, string $catalogName = ''): Tag
    {
        $catalog = null;
        if ('' !== $catalogName) {
            $catalog = $this->catalogService->findOneBy([
                'name' => $catalogName,
                'enabled' => true,
            ]);
            if (null === $catalog) {
                // 获取默认的 CatalogType
                $catalogType = $this->getDefaultCatalogType();

                $catalog = new Catalog();
                $catalog->setType($catalogType);
                $catalog->setName($catalogName);
                $catalog->setEnabled(true);
                $this->entityManager->persist($catalog);
                $this->entityManager->flush();
            }
        }

        $tag = $this->tagRepository->findOneBy(['name' => $name, 'catalog' => $catalog]);
        if (null === $tag) {
            $tag = new Tag();
            $tag->setName($name);
            $tag->setCatalog($catalog);
            $this->entityManager->persist($tag);
            $this->entityManager->flush();
        }

        return $tag;
    }

    private function getDefaultCatalogType(): CatalogType
    {
        // 获取第一个启用的 CatalogType 作为默认类型
        $catalogType = $this->catalogService->findCatalogTypeOneBy(['enabled' => true]);

        if (null === $catalogType) {
            // 如果没有找到，创建一个默认的 CatalogType
            $catalogType = new CatalogType();
            $catalogType->setCode('default');
            $catalogType->setName('默认分类');
            $catalogType->setDescription('系统默认的分类类型');
            $catalogType->setEnabled(true);
            $this->entityManager->persist($catalogType);
            $this->entityManager->flush();
        }

        return $catalogType;
    }

    public function loadTagsByUser(UserInterface $user): iterable
    {
        $logs = $this->assignLogRepository->findBy(['userId' => $user->getUserIdentifier()]);
        foreach ($logs as $log) {
            $tag = $log->getTag();
            if (null !== $tag) {
                yield $tag;
            }
        }
    }
}
