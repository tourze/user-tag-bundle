<?php

namespace UserTagBundle\Tests\Procedure\Tag;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Procedure\Tag\GetUserTagList;

/**
 * @internal
 */
#[CoversClass(GetUserTagList::class)]
#[RunTestsInSeparateProcesses]
final class GetUserTagListTest extends AbstractProcedureTestCase
{
    private GetUserTagList $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetUserTagList::class);
    }

    public function testExecute(): void
    {
        $result = $this->procedure->execute();

        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }

    public function testExecuteWithCatalogFilter(): void
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
        $tag->setType(TagType::StaticTag);
        $tag->setValid(true);
        $tag->setCatalog($catalog);

        self::getEntityManager()->persist($catalogType);
        self::getEntityManager()->persist($catalog);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        // 测试使用 categories 参数过滤
        $this->procedure->categories = [(string) $catalog->getId()];
        $result = $this->procedure->execute();

        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);

        // 验证返回的数据中包含刚创建的标签
        /** @var array{id: int, name: string}|null $foundTag */
        $foundTag = null;
        foreach ($result['data'] as $tagData) {
            /** @var array{id: int, name: string} $tagData */
            if ($tagData['id'] === $tag->getId()) {
                $foundTag = $tagData;
                break;
            }
        }
        $this->assertNotNull($foundTag, '应该能找到刚创建的标签');
        $this->assertSame('Test Tag', $foundTag['name']);
    }

    public function testExecuteWithInvalidCatalogFilter(): void
    {
        // 测试使用不存在的 catalog ID
        $this->procedure->categories = ['non-existent-catalog-id'];
        $result = $this->procedure->execute();

        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
        $this->assertEmpty($result['data'], '使用不存在的分类 ID 应该返回空的结果');
    }
}
