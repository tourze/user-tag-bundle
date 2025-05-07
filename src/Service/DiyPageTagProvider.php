<?php

namespace UserTagBundle\Service;

use AntdCpBundle\Service\SelectDataFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\EnumExtra\SelectDataFetcher;
use UserTagBundle\Entity\Tag;

#[AutoconfigureTag('diy-page.tag.provider')]
class DiyPageTagProvider implements SelectDataFetcher
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SelectDataFormatter $selectDataFormatter,
    ) {
    }

    public function genSelectData(): array
    {
        $tags = $this->entityManager
            ->createQueryBuilder()
            ->from(Tag::class, 'a')
            ->select('a')
            ->getQuery()
            ->toIterable();

        // 注意下面我们不使用标签id作为值，直接使用标签名作为返回值
        return $this->selectDataFormatter->buildOptionsFromEntities($tags, 'name', Tag::class);
    }
}
