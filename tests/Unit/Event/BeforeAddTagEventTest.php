<?php

namespace UserTagBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Event\BeforeAddTagEvent;

class BeforeAddTagEventTest extends TestCase
{
    public function testUserGetterSetter(): void
    {
        $event = new BeforeAddTagEvent();
        $user = $this->createMock(UserInterface::class);
        
        $event->setUser($user);
        self::assertSame($user, $event->getUser());
    }
    
    public function testTagGetterSetter(): void
    {
        $event = new BeforeAddTagEvent();
        $tag = new Tag();
        
        $event->setTag($tag);
        self::assertSame($tag, $event->getTag());
    }
}