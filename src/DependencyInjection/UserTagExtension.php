<?php

namespace UserTagBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class UserTagExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
