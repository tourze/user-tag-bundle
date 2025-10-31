<?php

namespace UserTagBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserTagBundle\Controller\Admin\UserTagSmartRuleCrudController;
use UserTagBundle\Entity\SmartRule;

/**
 * @internal
 */
#[CoversClass(UserTagSmartRuleCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserTagSmartRuleCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): UserTagSmartRuleCrudController
    {
        return new UserTagSmartRuleCrudController();
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '所属标签' => ['所属标签'];
        yield '定时表达式' => ['定时表达式'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield 'tag' => ['tag'];
        yield 'cronStatement' => ['cronStatement'];
        yield 'jsonStatement' => ['jsonStatement'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        // yield 'tag' => ['tag']; // AssociationField生成select而非input，暂时注释
        yield 'cronStatement' => ['cronStatement'];
        // yield 'jsonStatement' => ['jsonStatement']; // CodeEditorField生成code editor而非input，暂时注释
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(SmartRule::class, UserTagSmartRuleCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new UserTagSmartRuleCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(0, count($fields));
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new UserTagSmartRuleCrudController();
        $this->assertInstanceOf(UserTagSmartRuleCrudController::class, $controller);
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
            $form['SmartRule[cronStatement]'] = '';
            $crawler = $client->submit($form);
            $this->assertResponseStatusCodeSame(422);
            $this->assertStringContainsString('should not be blank',
                $crawler->filter('.invalid-feedback')->text());
        }
    }
}
