--TEST--
str_shuffle
--FILE--
str_shuffle("ABCDEF") == "<?php

srand(17);
echo str_shuffle("ABCDEF");

?>"
--EXPECT--
str_shuffle("ABCDEF") == "FBCEAD"