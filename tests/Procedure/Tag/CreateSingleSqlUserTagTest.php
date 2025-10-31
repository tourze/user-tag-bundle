<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Procedure\Tag\CreateSingleSqlUserTag;

/**
 * @internal
 */
#[CoversClass(CreateSingleSqlUserTag::class)]
#[RunTestsInSeparateProcesses]
final class CreateSingleSqlUserTagTest extends AbstractProcedureTestCase
{
    private CreateSingleSqlUserTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(CreateSingleSqlUserTag::class);
    }

    public function testExecuteSuccess(): void
    {
        $this->procedure->name = 'SQL Tag';
        $this->procedure->valid = true;
        $this->procedure->description = 'SQL tag description';
        $this->procedure->sqlStatement = 'SELECT id FROM users WHERE active = 1';
        $this->procedure->cronStatement = '0 1 * * *';

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('sqlStatement', $result);
        $this->assertArrayHasKey('cronStatement', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('SQL Tag', $result['name']);
        $this->assertSame(TagType::SqlTag->value, $result['type']);
        $this->assertTrue($result['valid']);
        $this->assertSame('SELECT id FROM users WHERE active = 1', $result['sqlStatement']);
        $this->assertSame('0 1 * * *', $result['cronStatement']);
        $this->assertSame('创建成功', $result['__message']);
    }
}
