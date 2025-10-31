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
final class SqlRuleSetterMethodTest extends AbstractEntityTestCase
{
    protected function createEntity(): SqlRule
    {
        return new SqlRule();
    }

    /**
     * @return iterable<int, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield ['cronStatement', '* * * * *'];
        yield ['sqlStatement', 'SELECT * FROM users'];
    }

    public function testSqlRuleSetters(): void
    {
        $sqlRule = new SqlRule();
        $tag = new Tag();

        // 测试所有setter方法不返回值（void）
        $sqlRule->setTag($tag);
        $sqlRule->setSqlStatement('SELECT * FROM users');
        $sqlRule->setCronStatement('* * * * *');

        // 验证值是否正确设置
        $this->assertSame($tag, $sqlRule->getTag());
        $this->assertSame('SELECT * FROM users', $sqlRule->getSqlStatement());
        $this->assertSame('* * * * *', $sqlRule->getCronStatement());
    }
}
