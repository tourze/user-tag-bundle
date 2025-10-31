<?php

namespace UserTagBundle\Tests\Procedure\Assign;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Assign\AssignTagToBizUser;

/**
 * @internal
 */
#[CoversClass(AssignTagToBizUser::class)]
#[RunTestsInSeparateProcesses]
final class AssignTagToBizUserTest extends AbstractProcedureTestCase
{
    private AssignTagToBizUser $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(AssignTagToBizUser::class);
    }

    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->tagId = 'invalid-id';
        $this->procedure->userId = 'user-id';

        $this->expectException(ApiException::class);
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

        // 设置 procedure 参数
        $this->procedure->tagId = (string) $tag->getId();
        $this->procedure->userId = 'test-user-id';

        // 由于测试环境中可能没有真实的用户加载器，我们期望抛出异常
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到指定用户');

        $this->procedure->execute();
    }
}
