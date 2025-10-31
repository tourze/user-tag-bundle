<?php

namespace UserTagBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Repository\SqlRuleRepository;

/**
 * @internal
 */
#[CoversClass(SqlRuleRepository::class)]
#[RunTestsInSeparateProcesses]
final class SqlRuleRepositoryTest extends AbstractRepositoryTestCase
{
    private SqlRuleRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SqlRuleRepository::class);
    }

    public function testSaveAndFind(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SqlRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */1 * * *');
        $rule->setSqlStatement('SELECT * FROM users WHERE active = 1');

        $this->repository->save($rule);

        $found = $this->repository->find($rule->getId());
        $this->assertInstanceOf(SqlRule::class, $found);
        $this->assertEquals('SELECT * FROM users WHERE active = 1', $found->getSqlStatement());
    }

    public function testRemove(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag 2');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SqlRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */2 * * *');
        $rule->setSqlStatement('SELECT * FROM users WHERE id > 100');

        $this->repository->save($rule);
        $id = $rule->getId();

        $this->repository->remove($rule);
        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $em = self::getEntityManager();

        $tag1 = new Tag();
        $tag1->setName('Sort Tag 1');
        $tag1->setValid(true);
        $em->persist($tag1);

        $tag2 = new Tag();
        $tag2->setName('Sort Tag 2');
        $tag2->setValid(true);
        $em->persist($tag2);
        $em->flush();

        $rule1 = new SqlRule();
        $rule1->setTag($tag1);
        $rule1->setCronStatement('0 13 * * *');
        $rule1->setSqlStatement('SELECT Z');

        $rule2 = new SqlRule();
        $rule2->setTag($tag2);
        $rule2->setCronStatement('0 13 * * *');
        $rule2->setSqlStatement('SELECT A');

        $this->repository->save($rule1);
        $this->repository->save($rule2);

        $firstByAsc = $this->repository->findOneBy(['cronStatement' => '0 13 * * *'], ['sqlStatement' => 'ASC']);
        $firstByDesc = $this->repository->findOneBy(['cronStatement' => '0 13 * * *'], ['sqlStatement' => 'DESC']);

        $this->assertInstanceOf(SqlRule::class, $firstByAsc);
        $this->assertInstanceOf(SqlRule::class, $firstByDesc);

        $this->assertEquals('SELECT A', $firstByAsc->getSqlStatement());
        $this->assertEquals('SELECT Z', $firstByDesc->getSqlStatement());
    }

    public function testFindByTagAssociation(): void
    {
        $tag1 = new Tag();
        $tag1->setName('Association Tag 1');
        $tag1->setValid(true);

        $tag2 = new Tag();
        $tag2->setName('Association Tag 2');
        $tag2->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag1);
        $em->persist($tag2);
        $em->flush();

        $rule1 = new SqlRule();
        $rule1->setTag($tag1);
        $rule1->setCronStatement('0 14 * * *');
        $rule1->setSqlStatement('SELECT * FROM assoc1');

        $rule2 = new SqlRule();
        $rule2->setTag($tag2);
        $rule2->setCronStatement('0 15 * * *');
        $rule2->setSqlStatement('SELECT * FROM assoc2');

        $this->repository->save($rule1);
        $this->repository->save($rule2);

        $rulesForTag1 = $this->repository->findBy(['tag' => $tag1]);
        $rulesForTag2 = $this->repository->findBy(['tag' => $tag2]);

        $this->assertCount(1, $rulesForTag1);
        $this->assertCount(1, $rulesForTag2);

        $this->assertEquals($tag1->getId(), $rulesForTag1[0]->getTag()->getId());
        $this->assertEquals($tag2->getId(), $rulesForTag2[0]->getTag()->getId());
    }

    public function testCountByTagAssociation(): void
    {
        $tag1 = new Tag();
        $tag1->setName('Count Association Tag 1');
        $tag1->setValid(true);

        $tag2 = new Tag();
        $tag2->setName('Count Association Tag 2');
        $tag2->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag1);
        $em->persist($tag2);
        $em->flush();

        $rule1 = new SqlRule();
        $rule1->setTag($tag1);
        $rule1->setCronStatement('0 16 * * *');
        $rule1->setSqlStatement('SELECT COUNT_ASSOC_1');

        $rule2 = new SqlRule();
        $rule2->setTag($tag2);
        $rule2->setCronStatement('0 17 * * *');
        $rule2->setSqlStatement('SELECT COUNT_ASSOC_2');

        $this->repository->save($rule1);
        $this->repository->save($rule2);

        $count1 = $this->repository->count(['tag' => $tag1]);
        $count2 = $this->repository->count(['tag' => $tag2]);

        $this->assertEquals(1, $count1);
        $this->assertEquals(1, $count2);
    }

    public function testFindOneByAssociationTagShouldReturnMatchingEntity(): void
    {
        $tag = new Tag();
        $tag->setName('Association Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SqlRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 18 * * *');
        $rule->setSqlStatement('SELECT * FROM association_test');

        $this->repository->save($rule);

        $found = $this->repository->findOneBy(['tag' => $tag]);

        $this->assertInstanceOf(SqlRule::class, $found);
        $this->assertEquals($tag->getId(), $found->getTag()->getId());
        $this->assertEquals('SELECT * FROM association_test', $found->getSqlStatement());
    }

    public function testCountByAssociationTagShouldReturnCorrectNumber(): void
    {
        $tag = new Tag();
        $tag->setName('Count By Association Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SqlRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 20 * * *');
        $rule->setSqlStatement('SELECT COUNT_BY_ASSOC');

        $this->repository->save($rule);

        $count = $this->repository->count(['tag' => $tag]);

        $this->assertEquals(1, $count);
    }

    protected function createNewEntity(): object
    {
        $tag = new Tag();
        $tag->setName('Test Tag ' . uniqid());
        $tag->setValid(true);
        self::getEntityManager()->persist($tag);

        $entity = new SqlRule();
        $entity->setTag($tag);
        $entity->setCronStatement('0 0 * * *');
        $entity->setSqlStatement('SELECT 1');

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<SqlRule>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
