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
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\CategoryRepository;
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
    public ?string $categoryId = null;

    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $tag = $this->tagRepository->find($this->id);
        if ($tag === null) {
            throw new ApiException('找不到标签');
        }
        $tag->setName($this->name);
        $tag->setType(TagType::tryFrom($this->type));
        $tag->setDescription($this->description);
        if ($this->categoryId !== null) {
            $category = $this->categoryRepository->find($this->categoryId);
            if ($category === null) {
                throw new ApiException('找不到指定分类');
            }
            $tag->setCategory($category);
        }
        $tag->setValid($this->valid);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return [
            '__message' => '更新成功',
        ];
    }
}
