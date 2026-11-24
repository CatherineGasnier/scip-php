<?php

declare(strict_types=1);

namespace TestData\Bench;

use TestData\ClassB;

final class ClassBBench
{
    public function benchB2(): void
    {
        (new ClassB())->b2();
    }
}
