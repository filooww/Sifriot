<?php

/*
function RegularComparison($reg, $source_field)
{
    $matches = array();
    return (preg_match_all(
    $reg,
    $source_field,
    $matches,
    PREG_OFFSET_CAPTURE) > 0);
}
*/
// $reg_exp0 = "'[!-/]|[:-@]|[A-Z]|[][\\^`]|[{|}~]'";
// $reg_exp1 = "'[''".chr(34)." ]'";

// $reg_exp = "'^[a-z][a-z0-9_]*$'";
// $reg_exp = "'^[a-z][a-z0-9_]'";
// $source_field = "aa_!j";
// $selit = RegularComparison($reg_exp, $source_field); //    /\d{4}/ year
// echo "<br>test 00 ***".$selit."===";

// $reg_exp = "[0-9]abc";

/*
$reg_exp = "'\d+%'";
$source_field = "123";
$selit = RegularComparison($reg_exp, $source_field);
echo "<br>test 00 ***".$selit."===";
*/
$a = 0;
error_reporting(0);
$a = mysqli_connect('lochost', 'root', 'msql06091949');
error_reporting(E_ALL);
echo '<br>test 00 ***';
print_r($a === false);
echo '===';
// \d+abc. \d+
