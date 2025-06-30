<?php

namespace UserTagBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use UserTagBundle\Controller\Admin\UserTagTagCrudController;
use UserTagBundle\Entity\Tag;

class UserTagTagCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(Tag::class, UserTagTagCrudController::getEntityFqcn());
    }
}