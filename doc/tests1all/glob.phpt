--TEST--
glob
--FILE--
<?php

echo 'glob("../tests/*m*") = ';
$r = glob("../tests/*m*");
print_r($r);


?>
--EXPECT--
glob("../tests/*m*") = Array
(
)
