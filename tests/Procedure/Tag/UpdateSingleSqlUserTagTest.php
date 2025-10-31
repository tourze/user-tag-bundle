<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Procedure\Tag\UpdateSingleSqlUserTag;

/**
 * @internal
 */
#[CoversClass(UpdateSingleSqlUserTag::class)]
#[RunTestsInSeparateProcesses]
final class UpdateSingleSqlUserTagTest extends AbstractProcedureTestCase
{
    private UpdateSingleSqlUserTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(UpdateSingleSqlUserTag::class);
    }

    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->id = 'invalid-tag-id';
        $this->procedure->name = 'Updated Name';
        $this->procedure->type = 'sql';
        $this->procedure->sqlStatement = 'SELECT id FROM users';
        $this->procedure->cronStatement = '0 0 * * *';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到标签');
        $this->procedure->execute();
    }

    public function testExecuteSuccess(): void
    {
        // 创建测试SQL标签
        $tag = new Tag();
        $tag->setName('SQL Tag');
        $tag->setType(TagType::SqlTag);
        $tag->setValid(true);

        $sqlRule = new SqlRule();
        $sqlRule->setTag($tag);
        $sqlRule->setSqlStatement('SELECT id FROM old_users');
        $sqlRule->setCronStatement('0 0 * * 0');

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->persist($sqlRule);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated SQL Tag';
        $this->procedure->type = 'sql';
        $this->procedure->valid = true;
        $this->procedure->sqlStatement = 'SELECT id FROM new_users';
        $this->procedure->cronStatement = '0 1 * * *';

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('sqlStatement', $result);
        $this->assertArrayHasKey('cronStatement', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('Updated SQL Tag', $result['name']);
        $this->assertSame('SELECT id FROM new_users', $result['sqlStatement']);
        $this->assertSame('0 1 * * *', $result['cronStatement']);
        $this->assertSame('编辑成功', $result['__message']);
    }

    public function testExecuteWithInvalidCatalog(): void
    {
        // 创建测试SQL标签
        $tag = new Tag();
        $tag->setName('SQL Tag');
        $tag->setType(TagType::SqlTag);
        $tag->setValid(true);

        $sqlRule = new SqlRule();
        $sqlRule->setTag($tag);
        $sqlRule->setSqlStatement('SELECT id FROM old_users');
        $sqlRule->setCronStatement('0 0 * * 0');

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->persist($sqlRule);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated SQL Tag';
        $this->procedure->type = 'sql';
        $this->procedure->valid = true;
        $this->procedure->sqlStatement = 'SELECT id FROM new_users';
        $this->procedure->cronStatement = '0 1 * * *';
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

        // 创建测试SQL标签
        $tag = new Tag();
        $tag->setName('SQL Tag');
        $tag->setType(TagType::SqlTag);
        $tag->setValid(true);

        $sqlRule = new SqlRule();
        $sqlRule->setTag($tag);
        $sqlRule->setSqlStatement('SELECT id FROM old_users');
        $sqlRule->setCronStatement('0 0 * * 0');

        self::getEntityManager()->persist($catalogType);
        self::getEntityManager()->persist($catalog);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->persist($sqlRule);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated SQL Tag';
        $this->procedure->type = 'sql';
        $this->procedure->valid = true;
        $this->procedure->sqlStatement = 'SELECT id FROM new_users';
        $this->procedure->cronStatement = '0 1 * * *';
        $this->procedure->catalogId = (string) $catalog->getId();

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('sqlStatement', $result);
        $this->assertArrayHasKey('cronStatement', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('Updated SQL Tag', $result['name']);
        $this->assertSame('SELECT id FROM new_users', $result['sqlStatement']);
        $this->assertSame('0 1 * * *', $result['cronStatement']);
        $this->assertSame('编辑成功', $result['__message']);
    }
}
