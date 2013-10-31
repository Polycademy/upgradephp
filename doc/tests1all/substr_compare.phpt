--TEST--
substr_compare
--FILE--
<?php


echo 'substr_compare("abcde", "bc", 1, 2) == '. substr_compare("abcde", "bc", 1, 2) . "   // 0\n";
echo 'substr_compare("abcde", "bcg", 1, 2) == '. substr_compare("abcde", "bcg", 1, 2). "   // 0\n"; 
echo 'substr_compare("abcde", "BC", 1, 2, true) == '. substr_compare("abcde", "BC", 1, 2, true) . "   // 0\n";
echo 'substr_compare("abcde", "bc", 1, 3) == '. substr_compare("abcde", "bc", 1, 3) ."   // 1\n";
echo 'substr_compare("abcde", "cd", 1, 2) == '. substr_compare("abcde", "cd", 1, 2) ."  // -1\n";
echo 'substr_compare("abcde", "abc", 5, 1) == '. substr_compare("abcde", "abc", 5, 1) . "   // warning\n";
?>
--EXPECT--
substr_compare("abcde", "bc", 1, 2) == 0   // 0
substr_compare("abcde", "bcg", 1, 2) == 0   // 0
substr_compare("abcde", "BC", 1, 2, true) == 0   // 0
substr_compare("abcde", "bc", 1, 3) == 1   // 1
substr_compare("abcde", "cd", 1, 2) == -1  // -1

Warning: substr_compare(): The length cannot exceed initial string length in /home/mario/www/berli/upgradephp/doc/tests1all/substr_compare.php on line 9
substr_compare("abcde", "abc", 5, 1) ==    // warning
