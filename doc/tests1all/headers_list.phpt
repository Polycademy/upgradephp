--TEST--
headers_list
--FILE--
<?php

header("Content-Type: text/x-display-on-console");
header("X-Server: upgradephp-test/2.75.01d");
echo "header(\"...\");\n\n";

echo "\$r = headers_list();\n\n";
error_reporting(E_ALL);
$r = headers_list();
print_r($r);

?>
--EXPECT--
header("...");

$r = headers_list();

Array
(
)
