<?php

namespace UserTagBundle\Tests\Unit\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Tag\UpdateSingleUserTag;
use UserTagBundle\Repository\CategoryRepository;
use UserTagBundle\Repository\TagRepository;

class UpdateSingleUserTagTest extends TestCase
{
    private UpdateSingleUserTag $procedure;
    private MockObject $tagRepository;
    private MockObject $categoryRepository;
    private MockObject $entityManager;
    
    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new UpdateSingleUserTag(
            $this->tagRepository,
            $this->categoryRepository,
            $this->entityManager
        );
    }
    
    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->id = 'invalid-id';
        
        $this->tagRepository->expects($this->once())
            ->method('find')
            ->with('invalid-id')
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithValidTag(): void
    {
        $this->procedure->id = 'valid-id';
        $this->procedure->name = 'Updated Tag';
        $this->procedure->type = 'static';
        $this->procedure->valid = true;
        
        $tag = $this->createMock(Tag::class);
        
        $this->tagRepository->expects($this->once())
            ->method('find')
            ->with('valid-id')
            ->willReturn($tag);
            
        $tag->expects($this->once())
            ->method('setName')
            ->with('Updated Tag');
            
        $tag->expects($this->once())
            ->method('setType');
            
        $tag->expects($this->once())
            ->method('setDescription');
            
        $tag->expects($this->once())
            ->method('setValid')
            ->with(true);
            
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($tag);
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
    }
}