--TEST--
get_headers
--FILE--
<?php

$r = get_headers("http://localhost/");
print_r($r);

?>
--EXPECT--
Array
(
    [0] => HTTP/1.1 200 OK
    [1] => Date: Mon, 30 Jul 2007 04:38:37 GMT
    [2] => Server: aEGiS_nanoweb/2.2.8 (Linux; PHP/5.2.1)
    [3] => Content-Type: text/html
    [4] => Last-Modified: Fri, 01 Sep 2006 16:22:06 GMT
    [5] => ETag: "19e700:6r0x:44f85e2e:99d"
    [6] => Accept-Ranges: bytes
    [7] => Connection: close
    [8] => Content-Length: 2461
)
