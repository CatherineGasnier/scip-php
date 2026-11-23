<?php

declare(strict_types=1);

namespace TestData;

use PHPUnit\Framework\TestCase;

final class ClassATests extends TestCase
{
    public function testA2(): void
    {
        self::assertSame(42, (new ClassA())->a2());
    }
}
