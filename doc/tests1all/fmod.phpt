--TEST--
fmod
--FILE--
<?php

$r = fmod(5.7, 1.3);
echo "fmod(5.7, 1.3) = $r\n";

?>
--EXPECT--
fmod(5.7, 1.3) = 0.5
