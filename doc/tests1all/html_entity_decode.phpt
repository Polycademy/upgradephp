--TEST--
html_entity_decode
--FILE--
<?php

echo 'html_entity_decode("&lt;&amp;&gt;&auml;&szlig;") = ';
echo html_entity_decode("&lt;&amp;&gt;&auml;&szlig;");

?>
--EXPECT--
html_entity_decode("&lt;&amp;&gt;&auml;&szlig;") = <&>дя