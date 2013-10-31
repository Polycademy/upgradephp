--TEST--
scandir
--FILE--
<?php

$r = scandir("..");
echo "..\n\n";
print_r($r);

?>
--EXPECT--
..

Array
(
    [0] => .
    [1] => ..
    [2] => README
    [3] => ctest
    [4] => devtools
    [5] => gettext.txt
    [6] => index.xhtml
    [7] => runtest
    [8] => tests0old
    [9] => tests1all
    [10] => tests2filter
)
