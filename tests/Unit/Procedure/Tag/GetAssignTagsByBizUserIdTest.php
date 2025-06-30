<?php

namespace UserTagBundle\Tests\Unit\Procedure\Tag;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Tag\GetAssignTagsByBizUserId;
use UserTagBundle\Repository\AssignLogRepository;

class GetAssignTagsByBizUserIdTest extends TestCase
{
    private GetAssignTagsByBizUserId $procedure;
    private MockObject $assignLogRepository;
    private MockObject $userLoader;
    
    protected function setUp(): void
    {
        $this->assignLogRepository = $this->createMock(AssignLogRepository::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        
        $this->procedure = new GetAssignTagsByBizUserId(
            $this->assignLogRepository,
            $this->userLoader
        );
    }
    
    public function testExecuteWithInvalidUser(): void
    {
        $this->procedure->userId = 'invalid-user';
        
        $this->userLoader->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('invalid-user')
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithValidUser(): void
    {
        $this->procedure->userId = 'valid-user';
        
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('valid-user');
        
        $tag1 = $this->createMock(Tag::class);
        $tag1->method('retrievePlainArray')->willReturn(['id' => 'tag1', 'name' => 'Tag 1']);
        
        $tag2 = $this->createMock(Tag::class);
        $tag2->method('retrievePlainArray')->willReturn(['id' => 'tag2', 'name' => 'Tag 2']);
        
        $assignLog1 = $this->createMock(AssignLog::class);
        $assignLog1->method('getTag')->willReturn($tag1);
        $assignLog1->method('getAssignTime')->willReturn(null);
        $assignLog1->method('getUnassignTime')->willReturn(null);
        $assignLog1->method('getCreateTime')->willReturn(null);
        
        $assignLog2 = $this->createMock(AssignLog::class);
        $assignLog2->method('getTag')->willReturn($tag2);
        $assignLog2->method('getAssignTime')->willReturn(null);
        $assignLog2->method('getUnassignTime')->willReturn(null);
        $assignLog2->method('getCreateTime')->willReturn(null);
        
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        
        $this->userLoader->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('valid-user')
            ->willReturn($user);
            
        $this->assignLogRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.user = :user')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('user', $user)
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('a.createTime', 'DESC')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
            
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([$assignLog1, $assignLog2]);
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('list', $result);
        self::assertCount(2, $result['list']);
    }
}