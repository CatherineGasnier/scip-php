<?php

declare(strict_types=1);

namespace TestData\Tests;

use TestData\ClassB;
use TestData\ClassD;

final class Helper
{
    public static function classB(): ClassB
    {
        return new ClassB();
    }

    public static function classD(): ClassD
    {
        return new ClassD();
    }
}
