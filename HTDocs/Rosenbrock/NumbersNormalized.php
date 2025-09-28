<?php
function TrimRightZeros($cn)
{
	for ($i = strlen($cn) - 1; $i > -1 && substr($cn, $i, 1) == "0"; $i--);
	if ($i == -1) return "";
	else return substr($cn, 0, $i + 1);
}
function TrimLeftZeros($cn)
{
	for ($i = 0; $i < strlen($cn) && substr($cn, $i, 1) == "0"; $i++);
	if ($i == strlen($cn)) return "";
	else return substr($cn, $i);
}
function GetNumberParts($cnn)
{
	$struct_parts = array("i"=>"", "f"=>"");
	$pos = strpos($cnn, ".");
	if ($pos === false) $struct_parts['i'] = $cnn;
	else
	{
		$struct_parts['i'] = TrimLeftZeros(substr($cnn, 0, $pos));
		$struct_parts['f'] = TrimRightZeros(substr($cnn, $pos + 1));
	}
	return $struct_parts;
}
function GetNormalParts($struct_parts)
{
	$struct_normal = array("m"=>"", "e"=>"");
	if ($struct_parts['i'] == "" && $struct_parts['f'] != "")
	{
		$str = TrimLeftZeros($struct_parts['f']);
		$struct_normal['m'] = substr($str, 0, 1).".".substr($str, 1);
		if (strlen($struct_parts['f']) - strlen($str) + 1 == 0) $struct_normal['e'] = "";
		else $struct_normal['e'] = "E-".(string)(strlen($struct_parts['f']) - strlen($str) + 1);
	}
	elseif ($struct_parts['i'] != "" && $struct_parts['f'] == "")
	{
		$str = TrimRightZeros($struct_parts['i']);
		$struct_normal['m'] = substr($str, 0, 1).".".substr($str, 1);
		if (strlen($struct_parts['i']) == 1) $struct_normal['e'] = "";
		else $struct_normal['e'] = "E+".(string)(strlen($struct_parts['i']) - 1);
	}
	else
	{
		$struct_normal['m'] = substr($struct_parts['i'], 0, 1).".".substr($struct_parts['i'], 1).$struct_parts['f'];
		if (strlen($struct_parts['i']) == 1) $struct_normal['e'] = "";
		else $struct_normal['e'] = "E+".(string)(strlen($struct_parts['i']) - 1);
	}
	return $struct_normal;
}
function RoundNormNum($mantissa, &$struct_normal)
{
	$mantissa = bcadd($mantissa, "0.".str_repeat("0", strlen($mantissa) - 3)."1");
	$mantissa = TrimRightZeros($mantissa);
	$compare_mantissa = bccomp($mantissa, "10");
	if ($compare_mantissa >= 0)
	{
		$struct_normal['m'] = substr($mantissa, 0, 1).".".str_replace(".", "", substr($mantissa, 1));
		if ($struct_normal['e'] == "") $new_exp = 1;
		elseif (substr($struct_normal['e'], 1, 1) == "-") $new_exp = (integer)substr($struct_normal['e'], 2) - 1;
		else $new_exp = (integer)substr($struct_normal['e'], 2) + 1;
		if ($new_exp == 0) $struct_normal['e'] = "";
		else $struct_normal['e'] = "E".(($new_exp < 0) ? "-" : "+").(string)$new_exp;
	}
	else $struct_normal['m'] = $mantissa;
}
function ReturnResult($struct_normal, $place_size)
{
	$i = strpos($struct_normal['m'].$struct_normal['e'], "E");
	if ($i !== false) $struct_normal['m'] .= "0";
	if (strlen($struct_normal['m'].$struct_normal['e']) > $place_size + 1) return str_repeat("*", $place_size);
	else return $struct_normal['m'].$struct_normal['e'];

}
function NormNum($cn, $place_size)
{
	if ($cn == "") return "0.0";
	$sign_symbol = (substr($cn, 0, 1) == "-") ? "-" : "";
	$cnn = (substr($cn, 0, 1) == "+" || substr($cn, 0, 1) == "-") ? substr($cn, 1) : $cn;
	$cnn = TrimLeftZeros($cnn);
	$struct_parts = GetNumberParts($cnn);
	if ($struct_parts['i'] == "" && $struct_parts['f'] == "") return "0.0";
	$struct_normal = GetNormalParts($struct_parts);
	if (strlen($struct_normal['m']) + strlen($struct_normal['e']) > $place_size)
	{
		$new_mantissa = TrimRightZeros(substr($struct_normal['m'], 0, $place_size - strlen($struct_normal['e'])));
		if (substr($struct_normal['m'], strlen($new_mantissa), 1) > "4") RoundNormNum($new_mantissa, $struct_normal);
		else $struct_normal['m'] = $new_mantissa;
	}
	$res = ReturnResult($struct_normal, $place_size);
    $res = $sign_symbol.$res;
	return $res;
}
?>
