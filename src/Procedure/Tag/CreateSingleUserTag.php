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
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;

#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodTag(name: '用户标签')]
abstract class CreateSingleUserTag extends LockableProcedure
{
    #[MethodParam(description: '用户标签名')]
    public string $name;

    #[MethodParam(description: '是否有效')]
    public bool $valid;

    #[MethodParam(description: '描述')]
    public ?string $description = null;

    #[MethodParam(description: '目录ID')]
    public ?string $catalogId = null;

    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    abstract public function execute(): array;

    protected function createTag(
        string $name,
        TagType $type,
        bool $valid,
        ?string $description = null,
        ?string $catalogId = null,
    ): Tag {
        $tag = new Tag();
        $tag->setName($name);
        $tag->setType($type);
        $tag->setDescription($description);
        if (null !== $catalogId) {
            $catalog = $this->catalogService->find($catalogId);
            if (null === $catalog) {
                throw new ApiException('找不到指定分类');
            }
            $tag->setCatalog($catalog);
        }
        $tag->setValid($valid);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $tag;
    }
}
