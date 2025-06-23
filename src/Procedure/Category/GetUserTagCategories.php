<?php

namespace UserTagBundle\Procedure\Category;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use UserTagBundle\Entity\Category;
use UserTagBundle\Repository\CategoryRepository;

#[MethodTag('用户标签')]
#[MethodDoc('获取标签分类')]
#[MethodExpose('GetUserTagCategories')]
class GetUserTagCategories extends BaseProcedure
{
    #[MethodParam('上级ID')]
    public ?string $parentId = null;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->from(Category::class, 'a')
            ->select('a');

        if ($this->parentId !== null) {
            $parent = $this->categoryRepository->find($this->parentId);
            if ($parent === null) {
                throw new ApiException('找不到上级分类');
            }
            $qb->where('a.parent=:parent')->setParameter('parent', $parent);
        } else {
            $qb->where('a.parent IS NULL');
        }

        $qb->addOrderBy('a.sortNumber', Criteria::DESC)
            ->addOrderBy('a.id', Criteria::DESC);
        $result = [];
        foreach ($qb->getQuery()->toIterable() as $item) {
            /* @var Category $item */
            $result[] = $item->retrieveAdminArray();
        }

        return $result;
    }
}
