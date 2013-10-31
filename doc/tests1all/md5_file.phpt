--TEST--
md5_file
--FILE--
md5_file(__FILE__) = <?php

echo md5_file(__FILE__);

?>
--EXPECT--
md5_file(__FILE__) = 187c2e06df4fb02560064398685e5113