<?php
function Calc_k($t, $k_max, $ln_v, $h_order, $lndec, $m, $eps)
{
//echo "<br><br><br><br>test Calc_k 00 ***".$t;
    if ($t == 0) return "1";
//echo "<br><br>test Calc_k 01 ***";
    $ln_t = LN((string)(2*$t), $ln_v, $h_order, $lndec, $h_order, $eps);
//echo "<br><br>test Calc_k 02 ***";
    $ln_m = LN($m, $ln_v, $h_order, $lndec, $h_order, $eps);
    $k = 1.;
    $fl = true;
    while ($k <= $k_max && $fl)
    {
        $z = RadiusCompare($k, $ln_t, $ln_m, $ln_v, $h_order, $lndec, $eps);
        if ($z < 0) $k += 1.;
        else $fl = false;
    }
    if ($k == 1) return "1";
    if ($k >= $k_max) return (string)$k_max;
    $k = BinSearch($k - 1., $k, $ln_t, $ln_m, $ln_v, $h_order, $lndec, $eps);
    return (string)$k;
}
function RadiusCompare($x, $ln_t, $ln_m, $ln_v, $h_order, $lndec, $eps)
{
    $z = $x * $ln_t;
//echo "<br><br>test RadiusCompare 00 ***";
    $z = $z - ($x / 2 - 1) * (float)LN((string)$x, $ln_v, $h_order, $lndec, $h_order, $eps);
    $z = $z - $ln_m;
    return $z;
}
function BinSearch($low_value, $hight_value, $ln_t, $ln_m, $ln_v, $h_order, $lndec, $eps)
{
    $low_v = $low_value;
    $hight_v = $hight_value;
    do
    {
        $middle_v = round(($low_v + $hight_v) / 2, $h_order);
        if (isset($p_middle) && $p_middle == $middle_v) return $middle_v;
        $p_middle = $middle_v;
        $q = round($hight_v - $low_v, $h_order);
        if ($q <= $eps) return $middle_v;
        $test = RadiusCompare($middle_v, $ln_t, $ln_m, $ln_v, $h_order, $lndec, $eps);
        if ($test > 0) $hight_v = $middle_v;
        else $low_v = $middle_v;
    } while ($hight_v - $low_v > $eps);
    return $middle_v;
}
//=========================
function CalcConcav($i, &$k_values, &$flag_concave, &$point_concave, $new_value, $di)
{
    $k_values[0] = $k_values[1];
    $k_values[1] = $k_values[2];
    $k_values[2] = $new_value;
    $fl = bcsub(bcdiv(bcadd($k_values[0], $k_values[2]), "2"), $k_values[1]);
    if (substr($flag_concave, 0, 1) != "-" && substr($fl, 0, 1) == "-" || substr($flag_concave, 0, 1) == "-" && substr($fl, 0, 1) != "-") $point_concave[] = (float)(($i - 1) / $di);
    $flag_concave = $fl;
}
//=========================
function Calc_N($Mm, $Mm1, $D, $C, $expDz, $ln_v, $h_order, $h_k, $lndec, $eps, $big_scale)
{
echo "<br><br>e^Dt = ".$expDz;
    $x = bcdiv($Mm, bcadd($expDz, $Mm1));
echo "<br>M/(e^Dt+M-1) = ".$x;
    $p = bcdiv($C, $D);
echo "<br>C/D = ".$p;
    $x = AX($x, $p, $ln_v, $h_order, $lndec, $h_k, $eps, $big_scale);
echo "<br>(M/(e^Dt+M-1))^(C/D) = ".$x;
    $coef = bcdiv($Mm1, $Mm);
echo "<br>(M-1)/M = ".$coef;
    $x = bcmul($coef, $x);
echo "<br>((M-1)/M)*(M/(e^Dt+M-1))^(C/D) = ".$x;
    $x = bcsub("1", $x);
echo "<br>1 - ((M-1)/M)*(M/(e^Dt+M-1))^(C/D) = ".$x;
    $x = bcmul($Mm, $x);
echo "<br>N = ".$x;
    return $x;
}
?>
