--TEST--
mhash
--FILE--
<?php

echo "HMAC-MD5 test cases (RFC2104/page 9):\n\n";

# 1
$k = str_pad("", 16, chr(0x0b));
$text = "Hi There";
$end = unpack("H32", mhash(MHASH_MD5, $text, $k));
echo "key =     0x0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b\n";
echo "data =    \"$text\"\n";
echo "digest =  0x$end[1]\n";
echo "should =  0x9294727a3638bb1c13f48ef8158bfc9d\n\n";

# 2
$k = "Jefe";
$text = "what do ya want for nothing?";
$end = unpack("H32", mhash(MHASH_MD5, $text, $k));
echo "key =     \"$k\"\n";
echo "data =    \"$text\"\n";
echo "digest =  0x$end[1]\n";
echo "should =  0x750c783e6ab0b503eaa86e310a5db738\n\n";



?>
--EXPECT--
HMAC-MD5 test cases (RFC2104/page 9):


Fatal error: Call to undefined function mhash() in /home/mario/www/berli/upgradephp/doc/tests1all/mhash on line 8
