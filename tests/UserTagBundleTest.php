<?php

namespace UserTagBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\BundleDependency\BundleDependencyInterface;
use UserTagBundle\UserTagBundle;

class UserTagBundleTest extends TestCase
{
    public function testInstanceImplementsBundleDependencyInterface(): void
    {
        $bundle = new UserTagBundle();
        $this->assertInstanceOf(BundleDependencyInterface::class, $bundle);
    }

    public function testGetBundleDependenciesReturnsArray(): void
    {
        $dependencies = UserTagBundle::getBundleDependencies();
        $this->assertIsArray($dependencies);
    }
} 