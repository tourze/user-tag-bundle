<?php

namespace UserTagBundle\Procedure\Category;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserTagBundle\Repository\CategoryRepository;

#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '更新单个标签分类')]
#[MethodExpose(method: 'UpdateSingleUserTagCategory')]
#[Log]
class UpdateSingleUserTagCategory extends LockableProcedure
{
    #[MethodParam(description: '分类ID')]
    public string $id;

    #[MethodParam(description: '用户标签名')]
    public string $name;

    #[MethodParam(description: '是否互斥分组')]
    public bool $mutex = false;

    #[MethodParam(description: '上级分类ID')]
    public ?string $parentId = null;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $category = $this->categoryRepository->find($this->id);
        if ($category === null) {
            throw new ApiException('找不到分类');
        }
        $category->setName($this->name);
        $category->setMutex($this->mutex);

        if ($this->parentId !== null) {
            $parent = $this->categoryRepository->find($this->parentId);
            if ($parent === null) {
                throw new ApiException('找不到上级分类');
            }
            $category->setParent($parent);
        } else {
            $category->setParent(null);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return [
            '__message' => '更新成功',
        ];
    }
}
