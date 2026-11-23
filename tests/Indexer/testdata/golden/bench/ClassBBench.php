  <?php
  
  declare(strict_types=1);
  
  namespace TestData\Bench;
  
  use TestData\ClassB;
//    ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
  
  final class ClassBBench
//            ^^^^^^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Bench/ClassBBench#
//            documentation
//            > ```php
//            > final class ClassBBench
//            > ```
  {
      public function benchB2(): void
//                    ^^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/Bench/ClassBBench#benchB2().
//                    documentation
//                    > ```php
//                    > public function benchB2(): void
//                    > ```
      {
          (new ClassB())->b2();
//             ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#
//                        ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassB#b2().
      }
  }
  
