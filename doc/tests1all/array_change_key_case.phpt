--TEST--
array_change_key_case
--FILE--
<?php

$a = array("low"=>32, "CamelCase"=>"Wiki");
print_r($a);
echo "\n";
$a = array_change_key_case($a, CASE_UPPER);
print_r($a);

?>
--EXPECT--
Array
(
    [low] => 32
    [CamelCase] => Wiki
)

Array
(
    [LOW] => 32
    [CAMELCASE] => Wiki
)
