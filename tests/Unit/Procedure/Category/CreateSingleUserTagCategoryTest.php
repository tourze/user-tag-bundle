<?php

namespace UserTagBundle\Tests\Unit\Procedure\Category;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Category;
use UserTagBundle\Procedure\Category\CreateSingleUserTagCategory;
use UserTagBundle\Repository\CategoryRepository;

class CreateSingleUserTagCategoryTest extends TestCase
{
    private CreateSingleUserTagCategory $procedure;
    private MockObject $categoryRepository;
    private MockObject $entityManager;
    
    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new CreateSingleUserTagCategory(
            $this->categoryRepository,
            $this->entityManager
        );
    }
    
    public function testExecuteWithParentNotFound(): void
    {
        $this->procedure->name = 'Test Category';
        $this->procedure->parentId = 'invalid-parent-id';
        
        $this->categoryRepository->expects($this->once())
            ->method('find')
            ->with('invalid-parent-id')
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithNewCategory(): void
    {
        $this->procedure->name = 'New Category';
        $this->procedure->mutex = true;
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Category::class));
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
    }
}