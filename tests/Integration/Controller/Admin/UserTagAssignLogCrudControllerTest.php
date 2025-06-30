<?php

namespace UserTagBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Controller\Admin\UserTagAssignLogCrudController;
use UserTagBundle\Entity\AssignLog;

class UserTagAssignLogCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(AssignLog::class, UserTagAssignLogCrudController::getEntityFqcn());
    }
}