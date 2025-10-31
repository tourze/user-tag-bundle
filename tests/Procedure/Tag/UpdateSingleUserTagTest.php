<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Tag\UpdateSingleUserTag;

/**
 * @internal
 */
#[CoversClass(UpdateSingleUserTag::class)]
#[RunTestsInSeparateProcesses]
final class UpdateSingleUserTagTest extends AbstractProcedureTestCase
{
    private UpdateSingleUserTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(UpdateSingleUserTag::class);
    }

    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->id = 'invalid-tag-id';
        $this->procedure->name = 'Updated Name';
        $this->procedure->type = 'static';
        $this->procedure->valid = true;

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到标签');
        $this->procedure->execute();
    }

    public function testExecuteSuccess(): void
    {
        // 创建测试标签
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(false);

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated Test Tag';
        $this->procedure->type = 'static';
        $this->procedure->valid = true;
        $this->procedure->description = 'Updated description';

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('Updated Test Tag', $result['name']);
        $this->assertTrue($result['valid']);
        $this->assertSame('Updated description', $result['description']);
        $this->assertSame('更新成功', $result['__message']);
    }

    public function testExecuteWithInvalidCatalog(): void
    {
        // 创建测试标签
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(false);

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated Test Tag';
        $this->procedure->type = 'static';
        $this->procedure->valid = true;
        $this->procedure->catalogId = 'invalid-catalog-id';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到指定分类');
        $this->procedure->execute();
    }

    public function testExecuteWithValidCatalog(): void
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

        // 创建测试标签
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(false);

        self::getEntityManager()->persist($catalogType);
        self::getEntityManager()->persist($catalog);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated Test Tag';
        $this->procedure->type = 'static';
        $this->procedure->valid = true;
        $this->procedure->description = 'Updated description';
        $this->procedure->catalogId = (string) $catalog->getId();

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('Updated Test Tag', $result['name']);
        $this->assertTrue($result['valid']);
        $this->assertSame('Updated description', $result['description']);
        $this->assertSame('更新成功', $result['__message']);
    }
}
