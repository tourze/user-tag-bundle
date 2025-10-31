<?php

declare(strict_types=1);

namespace UserTagBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use UserTagBundle\UserTagBundle;

/**
 * @internal
 */
#[CoversClass(UserTagBundle::class)]
#[RunTestsInSeparateProcesses]
final class UserTagBundleTest extends AbstractBundleTestCase
{
}
