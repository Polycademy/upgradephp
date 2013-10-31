--TEST--
json
--FILE--
<?php

$a = array(
  "x" => "y",
  "text" => "Hallöchen
",
  "abc" => TRUE,
  "int" => 512,
  "float" => 3.14159,
  "null" => NULL,
  "sub" => array(1,2,3),
  "sub2" => array(1,2,"a"=>3),
);

for ($n=0; $n<1000; $n++)
$js = json_encode($a);

echo "$js\n";

?>
--EXPECT--
{"x":"y","text":"Hall","abc":true,"int":512,"float":3.14159,"null":null,"sub":[1,2,3],"sub2":{"0":1,"1":2,"a":3}}
