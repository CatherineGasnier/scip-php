  <?php
  
  declare(strict_types=1);
  
  namespace TestData\Tests;
  
  use TestData\ClassB;
//    ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
  use TestData\ClassD;
//    ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#
  
  final class Helper
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/Helper#
//            documentation
//            > ```php
//            > final class Helper
//            > ```
  {
      public static function classB(): ClassB
//                           ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/Helper#classB().
//                           documentation
//                           > ```php
//                           > public static function classB(): \TestData\ClassB
//                           > ```
//                                     ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
      {
          return new ClassB();
//                   ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
      }
  
      public static function classD(): ClassD
//                           ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/Helper#classD().
//                           documentation
//                           > ```php
//                           > public static function classD(): \TestData\ClassD
//                           > ```
//                                     ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#
      {
          return new ClassD();
//                   ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#
      }
  }
  
