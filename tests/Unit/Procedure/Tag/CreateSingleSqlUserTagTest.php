<?php

namespace UserTagBundle\Tests\Unit\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\Category;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Procedure\Tag\CreateSingleSqlUserTag;
use UserTagBundle\Repository\CategoryRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateSingleSqlUserTagTest extends TestCase
{
    private CreateSingleSqlUserTag $procedure;
    private MockObject $categoryRepository;
    private MockObject $entityManager;
    private MockObject $container;
    
    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        
        $this->procedure = new CreateSingleSqlUserTag(
            $this->categoryRepository,
            $this->entityManager
        );
        
        // Set up container for service subscriber
        $this->procedure->setContainer($this->container);
        
        $this->container->expects($this->any())
            ->method('get')
            ->willReturn($this->entityManager);
    }
    
    public function testExecuteWithInvalidCategory(): void
    {
        $this->procedure->name = 'New SQL Tag';
        $this->procedure->categoryId = 'invalid-cat-id';
        $this->procedure->sqlStatement = 'SELECT * FROM users';
        $this->procedure->cronStatement = '0 0 * * *';
        $this->procedure->valid = true;
        
        $this->categoryRepository->expects($this->once())
            ->method('find')
            ->with('invalid-cat-id')
            ->willReturn(null);
            
        $this->entityManager->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) {
                return $callback();
            });
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithNewTag(): void
    {
        $this->procedure->name = 'New SQL Tag';
        $this->procedure->sqlStatement = 'SELECT * FROM users';
        $this->procedure->cronStatement = '0 0 * * *';
        $this->procedure->valid = true;
        
        $this->entityManager->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) {
                return $callback();
            });
            
        $this->entityManager->expects($this->exactly(2))
            ->method('persist');
            
        $this->entityManager->expects($this->any())
            ->method('flush');
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
    }
}