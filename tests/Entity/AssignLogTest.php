<?php

namespace UserTagBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;

/**
 * 注意：一些测试方法可能会测试实体尚未实现的方法
 * 这些是我们希望实体应该具有的功能，可以作为未来扩展的参考
 *
 * @internal
 */
#[CoversClass(AssignLog::class)]
final class AssignLogTest extends AbstractEntityTestCase
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
        yield ['valid', false];
        yield ['createdBy', 'admin'];
        yield ['createdBy', null];
        yield ['updatedBy', 'admin'];
        yield ['updatedBy', null];
        yield ['createdFromIp', '127.0.0.1'];
        yield ['createdFromIp', null];
        yield ['updatedFromIp', '127.0.0.1'];
        yield ['updatedFromIp', null];
        yield ['assignTime', new \DateTimeImmutable()];
        yield ['assignTime', null];
        yield ['unassignTime', new \DateTimeImmutable()];
        yield ['unassignTime', null];
        yield ['createTime', new \DateTimeImmutable()];
        yield ['createTime', null];
        yield ['updateTime', new \DateTimeImmutable()];
        yield ['updateTime', null];
    }

    public function testIdDefaultValue(): void
    {
        $entity = $this->createEntity();
        $this->assertEquals(0, $entity->getId());
    }

    public function testValidDefaultValue(): void
    {
        $entity = $this->createEntity();
        $this->assertFalse($entity->isValid());
    }

    public function testSetAndGetUser(): void
    {
        $entity = $this->createEntity();
        $user = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'user123';
            }
        };

        $entity->setUser($user);
        $this->assertSame($user, $entity->getUser());

        // 用户属性不能为null，不测试null值的情况
    }

    public function testSetAndGetTag(): void
    {
        $entity = $this->createEntity();
        $this->assertNull($entity->getTag());

        $tag = new Tag();
        $tag->setName('测试标签');

        $entity->setTag($tag);
        $this->assertSame($tag, $entity->getTag());

        $entity->setTag(null);
        $this->assertNull($entity->getTag());
    }

    public function testRetrievePlainArray(): void
    {
        $entity = $this->createEntity();
        $user = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'user123';
            }
        };

        $tag = new Tag();
        $tag->setName('测试标签');

        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(true);

        $assignTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $unassignTime = new \DateTimeImmutable('2023-01-02 10:00:00');
        $entity->setAssignTime($assignTime);
        $entity->setUnassignTime($unassignTime);

        $array = $entity->retrievePlainArray();
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
