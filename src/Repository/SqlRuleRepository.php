<?php

namespace UserTagBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use UserTagBundle\Entity\SqlRule;

/**
 * @method SqlRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method SqlRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method SqlRule[]    findAll()
 * @method SqlRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SqlRuleRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SqlRule::class);
    }
}
