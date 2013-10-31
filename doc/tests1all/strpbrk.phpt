--TEST--
strpbrk
--FILE--
<?php

?>
$text = 'This is a Simple text.';

echo strpbrk($text, 'mi');
<?php
$text = 'This is a Simple text.';
echo strpbrk($text, 'mi')."\n\n";

echo 'echo strpbrk($text, "S");'."\n";
echo strpbrk($text, "S")."\n\n";
?>
--EXPECT--
$text = 'This is a Simple text.';

echo strpbrk($text, 'mi');
is is a Simple text.

echo strpbrk($text, "S");
Simple text.

