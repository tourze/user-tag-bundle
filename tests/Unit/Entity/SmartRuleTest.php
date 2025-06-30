<?php

namespace UserTagBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Entity\SmartRule;
use UserTagBundle\Entity\Tag;

class SmartRuleTest extends TestCase
{
    public function testGetterSetters(): void
    {
        $smartRule = new SmartRule();
        
        // Test jsonStatement
        $jsonStatement = ['field' => 'value'];
        $smartRule->setJsonStatement($jsonStatement);
        self::assertSame($jsonStatement, $smartRule->getJsonStatement());
        
        // Test cronStatement
        $smartRule->setCronStatement('0 0 * * *');
        self::assertSame('0 0 * * *', $smartRule->getCronStatement());
        
        // Test tag
        $tag = new Tag();
        $smartRule->setTag($tag);
        self::assertSame($tag, $smartRule->getTag());
    }
}