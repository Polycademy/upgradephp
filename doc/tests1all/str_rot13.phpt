--TEST--
str_rot13
--FILE--
str_rot13('upgrade.php v4') = "<?php

echo str_rot13('upgrade.php v4');

?>"
--EXPECT--
str_rot13('upgrade.php v4') = "hctenqr.cuc i4"