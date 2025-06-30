<?php

namespace UserTagBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Entity\Tag;

class SqlRuleTest extends TestCase
{
    public function testGetterSetters(): void
    {
        $sqlRule = new SqlRule();
        
        // Test sqlStatement
        $sqlRule->setSqlStatement('SELECT * FROM users');
        self::assertSame('SELECT * FROM users', $sqlRule->getSqlStatement());
        
        // Test cronStatement
        $sqlRule->setCronStatement('0 0 * * *');
        self::assertSame('0 0 * * *', $sqlRule->getCronStatement());
        
        // Test tag
        $tag = new Tag();
        $sqlRule->setTag($tag);
        self::assertSame($tag, $sqlRule->getTag());
    }
}