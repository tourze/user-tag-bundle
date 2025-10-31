<?php

namespace UserTagBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use UserTagBundle\DependencyInjection\UserTagExtension;

/**
 * @internal
 */
#[CoversClass(UserTagExtension::class)]
final class UserTagExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
