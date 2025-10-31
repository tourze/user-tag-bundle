<?php

namespace UserTagBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Event\BeforeAddTagEvent;

/**
 * @internal
 */
#[CoversClass(BeforeAddTagEvent::class)]
final class BeforeAddTagEventTest extends AbstractEventTestCase
{
    public function testUserGetterSetter(): void
    {
        $event = new BeforeAddTagEvent();
        $user = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'test_user';
            }
        };

        $event->setUser($user);
        $this->assertSame($user, $event->getUser());
    }

    public function testTagGetterSetter(): void
    {
        $event = new BeforeAddTagEvent();
        $tag = new Tag();

        $event->setTag($tag);
        $this->assertSame($tag, $event->getTag());
    }
}
