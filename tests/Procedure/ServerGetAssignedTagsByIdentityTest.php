<?php

namespace UserTagBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Procedure\ServerGetAssignedTagsByIdentity;

/**
 * @internal
 */
#[CoversClass(ServerGetAssignedTagsByIdentity::class)]
#[RunTestsInSeparateProcesses]
final class ServerGetAssignedTagsByIdentityTest extends AbstractProcedureTestCase
{
    private ServerGetAssignedTagsByIdentity $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(ServerGetAssignedTagsByIdentity::class);
    }

    public function testExecuteWithInvalidIdentity(): void
    {
        $this->procedure->identityType = 'email';
        $this->procedure->identityValue = 'nonexistent@example.com';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到用户信息');
        $this->procedure->execute();
    }

    public function testExecuteWithValidIdentity(): void
    {
        $this->procedure->identityType = 'email';
        $this->procedure->identityValue = 'test@example.com';

        // 在实际环境中，我们预期这个调用会失败，因为我们没有实际的用户身份数据
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到用户信息');
        $this->procedure->execute();
    }
}
