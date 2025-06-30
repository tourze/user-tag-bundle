<?php

namespace UserTagBundle\Tests\Unit\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Category;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Procedure\Tag\CreateSingleUserTag;
use UserTagBundle\Repository\CategoryRepository;

class CreateSingleUserTagTest extends TestCase
{
    private CreateSingleUserTag $procedure;
    private MockObject $categoryRepository;
    private MockObject $entityManager;
    
    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new CreateSingleUserTag(
            $this->categoryRepository,
            $this->entityManager
        );
    }
    
    public function testExecuteWithInvalidCategory(): void
    {
        $this->procedure->name = 'New Tag';
        $this->procedure->categoryId = 'invalid-cat-id';
        $this->procedure->type = 'static';
        $this->procedure->valid = true;
        
        $this->categoryRepository->expects($this->once())
            ->method('find')
            ->with('invalid-cat-id')
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithNewTag(): void
    {
        $this->procedure->name = 'New Tag';
        $this->procedure->type = 'static';
        $this->procedure->valid = true;
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Tag::class));
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
    }
}