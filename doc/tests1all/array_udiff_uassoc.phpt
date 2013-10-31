--TEST--
array_udiff_uassoc
--FILE--
<?php

#-- from manual --

class cr {
    var $priv_member;
    function cr($val)
    {
        $this->priv_member = $val;
    }
}
    function comp_func_cr($a, $b)
    {
        if ($a->priv_member === $b->priv_member) return 0;
        return ($a->priv_member > $b->priv_member)? 1:-1;
    }
    
    function comp_func_key($a, $b)
    {
        if ($a === $b) return 0;
        return ($a > $b)? 1:-1;
    }


$a = array(
   "0.1" => new cr(9),
   "0.5" => new cr(12),
   0 => new cr(23),
   1=> new cr(4),
   2 => new cr(-15),
);
$b = array(
   "0.2" => new cr(9),
   "0.5" => new cr(22),
   0 => new cr(3),
   1=> new cr(4),
   2 => new cr(-15),
);

$result = array_udiff_uassoc($a, $b, "comp_func_cr", "comp_func_key");
$expected = array (
  '0.1' => new cr(9),
  '0.5' => new cr(12),
  0 => new cr(23),
);
print_r($result);

if ($result == $expected) {
   echo "\n--> works correctly\n";
}

?>
--EXPECT--
Array
(
    [0.1] => cr Object
        (
            [priv_member] => 9
        )

    [0.5] => cr Object
        (
            [priv_member] => 12
        )

    [0] => cr Object
        (
            [priv_member] => 23
        )

)

--> works correctly
