--TEST--
stripos
--FILE--
<?php

$l = stripos("ABCDEFef", "e");
echo "stripos(\"ABCDEFef\", \"e\") == $l\n\n";

$r = strripos("ABCDefEF", "e");
echo "strripos(\"ABCDefEF\", \"e\") == $r\n\n";

?>
--EXPECT--
stripos("ABCDEFef", "e") == 4

strripos("ABCDefEF", "e") == 6

