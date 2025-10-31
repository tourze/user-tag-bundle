<?php

namespace UserTagBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Service\LocalUserTagLoader;

/**
 * @internal
 */
#[CoversClass(LocalUserTagLoader::class)]
#[RunTestsInSeparateProcesses]
final class LocalUserTagLoaderTest extends AbstractIntegrationTestCase
{
    private LocalUserTagLoader $tagLoader;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $tagLoader = $container->get(LocalUserTagLoader::class);
        self::assertInstanceOf(LocalUserTagLoader::class, $tagLoader);
        $this->tagLoader = $tagLoader;
    }

    private function createCatalogType(string $code = 'test', string $name = 'Test Type'): CatalogType
    {
        $catalogType = new CatalogType();
        $catalogType->setCode($code);
        $catalogType->setName($name);
        $catalogType->setEnabled(true);

        $entityManager = self::getEntityManager();
        $entityManager->persist($catalogType);
        $entityManager->flush();

        return $catalogType;
    }

    private function createCatalog(string $name, string $slug, ?CatalogType $type = null): Catalog
    {
        if (null === $type) {
            $type = $this->createCatalogType();
        }

        $catalog = new Catalog();
        $catalog->setType($type);
        $catalog->setName($name);
        $catalog->setEnabled(true);

        return $catalog;
    }

    public function testLoadTagsByUser(): void
    {
        // 创建测试数据
        $catalog = $this->createCatalog('Test Catalog', 'test-catalog');

        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setCatalog($catalog);

        $entityManager = self::getEntityManager();
        $entityManager->persist($catalog);
        $entityManager->persist($tag);
        $entityManager->flush();

        // 创建一个简单的 UserInterface 实现
        $user = $this->createNormalUser('test-user');

        $result = $this->tagLoader->loadTagsByUser($user);

        $this->assertIsIterable($result);
    }

    public function testGetTagByNameWithCatalogName(): void
    {
        $catalogName = 'Test Catalog';
        $tagName = 'Test Tag';

        // 测试通过 catalogName 创建 Tag
        $tag = $this->tagLoader->getTagByName($tagName, $catalogName);

        $this->assertSame($tagName, $tag->getName());
        $this->assertNotNull($tag->getCatalog());
        $this->assertSame($catalogName, $tag->getCatalog()->getName());
        $this->assertTrue($tag->getCatalog()->isEnabled());
    }

    public function testGetTagByNameWithoutCatalogName(): void
    {
        $tagName = 'Standalone Tag';

        // 测试不指定 catalogName 创建 Tag
        $tag = $this->tagLoader->getTagByName($tagName);

        $this->assertSame($tagName, $tag->getName());
        $this->assertNull($tag->getCatalog());
    }

    public function testMutexFunctionalityThroughMetadata(): void
    {
        // 创建带有 mutex metadata 的 catalog
        $catalog = $this->createCatalog('Mutex Catalog', 'mutex-catalog');
        $catalog->setMetadata(['mutex' => true]);

        // 创建两个互斥的标签
        $tag1 = new Tag();
        $tag1->setName('Tag 1');
        $tag1->setCatalog($catalog);

        $tag2 = new Tag();
        $tag2->setName('Tag 2');
        $tag2->setCatalog($catalog);

        $entityManager = self::getEntityManager();
        $entityManager->persist($catalog);
        $entityManager->persist($tag1);
        $entityManager->persist($tag2);
        $entityManager->flush();

        $user = $this->createNormalUser('mutex-test-user');

        // 给用户分配第一个标签
        $assignLog1 = $this->tagLoader->assignTag($user, $tag1);
        $this->assertTrue($assignLog1->isValid());

        // 给用户分配第二个标签（应该移除第一个）
        $assignLog2 = $this->tagLoader->assignTag($user, $tag2);
        $this->assertTrue($assignLog2->isValid());

        // 验证用户只有第二个标签
        $userTags = iterator_to_array($this->tagLoader->loadTagsByUser($user));
        $this->assertCount(1, $userTags);
        $this->assertSame('Tag 2', $userTags[0]->getName());
    }

    public function testAssignTagWithoutMutex(): void
    {
        // 创建不带 mutex metadata 的 catalog
        $catalog = $this->createCatalog('Normal Catalog', 'normal-catalog');
        // 不设置 mutex metadata 或设置为 false

        $tag1 = new Tag();
        $tag1->setName('Tag 1');
        $tag1->setCatalog($catalog);

        $tag2 = new Tag();
        $tag2->setName('Tag 2');
        $tag2->setCatalog($catalog);

        $entityManager = self::getEntityManager();
        $entityManager->persist($catalog);
        $entityManager->persist($tag1);
        $entityManager->persist($tag2);
        $entityManager->flush();

        $user = $this->createNormalUser('normal-test-user');

        // 给用户分配两个标签
        $this->tagLoader->assignTag($user, $tag1);
        $this->tagLoader->assignTag($user, $tag2);

        // 验证用户有两个标签
        $userTags = iterator_to_array($this->tagLoader->loadTagsByUser($user));
        $this->assertCount(2, $userTags);
    }

    public function testUnassignTag(): void
    {
        // 创建测试数据
        $catalog = $this->createCatalog('Unassign Test Catalog', 'unassign-test-catalog');

        $tag = new Tag();
        $tag->setName('Unassign Test Tag');
        $tag->setCatalog($catalog);

        $entityManager = self::getEntityManager();
        $entityManager->persist($catalog);
        $entityManager->persist($tag);
        $entityManager->flush();

        $user = $this->createNormalUser('unassign-test-user');

        // 先分配标签
        $assignLog = $this->tagLoader->assignTag($user, $tag);
        $this->assertTrue($assignLog->isValid());

        // 然后解绑标签
        $unassignLog = $this->tagLoader->unassignTag($user, $tag);
        $this->assertFalse($unassignLog->isValid());
        $this->assertNotNull($unassignLog->getUnassignTime());
    }
}
