--TEST--
file_put_contents
--FILE--
<?php

$FN = "/tmp/test_fpc";

echo 'file_put_contents("/tmp/test_fpc", "abc-test-text");'."\n";
file_put_contents($FN, "abc-test-text");

echo 'echo file_get_contents("/tmp/test_fpc");'."\n => ";
echo file_get_contents($FN);

unlink($FN);

?>
--EXPECT--
file_put_contents("/tmp/test_fpc", "abc-test-text");
echo file_get_contents("/tmp/test_fpc");
 => abc-test-text