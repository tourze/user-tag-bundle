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
final class SmartRuleSetterMethodTest extends AbstractEntityTestCase
{
    protected function createEntity(): SmartRule
    {
        return new SmartRule();
    }

    /**
     * @return iterable<int, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield ['cronStatement', '* * * * *'];
        yield ['jsonStatement', ['key' => 'value']];
    }

    public function testSmartRuleSetters(): void
    {
        $smartRule = new SmartRule();
        $tag = new Tag();

        // 测试所有setter方法不返回值（void）
        $smartRule->setTag($tag);
        $smartRule->setJsonStatement(['test' => 'data']);
        $smartRule->setCronStatement('* * * * *');

        // 验证值是否正确设置
        $this->assertSame($tag, $smartRule->getTag());
        $this->assertSame(['test' => 'data'], $smartRule->getJsonStatement());
        $this->assertSame('* * * * *', $smartRule->getCronStatement());
    }
}
