<?php

namespace UserTagBundle\Tests\Unit\Procedure;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserIDBundle\Service\UserIdentityService;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Procedure\ServerAssignTagByIdentity;
use UserTagBundle\Repository\TagRepository;
use UserTagBundle\Service\LocalUserTagLoader;

class ServerAssignTagByIdentityTest extends TestCase
{
    private ServerAssignTagByIdentity $procedure;
    private MockObject $userTagLoader;
    private MockObject $tagRepository;
    private MockObject $userIdentityService;
    
    protected function setUp(): void
    {
        $this->userTagLoader = $this->createMock(LocalUserTagLoader::class);
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->userIdentityService = $this->createMock(UserIdentityService::class);
        
        $this->procedure = new ServerAssignTagByIdentity(
            $this->userTagLoader,
            $this->tagRepository,
            $this->userIdentityService
        );
    }
    
    public function testExecuteWithInvalidUser(): void
    {
        $this->procedure->identityType = 'email';
        $this->procedure->identityValue = 'invalid@example.com';
        $this->procedure->tagId = 'tag-id';
        
        $this->userIdentityService->expects($this->once())
            ->method('findByType')
            ->with('email', 'invalid@example.com')
            ->willReturn(null);
            
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->procedure->execute();
    }
    
    public function testExecuteWithValidUser(): void
    {
        $this->procedure->identityType = 'email';
        $this->procedure->identityValue = 'valid@example.com';
        $this->procedure->tagId = 'tag-id';
        
        $user = $this->createMock(UserInterface::class);
        $tag = new Tag();
        $assignLog = new AssignLog();
        
        $identity = $this->createMock(\Tourze\UserIDBundle\Contracts\IdentityInterface::class);
        $identity->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        
        $this->userIdentityService->expects($this->once())
            ->method('findByType')
            ->with('email', 'valid@example.com')
            ->willReturn($identity);
            
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