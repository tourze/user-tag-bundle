<?php

namespace UserTagBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Controller\Admin\UserTagCategoryCrudController;
use UserTagBundle\Entity\Category;

class UserTagCategoryCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(Category::class, UserTagCategoryCrudController::getEntityFqcn());
    }
}