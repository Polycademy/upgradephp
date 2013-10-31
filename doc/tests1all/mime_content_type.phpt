--TEST--
mime_content_type
--FILE--
<?php

$t = mime_content_type(__FILE__);
echo "mime_content_type(__FILE__) = $t\n\n";

?>
--EXPECT--
mime_content_type(__FILE__) = text/plain

