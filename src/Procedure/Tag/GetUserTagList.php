<?php

namespace UserTagBundle\Procedure\Tag;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\CategoryRepository;
use UserTagBundle\Repository\SmartRuleRepository;
use UserTagBundle\Repository\SqlRuleRepository;

#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '根据条件，获取标签列表')]
#[MethodExpose(method: 'GetUserTagList')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
class GetUserTagList extends BaseProcedure
{
    use PaginatorTrait;

    #[MethodParam(description: '分类ID列表')]
    public array $categories = [];

    #[MethodParam(description: '类型列表')]
    public array $types = [];

    #[MethodParam(description: '名称')]
    public ?string $name = null;

    #[MethodParam(description: '是否有效')]
    public ?bool $valid = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
        private readonly SqlRuleRepository $sqlRuleRepository,
        private readonly SmartRuleRepository $smartRuleRepository,
    ) {
    }

    public function execute(): array
    {
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->from(Tag::class, 'a')
            ->select('a');

        // 所属目录
        if (!empty($this->categories)) {
            $categories = $this->categoryRepository->findBy(['id' => $this->categories]);
            if ((bool) empty($categories)) {
                $qb->andWhere('0=1');
            } else {
                $qb->andWhere('a.category IN (:categories)')->setParameter('categories', $categories);
            }
        }

        if (null !== $this->name) {
            $qb->andWhere('a.name LIKE :name')->setParameter('name', '%' . $this->name . '%');
        }

        if (!empty($this->types)) {
            $types = [];
            foreach ($this->types as $type) {
                $types[] = TagType::from($type);
            }
            $qb->andWhere('a.type IN (:types)')->setParameter('types', $types);
        }

        if (null !== $this->valid) {
            $qb->andWhere('a.valid = :valid')->setParameter('valid', $this->valid);
        }

        $qb->addOrderBy('a.id', Criteria::DESC);
        $result = [];
        foreach ($qb->getQuery()->toIterable() as $tag) {
            /** @var Tag $tag */
            $tmp = $tag->retrievePlainArray();
            $tmp['sqlRule'] = null;
            $tmp['smartRule'] = null;

            if (TagType::SqlTag === $tag->getType()) {
                $rule = $this->sqlRuleRepository->findOneBy(['tag' => $tag]);
                if ((bool) $rule) {
                    $tmp['sqlRule'] = $rule->toArray();
                }
            }

            if (TagType::SmartTag === $tag->getType()) {
                $rule = $this->smartRuleRepository->findOneBy(['tag' => $tag]);
                if ((bool) $rule) {
                    $tmp['smartRule'] = $rule->toArray();
                }
            }

            $result[] = $tmp;
        }

        return $result;
    }
}
