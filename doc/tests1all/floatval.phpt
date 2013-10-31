--TEST--
floatval
--FILE--
floatval("1234.567abc") = <?php

$str = "1234.567abc";
echo floatval($str);

?>
--EXPECT--
floatval("1234.567abc") = 1234.567