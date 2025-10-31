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
use UserTagBundle\Repository\TagRepository;

#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '更新单个标签')]
#[MethodExpose(method: 'UpdateSingleUserTag')]
#[Log]
class UpdateSingleUserTag extends LockableProcedure
{
    #[MethodParam(description: '标签ID')]
    public string $id;

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
        private readonly CatalogService $catalogService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $tag = $this->tagRepository->find($this->id);
        if (null === $tag) {
            throw new ApiException('找不到标签');
        }
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

        return [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'type' => $tag->getType()->value,
            'valid' => $tag->isValid(),
            'description' => $tag->getDescription(),
            '__message' => '更新成功',
        ];
    }
}
