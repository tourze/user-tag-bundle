<?php

namespace UserTagBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Controller\Admin\UserTagSqlRuleCrudController;
use UserTagBundle\Entity\SqlRule;

class UserTagSqlRuleCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(SqlRule::class, UserTagSqlRuleCrudController::getEntityFqcn());
    }
}