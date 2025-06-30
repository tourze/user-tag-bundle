<?php

namespace UserTagBundle\Tests\Unit\Procedure\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Category;
use UserTagBundle\Procedure\Category\GetUserTagCategories;
use UserTagBundle\Repository\CategoryRepository;

class GetUserTagCategoriesTest extends TestCase
{
    private GetUserTagCategories $procedure;
    private MockObject $categoryRepository;
    private MockObject $entityManager;
    
    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new GetUserTagCategories(
            $this->categoryRepository,
            $this->entityManager
        );
    }
    
    public function testExecute(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        
        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('from')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.parent IS NULL')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
            
        $query->expects($this->once())
            ->method('toIterable')
            ->willReturn([]);
            
        $result = $this->procedure->execute();
        
        self::assertEmpty($result);
    }
}