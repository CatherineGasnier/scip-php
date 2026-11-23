  <?php
  
  declare(strict_types=1);
  
  namespace TestData;
  
  use PHPUnit\Framework\TestCase;
  
  final class ClassATests extends TestCase
//            ^^^^^^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassATests#
//            documentation
//            > ```php
//            > final class ClassATests extends PHPUnit\Framework\TestCase
//            > ```
  {
      public function testA2(): void
//                    ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassATests#testA2().
//                    documentation
//                    > ```php
//                    > public function testA2(): void
//                    > ```
      {
          self::assertSame(42, (new ClassA())->a2());
//        ^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassATests#
//                                  ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
//                                             ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#a2().
      }
  }
  
