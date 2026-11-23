  <?php
  
  declare(strict_types=1);
  
  namespace TestData\Tests;
  
  use PHPUnit\Framework\TestCase;
  use TestData\ClassB;
//    ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
  
  final class ClassBTests extends TestCase
//            ^^^^^^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/ClassBTests#
//            documentation
//            > ```php
//            > final class ClassBTests extends PHPUnit\Framework\TestCase
//            > ```
  {
      public function testB1(): void
//                    ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/ClassBTests#testB1().
//                    documentation
//                    > ```php
//                    > public function testB1(): void
//                    > ```
      {
          $b = Helper::classB();
//             ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/Helper#
//                     ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/Helper#classB().
          self::assertSame(ClassB::B1, $b->b1(1, Helper::classD()));
//        ^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/ClassBTests#
//                         ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
//                                 ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#B1.
//                                               ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/Helper#
//                                                       ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Tests/Helper#classD().
      }
  }
  
