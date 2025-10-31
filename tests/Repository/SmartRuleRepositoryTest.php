<?php

namespace UserTagBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserTagBundle\Entity\SmartRule;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Repository\SmartRuleRepository;

/**
 * @internal
 */
#[CoversClass(SmartRuleRepository::class)]
#[RunTestsInSeparateProcesses]
final class SmartRuleRepositoryTest extends AbstractRepositoryTestCase
{
    private SmartRuleRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SmartRuleRepository::class);
    }

    public function testSaveAndFind(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */1 * * *');
        $rule->setJsonStatement(['field' => 'value']);

        $this->repository->save($rule);

        $found = $this->repository->find($rule->getId());
        $this->assertInstanceOf(SmartRule::class, $found);
        $this->assertEquals(['field' => 'value'], $found->getJsonStatement());
    }

    public function testRemove(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag 2');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */2 * * *');
        $rule->setJsonStatement(['field2' => 'value2']);

        $this->repository->save($rule);
        $id = $rule->getId();

        $this->repository->remove($rule);
        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindOneByOrderBy(): void
    {
        $tag1 = new Tag();
        $tag1->setName('OrderBy Test Tag 1');
        $tag1->setValid(true);

        $tag2 = new Tag();
        $tag2->setName('OrderBy Test Tag 2');
        $tag2->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag1);
        $em->persist($tag2);
        $em->flush();

        $rule1 = new SmartRule();
        $rule1->setTag($tag1);
        $rule1->setCronStatement('0 */12 * * *');
        $rule1->setJsonStatement(['orderBy' => 'test']);

        $rule2 = new SmartRule();
        $rule2->setTag($tag2);
        $rule2->setCronStatement('0 */12 * * *');
        $rule2->setJsonStatement(['orderBy' => 'test']);

        $this->repository->save($rule2); // Save second rule first to get lower ID
        $this->repository->save($rule1); // Save first rule second to get higher ID

        // Find with DESC order should return the rule with higher ID (rule1)
        $foundDesc = $this->repository->findOneBy(['cronStatement' => '0 */12 * * *'], ['id' => 'DESC']);
        $this->assertInstanceOf(SmartRule::class, $foundDesc);
        $this->assertEquals($rule1->getId(), $foundDesc->getId());

        // Find with ASC order should return the rule with lower ID (rule2)
        $foundAsc = $this->repository->findOneBy(['cronStatement' => '0 */12 * * *'], ['id' => 'ASC']);
        $this->assertInstanceOf(SmartRule::class, $foundAsc);
        $this->assertEquals($rule2->getId(), $foundAsc->getId());
    }

    public function testQueryByAssociation(): void
    {
        $tag1 = new Tag();
        $tag1->setName('Association Test Tag 1');
        $tag1->setValid(true);

        $tag2 = new Tag();
        $tag2->setName('Association Test Tag 2');
        $tag2->setValid(false);

        $em = self::getEntityManager();
        $em->persist($tag1);
        $em->persist($tag2);
        $em->flush();

        $rule1 = new SmartRule();
        $rule1->setTag($tag1);
        $rule1->setCronStatement('0 */13 * * *');
        $rule1->setJsonStatement(['association' => 'test1']);

        $rule2 = new SmartRule();
        $rule2->setTag($tag2);
        $rule2->setCronStatement('0 */14 * * *');
        $rule2->setJsonStatement(['association' => 'test2']);

        $this->repository->save($rule1);
        $this->repository->save($rule2);

        // Find by tag object
        $resultsByTag = $this->repository->findBy(['tag' => $tag1]);
        $this->assertCount(1, $resultsByTag);
        $this->assertEquals($rule1->getId(), $resultsByTag[0]->getId());

        // Find by tag ID
        $resultsByTagId = $this->repository->findBy(['tag' => $tag1->getId()]);
        $this->assertCount(1, $resultsByTagId);
        $this->assertEquals($rule1->getId(), $resultsByTagId[0]->getId());
    }

    public function testQueryWithTagValidField(): void
    {
        $validTag = new Tag();
        $validTag->setName('Valid Tag Test');
        $validTag->setValid(true);

        $invalidTag = new Tag();
        $invalidTag->setName('Invalid Tag Test');
        $invalidTag->setValid(false);

        $nullValidTag = new Tag();
        $nullValidTag->setName('Null Valid Tag Test');
        $nullValidTag->setValid(null);

        $em = self::getEntityManager();
        $em->persist($validTag);
        $em->persist($invalidTag);
        $em->persist($nullValidTag);
        $em->flush();

        $validRule = new SmartRule();
        $validRule->setTag($validTag);
        $validRule->setCronStatement('0 */15 * * *');
        $validRule->setJsonStatement(['validTest' => 'valid']);

        $invalidRule = new SmartRule();
        $invalidRule->setTag($invalidTag);
        $invalidRule->setCronStatement('0 */16 * * *');
        $invalidRule->setJsonStatement(['validTest' => 'invalid']);

        $nullRule = new SmartRule();
        $nullRule->setTag($nullValidTag);
        $nullRule->setCronStatement('0 */17 * * *');
        $nullRule->setJsonStatement(['validTest' => 'null']);

        $this->repository->save($validRule);
        $this->repository->save($invalidRule);
        $this->repository->save($nullRule);

        // Use DQL to query rules with valid tags (filter by tag name to avoid DataFixtures interference)
        $qb = $this->repository->createQueryBuilder('sr')
            ->join('sr.tag', 't')
            ->where('t.valid = :valid')
            ->andWhere('t.name = :tagName')
            ->setParameter('valid', true)
            ->setParameter('tagName', 'Valid Tag Test')
        ;

        /** @var array<int, SmartRule> $validRules */
        $validRules = $qb->getQuery()->getResult();
        $this->assertCount(1, $validRules);
        $this->assertEquals($validRule->getId(), $validRules[0]->getId());

        // Query rules with invalid tags (filter by tag name to avoid DataFixtures interference)
        $qb2 = $this->repository->createQueryBuilder('sr')
            ->join('sr.tag', 't')
            ->where('t.valid = :valid')
            ->andWhere('t.name = :tagName')
            ->setParameter('valid', false)
            ->setParameter('tagName', 'Invalid Tag Test')
        ;

        /** @var array<int, SmartRule> $invalidRules */
        $invalidRules = $qb2->getQuery()->getResult();
        $this->assertCount(1, $invalidRules);
        $this->assertEquals($invalidRule->getId(), $invalidRules[0]->getId());

        // Query rules with null valid tags (filter by tag name to avoid DataFixtures interference)
        $qb3 = $this->repository->createQueryBuilder('sr')
            ->join('sr.tag', 't')
            ->where('t.valid IS NULL')
            ->andWhere('t.name = :tagName')
            ->setParameter('tagName', 'Null Valid Tag Test')
        ;

        /** @var array<int, SmartRule> $nullRules */
        $nullRules = $qb3->getQuery()->getResult();
        $this->assertCount(1, $nullRules);
        $this->assertEquals($nullRule->getId(), $nullRules[0]->getId());
    }

    public function testQueryWithTagDescriptionNullableField(): void
    {
        $tagWithDesc = new Tag();
        $tagWithDesc->setName('Tag With Description');
        $tagWithDesc->setValid(true);
        $tagWithDesc->setDescription('This tag has a description');

        $tagWithoutDesc = new Tag();
        $tagWithoutDesc->setName('Tag Without Description');
        $tagWithoutDesc->setValid(true);
        $tagWithoutDesc->setDescription(null);

        $em = self::getEntityManager();
        $em->persist($tagWithDesc);
        $em->persist($tagWithoutDesc);
        $em->flush();

        $ruleWithDesc = new SmartRule();
        $ruleWithDesc->setTag($tagWithDesc);
        $ruleWithDesc->setCronStatement('0 */18 * * *');
        $ruleWithDesc->setJsonStatement(['descTest' => 'withDesc']);

        $ruleWithoutDesc = new SmartRule();
        $ruleWithoutDesc->setTag($tagWithoutDesc);
        $ruleWithoutDesc->setCronStatement('0 */19 * * *');
        $ruleWithoutDesc->setJsonStatement(['descTest' => 'withoutDesc']);

        $this->repository->save($ruleWithDesc);
        $this->repository->save($ruleWithoutDesc);

        // Query rules where tag has description (filter by tag name to avoid DataFixtures interference)
        $qb = $this->repository->createQueryBuilder('sr')
            ->join('sr.tag', 't')
            ->where('t.description IS NOT NULL')
            ->andWhere('t.name = :tagName')
            ->setParameter('tagName', 'Tag With Description')
        ;

        /** @var array<int, SmartRule> $rulesWithDesc */
        $rulesWithDesc = $qb->getQuery()->getResult();
        $this->assertCount(1, $rulesWithDesc);
        $this->assertEquals($ruleWithDesc->getId(), $rulesWithDesc[0]->getId());

        // Query rules where tag has no description (filter by tag name to avoid DataFixtures interference)
        $qb2 = $this->repository->createQueryBuilder('sr')
            ->join('sr.tag', 't')
            ->where('t.description IS NULL')
            ->andWhere('t.name = :tagName')
            ->setParameter('tagName', 'Tag Without Description')
        ;

        /** @var array<int, SmartRule> $rulesWithoutDesc */
        $rulesWithoutDesc = $qb2->getQuery()->getResult();
        $this->assertCount(1, $rulesWithoutDesc);
        $this->assertEquals($ruleWithoutDesc->getId(), $rulesWithoutDesc[0]->getId());
    }

    public function testCountByAssociation(): void
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

        $rule1 = new SmartRule();
        $rule1->setTag($tag1);
        $rule1->setCronStatement('0 */20 * * *');
        $rule1->setJsonStatement(['countAssoc' => 'test1']);

        $rule2 = new SmartRule();
        $rule2->setTag($tag2);
        $rule2->setCronStatement('0 */21 * * *');
        $rule2->setJsonStatement(['countAssoc' => 'test2']);

        $this->repository->save($rule1);
        $this->repository->save($rule2);

        // Count rules by tag
        $countByTag1 = $this->repository->count(['tag' => $tag1]);
        $this->assertEquals(1, $countByTag1);

        $countByTag2 = $this->repository->count(['tag' => $tag2]);
        $this->assertEquals(1, $countByTag2);

        // Count rules by tag ID
        $countByTagId = $this->repository->count(['tag' => $tag1->getId()]);
        $this->assertEquals(1, $countByTagId);
    }

    public function testCountWithNullableFieldCriteria(): void
    {
        $validTag = new Tag();
        $validTag->setName('Count Nullable Test Tag');
        $validTag->setValid(true);

        $nullTag = new Tag();
        $nullTag->setName('Count Null Test Tag');
        $nullTag->setValid(null);

        $em = self::getEntityManager();
        $em->persist($validTag);
        $em->persist($nullTag);
        $em->flush();

        $validRule = new SmartRule();
        $validRule->setTag($validTag);
        $validRule->setCronStatement('0 */23 * * *');
        $validRule->setJsonStatement(['nullableCount' => 'valid']);

        $nullRule = new SmartRule();
        $nullRule->setTag($nullTag);
        $nullRule->setCronStatement('0 */24 * * *');
        $nullRule->setJsonStatement(['nullableCount' => 'null']);

        $this->repository->save($validRule);
        $this->repository->save($nullRule);

        // Use DQL for complex criteria with nullable fields (filter by tag name to avoid DataFixtures interference)
        $qb = $this->repository->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->join('sr.tag', 't')
            ->where('t.valid = :valid')
            ->andWhere('t.name = :tagName')
            ->setParameter('valid', true)
            ->setParameter('tagName', 'Count Nullable Test Tag')
        ;

        $countValid = $qb->getQuery()->getSingleScalarResult();
        $this->assertEquals(1, $countValid);

        $qb2 = $this->repository->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->join('sr.tag', 't')
            ->where('t.valid IS NULL')
            ->andWhere('t.name = :tagName')
            ->setParameter('tagName', 'Count Null Test Tag')
        ;

        $countNull = $qb2->getQuery()->getSingleScalarResult();
        $this->assertEquals(1, $countNull);
    }

    public function testQueryByCreateTimeAsNull(): void
    {
        $tag = new Tag();
        $tag->setName('Timestamp Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */25 * * *');
        $rule->setJsonStatement(['timestampTest' => 'value']);

        $this->repository->save($rule, false);  // Don't flush immediately

        // Manually set to null after persisting but before flushing
        $rule->setCreateTime(null);
        $rule->setUpdateTime(null);

        $em->flush();

        // Query with null createTime
        $qb = $this->repository->createQueryBuilder('sr')
            ->where('sr.createTime IS NULL')
        ;

        /** @var array<int, SmartRule> $nullCreateTimeRules */
        $nullCreateTimeRules = $qb->getQuery()->getResult();
        $this->assertGreaterThanOrEqual(1, count($nullCreateTimeRules));

        // Query with null updateTime
        $qb2 = $this->repository->createQueryBuilder('sr')
            ->where('sr.updateTime IS NULL')
        ;

        /** @var array<int, SmartRule> $nullUpdateTimeRules */
        $nullUpdateTimeRules = $qb2->getQuery()->getResult();
        $this->assertGreaterThanOrEqual(1, count($nullUpdateTimeRules));
    }

    public function testQueryByCreatedByAsNull(): void
    {
        $tag = new Tag();
        $tag->setName('Blameable Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */26 * * *');
        $rule->setJsonStatement(['blameableTest' => 'value']);
        $rule->setCreatedBy(null);
        $rule->setUpdatedBy(null);

        $this->repository->save($rule);

        // Query with null createdBy
        $qb = $this->repository->createQueryBuilder('sr')
            ->where('sr.createdBy IS NULL')
        ;

        /** @var array<int, SmartRule> $nullCreatedByRules */
        $nullCreatedByRules = $qb->getQuery()->getResult();
        $this->assertGreaterThanOrEqual(1, count($nullCreatedByRules));

        // Query with null updatedBy
        $qb2 = $this->repository->createQueryBuilder('sr')
            ->where('sr.updatedBy IS NULL')
        ;

        /** @var array<int, SmartRule> $nullUpdatedByRules */
        $nullUpdatedByRules = $qb2->getQuery()->getResult();
        $this->assertGreaterThanOrEqual(1, count($nullUpdatedByRules));
    }

    public function testCountByCreateTimeAsNull(): void
    {
        $tag = new Tag();
        $tag->setName('Count Timestamp Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */27 * * *');
        $rule->setJsonStatement(['countTimestampTest' => 'value']);

        $this->repository->save($rule, false);  // Don't flush immediately

        // Manually set to null after persisting but before flushing
        $rule->setCreateTime(null);
        $rule->setUpdateTime(null);

        $em->flush();

        // Count with null createTime
        $qb = $this->repository->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.createTime IS NULL')
        ;

        $countNullCreateTime = $qb->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $countNullCreateTime);

        // Count with null updateTime
        $qb2 = $this->repository->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.updateTime IS NULL')
        ;

        $countNullUpdateTime = $qb2->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $countNullUpdateTime);
    }

    public function testCountByCreatedByAsNull(): void
    {
        $tag = new Tag();
        $tag->setName('Count Blameable Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */28 * * *');
        $rule->setJsonStatement(['countBlameableTest' => 'value']);
        $rule->setCreatedBy(null);
        $rule->setUpdatedBy(null);

        $this->repository->save($rule);

        // Count with null createdBy
        $qb = $this->repository->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.createdBy IS NULL')
        ;

        $countNullCreatedBy = $qb->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $countNullCreatedBy);

        // Count with null updatedBy
        $qb2 = $this->repository->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.updatedBy IS NULL')
        ;

        $countNullUpdatedBy = $qb2->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $countNullUpdatedBy);
    }

    public function testQueryByUpdateTimeAsNull(): void
    {
        $tag = new Tag();
        $tag->setName('UpdateTime Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */29 * * *');
        $rule->setJsonStatement(['updateTimeTest' => 'value']);

        $this->repository->save($rule, false);  // Don't flush immediately

        // Manually set to null after persisting but before flushing
        $rule->setUpdateTime(null);

        $em->flush();

        $qb = $this->repository->createQueryBuilder('sr')
            ->where('sr.updateTime IS NULL')
        ;

        /** @var array<int, SmartRule> $nullUpdateTimeRules */
        $nullUpdateTimeRules = $qb->getQuery()->getResult();
        $this->assertGreaterThanOrEqual(1, count($nullUpdateTimeRules));
    }

    public function testCountByUpdateTimeAsNull(): void
    {
        $tag = new Tag();
        $tag->setName('Count UpdateTime Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */30 * * *');
        $rule->setJsonStatement(['countUpdateTimeTest' => 'value']);

        $this->repository->save($rule, false);  // Don't flush immediately

        // Manually set to null after persisting but before flushing
        $rule->setUpdateTime(null);

        $em->flush();

        $qb = $this->repository->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.updateTime IS NULL')
        ;

        $countNullUpdateTime = $qb->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $countNullUpdateTime);
    }

    public function testQueryByUpdatedByAsNull(): void
    {
        $tag = new Tag();
        $tag->setName('UpdatedBy Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */31 * * *');
        $rule->setJsonStatement(['updatedByTest' => 'value']);
        $rule->setUpdatedBy(null);

        $this->repository->save($rule);

        $qb = $this->repository->createQueryBuilder('sr')
            ->where('sr.updatedBy IS NULL')
        ;

        /** @var array<int, SmartRule> $nullUpdatedByRules */
        $nullUpdatedByRules = $qb->getQuery()->getResult();
        $this->assertGreaterThanOrEqual(1, count($nullUpdatedByRules));
    }

    public function testCountByUpdatedByAsNull(): void
    {
        $tag = new Tag();
        $tag->setName('Count UpdatedBy Test Tag');
        $tag->setValid(true);

        $em = self::getEntityManager();
        $em->persist($tag);
        $em->flush();

        $rule = new SmartRule();
        $rule->setTag($tag);
        $rule->setCronStatement('0 */32 * * *');
        $rule->setJsonStatement(['countUpdatedByTest' => 'value']);
        $rule->setUpdatedBy(null);

        $this->repository->save($rule);

        $qb = $this->repository->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.updatedBy IS NULL')
        ;

        $countNullUpdatedBy = $qb->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $countNullUpdatedBy);
    }

    protected function createNewEntity(): object
    {
        $tag = new Tag();
        $tag->setName('Test Tag ' . uniqid());
        $tag->setValid(true);
        self::getEntityManager()->persist($tag);

        $entity = new SmartRule();
        $entity->setTag($tag);
        $entity->setCronStatement('0 0 * * *');
        $entity->setJsonStatement(['rule' => 'test']);

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<SmartRule>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
