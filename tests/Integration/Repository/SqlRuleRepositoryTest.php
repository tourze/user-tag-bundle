<?php

namespace UserTagBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Repository\SqlRuleRepository;

class SqlRuleRepositoryTest extends TestCase
{
    public function testRepositoryEntityClass(): void
    {
        // Repository 继承自 ServiceEntityRepository，在构造函数中指定了实体类
        // 这里只测试 Repository 类是否存在
        self::assertTrue(class_exists(SqlRuleRepository::class));
        self::assertTrue(class_exists(SqlRule::class));
    }
}