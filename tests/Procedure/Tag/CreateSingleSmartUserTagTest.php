<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Procedure\Tag\CreateSingleSmartUserTag;

/**
 * @internal
 */
#[CoversClass(CreateSingleSmartUserTag::class)]
#[RunTestsInSeparateProcesses]
final class CreateSingleSmartUserTagTest extends AbstractProcedureTestCase
{
    private CreateSingleSmartUserTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(CreateSingleSmartUserTag::class);
    }

    public function testExecuteSuccess(): void
    {
        $this->procedure->name = 'Smart Tag';
        $this->procedure->valid = true;
        $this->procedure->description = 'Smart tag description';
        $this->procedure->jsonStatement = ['field' => 'value'];
        $this->procedure->cronStatement = '0 0 * * *';

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('jsonStatement', $result);
        $this->assertArrayHasKey('cronStatement', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('Smart Tag', $result['name']);
        $this->assertSame(TagType::SmartTag->value, $result['type']);
        $this->assertTrue($result['valid']);
        $this->assertSame(['field' => 'value'], $result['jsonStatement']);
        $this->assertSame('0 0 * * *', $result['cronStatement']);
        $this->assertSame('创建成功', $result['__message']);
    }
}
