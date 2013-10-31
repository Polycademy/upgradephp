--TEST--
class_parents
--FILE--
<?php

class a {}
class x extends a {}
class y extends x {}
class z extends a {}
class eve extends y {}

$y = new y;
$r = class_parents($y);
print_r($r);

?>
--EXPECT--
Array
(
    [x] => x
    [a] => a
)
