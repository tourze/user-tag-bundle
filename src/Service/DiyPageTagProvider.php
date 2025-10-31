<?php

namespace UserTagBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\EnumExtra\SelectDataFetcher;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Repository\TagRepository;

#[Autoconfigure(public: true)]
#[AutoconfigureTag(name: 'diy-page.tag.provider')]
readonly class DiyPageTagProvider implements SelectDataFetcher
{
    public function __construct(
        private TagRepository $tagRepository,
    ) {
    }

    public function genSelectData(): iterable
    {
        $tags = $this->tagRepository
            ->createQueryBuilder('a')
            ->select('a')
            ->getQuery()
            ->toIterable()
        ;
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
