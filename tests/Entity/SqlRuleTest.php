<?php

namespace UserTagBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(SqlRule::class)]
final class SqlRuleTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new SqlRule();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'cronStatement' => ['cronStatement', 'test_value'],
            'sqlStatement' => ['sqlStatement', 'test_value'],
        ];
    }

    public function testGetterSetters(): void
    {
        $sqlRule = new SqlRule();

        // Test sqlStatement
        $sqlRule->setSqlStatement('SELECT * FROM users');
        $this->assertSame('SELECT * FROM users', $sqlRule->getSqlStatement());

        // Test cronStatement
        $sqlRule->setCronStatement('0 0 * * *');
        $this->assertSame('0 0 * * *', $sqlRule->getCronStatement());

        // Test tag
        $tag = new Tag();
        $sqlRule->setTag($tag);
        $this->assertSame($tag, $sqlRule->getTag());
    }
}
