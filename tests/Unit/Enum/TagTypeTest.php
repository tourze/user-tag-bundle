<?php

namespace UserTagBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Enum\TagType;

class TagTypeTest extends TestCase
{
    public function testGetLabel(): void
    {
        self::assertSame('未知', TagType::Empty->getLabel());
        self::assertSame('静态标签', TagType::StaticTag->getLabel());
        self::assertSame('智能标签', TagType::SmartTag->getLabel());
        self::assertSame('SQL标签', TagType::SqlTag->getLabel());
    }
    
    public function testEnumValues(): void
    {
        self::assertSame('', TagType::Empty->value);
        self::assertSame('static', TagType::StaticTag->value);
        self::assertSame('smart', TagType::SmartTag->value);
        self::assertSame('sql', TagType::SqlTag->value);
    }
}