<?php

namespace UserTagBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Controller\Admin\UserTagSmartRuleCrudController;
use UserTagBundle\Entity\SmartRule;

class UserTagSmartRuleCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(SmartRule::class, UserTagSmartRuleCrudController::getEntityFqcn());
    }
}