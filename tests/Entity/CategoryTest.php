<?php

namespace UserTagBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Category;
use UserTagBundle\Entity\Tag;

/**
 * 注意：一些测试方法可能会测试实体尚未实现的方法
 * 这些是我们希望实体应该具有的功能，可以作为未来扩展的参考
 */
class CategoryTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
    }

    public function testIdDefaultValue(): void
    {
        $this->assertEquals(0, $this->category->getId());
    }

    public function testValidDefaultValue(): void
    {
        $this->assertFalse($this->category->isValid());
    }

    public function testSetAndGetValid(): void
    {
        $this->category->setValid(true);
        $this->assertTrue($this->category->isValid());
        
        $this->category->setValid(false);
        $this->assertFalse($this->category->isValid());
        
        $this->category->setValid(null);
        $this->assertNull($this->category->isValid());
    }

    public function testSetAndGetName(): void
    {
        $name = '测试分类';
        $this->category->setName($name);
        $this->assertEquals($name, $this->category->getName());
    }

    public function testSetAndGetDescription(): void
    {
        $this->assertNull($this->category->getDescription());
        
        $description = '这是一个测试分类的描述';
        $this->category->setDescription($description);
        $this->assertEquals($description, $this->category->getDescription());
        
        $this->category->setDescription(null);
        $this->assertNull($this->category->getDescription());
    }

    public function testSetAndGetMutex(): void
    {
        $this->assertFalse($this->category->isMutex());
        
        $this->category->setMutex(true);
        $this->assertTrue($this->category->isMutex());
        
        $this->category->setMutex(false);
        $this->assertFalse($this->category->isMutex());
    }

    public function testSetAndGetCreatedBy(): void
    {
        $this->assertNull($this->category->getCreatedBy());
        
        $createdBy = 'admin';
        $this->category->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->category->getCreatedBy());
        
        $this->category->setCreatedBy(null);
        $this->assertNull($this->category->getCreatedBy());
    }

    public function testSetAndGetUpdatedBy(): void
    {
        $this->assertNull($this->category->getUpdatedBy());
        
        $updatedBy = 'admin';
        $this->category->setUpdatedBy($updatedBy);
        $this->assertEquals($updatedBy, $this->category->getUpdatedBy());
        
        $this->category->setUpdatedBy(null);
        $this->assertNull($this->category->getUpdatedBy());
    }

    public function testSetAndGetCreatedFromIp(): void
    {
        $this->assertNull($this->category->getCreatedFromIp());
        
        $ip = '127.0.0.1';
        $this->category->setCreatedFromIp($ip);
        $this->assertEquals($ip, $this->category->getCreatedFromIp());
        
        $this->category->setCreatedFromIp(null);
        $this->assertNull($this->category->getCreatedFromIp());
    }

    public function testSetAndGetUpdatedFromIp(): void
    {
        $this->assertNull($this->category->getUpdatedFromIp());
        
        $ip = '127.0.0.1';
        $this->category->setUpdatedFromIp($ip);
        $this->assertEquals($ip, $this->category->getUpdatedFromIp());
        
        $this->category->setUpdatedFromIp(null);
        $this->assertNull($this->category->getUpdatedFromIp());
    }

    public function testSetAndGetCreateTime(): void
    {
        $this->assertNull($this->category->getCreateTime());
        
        $now = new \DateTime();
        $this->category->setCreateTime($now);
        $this->assertSame($now, $this->category->getCreateTime());
        
        $this->category->setCreateTime(null);
        $this->assertNull($this->category->getCreateTime());
    }

    public function testSetAndGetUpdateTime(): void
    {
        $this->assertNull($this->category->getUpdateTime());
        
        $this->assertNull($this->category->getUpdateTime());
        
        $now = new \DateTime();
        $this->category->setUpdateTime($now);
        $this->assertSame($now, $this->category->getUpdateTime());
        
        $this->category->setUpdateTime(null);
        $this->assertNull($this->category->getUpdateTime());
    }

    public function testToString_withoutId(): void
    {
        // ID为默认值0时，应返回空字符串
        $this->assertEquals('', $this->category->__toString());
    }

    public function testToString_withId(): void
    {
        // 模拟反射设置ID
        $reflectionClass = new \ReflectionClass(Category::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->category, 1);
        
        $this->category->setName('测试分类');
        
        $this->assertEquals('测试分类', $this->category->__toString());
    }

    public function testTagsCollection(): void
    {
        // 测试tags集合初始化
        $this->assertInstanceOf(ArrayCollection::class, $this->category->getTags());
        $this->assertCount(0, $this->category->getTags());
        
        // 添加标签
        $tag1 = new Tag();
        $tag1->setName('标签1');
        
        $tag2 = new Tag();
        $tag2->setName('标签2');
        
        $this->category->addTag($tag1);
        $this->category->addTag($tag2);
        
        $this->assertCount(2, $this->category->getTags());
        $this->assertTrue($this->category->getTags()->contains($tag1));
        $this->assertTrue($this->category->getTags()->contains($tag2));
        
        // 移除标签
        $this->category->removeTag($tag1);
        $this->assertCount(1, $this->category->getTags());
        $this->assertFalse($this->category->getTags()->contains($tag1));
        $this->assertTrue($this->category->getTags()->contains($tag2));
    }

    public function testRetrievePlainArray(): void
    {
        $this->category->setName('测试分类');
        $this->category->setDescription('这是一个测试分类的描述');
        $this->category->setValid(true);
        $this->category->setMutex(true);
        
        $now = new \DateTime();
        $this->category->setCreateTime($now);
        $this->category->setUpdateTime($now);
        
        $array = $this->category->retrievePlainArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('valid', $array);
        $this->assertArrayHasKey('mutex', $array);
        
        $this->assertEquals('测试分类', $array['name']);
        $this->assertEquals('这是一个测试分类的描述', $array['description']);
        $this->assertTrue($array['valid']);
        $this->assertTrue($array['mutex']);
    }
} 