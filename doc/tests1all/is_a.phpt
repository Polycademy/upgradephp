--TEST--
is_a
--FILE--
<?php

class y {}
class x extends y {}
$x = new x;

echo is_a($x, "x") ? "works" : "failed";
echo "\n";
echo is_a($x, "y") ? "works" : "failed";

?>
--EXPECT--
works
works