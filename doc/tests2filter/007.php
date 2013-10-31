<?php

var_dump(input_has_variable(INPUT_GET, "a"));
var_dump(input_has_variable(INPUT_GET, "abc"));
var_dump(input_has_variable(INPUT_GET, "nonex"));
var_dump(input_has_variable(INPUT_GET, " "));
var_dump(input_has_variable(INPUT_GET, ""));
var_dump(input_has_variable(INPUT_GET, array()));

var_dump(input_has_variable(INPUT_POST, "b"));
var_dump(input_has_variable(INPUT_POST, "bbc"));
var_dump(input_has_variable(INPUT_POST, "nonex"));
var_dump(input_has_variable(INPUT_POST, " "));
var_dump(input_has_variable(INPUT_POST, ""));
var_dump(input_has_variable(INPUT_POST, array()));

var_dump(input_has_variable(-1, ""));
var_dump(input_has_variable("", ""));
var_dump(input_has_variable(array(), array()));
var_dump(input_has_variable(array(), ""));
var_dump(input_has_variable("", array()));

echo "Done\n";
?>
