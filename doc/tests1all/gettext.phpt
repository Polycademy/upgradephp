--TEST--
gettext
--FILE--
<?php

#-- load
echo "bindtextdomain(\"tar\");\n";
bindtextdomain("tar");
textdomain("tar");

#-- query
echo "_(\"stdin\") == \"";
echo _("stdin");
echo "\"\n";

?>
--EXPECT--
bindtextdomain("tar");

Warning: Wrong parameter count for bindtextdomain() in /home/mario/www/berli/upgradephp/doc/tests1all/gettext on line 5
_("stdin") == "stdin"
