<?php

namespace UserTagBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use UserTagBundle\Exception\UnexpectedArgumentException;

/**
 * @internal
 */
#[CoversClass(UnexpectedArgumentException::class)]
final class UnexpectedArgumentExceptionTest extends AbstractExceptionTestCase
{
}
