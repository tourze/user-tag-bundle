<?php

namespace UserTagBundle;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use UserTagBundle\Entity\Category;
use UserTagBundle\Entity\Tag;

class AdminMenu implements MenuProviderInterface
{
    public function __construct(private readonly LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (!$item->getChild('标签中心')) {
            $item->addChild('标签中心');
        }
        $item->getChild('标签中心')->addChild('标签目录')->setUri($this->linkGenerator->getCurdListPage(Category::class));
        $item->getChild('标签中心')->addChild('标签列表')->setUri($this->linkGenerator->getCurdListPage(Tag::class));
    }
}
