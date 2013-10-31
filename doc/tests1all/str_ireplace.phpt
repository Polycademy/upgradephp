--TEST--
str_ireplace
--FILE--
<?php

$text = "ABC_abc_DEF_def_AAaaAAaa_DDddEEee";
echo "\$text = \"$text\";\n";
echo 'str_ireplace("abc", "xxx", $text);'."\n\n";

$text = str_ireplace("abc", "xxx", $text);
echo "\$text = \"$text\";\n";

?>
--EXPECT--
$text = "ABC_abc_DEF_def_AAaaAAaa_DDddEEee";
str_ireplace("abc", "xxx", $text);

$text = "xxx_xxx_DEF_def_AAaaAAaa_DDddEEee";
