--TEST--
array_diff_assoc
--FILE--
<?php


?>
$a = array(5=>5, 14=>10000, "ccc"=>555, 555);
$b = array(5=>5, 13=>10000,         15=>555);
$res = array_diff_assoc($a, $b);

print_r($res);
<?php
$a = array(5=>5, 14=>10000, "ccc"=>555, 555);
$b = array(5=>5, 13=>10000,         15=>555);
$res = array_diff_assoc($a, $b);
print_r($res);
?>
--EXPECT--
$a = array(5=>5, 14=>10000, "ccc"=>555, 555);
$b = array(5=>5, 13=>10000,         15=>555);
$res = array_diff_assoc($a, $b);

print_r($res);
Array
(
    [14] => 10000
    [ccc] => 555
)
