<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use BizUserBundle\Entity\BizUser;
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
        $user = $this->createNormalUser('test@example.com', 'password');
        self::assertInstanceOf(BizUser::class, $user);

        $this->procedure->userId = (string) $user->getId();

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('list', $result);
        $this->assertIsArray($result['list']);
    }
}
