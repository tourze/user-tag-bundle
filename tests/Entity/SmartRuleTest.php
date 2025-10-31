<?php

namespace UserTagBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserTagBundle\Entity\SmartRule;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(SmartRule::class)]
final class SmartRuleTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new SmartRule();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'cronStatement' => ['cronStatement', 'test_value'],
            'jsonStatement' => ['jsonStatement', ['key' => 'value']],
        ];
    }

    public function testGetterSetters(): void
    {
        $smartRule = new SmartRule();

        // Test jsonStatement
        $jsonStatement = ['field' => 'value'];
        $smartRule->setJsonStatement($jsonStatement);
        $this->assertSame($jsonStatement, $smartRule->getJsonStatement());

        // Test cronStatement
        $smartRule->setCronStatement('0 0 * * *');
        $this->assertSame('0 0 * * *', $smartRule->getCronStatement());

        // Test tag
        $tag = new Tag();
        $smartRule->setTag($tag);
        $this->assertSame($tag, $smartRule->getTag());
    }
}
