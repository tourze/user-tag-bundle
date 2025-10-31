<?php

namespace UserTagBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use UserTagBundle\Enum\TagType;

/**
 * @internal
 */
#[CoversClass(TagType::class)]
final class TagTypeTest extends AbstractEnumTestCase
{
    public function testGetLabel(): void
    {
        $this->assertSame('未知', TagType::Empty->getLabel());
        $this->assertSame('静态标签', TagType::StaticTag->getLabel());
        $this->assertSame('智能标签', TagType::SmartTag->getLabel());
        $this->assertSame('SQL标签', TagType::SqlTag->getLabel());
    }

    public function testEnumValues(): void
    {
        $this->assertSame('', TagType::Empty->value);
        $this->assertSame('static', TagType::StaticTag->value);
        $this->assertSame('smart', TagType::SmartTag->value);
        $this->assertSame('sql', TagType::SqlTag->value);
    }

    public function testToArray(): void
    {
        $this->assertEquals([
            'value' => '',
            'label' => '未知',
        ], TagType::Empty->toArray());

        $this->assertEquals([
            'value' => 'static',
            'label' => '静态标签',
        ], TagType::StaticTag->toArray());

        $this->assertEquals([
            'value' => 'smart',
            'label' => '智能标签',
        ], TagType::SmartTag->toArray());

        $this->assertEquals([
            'value' => 'sql',
            'label' => 'SQL标签',
        ], TagType::SqlTag->toArray());
    }

    public function testValueAndLabelCombined(): void
    {
        $cases = [
            [TagType::Empty, '', '未知'],
            [TagType::StaticTag, 'static', '静态标签'],
            [TagType::SmartTag, 'smart', '智能标签'],
            [TagType::SqlTag, 'sql', 'SQL标签'],
        ];

        foreach ($cases as [$case, $expectedValue, $expectedLabel]) {
            $this->assertSame($expectedValue, $case->value);
            $this->assertSame($expectedLabel, $case->getLabel());
        }
    }

    public function testTryFromWithValidValue(): void
    {
        $this->assertSame(TagType::Empty, TagType::tryFrom(''));
        $this->assertSame(TagType::StaticTag, TagType::tryFrom('static'));
        $this->assertSame(TagType::SmartTag, TagType::tryFrom('smart'));
        $this->assertSame(TagType::SqlTag, TagType::tryFrom('sql'));
    }

    public function testValueUniqueness(): void
    {
        $values = [];
        foreach (TagType::cases() as $case) {
            $this->assertNotContains($case->value, $values, 'Duplicate value found: ' . $case->value);
            $values[] = $case->value;
        }
    }

    public function testLabelUniqueness(): void
    {
        $labels = [];
        foreach (TagType::cases() as $case) {
            $label = $case->getLabel();
            $this->assertNotContains($label, $labels, 'Duplicate label found: ' . $label);
            $labels[] = $label;
        }
    }
}
