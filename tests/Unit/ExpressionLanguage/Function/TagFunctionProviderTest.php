<?php

namespace UserTagBundle\Tests\Unit\ExpressionLanguage\Function;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;
use UserTagBundle\ExpressionLanguage\Function\TagFunctionProvider;
use UserTagBundle\Repository\AssignLogRepository;
use UserTagBundle\Service\LocalUserTagLoader;

class TagFunctionProviderTest extends TestCase
{
    private TagFunctionProvider $provider;
    private MockObject $logger;
    private MockObject $userTagService;
    private MockObject $tagUserRepository;
    
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userTagService = $this->createMock(LocalUserTagLoader::class);
        $this->tagUserRepository = $this->createMock(AssignLogRepository::class);
        
        $this->provider = new TagFunctionProvider(
            $this->logger,
            $this->userTagService,
            $this->tagUserRepository
        );
    }
    
    public function testGetFunctions(): void
    {
        $functions = $this->provider->getFunctions();
        
        self::assertCount(1, $functions);
        self::assertSame('checkCrmCustomerHasTag', $functions[0]->getName());
    }
    
    public function testCheckCrmCustomerHasTagWithNullUser(): void
    {
        $result = $this->provider->checkCrmCustomerHasTag([], null, 'test');
        
        self::assertFalse($result);
    }
    
    public function testCheckCrmCustomerHasTagWithValidTag(): void
    {
        $user = $this->createMock(UserInterface::class);
        $tag = new Tag();
        $assignLog = new AssignLog();
        
        $this->userTagService->expects($this->once())
            ->method('getTagByName')
            ->with('test', '')
            ->willReturn($tag);
            
        $this->tagUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'user' => $user,
                'tag' => $tag,
                'valid' => true,
            ])
            ->willReturn($assignLog);
            
        $result = $this->provider->checkCrmCustomerHasTag([], $user, 'test');
        
        self::assertTrue($result);
    }
    
    public function testCheckCrmCustomerHasTagWithoutAssignLog(): void
    {
        $user = $this->createMock(UserInterface::class);
        $tag = new Tag();
        
        $this->userTagService->expects($this->once())
            ->method('getTagByName')
            ->with('test', 'category')
            ->willReturn($tag);
            
        $this->tagUserRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
            
        $result = $this->provider->checkCrmCustomerHasTag([], $user, 'test', 'category');
        
        self::assertFalse($result);
    }
}