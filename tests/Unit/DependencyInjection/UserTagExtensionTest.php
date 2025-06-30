<?php

namespace UserTagBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use UserTagBundle\AdminMenu;
use UserTagBundle\DependencyInjection\UserTagExtension;

class UserTagExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new UserTagExtension();
        
        $extension->load([], $container);
        
        // 验证 Extension 被正确加载
        self::assertTrue($container->hasDefinition('UserTagBundle\AdminMenu') || 
                       $container->hasDefinition(AdminMenu::class));
    }
}