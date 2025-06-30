<?php

namespace UserTagBundle\Tests\Unit\Procedure\Category;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Category;
use UserTagBundle\Procedure\Category\UpdateSingleUserTagCategory;
use UserTagBundle\Repository\CategoryRepository;

class UpdateSingleUserTagCategoryTest extends TestCase
{
    private UpdateSingleUserTagCategory $procedure;
    private MockObject $categoryRepository;
    private MockObject $entityManager;
    
    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new UpdateSingleUserTagCategory(
            $this->categoryRepository,
            $this->entityManager
        );
    }
    
    public function testExecuteWithInvalidCategory(): void
    {
        $this->procedure->id = 'invalid-id';
        
        $this->categoryRepository->expects($this->once())
            ->method('find')
            ->with('invalid-id')
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithValidCategory(): void
    {
        $this->procedure->id = 'valid-id';
        $this->procedure->name = 'Updated Name';
        $this->procedure->mutex = false;
        
        $category = $this->createMock(Category::class);
        
        $this->categoryRepository->expects($this->once())
            ->method('find')
            ->with('valid-id')
            ->willReturn($category);
            
        $category->expects($this->once())
            ->method('setName')
            ->with('Updated Name');
            
        $category->expects($this->once())
            ->method('setMutex')
            ->with(false);
            
        $category->expects($this->once())
            ->method('setParent')
            ->with(null);
            
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($category);
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
    }
}