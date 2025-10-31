<?php

namespace UserTagBundle\Tests\Procedure\Assign;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Assign\AdminGetAssignLogsByTag;

/**
 * @internal
 */
#[CoversClass(AdminGetAssignLogsByTag::class)]
#[RunTestsInSeparateProcesses]
final class AdminGetAssignLogsByTagTest extends AbstractProcedureTestCase
{
    private AdminGetAssignLogsByTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(AdminGetAssignLogsByTag::class);
    }

    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->tagId = 'invalid-id';

        $this->expectException(\AssertionError::class);
        $this->procedure->execute();
    }

    public function testExecuteWithValidTag(): void
    {
        // 创建测试标签
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(true);

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        // 创建测试分配日志
        $assignLog = new AssignLog();
        $assignLog->setTag($tag);
        $assignLog->setUserId('test-user-id');

        self::getEntityManager()->persist($assignLog);
        self::getEntityManager()->flush();

        // 设置 procedure 参数
        $this->procedure->tagId = (string) $tag->getId();

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertIsArray($result['list']);
        $this->assertIsArray($result['pagination']);
        $this->assertCount(1, $result['list']);
    }
}
