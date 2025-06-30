<?php

namespace UserTagBundle\Tests\Unit\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Procedure\Tag\GetUserTagList;
use UserTagBundle\Repository\CategoryRepository;
use UserTagBundle\Repository\SmartRuleRepository;
use UserTagBundle\Repository\SqlRuleRepository;

class GetUserTagListTest extends TestCase
{
    private GetUserTagList $procedure;
    private MockObject $entityManager;
    private MockObject $categoryRepository;
    private MockObject $sqlRuleRepository;
    private MockObject $smartRuleRepository;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->sqlRuleRepository = $this->createMock(SqlRuleRepository::class);
        $this->smartRuleRepository = $this->createMock(SmartRuleRepository::class);
        
        $this->procedure = new GetUserTagList(
            $this->entityManager,
            $this->categoryRepository,
            $this->sqlRuleRepository,
            $this->smartRuleRepository
        );
    }
    
    public function testExecute(): void
    {
        // Don't set page/pageSize properties directly as they're handled by PaginatorTrait
        
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