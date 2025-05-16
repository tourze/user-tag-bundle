<?php

namespace UserTagBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Category;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Event\BeforeAddTagEvent;
use UserTagBundle\Repository\AssignLogRepository;
use UserTagBundle\Repository\CategoryRepository;
use UserTagBundle\Repository\TagRepository;
use UserTagBundle\Service\LocalUserTagLoader;

class LocalUserTagLoaderTest extends TestCase
{
    private MockObject $assignLogRepository;
    private MockObject $entityManager;
    private MockObject $tagRepository;
    private MockObject $categoryRepository;
    private MockObject $eventDispatcher;
    private LocalUserTagLoader $tagLoader;

    protected function setUp(): void
    {
        $this->assignLogRepository = $this->createMock(AssignLogRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->tagLoader = new LocalUserTagLoader(
            $this->assignLogRepository,
            $this->entityManager,
            $this->tagRepository,
            $this->categoryRepository,
            $this->eventDispatcher
        );
    }

    public function testAssignTag_newAssignment(): void
    {
        // 准备测试数据
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user123');
        
        $tag = new Tag();
        $tag->setName('测试标签');
        
        // 使用反射设置ID
        $reflectionClass = new \ReflectionClass(Tag::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($tag, 1);
        
        $category = new Category();
        $category->setName('测试分类');
        $category->setMutex(false);
        $tag->setCategory($category);

        // 设置存储库模拟行为
        $this->assignLogRepository->expects($this->once())
            ->method('findBy')
            ->with(['user' => $user])
            ->willReturn([]);

        // 验证事件分发
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($user, $tag) {
                return $event instanceof BeforeAddTagEvent
                    && $event->getUser() === $user
                    && $event->getTag() === $tag;
            }));

        // 验证实体管理器行为
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($assignLog) use ($user, $tag) {
                return $assignLog instanceof AssignLog
                    && $assignLog->getUser() === $user
                    && $assignLog->getTag() === $tag
                    && $assignLog->isValid() === true;
            }));

        // LocalUserTagLoader::assignTag方法会调用两次flush
        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->assignTag($user, $tag);

        // 验证结果
        $this->assertInstanceOf(AssignLog::class, $result);
        $this->assertSame($user, $result->getUser());
        $this->assertSame($tag, $result->getTag());
        $this->assertTrue($result->isValid());
        $this->assertNotNull($result->getAssignTime());
    }

    public function testAssignTag_existingAssignment(): void
    {
        // 准备测试数据
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user123');
        
        $tag = new Tag();
        $tag->setName('测试标签');
        
        // 使用反射设置ID
        $reflectionClass = new \ReflectionClass(Tag::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($tag, 1);
        
        $category = new Category();
        $category->setName('测试分类');
        $category->setMutex(false);
        $tag->setCategory($category);

        // 创建现有的分配日志
        $existingLog = new AssignLog();
        $existingLog->setUser($user);
        $existingLog->setTag($tag);
        $existingLog->setValid(false);

        // 设置存储库模拟行为
        $this->assignLogRepository->expects($this->once())
            ->method('findBy')
            ->with(['user' => $user])
            ->willReturn([$existingLog]);

        // 验证事件分发
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(BeforeAddTagEvent::class));

        // 验证实体管理器行为
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($assignLog) use ($existingLog) {
                return $assignLog === $existingLog
                    && $assignLog->isValid() === true;
            }));

        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->assignTag($user, $tag);

        // 验证结果
        $this->assertSame($existingLog, $result);
        $this->assertTrue($result->isValid());
        $this->assertNotNull($result->getAssignTime());
    }

    public function testAssignTag_withMutexCategory(): void
    {
        // 准备测试数据
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user123');
        
        $tag1 = new Tag();
        $tag1->setName('标签1');
        
        // 使用反射设置ID
        $reflectionClass = new \ReflectionClass(Tag::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($tag1, 1);
        
        $tag2 = new Tag();
        $tag2->setName('标签2');
        
        // 使用反射设置ID
        $idProperty->setValue($tag2, 2);
        
        $category = new Category();
        $category->setName('测试分类');
        $category->setMutex(true); // 互斥分类
        $category->addTag($tag1);
        $category->addTag($tag2);
        
        $tag1->setCategory($category);
        $tag2->setCategory($category);

        // 创建现有的分配日志
        $existingLog1 = new AssignLog();
        $existingLog1->setUser($user);
        $existingLog1->setTag($tag1);
        $existingLog1->setValid(true);

        // 设置存储库模拟行为
        $this->assignLogRepository->expects($this->once())
            ->method('findBy')
            ->with(['user' => $user])
            ->willReturn([$existingLog1]);

        // 验证事件分发
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(BeforeAddTagEvent::class));

        // 验证删除互斥标签
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($existingLog1);

        // 验证实体管理器行为
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(AssignLog::class));

        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->assignTag($user, $tag2);

        // 验证结果
        $this->assertInstanceOf(AssignLog::class, $result);
        $this->assertSame($user, $result->getUser());
        $this->assertSame($tag2, $result->getTag());
        $this->assertTrue($result->isValid());
    }

    public function testUnassignTag_existingTag(): void
    {
        // 准备测试数据
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user123');
        
        $tag = new Tag();
        $tag->setName('测试标签');
        
        // 使用反射设置ID
        $reflectionClass = new \ReflectionClass(Tag::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($tag, 1);

        // 创建现有的分配日志
        $existingLog = new AssignLog();
        $existingLog->setUser($user);
        $existingLog->setTag($tag);
        $existingLog->setValid(true);

        // 设置存储库模拟行为
        $this->assignLogRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'user' => $user,
                'tag' => $tag,
            ])
            ->willReturn($existingLog);

        // 验证实体管理器行为
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($assignLog) use ($existingLog) {
                return $assignLog === $existingLog
                    && $assignLog->isValid() === false
                    && $assignLog->getUnassignTime() !== null;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->unassignTag($user, $tag);

        // 验证结果
        $this->assertSame($existingLog, $result);
        $this->assertFalse($result->isValid());
        $this->assertNotNull($result->getUnassignTime());
    }

    public function testUnassignTag_nonExistingTag(): void
    {
        // 准备测试数据
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user123');
        
        $tag = new Tag();
        $tag->setName('测试标签');
        
        // 使用反射设置ID
        $reflectionClass = new \ReflectionClass(Tag::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($tag, 1);

        // 设置存储库模拟行为
        $this->assignLogRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'user' => $user,
                'tag' => $tag,
            ])
            ->willReturn(null);

        // 验证实体管理器行为
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($assignLog) use ($user, $tag) {
                return $assignLog instanceof AssignLog
                    && $assignLog->getUser() === $user
                    && $assignLog->getTag() === $tag
                    && $assignLog->isValid() === false
                    && $assignLog->getUnassignTime() !== null;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->unassignTag($user, $tag);

        // 验证结果
        $this->assertInstanceOf(AssignLog::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertNotNull($result->getUnassignTime());
    }

    public function testGetTagByName_existingTag(): void
    {
        // 准备测试数据
        $tagName = '测试标签';
        $categoryName = '测试分类';
        
        $category = new Category();
        $category->setName($categoryName);
        $category->setValid(true);
        
        $tag = new Tag();
        $tag->setName($tagName);
        $tag->setCategory($category);

        // 设置存储库模拟行为
        $this->categoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'name' => $categoryName,
                'valid' => true,
            ])
            ->willReturn($category);

        $this->tagRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'name' => $tagName,
                'category' => $category,
            ])
            ->willReturn($tag);

        // 不应该进行创建操作
        $this->entityManager->expects($this->never())
            ->method('persist');
        $this->entityManager->expects($this->never())
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->getTagByName($tagName, $categoryName);

        // 验证结果
        $this->assertSame($tag, $result);
    }

    public function testGetTagByName_createNewTag(): void
    {
        // 准备测试数据
        $tagName = '测试标签';
        $categoryName = '测试分类';
        
        $category = new Category();
        $category->setName($categoryName);
        $category->setValid(true);

        // 设置存储库模拟行为
        $this->categoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'name' => $categoryName,
                'valid' => true,
            ])
            ->willReturn($category);

        $this->tagRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'name' => $tagName,
                'category' => $category,
            ])
            ->willReturn(null);

        // 验证实体管理器行为 - 仅创建标签
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) use ($tagName, $category) {
                return $entity instanceof Tag
                    && $entity->getName() === $tagName
                    && $entity->getCategory() === $category;
            }));
        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->getTagByName($tagName, $categoryName);

        // 验证结果
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals($tagName, $result->getName());
        $this->assertSame($category, $result->getCategory());
    }

    public function testGetTagByName_createNewCategoryAndTag(): void
    {
        // 准备测试数据
        $tagName = '测试标签';
        $categoryName = '测试分类';

        // 设置存储库模拟行为
        $this->categoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'name' => $categoryName,
                'valid' => true,
            ])
            ->willReturn(null);

        $this->tagRepository->expects($this->once())
            ->method('findOneBy')
            ->with($this->callback(function($criteria) use ($tagName) {
                return isset($criteria['name']) && $criteria['name'] === $tagName
                    && isset($criteria['category']) && $criteria['category'] instanceof Category;
            }))
            ->willReturn(null);

        // 验证实体管理器行为 - 创建分类和标签
        // 由于PHPUnit 10不支持withConsecutive，我们使用可预测的调用序列
        $persistCalls = 0;
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->will($this->returnCallback(function($entity) use (&$persistCalls, $tagName, $categoryName) {
                if ($persistCalls === 0) {
                    $this->assertInstanceOf(Category::class, $entity);
                    $this->assertEquals($categoryName, $entity->getName());
                    $this->assertTrue($entity->isValid());
                } elseif ($persistCalls === 1) {
                    $this->assertInstanceOf(Tag::class, $entity);
                    $this->assertEquals($tagName, $entity->getName());
                }
                $persistCalls++;
            }));
            
        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->getTagByName($tagName, $categoryName);

        // 验证结果
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals($tagName, $result->getName());
        $this->assertInstanceOf(Category::class, $result->getCategory());
        $this->assertEquals($categoryName, $result->getCategory()->getName());
    }

    public function testGetTagByName_withoutCategory(): void
    {
        // 准备测试数据
        $tagName = '测试标签';
        $categoryName = '';

        // 设置存储库模拟行为
        $this->categoryRepository->expects($this->never())
            ->method('findOneBy');

        $this->tagRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'name' => $tagName,
                'category' => null,
            ])
            ->willReturn(null);

        // 验证实体管理器行为 - 仅创建标签
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) use ($tagName) {
                return $entity instanceof Tag
                    && $entity->getName() === $tagName
                    && $entity->getCategory() === null;
            }));
        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试
        $result = $this->tagLoader->getTagByName($tagName);

        // 验证结果
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals($tagName, $result->getName());
        $this->assertNull($result->getCategory());
    }

    public function testLoadTagsByUser(): void
    {
        // 准备测试数据
        $user = $this->createMock(UserInterface::class);
        
        $tag1 = new Tag();
        $tag1->setName('标签1');
        
        $tag2 = new Tag();
        $tag2->setName('标签2');
        
        $log1 = new AssignLog();
        $log1->setUser($user);
        $log1->setTag($tag1);
        
        $log2 = new AssignLog();
        $log2->setUser($user);
        $log2->setTag($tag2);

        // 设置存储库模拟行为
        $this->assignLogRepository->expects($this->once())
            ->method('findBy')
            ->with(['user' => $user])
            ->willReturn([$log1, $log2]);

        // 执行测试
        $result = iterator_to_array($this->tagLoader->loadTagsByUser($user));

        // 验证结果
        $this->assertCount(2, $result);
        $this->assertSame($tag1, $result[0]);
        $this->assertSame($tag2, $result[1]);
    }

    public function testLoadTagsByUser_emptyResult(): void
    {
        // 准备测试数据
        $user = $this->createMock(UserInterface::class);

        // 设置存储库模拟行为
        $this->assignLogRepository->expects($this->once())
            ->method('findBy')
            ->with(['user' => $user])
            ->willReturn([]);

        // 执行测试
        $result = iterator_to_array($this->tagLoader->loadTagsByUser($user));

        // 验证结果
        $this->assertCount(0, $result);
    }
} 