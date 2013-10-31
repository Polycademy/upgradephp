--TEST--
base16
--FILE--
<?php
include "../../ext/nonstandard/base64.php";

echo base16_encode("123");
echo "\n";
echo base16_decode("313233");

?>
--EXPECT--
313233

123
