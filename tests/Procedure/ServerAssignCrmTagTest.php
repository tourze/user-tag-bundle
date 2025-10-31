<?php

namespace UserTagBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\ServerAssignCrmTag;

/**
 * @internal
 */
#[CoversClass(ServerAssignCrmTag::class)]
#[RunTestsInSeparateProcesses]
final class ServerAssignCrmTagTest extends AbstractProcedureTestCase
{
    private ServerAssignCrmTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(ServerAssignCrmTag::class);
    }

    public function testExecuteWithInvalidUser(): void
    {
        $this->procedure->identity = 'invalid-user';
        $this->procedure->tagId = 'some-tag-id';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到用户信息');
        $this->procedure->execute();
    }

    public function testExecuteWithInvalidTag(): void
    {
        // 创建测试用户
        $user = $this->createNormalUser('test@example.com', 'password');

        $this->procedure->identity = 'test@example.com';
        $this->procedure->tagId = 'invalid-tag-id';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到标签信息');
        $this->procedure->execute();
    }

    public function testExecuteSuccess(): void
    {
        // 创建测试用户
        $user = $this->createNormalUser('test@example.com', 'password');

        // 创建测试标签
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(true);

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $this->procedure->identity = 'test@example.com';
        $this->procedure->tagId = (string) $tag->getId();

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('__message', $result);
        $this->assertSame('分配成功', $result['__message']);
    }
}
