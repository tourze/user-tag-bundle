<?php

namespace UserTagBundle\Tests;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use UserTagBundle\AdminMenu;
use UserTagBundle\Entity\Category;
use UserTagBundle\Entity\Tag;

class AdminMenuTest extends TestCase
{
    private MockObject $linkGenerator;
    private AdminMenu $adminMenu;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function testInvoke_withoutExistingMenuItem(): void
    {
        // 创建菜单项Mock
        $menuItem = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
            
        $tagCenterMenuItem = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
            
        $tagDirMenuItem = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
            
        $tagListMenuItem = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
        
        // 配置菜单项行为
        // getChild方法会被AdminMenu::__invoke调用3次
        $menuItem->expects($this->exactly(3))
            ->method('getChild')
            ->with('标签中心')
            ->willReturn(null, $tagCenterMenuItem, $tagCenterMenuItem);
        
        $menuItem->expects($this->once())
            ->method('addChild')
            ->with('标签中心')
            ->willReturn($tagCenterMenuItem);
        
        // 配置标签中心子菜单
        $tagCenterMenuItem->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function($childName) use ($tagDirMenuItem, $tagListMenuItem) {
                if ($childName === '标签目录') {
                    return $tagDirMenuItem;
                } elseif ($childName === '标签列表') {
                    return $tagListMenuItem;
                }
                return null;
            });
        
        // 配置链接生成器
        $categoryListUrl = '/admin?entity=Category&action=list';
        $tagListUrl = '/admin?entity=Tag&action=list';
        
        $this->linkGenerator->expects($this->exactly(2))
            ->method('getCurdListPage')
            ->willReturnCallback(function($entityClass) use ($categoryListUrl, $tagListUrl) {
                if ($entityClass === Category::class) {
                    return $categoryListUrl;
                } elseif ($entityClass === Tag::class) {
                    return $tagListUrl;
                }
                return null;
            });
        
        // 配置URI设置
        $tagDirMenuItem->expects($this->once())
            ->method('setUri')
            ->with($categoryListUrl)
            ->willReturnSelf();
            
        $tagListMenuItem->expects($this->once())
            ->method('setUri')
            ->with($tagListUrl)
            ->willReturnSelf();
        
        // 执行测试
        ($this->adminMenu)($menuItem);
    }

    public function testInvoke_withExistingMenuItem(): void
    {
        // 创建菜单项Mock
        $menuItem = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
            
        $tagCenterMenuItem = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
            
        $tagDirMenuItem = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
            
        $tagListMenuItem = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
        
        // 配置菜单项行为 - 已存在的菜单项
        // getChild方法会被AdminMenu::__invoke调用3次
        $menuItem->expects($this->exactly(3))
            ->method('getChild')
            ->with('标签中心')
            ->willReturn($tagCenterMenuItem);
        
        $menuItem->expects($this->never())
            ->method('addChild');
        
        // 配置标签中心子菜单
        $tagCenterMenuItem->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function($childName) use ($tagDirMenuItem, $tagListMenuItem) {
                if ($childName === '标签目录') {
                    return $tagDirMenuItem;
                } elseif ($childName === '标签列表') {
                    return $tagListMenuItem;
                }
                return null;
            });
        
        // 配置链接生成器
        $categoryListUrl = '/admin?entity=Category&action=list';
        $tagListUrl = '/admin?entity=Tag&action=list';
        
        $this->linkGenerator->expects($this->exactly(2))
            ->method('getCurdListPage')
            ->willReturnCallback(function($entityClass) use ($categoryListUrl, $tagListUrl) {
                if ($entityClass === Category::class) {
                    return $categoryListUrl;
                } elseif ($entityClass === Tag::class) {
                    return $tagListUrl;
                }
                return null;
            });
        
        // 配置URI设置
        $tagDirMenuItem->expects($this->once())
            ->method('setUri')
            ->with($categoryListUrl)
            ->willReturnSelf();
            
        $tagListMenuItem->expects($this->once())
            ->method('setUri')
            ->with($tagListUrl)
            ->willReturnSelf();
        
        // 执行测试
        ($this->adminMenu)($menuItem);
    }
} 