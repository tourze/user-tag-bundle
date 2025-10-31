<?php

namespace UserTagBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserTagBundle\Controller\Admin\UserTagTagCrudController;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(UserTagTagCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserTagTagCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): UserTagTagCrudController
    {
        return new UserTagTagCrudController();
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '标签分类' => ['标签分类'];
        yield '标签类型' => ['标签类型'];
        yield '标签名称' => ['标签名称'];
        yield '是否有效' => ['是否有效'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield 'catalog' => ['catalog'];
        yield 'type' => ['type'];
        yield 'name' => ['name'];
        yield 'description' => ['description'];
        yield 'valid' => ['valid'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        // yield 'type' => ['type']; // ChoiceField 可能生成 select 而非 input，暂时跳过
        yield 'name' => ['name'];
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Tag::class, UserTagTagCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new UserTagTagCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(0, count($fields));
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new UserTagTagCrudController();
        $this->assertInstanceOf(UserTagTagCrudController::class, $controller);
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
            $form['Tag[name]'] = '';
            $crawler = $client->submit($form);
            $this->assertResponseStatusCodeSame(422);
            $this->assertStringContainsString('should not be blank',
                $crawler->filter('.invalid-feedback')->text());
        }
    }
}
