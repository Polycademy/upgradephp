--TEST--
strripos
--FILE--
<?php

echo 'strripos("aaaabc_DEFff_ABCccc", "a") == ';
echo strripos("aaaabc_DEFff_ABCccc", "a");

?>
--EXPECT--
strripos("aaaabc_DEFff_ABCccc", "a") == 13