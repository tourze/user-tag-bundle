<?php

namespace UserTagBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\EnumExtra\SelectDataFetcher;
use UserTagBundle\Entity\Tag;

#[AutoconfigureTag('diy-page.tag.provider')]
class DiyPageTagProvider implements SelectDataFetcher
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function genSelectData(): iterable
    {
        $tags = $this->entityManager
            ->createQueryBuilder()
            ->from(Tag::class, 'a')
            ->select('a')
            ->getQuery()
            ->toIterable();
        foreach ($tags as $tag) {
            /** @var Tag $tag */
            // 注意下面我们不使用标签id作为值，直接使用标签名作为返回值
            yield [
                'label' => $tag->getName(),
                'text' => $tag->getName(),
                'value' => $tag->getName(),
                'name' => $tag->getName(),
            ];
        }
    }
}
