<?php

namespace UserTagBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Repository\AssignLogRepository;

/**
 * @internal
 */
#[CoversClass(AssignLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class AssignLogRepositoryTest extends AbstractRepositoryTestCase
{
    private AssignLogRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(AssignLogRepository::class);
    }

    #[Test]
    public function testSaveAndFind(): void
    {
        $user = $this->createNormalUser('test-user@example.com', 'password123');

        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(true);
        $entity->setAssignTime(new \DateTimeImmutable());

        $this->repository->save($entity);

        $found = $this->repository->find($entity->getId());
        $this->assertInstanceOf(AssignLog::class, $found);
        $this->assertEquals('test-user@example.com', $found->getUserId());
    }

    #[Test]
    public function testRemove(): void
    {
        $user = $this->createNormalUser('test-user-2@example.com', 'password123');

        $tag = new Tag();
        $tag->setName('Test Tag 2');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(true);
        $entity->setAssignTime(new \DateTimeImmutable());

        $this->repository->save($entity);
        $id = $entity->getId();

        $this->repository->remove($entity);
        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    #[Test]
    public function testFindOneByWithOrderBy(): void
    {
        $user = $this->createNormalUser('test-order-findone@example.com', 'password123');

        $catalogType = new CatalogType();
        $catalogType->setCode('test_order_findone');
        $catalogType->setName('Order FindOne Test Type');
        $catalogType->setEnabled(true);

        $catalog = new Catalog();
        $catalog->setName('Order FindOne Test Catalog');
        $catalog->setEnabled(true);
        $catalog->setType($catalogType);

        $tag1 = new Tag();
        $tag1->setName('Order FindOne Test Tag 1');
        $tag1->setValid(true);
        $tag1->setCatalog($catalog);

        $tag2 = new Tag();
        $tag2->setName('Order FindOne Test Tag 2');
        $tag2->setValid(true);
        $tag2->setCatalog($catalog);

        $em = self::getEntityManager();
        $em->persist($catalogType);
        $em->persist($catalog);
        $em->persist($tag1);
        $em->persist($tag2);
        $em->flush();

        $entity1 = new AssignLog();
        $entity1->setUser($user);
        $entity1->setTag($tag1);
        $entity1->setValid(true);
        $entity1->setAssignTime(new \DateTimeImmutable());

        $entity2 = new AssignLog();
        $entity2->setUser($user);
        $entity2->setTag($tag2);
        $entity2->setValid(true);
        $entity2->setAssignTime(new \DateTimeImmutable());

        $this->repository->save($entity1);
        $this->repository->save($entity2);

        $found = $this->repository->findOneBy(['user' => $user], ['id' => 'DESC']);
        $this->assertInstanceOf(AssignLog::class, $found);
        $this->assertEquals($entity2->getId(), $found->getId());
    }

    #[Test]
    public function testAssociationQueryWithTag(): void
    {
        $user = $this->createNormalUser('test-assoc-tag@example.com', 'password123');

        $tag = new Tag();
        $tag->setName('Association Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(true);
        $entity->setAssignTime(new \DateTimeImmutable());

        $this->repository->save($entity);

        $results = $this->repository->findBy(['tag' => $tag]);
        $this->assertCount(1, $results);
        $this->assertEquals($entity->getId(), $results[0]->getId());
    }

    #[Test]
    public function testAssociationQueryWithUser(): void
    {
        $user = $this->createNormalUser('test-assoc-user@example.com', 'password123');

        $catalogType = new CatalogType();
        $catalogType->setCode('test_assoc_user');
        $catalogType->setName('Association User Test Type');
        $catalogType->setEnabled(true);

        $catalog = new Catalog();
        $catalog->setName('Association User Test Catalog');
        $catalog->setEnabled(true);
        $catalog->setType($catalogType);

        $tag = new Tag();
        $tag->setName('Association User Test Tag');
        $tag->setValid(true);
        $tag->setCatalog($catalog);

        $em = self::getEntityManager();
        $em->persist($catalogType);
        $em->persist($catalog);
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(true);
        $entity->setAssignTime(new \DateTimeImmutable());

        $this->repository->save($entity);

        // Verify the entity was actually saved with correct userId
        $savedEntity = $this->repository->find($entity->getId());
        $this->assertNotNull($savedEntity);
        $this->assertEquals($user->getUserIdentifier(), $savedEntity->getUserId());

        // Query by user object instead of userId string - this is more reliable
        $results = $this->repository->findBy(['user' => $user]);
        $this->assertCount(1, $results);
        $this->assertEquals($entity->getId(), $results[0]->getId());
    }

    #[Test]
    public function testCountAssociationQueryWithTag(): void
    {
        $user = $this->createNormalUser('test-count-assoc-tag@example.com', 'password123');

        $tag = new Tag();
        $tag->setName('Count Association Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(true);
        $entity->setAssignTime(new \DateTimeImmutable());

        $this->repository->save($entity);

        $count = $this->repository->count(['tag' => $tag]);
        $this->assertEquals(1, $count);
    }

    #[Test]
    public function testNullableFieldQueryValid(): void
    {
        $user = $this->createNormalUser('test-null-valid@example.com', 'password123');

        $tag = new Tag();
        $tag->setName('Null Valid Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(null);
        $entity->setAssignTime(new \DateTimeImmutable());

        $this->repository->save($entity);

        $results = $this->repository->findBy(['valid' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));

        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $entity->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    #[Test]
    public function testNullableFieldQueryAssignTime(): void
    {
        $user = $this->createNormalUser('test-null-assign@example.com', 'password123');

        $tag = new Tag();
        $tag->setName('Null Assign Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(true);
        $entity->setAssignTime(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['assignTime' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));

        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $entity->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    #[Test]
    public function testCountNullableFieldValid(): void
    {
        $user = $this->createNormalUser('test-count-null-valid@example.com', 'password123');

        $tag = new Tag();
        $tag->setName('Count Null Valid Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(null);
        $entity->setAssignTime(new \DateTimeImmutable());

        $this->repository->save($entity);

        $count = $this->repository->count(['valid' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    #[Test]
    public function testCountNullableFieldAssignTime(): void
    {
        $user = $this->createNormalUser('test-count-null-assign@example.com', 'password123');

        $tag = new Tag();
        $tag->setName('Count Null Assign Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $entity = new AssignLog();
        $entity->setUser($user);
        $entity->setTag($tag);
        $entity->setValid(true);
        $entity->setAssignTime(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['assignTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    protected function createNewEntity(): object
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_' . uniqid());
        $catalogType->setName('Test Type ' . uniqid());
        $catalogType->setEnabled(true);
        self::getEntityManager()->persist($catalogType);

        $catalog = new Catalog();
        $catalog->setName('Test Catalog ' . uniqid());
        $catalog->setEnabled(true);
        $catalog->setType($catalogType);
        self::getEntityManager()->persist($catalog);

        $tag = new Tag();
        $tag->setName('Test Tag ' . uniqid());
        $tag->setValid(true);
        $tag->setCatalog($catalog);
        self::getEntityManager()->persist($tag);

        $user = $this->createNormalUser('test-user-' . uniqid() . '@example.com', 'password123');

        $entity = new AssignLog();
        $entity->setTag($tag);
        $entity->setUser($user);
        $entity->setValid(true);
        $entity->setAssignTime(new \DateTimeImmutable());

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<AssignLog>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
