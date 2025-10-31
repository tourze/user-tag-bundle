<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Tag\DeleteSingleUserTag;

/**
 * @internal
 */
#[CoversClass(DeleteSingleUserTag::class)]
#[RunTestsInSeparateProcesses]
final class DeleteSingleUserTagTest extends AbstractProcedureTestCase
{
    private DeleteSingleUserTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(DeleteSingleUserTag::class);
    }

    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->id = 'invalid-tag-id';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到标签');
        $this->procedure->execute();
    }

    public function testExecuteSuccess(): void
    {
        // 创建测试标签
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(true);

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $tagId = (string) $tag->getId();
        $this->procedure->id = $tagId;

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('__message', $result);
        $this->assertSame('删除成功', $result['__message']);

        // 验证标签已被删除
        $deletedTag = self::getEntityManager()->find(Tag::class, $tagId);
        $this->assertNull($deletedTag);
    }
}
