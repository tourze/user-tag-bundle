<?php

namespace UserTagBundle\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\CategoryRepository;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodTag('用户标签')]
#[MethodDoc('创建单个标签')]
#[MethodExpose('CreateSingleUserTag')]
#[Log]
class CreateSingleUserTag extends LockableProcedure
{
    #[MethodParam('用户标签名')]
    public string $name;

    #[MethodParam('标签类型')]
    public string $type;

    #[MethodParam('是否有效')]
    public bool $valid;

    #[MethodParam('描述')]
    public ?string $description = null;

    #[MethodParam('目录ID')]
    public ?string $categoryId = null;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $this->createTag(
            $this->name,
            TagType::tryFrom($this->type),
            $this->valid,
            $this->description,
            $this->categoryId,
        );

        return [
            '__message' => '创建成功',
        ];
    }

    protected function createTag(
        string $name,
        TagType $type,
        bool $valid,
        ?string $description = null,
        ?string $categoryId = null,
    ): Tag {
        $tag = new Tag();
        $tag->setName($name);
        $tag->setType($type);
        $tag->setDescription($description);
        if ($categoryId !== null) {
            $category = $this->categoryRepository->find($categoryId);
            if ($category === null) {
                throw new ApiException('找不到指定分类');
            }
            $tag->setCategory($category);
        }
        $tag->setValid($valid);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $tag;
    }
}
