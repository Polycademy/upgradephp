--TEST--
strftime
--FILE--
<?php


$format = "time=%T date=%D";
$str = strftime($format, time());

$r = strptime($str, $format);

echo "$str\n";
print_r($r);


?>
--EXPECT--
time=06:38:38 date=07/30/07
Array
(
    [tm_sec] => 38
    [tm_min] => 38
    [tm_hour] => 6
    [tm_mday] => 30
    [tm_mon] => 6
    [tm_year] => 107
    [tm_wday] => 1
    [tm_yday] => 210
    [unparsed] => 
)
