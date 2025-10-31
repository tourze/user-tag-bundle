<?php

namespace UserTagBundle\Tests\ExpressionLanguage\Function;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use UserTagBundle\ExpressionLanguage\Function\TagFunctionProvider;

/**
 * @internal
 */
#[CoversClass(TagFunctionProvider::class)]
#[RunTestsInSeparateProcesses]
final class TagFunctionProviderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    private function getProvider(): TagFunctionProvider
    {
        return self::getService(TagFunctionProvider::class);
    }

    public function testGetFunctions(): void
    {
        $provider = $this->getProvider();
        $functions = $provider->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertSame('checkCrmCustomerHasTag', $functions[0]->getName());
    }

    public function testCheckCrmCustomerHasTagWithNullUser(): void
    {
        $provider = $this->getProvider();
        $result = $provider->checkCrmCustomerHasTag([], null, 'test');

        $this->assertFalse($result);
    }

    public function testCheckCrmCustomerHasTagWithNonExistentTag(): void
    {
        $provider = $this->getProvider();
        $user = $this->createNormalUser('test@example.com', 'password123');

        $result = $provider->checkCrmCustomerHasTag([], $user, 'nonexistent-tag');

        $this->assertFalse($result);
    }

    public function testCheckCrmCustomerHasTagWithCategoryParameter(): void
    {
        $provider = $this->getProvider();
        $user = $this->createNormalUser('test@example.com', 'password123');

        $result = $provider->checkCrmCustomerHasTag([], $user, 'test-tag', 'test-category');

        $this->assertFalse($result);
    }
}
