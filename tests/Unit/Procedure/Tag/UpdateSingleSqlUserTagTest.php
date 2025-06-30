<?php

namespace UserTagBundle\Tests\Unit\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Tag\UpdateSingleSqlUserTag;
use UserTagBundle\Repository\CategoryRepository;
use UserTagBundle\Repository\SqlRuleRepository;
use UserTagBundle\Repository\TagRepository;

class UpdateSingleSqlUserTagTest extends TestCase
{
    private UpdateSingleSqlUserTag $procedure;
    private MockObject $tagRepository;
    private MockObject $entityManager;
    private MockObject $categoryRepository;
    private MockObject $ruleRepository;
    
    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->ruleRepository = $this->createMock(SqlRuleRepository::class);
        
        $this->procedure = new UpdateSingleSqlUserTag(
            $this->tagRepository,
            $this->entityManager,
            $this->categoryRepository,
            $this->ruleRepository
        );
    }
    
    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->id = 'invalid-id';
        
        $this->tagRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 'invalid-id'])
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithValidTag(): void
    {
        $this->procedure->id = 'valid-id';
        $this->procedure->name = 'Updated SQL Tag';
        $this->procedure->sqlStatement = 'UPDATE users SET active = 1';
        $this->procedure->cronStatement = '0 0 * * *';
        $this->procedure->type = 'sql';
        $this->procedure->valid = true;
        
        $tag = $this->createMock(Tag::class);
        $sqlRule = $this->createMock(SqlRule::class);
        
        $this->tagRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 'valid-id'])
            ->willReturn($tag);
            
        $this->entityManager->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) {
                return $callback();
            });
            
        $this->ruleRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['tag' => $tag])
            ->willReturn($sqlRule);
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
    }
}