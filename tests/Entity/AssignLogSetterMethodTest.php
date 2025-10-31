<?php

namespace UserTagBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(AssignLog::class)]
final class AssignLogSetterMethodTest extends AbstractEntityTestCase
{
    protected function createEntity(): AssignLog
    {
        return new AssignLog();
    }

    /**
     * @return iterable<int, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield ['valid', true];
        yield ['userId', 'test_user_id'];
        yield ['assignTime', new \DateTimeImmutable()];
        yield ['unassignTime', new \DateTimeImmutable()];
    }

    public function testAssignLogSetters(): void
    {
        $assignLog = new AssignLog();
        $tag = new Tag();
        $user = new InMemoryUser('test_user', 'password');

        // 测试所有setter方法不返回值（void）
        $assignLog->setValid(true);
        $assignLog->setTag($tag);
        $assignLog->setUser($user);
        $assignLog->setUserId('test_user_id');
        $assignLog->setAssignTime(new \DateTime());
        $assignLog->setUnassignTime(new \DateTime());

        // 验证值是否正确设置
        $this->assertTrue($assignLog->isValid());
        $this->assertSame($tag, $assignLog->getTag());
        $this->assertSame($user, $assignLog->getUser());
        $this->assertSame('test_user_id', $assignLog->getUserId());
        $this->assertInstanceOf(\DateTimeInterface::class, $assignLog->getAssignTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $assignLog->getUnassignTime());
    }
}
