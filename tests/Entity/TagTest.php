<?php

namespace UserTagBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Category;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;

class TagTest extends TestCase
{
    private Tag $tag;

    protected function setUp(): void
    {
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

    public function testSetAndGetCategory(): void
    {
        $this->assertNull($this->tag->getCategory());
        
        $category = new Category();
        $category->setName('测试分类');
        
        $this->tag->setCategory($category);
        $this->assertSame($category, $this->tag->getCategory());
        
        $this->tag->setCategory(null);
        $this->assertNull($this->tag->getCategory());
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
        
        $now = new \DateTime();
        $this->tag->setCreateTime($now);
        $this->assertSame($now, $this->tag->getCreateTime());
        
        $this->tag->setCreateTime(null);
        $this->assertNull($this->tag->getCreateTime());
    }

    public function testSetAndGetUpdateTime(): void
    {
        $this->assertNull($this->tag->getUpdateTime());
        
        $now = new \DateTime();
        $this->tag->setUpdateTime($now);
        $this->assertSame($now, $this->tag->getUpdateTime());
        
        $this->tag->setUpdateTime(null);
        $this->assertNull($this->tag->getUpdateTime());
    }

    public function testToString_withoutId(): void
    {
        // ID为默认值0时，应返回空字符串
        $this->assertEquals('', $this->tag->__toString());
    }

    public function testToString_withIdAndCategory(): void
    {
        // 模拟反射设置ID
        $reflectionClass = new \ReflectionClass(Tag::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->tag, 1);
        
        $category = new Category();
        $category->setName('测试分类');
        $this->tag->setCategory($category);
        $this->tag->setName('测试标签');
        
        $expected = $category . ':测试标签';
        $this->assertEquals($expected, $this->tag->__toString());
    }

    public function testRetrievePlainArray(): void
    {
        $this->tag->setName('测试标签');
        $this->tag->setType(TagType::StaticTag);
        $this->tag->setDescription('这是一个测试描述');
        $this->tag->setValid(true);
        
        $category = new Category();
        $category->setName('测试分类');
        $this->tag->setCategory($category);
        
        $now = new \DateTime();
        $this->tag->setCreateTime($now);
        $this->tag->setUpdateTime($now);
        
        $array = $this->tag->retrievePlainArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('valid', $array);
        $this->assertArrayHasKey('type', $array);
        
        $this->assertEquals('测试标签', $array['name']);
        $this->assertEquals('这是一个测试描述', $array['description']);
        $this->assertTrue($array['valid']);
        $this->assertIsArray($array['type']);
    }
} 