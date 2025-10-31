<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\SmartRule;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Procedure\Tag\UpdateSingleSmartUserTag;

/**
 * @internal
 */
#[CoversClass(UpdateSingleSmartUserTag::class)]
#[RunTestsInSeparateProcesses]
final class UpdateSingleSmartUserTagTest extends AbstractProcedureTestCase
{
    private UpdateSingleSmartUserTag $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(UpdateSingleSmartUserTag::class);
    }

    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->id = 'invalid-tag-id';
        $this->procedure->name = 'Updated Name';
        $this->procedure->type = 'smart';
        $this->procedure->jsonStatement = ['field' => 'value'];
        $this->procedure->cronStatement = '0 0 * * *';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到标签');
        $this->procedure->execute();
    }

    public function testExecuteSuccess(): void
    {
        // 创建测试智能标签
        $tag = new Tag();
        $tag->setName('Smart Tag');
        $tag->setType(TagType::SmartTag);
        $tag->setValid(true);

        $smartRule = new SmartRule();
        $smartRule->setTag($tag);
        $smartRule->setJsonStatement(['old' => 'value']);
        $smartRule->setCronStatement('0 0 * * 0');

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->persist($smartRule);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated Smart Tag';
        $this->procedure->type = 'smart';
        $this->procedure->valid = true;
        $this->procedure->jsonStatement = ['new' => 'value'];
        $this->procedure->cronStatement = '0 1 * * *';

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('jsonStatement', $result);
        $this->assertArrayHasKey('cronStatement', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('Updated Smart Tag', $result['name']);
        $this->assertSame(['new' => 'value'], $result['jsonStatement']);
        $this->assertSame('0 1 * * *', $result['cronStatement']);
        $this->assertSame('编辑成功', $result['__message']);
    }

    public function testExecuteWithInvalidCatalog(): void
    {
        // 创建测试智能标签
        $tag = new Tag();
        $tag->setName('Smart Tag');
        $tag->setType(TagType::SmartTag);
        $tag->setValid(true);

        $smartRule = new SmartRule();
        $smartRule->setTag($tag);
        $smartRule->setJsonStatement(['old' => 'value']);
        $smartRule->setCronStatement('0 0 * * 0');

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->persist($smartRule);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated Smart Tag';
        $this->procedure->type = 'smart';
        $this->procedure->valid = true;
        $this->procedure->jsonStatement = ['new' => 'value'];
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

        // 创建测试智能标签
        $tag = new Tag();
        $tag->setName('Smart Tag');
        $tag->setType(TagType::SmartTag);
        $tag->setValid(true);

        $smartRule = new SmartRule();
        $smartRule->setTag($tag);
        $smartRule->setJsonStatement(['old' => 'value']);
        $smartRule->setCronStatement('0 0 * * 0');

        self::getEntityManager()->persist($catalogType);
        self::getEntityManager()->persist($catalog);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->persist($smartRule);
        self::getEntityManager()->flush();

        $this->procedure->id = (string) $tag->getId();
        $this->procedure->name = 'Updated Smart Tag';
        $this->procedure->type = 'smart';
        $this->procedure->valid = true;
        $this->procedure->jsonStatement = ['new' => 'value'];
        $this->procedure->cronStatement = '0 1 * * *';
        $this->procedure->catalogId = (string) $catalog->getId();

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('jsonStatement', $result);
        $this->assertArrayHasKey('cronStatement', $result);
        $this->assertArrayHasKey('__message', $result);

        $this->assertSame('Updated Smart Tag', $result['name']);
        $this->assertSame(['new' => 'value'], $result['jsonStatement']);
        $this->assertSame('0 1 * * *', $result['cronStatement']);
        $this->assertSame('编辑成功', $result['__message']);
    }
}
