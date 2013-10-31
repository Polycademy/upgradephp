--TEST--
str_split
--FILE--
<?php


?>
$str = "Hello Friend";
$r = str_split($str, 3);
print_r($r);

<?php
$str = "Hello_Friend";
$r = str_split($str, 3);
print_r($r);
?>
--EXPECT--
$str = "Hello Friend";
$r = str_split($str, 3);
print_r($r);

Array
(
    [0] => Hel
    [1] => lo_
    [2] => Fri
    [3] => end
)
