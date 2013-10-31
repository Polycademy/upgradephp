--TEST--
array_chunk
--FILE--
<?php

$a = array("a", "b", "c", "d", "e");
$r = array_chunk($a, 2, 1);
echo 'array_chunk($a, 2, 1) = ';
print_r($r);

?>
--EXPECT--
array_chunk($a, 2, 1) = Array
(
    [0] => Array
        (
            [0] => a
            [1] => b
        )

    [1] => Array
        (
            [2] => c
            [3] => d
        )

    [2] => Array
        (
            [4] => e
        )

)
