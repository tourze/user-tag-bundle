<?php

namespace UserTagBundle\Procedure\Tag;

use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CatalogBundle\Service\CatalogService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\SmartRuleRepository;
use UserTagBundle\Repository\SqlRuleRepository;
use UserTagBundle\Repository\TagRepository;

#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '根据条件，获取标签列表')]
#[MethodExpose(method: 'GetUserTagList')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
class GetUserTagList extends BaseProcedure
{
    use PaginatorTrait;

    /** @var array<int, string> */
    #[MethodParam(description: '分类ID列表')]
    public array $categories = [];

    /** @var array<int, string> */
    #[MethodParam(description: '类型列表')]
    public array $types = [];

    #[MethodParam(description: '名称')]
    public ?string $name = null;

    #[MethodParam(description: '是否有效')]
    public ?bool $valid = null;

    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly SqlRuleRepository $sqlRuleRepository,
        private readonly SmartRuleRepository $smartRuleRepository,
        private readonly TagRepository $tagRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $qb = $this->buildBaseQuery();
        $this->applyFilters($qb);

        $result = [];
        foreach ($qb->getQuery()->toIterable() as $tag) {
            /** @var Tag $tag */
            $result[] = $this->formatTagWithRules($tag);
        }

        return ['data' => $result];
    }

    private function buildBaseQuery(): QueryBuilder
    {
        return $this->tagRepository
            ->createQueryBuilder('a')
            ->select('a')
            ->addOrderBy('a.id', Order::Descending->value)
        ;
    }

    private function applyFilters(QueryBuilder $qb): void
    {
        $this->applyCategoriesFilter($qb);
        $this->applyNameFilter($qb);
        $this->applyTypesFilter($qb);
        $this->applyValidFilter($qb);
    }

    private function applyCategoriesFilter(QueryBuilder $qb): void
    {
        if ([] === $this->categories) {
            return;
        }

        $catalogs = $this->catalogService->findByIds($this->categories);
        if ([] === $catalogs) {
            $qb->andWhere('0=1');
        } else {
            $qb->andWhere('a.catalog IN (:catalogs)')->setParameter('catalogs', $catalogs);
        }
    }

    private function applyNameFilter(QueryBuilder $qb): void
    {
        if (null !== $this->name) {
            $qb->andWhere('a.name LIKE :name')->setParameter('name', '%' . $this->name . '%');
        }
    }

    private function applyTypesFilter(QueryBuilder $qb): void
    {
        if ([] === $this->types) {
            return;
        }

        $types = [];
        foreach ($this->types as $type) {
            $types[] = TagType::from($type);
        }
        $qb->andWhere('a.type IN (:types)')->setParameter('types', $types);
    }

    private function applyValidFilter(QueryBuilder $qb): void
    {
        if (null !== $this->valid) {
            $qb->andWhere('a.valid = :valid')->setParameter('valid', $this->valid);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatTagWithRules(Tag $tag): array
    {
        $result = $tag->retrievePlainArray();
        $result['sqlRule'] = null;
        $result['smartRule'] = null;

        if (TagType::SqlTag === $tag->getType()) {
            $result['sqlRule'] = $this->getSqlRule($tag);
        }

        if (TagType::SmartTag === $tag->getType()) {
            $result['smartRule'] = $this->getSmartRule($tag);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getSqlRule(Tag $tag): ?array
    {
        $rule = $this->sqlRuleRepository->findOneBy(['tag' => $tag]);

        return $rule?->toArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getSmartRule(Tag $tag): ?array
    {
        $rule = $this->smartRuleRepository->findOneBy(['tag' => $tag]);

        return $rule?->toArray();
    }
}
