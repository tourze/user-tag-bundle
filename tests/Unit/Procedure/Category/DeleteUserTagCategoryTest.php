<?php

namespace UserTagBundle\Tests\Unit\Procedure\Category;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Category;
use UserTagBundle\Procedure\Category\DeleteUserTagCategory;
use UserTagBundle\Repository\CategoryRepository;

class DeleteUserTagCategoryTest extends TestCase
{
    private DeleteUserTagCategory $procedure;
    private MockObject $categoryRepository;
    private MockObject $entityManager;
    
    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new DeleteUserTagCategory(
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
        
        $category = $this->createMock(Category::class);
        
        $this->categoryRepository->expects($this->once())
            ->method('find')
            ->with('valid-id')
            ->willReturn($category);
            
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($category);
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
    }
}