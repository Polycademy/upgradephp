--TEST--
vprintf
--FILE--
<?php

vprintf("%d, %d\n", array(17, 5));

?>
--EXPECT--
17, 5
