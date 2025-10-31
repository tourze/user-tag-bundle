<?php

namespace UserTagBundle\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CatalogBundle\Service\CatalogService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\SmartRuleRepository;
use UserTagBundle\Repository\TagRepository;

#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '编辑单个智能标签')]
#[MethodExpose(method: 'UpdateSingleSmartUserTag')]
#[Log]
class UpdateSingleSmartUserTag extends LockableProcedure
{
    #[MethodParam(description: 'id')]
    public string $id;

    /** @var array<string, mixed> */
    #[MethodParam(description: 'JSON表达式')]
    public array $jsonStatement;

    #[MethodParam(description: '频率/定时表达式')]
    public string $cronStatement;

    #[MethodParam(description: '用户标签名')]
    public string $name;

    #[MethodParam(description: '标签类型')]
    public string $type;

    #[MethodParam(description: '是否有效')]
    public bool $valid;

    #[MethodParam(description: '描述')]
    public ?string $description = null;

    #[MethodParam(description: '目录ID')]
    public ?string $catalogId = null;

    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CatalogService $catalogService,
        private readonly SmartRuleRepository $ruleRepository,
    ) {
    }

    public function execute(): array
    {
        $tag = $this->tagRepository->findOneBy(['id' => $this->id]);
        if (null === $tag) {
            throw new ApiException('找不到标签');
        }

        $rule = null;
        $this->entityManager->wrapInTransaction(function () use ($tag, &$rule): void {
            $tag->setName($this->name);
            $type = TagType::tryFrom($this->type);
            if (null !== $type) {
                $tag->setType($type);
            }
            $tag->setDescription($this->description);
            if (null !== $this->catalogId) {
                $catalog = $this->catalogService->find($this->catalogId);
                if (null === $catalog) {
                    throw new ApiException('找不到指定分类');
                }
                $tag->setCatalog($catalog);
            }
            $tag->setValid($this->valid);
            $this->entityManager->persist($tag);
            $this->entityManager->flush();

            $rule = $this->ruleRepository->findOneBy(['tag' => $tag]);
            if (null === $rule) {
                throw new ApiException('找不到关联的SmartRule');
            }
            $rule->setJsonStatement($this->jsonStatement);
            $rule->setCronStatement($this->cronStatement);
            $this->entityManager->persist($rule);
            $this->entityManager->flush();
        });

        if (null === $rule) {
            throw new \RuntimeException('Rule not found after transaction');
        }

        return [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'jsonStatement' => $rule->getJsonStatement(),
            'cronStatement' => $rule->getCronStatement(),
            '__message' => '编辑成功',
        ];
    }
}
