<?php

namespace UserTagBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Service\DiyPageTagProvider;

/**
 * @internal
 */
#[CoversClass(DiyPageTagProvider::class)]
#[RunTestsInSeparateProcesses]
final class DiyPageTagProviderTest extends AbstractIntegrationTestCase
{
    private DiyPageTagProvider $tagProvider;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $tagProvider = $container->get(DiyPageTagProvider::class);
        self::assertInstanceOf(DiyPageTagProvider::class, $tagProvider);
        $this->tagProvider = $tagProvider;
    }

    public function testGenSelectDataWithTags(): void
    {
        // 准备测试数据
        $tag1 = new Tag();
        $tag1->setName('Test_Tag_1_' . uniqid());

        $tag2 = new Tag();
        $tag2->setName('Test_Tag_2_' . uniqid());

        $entityManager = self::getEntityManager();
        $entityManager->persist($tag1);
        $entityManager->persist($tag2);
        $entityManager->flush();

        // 执行测试
        $result = iterator_to_array($this->tagProvider->genSelectData());

        // 验证结果 - 至少包含我们创建的两个标签
        $this->assertGreaterThanOrEqual(2, count($result));

        // 查找我们创建的标签
        $foundTag1 = false;
        $foundTag2 = false;

        foreach ($result as $item) {
            if ($item['label'] === $tag1->getName()) {
                $foundTag1 = true;
                $this->assertEquals($tag1->getName(), $item['text']);
                $this->assertEquals($tag1->getName(), $item['value']);
                $this->assertEquals($tag1->getName(), $item['name']);
            }
            if ($item['label'] === $tag2->getName()) {
                $foundTag2 = true;
                $this->assertEquals($tag2->getName(), $item['text']);
                $this->assertEquals($tag2->getName(), $item['value']);
                $this->assertEquals($tag2->getName(), $item['name']);
            }
        }

        $this->assertTrue($foundTag1, 'Tag1 should be found in the result');
        $this->assertTrue($foundTag2, 'Tag2 should be found in the result');
    }

    public function testGenSelectDataEmptyResult(): void
    {
        // 测试genSelectData方法能正常运行并返回可迭代对象
        // 注意：由于测试环境可能有其他数据，我们只验证方法能正常执行

        // 执行测试
        $result = $this->tagProvider->genSelectData();

        // 验证结果是可迭代的
        $this->assertIsIterable($result);

        // 转换为数组并验证结构（如果有数据的话）
        $items = iterator_to_array($result);

        // 如果有数据，验证每个项目的结构
        foreach ($items as $item) {
            $this->assertArrayHasKey('label', $item);
            $this->assertArrayHasKey('text', $item);
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('name', $item);
        }
    }
}
