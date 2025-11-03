<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Procedure\Tag\GetAssignTagsByBizUserId;

/**
 * @internal
 */
#[CoversClass(GetAssignTagsByBizUserId::class)]
#[RunTestsInSeparateProcesses]
final class GetAssignTagsByBizUserIdTest extends AbstractProcedureTestCase
{
    private GetAssignTagsByBizUserId $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetAssignTagsByBizUserId::class);
    }

    public function testExecute(): void
    {
        // 创建测试用户
        $username = 'test@example.com';
        $user = $this->createNormalUser($username, 'password');

        // Procedure 使用 UserLoaderInterface::loadUserByIdentifier() 查找用户
        // 该方法期望 userIdentifier（username），而非数字 ID
        $this->procedure->userId = $user->getUserIdentifier();

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('list', $result);
        $this->assertIsArray($result['list']);
    }
}
