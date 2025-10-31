<?php

namespace UserTagBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\CatalogBundle\Repository\CatalogRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Repository\TagRepository;

/**
 * @internal
 */
#[CoversClass(TagRepository::class)]
#[RunTestsInSeparateProcesses]
final class TagRepositoryTest extends AbstractRepositoryTestCase
{
    private TagRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(TagRepository::class);
    }

    #[Test]
    public function testFindOneByWithMatchingCriteria(): void
    {
        $tag = new Tag();
        $tag->setName('Unique Tag Name');
        $tag->setValid(true);
        $this->repository->save($tag);

        $found = $this->repository->findOneBy(['name' => 'Unique Tag Name']);
        $this->assertInstanceOf(Tag::class, $found);
        $this->assertEquals('Unique Tag Name', $found->getName());
    }

    #[Test]
    public function testSaveAndRemove(): void
    {
        $tag = new Tag();
        $tag->setName('Tag to Remove');
        $tag->setValid(true);

        $this->repository->save($tag);
        $id = $tag->getId();

        $found = $this->repository->find($id);
        $this->assertInstanceOf(Tag::class, $found);

        $this->repository->remove($tag);
        $removed = $this->repository->find($id);
        $this->assertNull($removed);
    }

    #[Test]
    public function testFindOneByWithOrderBy(): void
    {
        $tag1 = new Tag();
        $tag1->setName('ZZZ Order Test Tag');
        $tag1->setValid(true);
        $this->repository->save($tag1);

        $tag2 = new Tag();
        $tag2->setName('AAA Order Test Tag');
        $tag2->setValid(true);
        $this->repository->save($tag2);

        $firstByNameAsc = $this->repository->findOneBy(['valid' => true], ['name' => 'ASC']);
        $this->assertInstanceOf(Tag::class, $firstByNameAsc);

        $firstByNameDesc = $this->repository->findOneBy(['valid' => true], ['name' => 'DESC']);
        $this->assertInstanceOf(Tag::class, $firstByNameDesc);

        // 验证排序结果不同
        $this->assertNotEquals($firstByNameAsc->getId(), $firstByNameDesc->getId());
    }

    #[Test]
    public function testRemoveMethodFunctionality(): void
    {
        $tag = new Tag();
        $tag->setName('Remove Method Test Tag');
        $tag->setValid(true);
        $this->repository->save($tag);
        $id = $tag->getId();

        // 验证保存成功
        $found = $this->repository->find($id);
        $this->assertInstanceOf(Tag::class, $found);

        // 测试不刷新的删除
        $this->repository->remove($tag, false);
        $stillExists = $this->repository->find($id);
        $this->assertInstanceOf(Tag::class, $stillExists); // 还应该存在

        // 手动刷新
        self::getEntityManager()->flush();
        $removed = $this->repository->find($id);
        $this->assertNull($removed);
    }

    #[Test]
    public function testFindByWithCatalogAssociation(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_tag');
        $catalogType->setName('Test Tag Type');
        $catalogType->setEnabled(true);

        $catalog = new Catalog();
        $catalog->setName('Test Catalog for Association');
        $catalog->setEnabled(true);
        $catalog->setType($catalogType);

        $catalogRepository = self::getService(CatalogRepository::class);
        $em = self::getEntityManager();
        $em->persist($catalogType);
        $catalogRepository->save($catalog);

        $tag1 = new Tag();
        $tag1->setName('Tag with Catalog');
        $tag1->setValid(true);
        $tag1->setCatalog($catalog);
        $this->repository->save($tag1);

        $tag2 = new Tag();
        $tag2->setName('Tag without Catalog');
        $tag2->setValid(true);
        $this->repository->save($tag2);

        // 测试根据关联对象查询
        $tagsWithCatalog = $this->repository->findBy(['catalog' => $catalog]);
        $this->assertGreaterThanOrEqual(1, count($tagsWithCatalog));

        foreach ($tagsWithCatalog as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
            $this->assertEquals($catalog->getId(), $tag->getCatalog()?->getId());
        }

        // 测试查询没有关联的标签
        $tagsWithoutCatalog = $this->repository->findBy(['catalog' => null]);
        $this->assertGreaterThanOrEqual(1, count($tagsWithoutCatalog));

        foreach ($tagsWithoutCatalog as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
            $this->assertNull($tag->getCatalog());
        }
    }

    #[Test]
    public function testFindOneByWithCatalogAssociation(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_findone');
        $catalogType->setName('Test FindOne Type');
        $catalogType->setEnabled(true);

        $catalog = new Catalog();
        $catalog->setName('Test Catalog for FindOneBy');
        $catalog->setEnabled(true);
        $catalog->setType($catalogType);

        $catalogRepository = self::getService(CatalogRepository::class);
        $em = self::getEntityManager();
        $em->persist($catalogType);
        $catalogRepository->save($catalog);

        $tag = new Tag();
        $tag->setName('Unique Catalog Tag');
        $tag->setValid(true);
        $tag->setCatalog($catalog);
        $this->repository->save($tag);

        $found = $this->repository->findOneBy(['catalog' => $catalog]);
        $this->assertInstanceOf(Tag::class, $found);
        $this->assertEquals($catalog->getId(), $found->getCatalog()?->getId());

        $notFound = $this->repository->findOneBy(['catalog' => null, 'name' => 'Unique Catalog Tag']);
        $this->assertNull($notFound);
    }

    #[Test]
    public function testFindByWithNullableFields(): void
    {
        $tag1 = new Tag();
        $tag1->setName('Tag with Description');
        $tag1->setValid(true);
        $tag1->setDescription('This tag has a description');
        $this->repository->save($tag1);

        $tag2 = new Tag();
        $tag2->setName('Tag without Description');
        $tag2->setValid(true);
        $tag2->setDescription(null);
        $this->repository->save($tag2);

        $tag3 = new Tag();
        $tag3->setName('Tag with null valid');
        $tag3->setValid(null);
        $this->repository->save($tag3);

        // 测试查询有描述的标签
        $tagsWithDescription = $this->repository->findBy(['description' => 'This tag has a description']);
        $this->assertGreaterThanOrEqual(1, count($tagsWithDescription));

        foreach ($tagsWithDescription as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
            $this->assertNotNull($tag->getDescription());
        }

        // 测试查询valid为null的标签
        $tagsWithNullValid = $this->repository->findBy(['valid' => null]);
        $this->assertGreaterThanOrEqual(1, count($tagsWithNullValid));

        foreach ($tagsWithNullValid as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
            $this->assertNull($tag->isValid());
        }
    }

    #[Test]
    public function testFindOneByWithNullableFields(): void
    {
        $tag = new Tag();
        $tag->setName('Nullable Field Test Tag');
        $tag->setValid(null);
        $tag->setDescription(null);
        $this->repository->save($tag);

        $foundByNullValid = $this->repository->findOneBy(['valid' => null]);
        $this->assertInstanceOf(Tag::class, $foundByNullValid);
        $this->assertNull($foundByNullValid->isValid());

        $foundByNullDescription = $this->repository->findOneBy(['description' => null]);
        $this->assertInstanceOf(Tag::class, $foundByNullDescription);
        $this->assertNull($foundByNullDescription->getDescription());

        // 测试同时查询多个null字段
        $foundByMultipleNull = $this->repository->findOneBy([
            'valid' => null,
            'description' => null,
        ]);
        $this->assertInstanceOf(Tag::class, $foundByMultipleNull);
        $this->assertNull($foundByMultipleNull->isValid());
        $this->assertNull($foundByMultipleNull->getDescription());
    }

    #[Test]
    public function testCountWithCatalogAssociation(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_count');
        $catalogType->setName('Test Count Type');
        $catalogType->setEnabled(true);

        $catalog = new Catalog();
        $catalog->setName('Count Test Catalog');
        $catalog->setEnabled(true);
        $catalog->setType($catalogType);

        $catalogRepository = self::getService(CatalogRepository::class);
        $em = self::getEntityManager();
        $em->persist($catalogType);
        $catalogRepository->save($catalog);

        $initialCountWithCatalog = $this->repository->count(['catalog' => $catalog]);
        $initialCountWithoutCatalog = $this->repository->count(['catalog' => null]);

        $tag1 = new Tag();
        $tag1->setName('Tag for Count with Catalog');
        $tag1->setValid(true);
        $tag1->setCatalog($catalog);
        $this->repository->save($tag1);

        $tag2 = new Tag();
        $tag2->setName('Tag for Count without Catalog');
        $tag2->setValid(true);
        $this->repository->save($tag2);

        $newCountWithCatalog = $this->repository->count(['catalog' => $catalog]);
        $newCountWithoutCatalog = $this->repository->count(['catalog' => null]);

        $this->assertEquals($initialCountWithCatalog + 1, $newCountWithCatalog);
        $this->assertEquals($initialCountWithoutCatalog + 1, $newCountWithoutCatalog);
    }

    #[Test]
    public function testCountWithNullableFields(): void
    {
        $initialCountWithDescription = $this->repository->count(['description' => 'Specific description for count']);
        $initialCountWithNullValid = $this->repository->count(['valid' => null]);

        $tag1 = new Tag();
        $tag1->setName('Count Test Tag with Description');
        $tag1->setValid(true);
        $tag1->setDescription('Specific description for count');
        $this->repository->save($tag1);

        $tag2 = new Tag();
        $tag2->setName('Count Test Tag with Null Valid');
        $tag2->setValid(null);
        $this->repository->save($tag2);

        $newCountWithDescription = $this->repository->count(['description' => 'Specific description for count']);
        $newCountWithNullValid = $this->repository->count(['valid' => null]);

        $this->assertEquals($initialCountWithDescription + 1, $newCountWithDescription);
        $this->assertEquals($initialCountWithNullValid + 1, $newCountWithNullValid);
    }

    #[Test]
    public function testCountWithMultipleCriteria(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_multiple');
        $catalogType->setName('Test Multiple Type');
        $catalogType->setEnabled(true);

        $catalog = new Catalog();
        $catalog->setName('Multiple Criteria Count Catalog');
        $catalog->setEnabled(true);
        $catalog->setType($catalogType);

        $catalogRepository = self::getService(CatalogRepository::class);
        $em = self::getEntityManager();
        $em->persist($catalogType);
        $catalogRepository->save($catalog);

        $initialCount = $this->repository->count([
            'valid' => true,
            'catalog' => $catalog,
        ]);

        $tag = new Tag();
        $tag->setName('Multiple Criteria Count Tag');
        $tag->setValid(true);
        $tag->setCatalog($catalog);
        $this->repository->save($tag);

        $newCount = $this->repository->count([
            'valid' => true,
            'catalog' => $catalog,
        ]);

        $this->assertEquals($initialCount + 1, $newCount);

        // 测试不匹配的条件
        $noMatchCount = $this->repository->count([
            'valid' => false,
            'catalog' => $catalog,
        ]);
        $this->assertEquals(0, $noMatchCount);
    }

    protected function createNewEntity(): object
    {
        $entity = new Tag();
        $entity->setName('Test Tag ' . uniqid());
        $entity->setValid(true);

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<Tag>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
