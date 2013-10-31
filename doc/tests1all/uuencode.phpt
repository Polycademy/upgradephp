--TEST--
uuencode
--FILE--
<?php

$uu = convert_uuencode("ABCDEFGHIJ");
echo "\$uu = \"$uu\"\n\n";

$text = convert_uudecode($uu);
echo "\$text = \"$text\"\n\n";

?>
--EXPECT--
$uu = "*04)#1$5&1TA)2@``
`
"

$text = "ABCDEFGHIJ"

