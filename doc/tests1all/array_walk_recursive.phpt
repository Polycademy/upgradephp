--TEST--
array_walk_recursive
--FILE--
<?php

$a = array(
  "x1",
  "x2\\",
  array(
     "x3aaa1",
     "x3a\\a2",
  ),
  "x4\+end",
);

echo "__start:\n";
array_walk_recursive($a, "outp");

function outp(&$a) {
   var_dump($a);
   $a = stripslashes($a);
}

echo "\n__result:\n";
var_dump($a);

?>
--EXPECT--
__start:
string(2) "x1"
string(3) "x2\"
string(6) "x3aaa1"
string(6) "x3a\a2"
string(7) "x4\+end"

__result:
array(4) {
  [0]=>
  string(2) "x1"
  [1]=>
  string(2) "x2"
  [2]=>
  &array(2) {
    [0]=>
    string(6) "x3aaa1"
    [1]=>
    string(5) "x3aa2"
  }
  [3]=>
  string(6) "x4+end"
}
