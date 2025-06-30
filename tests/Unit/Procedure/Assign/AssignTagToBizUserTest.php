<?php

namespace UserTagBundle\Tests\Unit\Procedure\Assign;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\Assign\AssignTagToBizUser;
use UserTagBundle\Repository\TagRepository;
use UserTagBundle\Service\LocalUserTagLoader;

class AssignTagToBizUserTest extends TestCase
{
    private AssignTagToBizUser $procedure;
    private MockObject $userLoader;
    private MockObject $tagRepository;
    private MockObject $userTagLoader;
    
    protected function setUp(): void
    {
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->userTagLoader = $this->createMock(LocalUserTagLoader::class);
        
        $this->procedure = new AssignTagToBizUser(
            $this->userLoader,
            $this->tagRepository,
            $this->userTagLoader
        );
    }
    
    public function testExecuteWithInvalidTag(): void
    {
        $this->procedure->tagId = 'invalid-id';
        $this->procedure->userId = 'user-id';
        
        $user = $this->createMock(UserInterface::class);
        
        $this->userLoader->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('user-id')
            ->willReturn($user);
        
        $this->tagRepository->expects($this->once())
            ->method('find')
            ->with('invalid-id')
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithValidTag(): void
    {
        $this->procedure->tagId = 'valid-tag-id';
        $this->procedure->userId = 'user-id';
        
        $tag = new Tag();
        $tag->setName('Test Tag');
        
        $user = $this->createMock(UserInterface::class);
        $assignLog = $this->createMock(AssignLog::class);
        $assignLog->expects($this->once())
            ->method('getId')
            ->willReturn('log-id');
        
        $this->userLoader->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('user-id')
            ->willReturn($user);
        
        $this->tagRepository->expects($this->once())
            ->method('find')
            ->with('valid-tag-id')
            ->willReturn($tag);
            
        $this->userTagLoader->expects($this->once())
            ->method('assignTag')
            ->with($user, $tag)
            ->willReturn($assignLog);
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
        self::assertArrayHasKey('id', $result);
        self::assertSame('log-id', $result['id']);
    }
}