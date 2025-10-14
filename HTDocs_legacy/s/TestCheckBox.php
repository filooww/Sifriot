<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<?php
function TestBool($b)
{
	if ($b) return "Y";
	else return "N";
}
function NewTableID($arr, $max_m)
{
    if (count($arr) == 0) return $max_m + 1;
    $key_arr = array();
    foreach ($arr as $k => $v) if (gettype($k) == "integer" && (integer)$k > $max_m) $key_arr[] = $k;
    $prev = $max_m;
    for ($i = 0; $i < count($key_arr); $i++)
    {
        if ($key_arr[$i] - $prev > 1) return $prev + 1;
        $prev = $key_arr[$i];
    }
    return $prev + 1;
}

$arr = array
(
 "new" => array("YYYYY", "", "", false),
 0 => array("(special)", "(special)", "", false),
 -1 => array("English", "English", "main language", false),
 2 => array("English", "English", "main language", false),
 "qqq" => array("QQQQQQ", "", "", false),
 3 => array("Hebrew", "Hebrew", "", false)
);

$new_id = NewTableID($arr, 1);
echo "<br>test 00 ***"; print_r($arr);
echo "<br>test 01 ***".$new_id."===";

/*
$arr = array

(
 0 => array("(special)", "(special)", "", false),
 1 => array("English", "English", "main language", false),
 2 => array("Russian", 1 => "Russian", "", false),
 3 => array("Hebrew", "Hebrew", "", false),
 "new" => array("YYYYY", "", "", false)
);
*/
