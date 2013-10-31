--TEST--
str_word_count
--FILE--
<?php


$r = str_word_count("This is-a sample string.", 2);
echo 'str_word_count("This is-a sample string.", 2) = ';
print_r($r);


?>
--EXPECT--
str_word_count("This is-a sample string.", 2) = Array
(
    [0] => This
    [5] => is-a
    [10] => sample
    [17] => string
)
