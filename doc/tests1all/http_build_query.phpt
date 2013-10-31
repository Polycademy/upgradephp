--TEST--
http_build_query
--FILE--
<?php


?>
$data = array('foo', 'bar', "sub"=>array('b', 'c'), 'cow' => 'milk', 'php' =>'hypertext processor');
echo http_build_query($data, "num_");

<?php
$data = array('foo', 'bar', "sub"=>array('b', 'c'), 'cow' => 'milk', 'php' =>'hypertext processor');
echo http_build_query($data, "num_");
?>
--EXPECT--
$data = array('foo', 'bar', "sub"=>array('b', 'c'), 'cow' => 'milk', 'php' =>'hypertext processor');
echo http_build_query($data, "num_");

num_0=foo&num_1=bar&sub%5B0%5D=b&sub%5B1%5D=c&cow=milk&php=hypertext+processor