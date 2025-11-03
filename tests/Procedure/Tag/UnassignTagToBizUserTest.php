<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Tag\UnassignTagToBizUser;

/**
 * @internal
 */
#[CoversClass(UnassignTagToBizUser::class)]
#[RunTestsInSeparateProcesses]
final class UnassignTagToBizUserTest extends AbstractProcedureTestCase
{
    private UnassignTagToBizUser $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(UnassignTagToBizUser::class);
    }

    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->tagId = 'invalid-tag-id';
        $this->procedure->userId = 'user-id';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到指定用户');
        $this->procedure->execute();
    }

    public function testExecuteSuccess(): void
    {
        // 创建测试用户
        $username = 'test@example.com';
        $user = $this->createNormalUser($username, 'password');

        // 创建测试标签
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(true);

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        // Procedure 使用 UserLoaderInterface::loadUserByIdentifier() 查找用户
        // 该方法期望 userIdentifier（username），而非数字 ID
        $this->procedure->tagId = (string) $tag->getId();
        $this->procedure->userId = $user->getUserIdentifier();

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('__message', $result);
        $this->assertSame('解除成功', $result['__message']);
    }
}
