<?php

namespace UserTagBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use UserTagBundle\Entity\SmartRule;

/**
 * @method SmartRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmartRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmartRule[]    findAll()
 * @method SmartRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmartRuleRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SmartRule::class);
    }
}
