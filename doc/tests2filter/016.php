<?php
$values = Array(
'a@b.c',	
'abuse@example.com',	
'test!.!@#$%^&*@example.com',	
'test@@#$%^&*())).com',	
'test@.com',	
'test@com',	
'@',	
'[]()/@example.com',	
'QWERTYUIOPASDFGHJKLZXCVBNM@QWERTYUIOPASDFGHJKLZXCVBNM.NET',	
);
foreach ($values as $value) {
	var_dump(filter_var($value, FILTER_VALIDATE_EMAIL));
}

echo "Done\n";
?>
