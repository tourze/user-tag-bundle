<?php

namespace UserTagBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\SmartRule;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Entity\Tag;

#[Autoconfigure(public: true)]
#[AutoconfigureTag(name: 'easy-admin-menu.provider')]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('标签中心')) {
            $item->addChild('标签中心');
        }

        $tagCenter = $item->getChild('标签中心');

        if (null !== $tagCenter) {
            $tagCenter->addChild('标签分类')->setUri($this->linkGenerator->getCurdListPage(Catalog::class));
            $tagCenter->addChild('用户标签')->setUri($this->linkGenerator->getCurdListPage(Tag::class));
            $tagCenter->addChild('打标记录')->setUri($this->linkGenerator->getCurdListPage(AssignLog::class));
            $tagCenter->addChild('智能规则')->setUri($this->linkGenerator->getCurdListPage(SmartRule::class));
            $tagCenter->addChild('SQL规则')->setUri($this->linkGenerator->getCurdListPage(SqlRule::class));
        }
    }
}
