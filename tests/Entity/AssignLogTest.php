<?php

namespace UserTagBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;

/**
 * 注意：一些测试方法可能会测试实体尚未实现的方法
 * 这些是我们希望实体应该具有的功能，可以作为未来扩展的参考
 */
class AssignLogTest extends TestCase
{
    private AssignLog $assignLog;

    protected function setUp(): void
    {
        $this->assignLog = new AssignLog();
    }

    public function testIdDefaultValue(): void
    {
        $this->assertEquals(0, $this->assignLog->getId());
    }

    public function testValidDefaultValue(): void
    {
        $this->assertFalse($this->assignLog->isValid());
    }

    public function testSetAndGetValid(): void
    {
        $this->assignLog->setValid(true);
        $this->assertTrue($this->assignLog->isValid());
        
        $this->assignLog->setValid(false);
        $this->assertFalse($this->assignLog->isValid());
        
        $this->assignLog->setValid(null);
        $this->assertNull($this->assignLog->isValid());
    }

    public function testSetAndGetUser(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user123');
        
        $this->assignLog->setUser($user);
        $this->assertSame($user, $this->assignLog->getUser());
        
        // 用户属性不能为null，不测试null值的情况
    }

    public function testSetAndGetTag(): void
    {
        $this->assertNull($this->assignLog->getTag());
        
        $tag = new Tag();
        $tag->setName('测试标签');
        
        $this->assignLog->setTag($tag);
        $this->assertSame($tag, $this->assignLog->getTag());
        
        $this->assignLog->setTag(null);
        $this->assertNull($this->assignLog->getTag());
    }

    public function testSetAndGetAssignTime(): void
    {
        $this->assertNull($this->assignLog->getAssignTime());
        
        $now = new \DateTimeImmutable();
        $this->assignLog->setAssignTime($now);
        $this->assertSame($now, $this->assignLog->getAssignTime());
        
        $this->assignLog->setAssignTime(null);
        $this->assertNull($this->assignLog->getAssignTime());
    }

    public function testSetAndGetUnassignTime(): void
    {
        $this->assertNull($this->assignLog->getUnassignTime());
        
        $now = new \DateTimeImmutable();
        $this->assignLog->setUnassignTime($now);
        $this->assertSame($now, $this->assignLog->getUnassignTime());
        
        $this->assignLog->setUnassignTime(null);
        $this->assertNull($this->assignLog->getUnassignTime());
    }

    public function testSetAndGetCreatedBy(): void
    {
        $this->assertNull($this->assignLog->getCreatedBy());
        
        $createdBy = 'admin';
        $this->assignLog->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->assignLog->getCreatedBy());
        
        $this->assignLog->setCreatedBy(null);
        $this->assertNull($this->assignLog->getCreatedBy());
    }

    public function testSetAndGetUpdatedBy(): void
    {
        $this->assertNull($this->assignLog->getUpdatedBy());
        
        $updatedBy = 'admin';
        $this->assignLog->setUpdatedBy($updatedBy);
        $this->assertEquals($updatedBy, $this->assignLog->getUpdatedBy());
        
        $this->assignLog->setUpdatedBy(null);
        $this->assertNull($this->assignLog->getUpdatedBy());
    }

    public function testSetAndGetCreatedFromIp(): void
    {
        $this->assertNull($this->assignLog->getCreatedFromIp());
        
        $ip = '127.0.0.1';
        $this->assignLog->setCreatedFromIp($ip);
        $this->assertEquals($ip, $this->assignLog->getCreatedFromIp());
        
        $this->assignLog->setCreatedFromIp(null);
        $this->assertNull($this->assignLog->getCreatedFromIp());
    }

    public function testSetAndGetUpdatedFromIp(): void
    {
        $this->assertNull($this->assignLog->getUpdatedFromIp());
        
        $ip = '127.0.0.1';
        $this->assignLog->setUpdatedFromIp($ip);
        $this->assertEquals($ip, $this->assignLog->getUpdatedFromIp());
        
        $this->assignLog->setUpdatedFromIp(null);
        $this->assertNull($this->assignLog->getUpdatedFromIp());
    }

    public function testSetAndGetCreateTime(): void
    {
        $this->assertNull($this->assignLog->getCreateTime());
        
        $now = new \DateTimeImmutable();
        $this->assignLog->setCreateTime($now);
        $this->assertSame($now, $this->assignLog->getCreateTime());
        
        $this->assignLog->setCreateTime(null);
        $this->assertNull($this->assignLog->getCreateTime());
    }

    public function testSetAndGetUpdateTime(): void
    {
        $this->assertNull($this->assignLog->getUpdateTime());
        
        $now = new \DateTimeImmutable();
        $this->assignLog->setUpdateTime($now);
        $this->assertSame($now, $this->assignLog->getUpdateTime());
        
        $this->assignLog->setUpdateTime(null);
        $this->assertNull($this->assignLog->getUpdateTime());
    }

    public function testRetrievePlainArray(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user123');
        
        $tag = new Tag();
        $tag->setName('测试标签');
        
        $this->assignLog->setUser($user);
        $this->assignLog->setTag($tag);
        $this->assignLog->setValid(true);
        
        $assignTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $unassignTime = new \DateTimeImmutable('2023-01-02 10:00:00');
        $this->assignLog->setAssignTime($assignTime);
        $this->assignLog->setUnassignTime($unassignTime);
        
        $array = $this->assignLog->retrievePlainArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('valid', $array);
        $this->assertArrayHasKey('user', $array);
        $this->assertArrayHasKey('tag', $array);
        $this->assertArrayHasKey('assignTime', $array);
        $this->assertArrayHasKey('unassignTime', $array);
        
        $this->assertTrue($array['valid']);
        $this->assertEquals('user123', $array['user']);
        $this->assertNotNull($array['tag']);
        $this->assertEquals($assignTime->format('Y-m-d H:i:s'), $array['assignTime']);
        $this->assertEquals($unassignTime->format('Y-m-d H:i:s'), $array['unassignTime']);
    }
} 