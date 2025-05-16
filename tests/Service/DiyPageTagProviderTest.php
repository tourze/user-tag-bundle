<?php

namespace UserTagBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Service\DiyPageTagProvider;

class DiyPageTagProviderTest extends TestCase
{
    private MockObject $entityManager;
    private DiyPageTagProvider $tagProvider;
    private MockObject $query;
    private MockObject $queryBuilder;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->query = $this->createMock(Query::class);
        
        $this->tagProvider = new DiyPageTagProvider($this->entityManager);
    }

    public function testGenSelectData_withTags(): void
    {
        // 准备测试数据
        $tag1 = new Tag();
        $tag1->setName('标签1');
        
        $tag2 = new Tag();
        $tag2->setName('标签2');
        
        // 设置模拟行为
        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);
            
        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(Tag::class, 'a')
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('a')
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);
            
        $this->query->expects($this->once())
            ->method('toIterable')
            ->willReturn([$tag1, $tag2]);

        // 执行测试
        $result = iterator_to_array($this->tagProvider->genSelectData());

        // 验证结果
        $this->assertCount(2, $result);
        
        $this->assertEquals('标签1', $result[0]['label']);
        $this->assertEquals('标签1', $result[0]['text']);
        $this->assertEquals('标签1', $result[0]['value']);
        $this->assertEquals('标签1', $result[0]['name']);
        
        $this->assertEquals('标签2', $result[1]['label']);
        $this->assertEquals('标签2', $result[1]['text']);
        $this->assertEquals('标签2', $result[1]['value']);
        $this->assertEquals('标签2', $result[1]['name']);
    }

    public function testGenSelectData_emptyResult(): void
    {
        // 设置模拟行为
        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);
            
        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(Tag::class, 'a')
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('a')
            ->willReturnSelf();
            
        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);
            
        $this->query->expects($this->once())
            ->method('toIterable')
            ->willReturn([]);

        // 执行测试
        $result = iterator_to_array($this->tagProvider->genSelectData());

        // 验证结果
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
} 