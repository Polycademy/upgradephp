--TEST--
fnmatch
--FILE--
<?php

$text = "Some text goes here...";
echo '$text = "Some text goes here...";';
echo "\nfnmatch('go*', $text) = ". (fnmatch('go*', $text) ?1:0);
echo "\nfnmatch('*go', $text) = ". (fnmatch('*go', $text) ?1:0);
echo "\nfnmatch('*go*', $text) = ". (fnmatch('*go*', $text) ?1:0);
echo "\nfnmatch('*te?x*', $text) = ". (fnmatch('*te?x*', $text) ?1:0);
echo "\nfnmatch('*te?t*', $text) = ". (fnmatch('*te?t*', $text) ?1:0);
echo "\nfnmatch('So?m*', $text) = ". (fnmatch('So?m*', $text) ?1:0);
echo "\nfnmatch('So*m*', $text) = ". (fnmatch('*So*m*', $text) ?1:0);

?>
--EXPECT--
$text = "Some text goes here...";
fnmatch('go*', Some text goes here...) = 0
fnmatch('*go', Some text goes here...) = 0
fnmatch('*go*', Some text goes here...) = 1
fnmatch('*te?x*', Some text goes here...) = 0
fnmatch('*te?t*', Some text goes here...) = 1
fnmatch('So?m*', Some text goes here...) = 0
fnmatch('So*m*', Some text goes here...) = 1