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
final class TagTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Tag();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'name' => ['name', 'test_tag'],
            'description' => ['description', 'test_description'],
            'valid' => ['valid', true],
            'type' => ['type', TagType::StaticTag],
            'catalog' => ['catalog', null],
            'createdBy' => ['createdBy', 'admin'],
            'updatedBy' => ['updatedBy', 'admin'],
            'createdFromIp' => ['createdFromIp', '127.0.0.1'],
            'updatedFromIp' => ['updatedFromIp', '127.0.0.1'],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tag = new Tag();
    }

    public function testIdDefaultValue(): void
    {
        $this->assertEquals(0, $this->tag->getId());
    }

    public function testValidDefaultValue(): void
    {
        $this->assertFalse($this->tag->isValid());
    }

    public function testSetAndGetValid(): void
    {
        $this->tag->setValid(true);
        $this->assertTrue($this->tag->isValid());

        $this->tag->setValid(false);
        $this->assertFalse($this->tag->isValid());

        $this->tag->setValid(null);
        $this->assertNull($this->tag->isValid());
    }

    public function testSetAndGetName(): void
    {
        $name = '测试标签';
        $this->tag->setName($name);
        $this->assertEquals($name, $this->tag->getName());
    }

    public function testSetAndGetType(): void
    {
        $this->assertEquals(TagType::StaticTag, $this->tag->getType());

        $this->tag->setType(TagType::SmartTag);
        $this->assertEquals(TagType::SmartTag, $this->tag->getType());
    }

    public function testSetAndGetCatalog(): void
    {
        $this->assertNull($this->tag->getCatalog());

        $catalog = new Catalog();
        $catalog->setName('测试分类');
        $catalog->setEnabled(true);

        $this->tag->setCatalog($catalog);
        $this->assertSame($catalog, $this->tag->getCatalog());

        $this->tag->setCatalog(null);
        $this->assertNull($this->tag->getCatalog());
    }

    public function testSetAndGetDescription(): void
    {
        $this->assertNull($this->tag->getDescription());

        $description = '这是一个测试标签的描述';
        $this->tag->setDescription($description);
        $this->assertEquals($description, $this->tag->getDescription());

        $this->tag->setDescription(null);
        $this->assertNull($this->tag->getDescription());
    }

    public function testSetAndGetCreatedBy(): void
    {
        $this->assertNull($this->tag->getCreatedBy());

        $createdBy = 'admin';
        $this->tag->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->tag->getCreatedBy());

        $this->tag->setCreatedBy(null);
        $this->assertNull($this->tag->getCreatedBy());
    }

    public function testSetAndGetUpdatedBy(): void
    {
        $this->assertNull($this->tag->getUpdatedBy());

        $updatedBy = 'admin';
        $this->tag->setUpdatedBy($updatedBy);
        $this->assertEquals($updatedBy, $this->tag->getUpdatedBy());

        $this->tag->setUpdatedBy(null);
        $this->assertNull($this->tag->getUpdatedBy());
    }

    public function testSetAndGetCreatedFromIp(): void
    {
        $this->assertNull($this->tag->getCreatedFromIp());

        $ip = '127.0.0.1';
        $this->tag->setCreatedFromIp($ip);
        $this->assertEquals($ip, $this->tag->getCreatedFromIp());

        $this->tag->setCreatedFromIp(null);
        $this->assertNull($this->tag->getCreatedFromIp());
    }

    public function testSetAndGetUpdatedFromIp(): void
    {
        $this->assertNull($this->tag->getUpdatedFromIp());

        $ip = '127.0.0.1';
        $this->tag->setUpdatedFromIp($ip);
        $this->assertEquals($ip, $this->tag->getUpdatedFromIp());

        $this->tag->setUpdatedFromIp(null);
        $this->assertNull($this->tag->getUpdatedFromIp());
    }

    public function testSetAndGetCreateTime(): void
    {
        $this->assertNull($this->tag->getCreateTime());

        $now = new \DateTimeImmutable();
        $this->tag->setCreateTime($now);
        $this->assertSame($now, $this->tag->getCreateTime());

        $this->tag->setCreateTime(null);
        $this->assertNull($this->tag->getCreateTime());
    }

    public function testSetAndGetUpdateTime(): void
    {
        $this->assertNull($this->tag->getUpdateTime());

        $now = new \DateTimeImmutable();
        $this->tag->setUpdateTime($now);
        $this->assertSame($now, $this->tag->getUpdateTime());

        $this->tag->setUpdateTime(null);
        $this->assertNull($this->tag->getUpdateTime());
    }

    public function testToStringWithoutId(): void
    {
        // ID为默认值0时，应返回空字符串
        $this->assertEquals('', $this->tag->__toString());
    }

    public function testToStringWithIdAndCatalog(): void
    {
        // 模拟反射设置ID
        $reflectionClass = new \ReflectionClass(Tag::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->tag, 1);

        $catalog = new Catalog();
        $catalog->setName('测试分类');
        $catalog->setEnabled(true);
        $this->tag->setCatalog($catalog);
        $this->tag->setName('测试标签');

        $expected = $catalog . ':测试标签';
        $this->assertEquals($expected, $this->tag->__toString());
    }

    public function testRetrievePlainArray(): void
    {
        $this->tag->setName('测试标签');
        $this->tag->setType(TagType::StaticTag);
        $this->tag->setDescription('这是一个测试描述');
        $this->tag->setValid(true);

        $catalog = new Catalog();
        $catalog->setName('测试分类');
        $catalog->setEnabled(true);
        $this->tag->setCatalog($catalog);

        $now = new \DateTimeImmutable();
        $this->tag->setCreateTime($now);
        $this->tag->setUpdateTime($now);

        $array = $this->tag->retrievePlainArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('valid', $array);
        $this->assertArrayHasKey('type', $array);

        $this->assertEquals('测试标签', $array['name']);
        $this->assertEquals('这是一个测试描述', $array['description']);
        $this->assertTrue($array['valid']);
    }
}
