<?php

namespace UserTagBundle\Tests\Unit\Procedure;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\ServerAssignCrmTag;
use UserTagBundle\Repository\TagRepository;
use UserTagBundle\Service\LocalUserTagLoader;

class ServerAssignCrmTagTest extends TestCase
{
    private ServerAssignCrmTag $procedure;
    private MockObject $userTagLoader;
    private MockObject $tagRepository;
    private MockObject $userLoader;
    
    protected function setUp(): void
    {
        $this->userTagLoader = $this->createMock(LocalUserTagLoader::class);
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        
        $this->procedure = new ServerAssignCrmTag(
            $this->userTagLoader,
            $this->tagRepository,
            $this->userLoader
        );
    }
    
    public function testExecuteWithInvalidUser(): void
    {
        $this->procedure->identity = 'invalid-user';
        $this->procedure->tagId = 'tag-id';
        
        $this->userLoader->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('invalid-user')
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithValidUser(): void
    {
        $this->procedure->identity = 'valid-user';
        $this->procedure->tagId = 'tag-id';
        
        $user = $this->createMock(UserInterface::class);
        $tag = new Tag();
        $assignLog = new AssignLog();
        
        $this->userLoader->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('valid-user')
            ->willReturn($user);
            
        $this->tagRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 'tag-id', 'valid' => true])
            ->willReturn($tag);
            
        $this->userTagLoader->expects($this->once())
            ->method('assignTag')
            ->with($user, $tag);
            
        $result = $this->procedure->execute();
        
        self::assertArrayHasKey('__message', $result);
    }
}