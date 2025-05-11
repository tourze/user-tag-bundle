<?php

namespace UserTagBundle\Service;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Symfony\AopLockBundle\Attribute\Lockable;
use Tourze\UserTagContracts\TagServiceInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Category;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Event\BeforeAddTagEvent;
use UserTagBundle\Repository\AssignLogRepository;
use UserTagBundle\Repository\CategoryRepository;
use UserTagBundle\Repository\TagRepository;

/**
 * 通用的CRM服务，未完整
 * 第三方Bundle如果想覆盖这里的行为，可以使用Decorate功能
 *
 * @see https://symfony.com/doc/current/service_container/service_decoration.html
 * @see https://symfony.com/blog/new-in-symfony-6-1-service-decoration-attributes
 */
class LocalUserTagService implements TagServiceInterface
{
    public function __construct(
        private readonly AssignLogRepository $assignLogRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TagRepository $tagRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * 为指定消费者打标
     */
    #[Lockable('crm_tag_user_{{ user.id }}')]
    public function assignTag(UserInterface $user, Tag $tag): AssignLog
    {
        $event = new BeforeAddTagEvent();
        $event->setUser($user);
        $event->setTag($tag);
        $this->eventDispatcher->dispatch($event);
        // 上面的事件处理中，是可能会发生异常的

        // TODO 互斥检查
        $removeTags = [];
        if ($tag->getCategory()?->isMutex()) {
            foreach ($tag->getCategory()->getTags() as $item) {
                if ($item->getId() !== $tag->getId()) {
                    $removeTags[] = $item;
                }
            }
        }

        $items = $this->assignLogRepository->findBy([
            'user' => $user,
        ]);
        $assignLog = null;
        foreach ($items as $item) {
            if ($item->getTag()->getId() === $tag->getId()) {
                $assignLog = $item;
                continue;
            }
            if (in_array($item->getTag(), $removeTags)) {
                $this->entityManager->remove($item);
            }
        }
        $this->entityManager->flush();

        if (!$assignLog) {
            $assignLog = new AssignLog();
        }
        $assignLog->setUser($user);
        $assignLog->setTag($tag);
        $assignLog->setValid(true);
        $assignLog->setAssignTime(Carbon::now());
        $this->entityManager->persist($assignLog);
        $this->entityManager->flush();

        return $assignLog;
    }

    /**
     * 解绑标签
     */
    #[Lockable('crm_tag_user_{{ user.getUserIdentifier() }}')]
    public function unassignTag(UserInterface $user, Tag $tag): AssignLog
    {
        $assignLog = $this->assignLogRepository->findOneBy([
            'user' => $user,
            'tag' => $tag,
        ]);

        if (!$assignLog) {
            $assignLog = new AssignLog();
        }
        $assignLog->setUser($user);
        $assignLog->setTag($tag);
        $assignLog->setValid(false);
        $assignLog->setUnassignTime(Carbon::now());
        $this->entityManager->persist($assignLog);
        $this->entityManager->flush();

        return $assignLog;
    }

    /**
     * 拉取标签
     */
    public function getTagByName(string $name, string $categoryName = ''): Tag
    {
        $category = null;
        if (!empty($categoryName)) {
            $category = $this->categoryRepository->findOneBy([
                'name' => $categoryName,
                'valid' => true,
            ]);
            if (!$category) {
                $category = new Category();
                $category->setName($categoryName);
                $category->setValid(true);
                $this->entityManager->persist($category);
                $this->entityManager->flush();
            }
        }

        $tag = $this->tagRepository->findOneBy(['name' => $name, 'category' => $category]);
        if (!$tag) {
            $tag = new Tag();
            $tag->setName($name);
            $tag->setCategory($category);
            $this->entityManager->persist($tag);
            $this->entityManager->flush();
        }

        return $tag;
    }

    public function loadTagsByUser(UserInterface $user): iterable
    {
        $logs = $this->assignLogRepository->findBy(['user' => $user]);
        foreach ($logs as $log) {
            yield $log->getTag();
        }
    }
}
