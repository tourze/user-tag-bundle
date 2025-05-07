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
use UserTagBundle\Entity\Category;
use UserTagBundle\Repository\CategoryRepository;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodTag('用户标签')]
#[MethodDoc('创建单个标签分类')]
#[MethodExpose('CreateSingleUserTagCategory')]
#[Log]
class CreateSingleUserTagCategory extends LockableProcedure
{
    #[MethodParam('用户标签名')]
    public string $name;

    #[MethodParam('是否互斥分组')]
    public bool $mutex = false;

    #[MethodParam('上级分类ID')]
    public ?string $parentId = null;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $category = new Category();
        $category->setName($this->name);
        $category->setMutex($this->mutex);
        if ($this->parentId) {
            $parent = $this->categoryRepository->find($this->parentId);
            if (!$parent) {
                throw new ApiException('找不到上级分类');
            }
            $category->setParent($parent);
        }
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return [
            '__message' => '创建成功',
        ];
    }
}
