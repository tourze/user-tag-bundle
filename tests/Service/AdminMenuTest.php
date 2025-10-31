<?php

namespace UserTagBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use UserTagBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses] final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private LinkGeneratorInterface $linkGenerator;

    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $this->linkGenerator = new class implements LinkGeneratorInterface {
            /**
             * @param array<string, mixed> $parameters
             */
            public function getCurdListPage(string $controller, array $parameters = []): string
            {
                return '/admin/test-url';
            }

            /**
             * @param array<string, mixed> $parameters
             */
            public function getCurdDetailPage(string $controller, int|string $entityId, array $parameters = []): string
            {
                return '/admin/test-url/detail';
            }

            /**
             * @param array<string, mixed> $parameters
             */
            public function getCurdEditPage(string $controller, int|string $entityId, array $parameters = []): string
            {
                return '/admin/test-url/edit';
            }

            /**
             * @param array<string, mixed> $parameters
             */
            public function getCurdNewPage(string $controller, array $parameters = []): string
            {
                return '/admin/test-url/new';
            }

            public function extractEntityFqcn(string $controller): string
            {
                return 'TestEntity';
            }

            public function setDashboard(string $dashboardControllerFqcn): void
            {
                // 测试环境中无需实际设置 Dashboard
            }
        };
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testInvoke(): void
    {
        // 使用PHPUnit的mock系统来创建ItemInterface的mock对象
        $mockMenuItem = $this->createMock(ItemInterface::class);
        $mockChildItem = $this->createMock(ItemInterface::class);

        // 配置mock行为 - 第一次调用getChild返回null，第二次返回mockChildItem
        $mockMenuItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('标签中心')
            ->willReturnOnConsecutiveCalls(null, $mockChildItem)
        ;

        $mockMenuItem->expects($this->once())
            ->method('addChild')
            ->with('标签中心')
            ->willReturn($mockChildItem)
        ;

        // 配置子项目的mock行为 - 需要5个addChild调用（标签分类、用户标签、打标记录、智能规则、SQL规则）
        $mockChildItem->expects($this->exactly(5))
            ->method('addChild')
            ->willReturn($mockChildItem)
        ;

        // 配置setUri方法调用
        $mockChildItem->expects($this->exactly(5))
            ->method('setUri')
            ->willReturn($mockChildItem)
        ;

        // 测试 AdminMenu 是可调用的
        $this->assertIsCallable($this->adminMenu);

        // 调用 AdminMenu - 这个测试主要验证没有运行时错误
        // Mock 的 expects() 会自动验证调用次数，如果有异常测试会失败
        ($this->adminMenu)($mockMenuItem);
    }
}
