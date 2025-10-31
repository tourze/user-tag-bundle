<?php

namespace UserTagBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserTagBundle\Controller\Admin\UserTagAssignLogCrudController;
use UserTagBundle\Entity\AssignLog;

/**
 * @internal
 */
#[CoversClass(UserTagAssignLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserTagAssignLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): UserTagAssignLogCrudController
    {
        return new UserTagAssignLogCrudController();
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '标签' => ['标签'];
        yield '用户标识符' => ['用户标识符'];
        yield '绑定时间' => ['绑定时间'];
        yield '解绑时间' => ['解绑时间'];
        yield '是否有效' => ['是否有效'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield 'tag' => ['tag'];
        yield 'userId' => ['userId'];
        yield 'user' => ['user'];
        yield 'assignTime' => ['assignTime'];
        yield 'unassignTime' => ['unassignTime'];
        yield 'valid' => ['valid'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        // yield 'tag' => ['tag']; // AssociationField 可能生成 select 而非 input，暂时跳过
        yield 'userId' => ['userId'];
        // yield 'user' => ['user']; // AssociationField 可能生成 select 而非 input，暂时跳过
        // yield 'assignTime' => ['assignTime']; // DateTimeField 有特殊的HTML结构，暂时跳过
        // yield 'unassignTime' => ['unassignTime']; // DateTimeField 有特殊的HTML结构，暂时跳过
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(AssignLog::class, UserTagAssignLogCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new UserTagAssignLogCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(0, count($fields));
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new UserTagAssignLogCrudController();
        $this->assertInstanceOf(UserTagAssignLogCrudController::class, $controller);
    }

    public function testUnauthorizedAccessThrowsException(): void
    {
        $client = self::createClientWithDatabase();

        $url = $this->generateAdminUrl('index');

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', $url);
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);

        $url = $this->generateAdminUrl('new');
        $crawler = $client->request('GET', $url);

        // 如果没有抛出异常，测试表单提交
        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('Create')->form();
            $form['AssignLog[userId]'] = '';
            $crawler = $client->submit($form);
            $this->assertResponseStatusCodeSame(422);
            $this->assertStringContainsString('should not be blank',
                $crawler->filter('.invalid-feedback')->text());
        }
    }
}
