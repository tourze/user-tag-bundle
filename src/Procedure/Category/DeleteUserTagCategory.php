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

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodTag('用户标签')]
#[MethodDoc('删除标签分类')]
#[MethodExpose('DeleteUserTagCategory')]
#[Log]
class DeleteUserTagCategory extends LockableProcedure
{
    #[MethodParam('分类ID')]
    public string $id;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $category = $this->categoryRepository->find($this->id);
        if (!$category) {
            throw new ApiException('找不到分类');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return [
            '__message' => '删除成功',
        ];
    }
}
