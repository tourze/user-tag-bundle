<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Procedure\Tag\CreateSingleSmartUserTag;

/**
 * @internal
 */
#[CoversClass(CreateSingleSmartUserTag::class)]
#[RunTestsInSeparateProcesses]
final class CreateSingleUserTagTest extends AbstractProcedureTestCase
{
    private CreateSingleSmartUserTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(CreateSingleSmartUserTag::class);
    }

    public function testExecuteWithInvalidCatalog(): void
    {
        $this->procedure->name = 'Test Tag';
        $this->procedure->valid = true;
        $this->procedure->catalogId = 'invalid-catalog-id';
        $this->procedure->jsonStatement = ['test' => 'value'];
        $this->procedure->cronStatement = '0 0 * * *';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到指定分类');
        $this->procedure->execute();
    }

    public function testExecuteSuccess(): void
    {
        // 创建测试分类类型
        $catalogType = new CatalogType();
        $catalogType->setCode('tag_catalog');
        $catalogType->setName('Tag Catalog');
        $catalogType->setEnabled(true);

        // 创建测试分类
        $catalog = new Catalog();
        $catalog->setType($catalogType);
        $catalog->setName('Test Catalog');
        $catalog->setEnabled(true);

        self::getEntityManager()->persist($catalogType);
        self::getEntityManager()->persist($catalog);
        self::getEntityManager()->flush();

        $this->procedure->name = 'Test Tag';
        $this->procedure->valid = true;
        $this->procedure->description = 'Test Description';
        $this->procedure->catalogId = (string) $catalog->getId();
        $this->procedure->jsonStatement = ['test' => 'value'];
        $this->procedure->cronStatement = '0 0 * * *';

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('Test Tag', $result['name']);
        $this->assertSame(TagType::SmartTag->value, $result['type']);
        $this->assertTrue($result['valid']);
        $this->assertSame('Test Description', $result['description']);
        $this->assertSame('创建成功', $result['__message']);
    }

    public function testExecuteWithoutCatalog(): void
    {
        $this->procedure->name = 'Test Tag Without Catalog';
        $this->procedure->valid = true;
        $this->procedure->jsonStatement = ['test' => 'value'];
        $this->procedure->cronStatement = '0 0 * * *';

        $result = $this->procedure->execute();

        $this->assertSame('Test Tag Without Catalog', $result['name']);
        $this->assertSame('创建成功', $result['__message']);
    }
}
