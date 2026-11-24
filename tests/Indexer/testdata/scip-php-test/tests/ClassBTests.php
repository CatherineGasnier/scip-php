<?php

declare(strict_types=1);

namespace TestData\Tests;

use PHPUnit\Framework\TestCase;
use TestData\ClassB;

final class ClassBTests extends TestCase
{
    public function testB1(): void
    {
        $b = Helper::classB();
        self::assertSame(ClassB::B1, $b->b1(1, Helper::classD()));
    }
}
