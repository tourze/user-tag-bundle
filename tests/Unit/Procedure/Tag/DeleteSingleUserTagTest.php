<?php

namespace UserTagBundle\Tests\Unit\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Tag\DeleteSingleUserTag;
use UserTagBundle\Repository\TagRepository;

class DeleteSingleUserTagTest extends TestCase
{
    private DeleteSingleUserTag $procedure;
    private MockObject $tagRepository;
    private MockObject $entityManager;
    
    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new DeleteSingleUserTag(
            $this->tagRepository,
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
        
        $tag = $this->createMock(Tag::class);
        
        $this->tagRepository->expects($this->once())
            ->method('find')
            ->with('valid-id')
            ->willReturn($tag);
            
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($tag);
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
        self::assertSame('删除成功', $result['__message']);
    }
}