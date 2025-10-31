<?php

namespace UserTagBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;

/**
 * @internal
 */
#[CoversClass(Tag::class)]
final class TagSetterMethodTest extends AbstractEntityTestCase
{
    protected function createEntity(): Tag
    {
        return new Tag();
    }

    /**
     * @return iterable<int, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield ['name', 'Test Tag'];
        yield ['description', 'Test Description'];
        yield ['valid', true];
        yield ['type', TagType::StaticTag];
    }

    public function testTagSetters(): void
    {
        $tag = new Tag();
        $catalog = new Catalog();

        // 测试所有setter方法不返回值（void）
        $tag->setName('Test Tag');
        $tag->setType(TagType::StaticTag);
        $tag->setCatalog($catalog);
        $tag->setDescription('Test Description');
        $tag->setValid(true);

        // 验证值是否正确设置
        $this->assertSame('Test Tag', $tag->getName());
        $this->assertSame(TagType::StaticTag, $tag->getType());
        $this->assertSame($catalog, $tag->getCatalog());
        $this->assertSame('Test Description', $tag->getDescription());
        $this->assertTrue($tag->isValid());
    }
}
