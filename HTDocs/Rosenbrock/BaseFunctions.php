<?php
function AbsV($x)
{
	if (substr($x, 0, 1) == "-") return substr($x, 1); 
	else return $x;
}
function EX($x, $eps, $big_scale)
{
	if (bccomp($x, "0") == 0) return "1";
	$arg = (substr($x, 0, 1) == "-") ? substr($x, 1) : $x;
	$n = 0;
	$u = "1";
	$s = "0";
	do
	{
		if ($n % 2 == 0) $s = bcadd($s, $u);
		else $s = bcsub($s, $u);
		$n++;
		$u = bcmul(bcdiv($arg, (string)$n), $u);
        $relerr = bcdiv($u, $s, $big_scale);
	} while (bccomp(AbsV($relerr), $eps) > 0);
	if (substr($x, 0, 1) != "-") $s = bcdiv("1", $s, $big_scale);
	return $s;
}
function AX($a, $x, $ln_v, $h_order, $lndec, $h_k, $eps, $big_scale)
{
	$arr = explode(".", $x);
	if (count($arr) == 1) $arr[] = "";
	if ($arr[0] == "") $arr[0] = "0";
	if ($arr[1] == "") $arr[1] = "0";
	if ($arr[0] == "0" && $arr[1] == "0") return "1";
	$ai = bcpow($a, $arr[0]);
	$f = ".".$arr[1];
	$lna = LN($a, $ln_v, $h_order, $lndec, $h_k, $eps);
	$af = EX(bcmul($f, $lna), $eps, $big_scale);
	return bcmul($ai, $af);
}
function SeriesLN($y, $eps)
{
	$y2 = $y * $y;
	$g = 1 / (1 - $y2);
	$s = 0;
	$k = 1;
	$u = $y;
	do
	{
		$s += $u;
		$k_prev = $k;
		$k += 2;
		$kk = $k_prev / $k;
		$u = $y2 * $kk * $u;
		$r = $g * $u / $kk;
	} while ($r > $eps);
	return $s;
}
function LN($x, $ln_v, $h_order, $lndec, $h_k, $eps)
{
//echo "<br>test LN 00 ***".$x;
    if ($x == "0") return "";
	$arr = explode("E", NormNum($x, strlen($x) + $h_k + 3)); // calc_scale ?????
//echo "<br>test LN 01 ***"; print_r($arr);
	if (count($arr) == 1) $arr[] = "0";
	$z = GetMantissaLN($arr[0], $h_order, $h_k, $ln_v);
//echo "<br>test LN 02 ***".$z;
    $z = bcadd($z, bcmul($lndec, $arr[1]));
//echo "<br>test LN 03 ***".$z;
	return $z;
}
function GetMantissaLN($x, $h_order, $h_k, $ln_v)
{
//echo "<br>test GetMantissaLN 00 ***"; print_r($h_k);
    if (bccomp($x, "10") >= 0) return (string)end($ln_v);
    elseif (bccomp($x, "1") < 0) return (string)reset($ln_v);
	else
    {
//echo "<br>test GetMantissaLN 01 ***".$x;
        $r = pow(10, -$h_order);
//echo "<br>test GetMantissaLN 02 ***".$r;
        $pos_dp = strpos($x, ".");
//echo "<br>test GetMantissaLN 03 ***".$pos_dp."===";
////        $y = substr($x, 0, $pos_dp + $h_k);
        $y = $x;
//echo "<br>test GetMantissaLN 04 ***".$y;
        $y0 = substr($x, 0, $pos_dp + $h_order + 1);
//echo "<br>test GetMantissaLN 05 ***".$y0;
        $y1 = bcadd($y0, (string)$r);
        $pos_dp = strpos($y1, ".");
        $y1 = substr($y1, 0, $pos_dp + $h_order + 1);
//echo "<br>test GetMantissaLN 06 ***".$y1;
        $ln0 = $ln_v[ANull($y0, $h_order, 10)];
        if ($y1 == "10.000") $ind = $y1;
        else $ind = ANull($y1, $h_order, 10);
//echo "<br>test GetMantissaLN 06-1 ***".$ind;
        $ln1 = $ln_v[$ind];
//echo "<br>test GetMantissaLN 07 ***".$ln0."===".$ln1."===";
        if (bccomp($y, $y0) == 0) return (string)$ln0;
        elseif (bccomp($y, $y1) == 0) return (string)$ln1;
        else
        {
            $coef = ($ln1 - $ln0) / $r;
//echo "<br>test GetMantissaLN 08 ***".$coef."===";
            $add_x = bcmul((string)$coef, bcsub($y, $y0));
//echo "<br>test GetMantissaLN 09 ***".$add_x."===";
            $lnx = bcadd((string)$ln0, $add_x);
//echo "<br>test GetMantissaLN 10 ***".$lnx."===";
            return $lnx;
        }
    }
}
function LN10($eps)
{
	$lndec = SeriesLN((float)(9/11), $eps);
	return 2 * $lndec;
}
function Dividers($num)
{
	for ($i = 1; $i*$i <= $num; $i++) if ($num % $i == 0) $arr_div[] = $i;
	$i = count($arr_div) - 1;
	while ($i > -1)
	{
		$n = $num / $arr_div[$i];
		if (!in_array($n, $arr_div)) $arr_div[] = $n;
		$i--;
	}
	if (!in_array($num, $arr_div)) $arr_div[] = $num;
	return $arr_div;
}
function MatrixByVector($Matr, $Vect)
{
	for ($i = 0; $i < count($Vect); $i++)
	{
		$s = "0";
		for ($j = 0; $j < count($Vect); $j++) $s = bcadd($s, bcmul($Matr[$i][$j], $Vect[$j]));
		$arr[] = $s;
	}
	return $arr;
}
function FractionOrder($n)
{
    $s = (string)$n;
    $pos = strpos(ltrim(rtrim($s)), ".");
    if ($pos === false) return 0;
    else return (strlen($s) - $pos - 1);
}
function LN_values($x_min, $x_max, $step_round, $step, $lndec, $eps)
{
    $ln_v = array();
    for ($x = $x_min; $x < $x_max; $x += $step)
    {
        $y = round($x, $step_round);
        $z = ($y - 1) / ($y + 1);
	    $ln_v[ANull((string)$y, $step_round, $x_max)] = 2 * SeriesLN($z, $eps);
    }
    return $ln_v;
}
function ANull($x, $step_round, $x_max)
{
    if ($x == $x_max) return (string)$x.".".str_repeat("0", $step_round);
    if (strlen($x) == 1) return $x.".".str_repeat("0", $step_round);
    if (strlen($x) == 2) return $x.str_repeat("0", $step_round);
    if ($step_round > strlen($x) - 2) return $x.str_repeat("0", $step_round - strlen($x) + 2);
    return $x;
}
function SerieByInverseNumbers($x, $d, $eps)
{
    if ($x <= 0. || $x > 1.) return array();
    if ($x == 1.) return array(1=>0);
    $xv = $x;
    $n = 1;
    $s = 0.0;
    $a_n_arr = array();
    do
    {
        $a_n_arr[$n] = (integer)($xv / $d);
        $ad = $a_n_arr[$n] * $d;
        $xv = $xv - $ad;
        $s = $s + $ad;
        $n++;
        $d = $d / 2.0;
    } while ($x - $s > $eps);
    return $a_n_arr;
}

?>
