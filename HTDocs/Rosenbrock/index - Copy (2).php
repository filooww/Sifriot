<?php
require_once("BaseFunctions.php");
require_once("NumbersNormalized.php");
require_once("ServiceFunctions.php");
require_once("ModelFunctions.php");
require_once("CalcFunctions.php");

$k_max = 100;
$eps_log = .001;
$h_order = 3;
$eps_k = pow(10, -16);
$lndec = LN10($eps_k);
$ln_v = LN_values(1, 10, $h_order, $eps_log, $lndec, $eps_k);
$k_values = array("k"=>array(0, 0, 0), "R"=>array(0,0,0));
$flag_concave = array("k"=>0, "R"=>0);
$point_concave = array("k"=>0, "R"=>0);

$res_format = "%6d %-3s %-21s %-21s %-21s %-17s %-17s";
$eps = "0.".str_repeat("0", 200)."1";
$common_calc_scale = 200;
bcscale($common_calc_scale);
$time_limit = 200000;
set_time_limit($time_limit);
$big_scale = 200;
$Mm = bcmul("6.513", bcpow("10", "184"));
$tm = "854";
$te = 5; // $te = 900;
$D = "0.5";
$di = 8192;
$C0 = "0.4995";
$Ce = "0.4933";
$Ci = "0.5";
$ze = "1.0";
$zi = "3.0";

$er1 = bcsub($C0, $Ce);
$er2 = bcsub($Ci, $C0);
$ze2 = bcpow($ze, 2);
$ze3 = bcmul($ze2, $ze);
$ze4 = bcmul($ze3, $ze);
$zi2 = bcpow($zi, 2);
$zi3 = bcmul($zi2, $zi);
$zi4 = bcmul($zi3, $zi);
$zi5 = bcmul($zi4, $zi);
$er3 = f_er3($ze, $ze2, $ze3, $ze4, $zi, $zi2, $zi3, $zi4);
$de2 = bcmul($ze2, bcmul($zi2, $er3));
$de3 = bcmul($ze, bcmul($zi, $de2));
$de4 = bcmul($zi, $de3);
$de5 = $de4;
$de6 = $de5;
$C6 = f_C6($de6, $er1, $er2, $ze, $ze2, $ze3, $zi, $zi2, $zi4);
$C5 = f_C5($de5, $er1, $er2, $ze, $ze2, $ze3, $zi, $zi2, $zi3, $zi4);
$C4 = f_C4($de4, $er1, $er2, $ze, $ze2, $ze3, $ze4, $zi, $zi2, $zi3, $zi4, $zi5);
$C3 = f_C3($de3, $er1, $er2, $ze, $ze2, $ze3, $ze4, $zi, $zi2, $zi3, $zi5);
$C2 = f_C2($de2, $er1, $er2, $ze, $ze2, $ze4, $zi, $zi2, $zi5);

$Cz = $C0;
$J = $di * $tm;
$dz = bcdiv("1", (string)$J);
$k = 1;
$N = 1;
$z = 0;
$M = 1;
$expDz = "1";
$fh = fopen("f_store".".txt", "a");
fwrite($fh, "\r\nC0 = ".$C0."   Ce = ".$Ce."   Ci = ".$Ci."   C6 = ".NormNum($C6, 30)."   C5 = ".NormNum($C5, 25)."   C4 = ".NormNum($C4, 25)."   C3 = ".NormNum($C3, 25)."   C2 = ".NormNum($C2, 25)."   zi = ".$zi."   ze = ".$ze."   J = ".$J);
fclose($fh);
$tt = time();
$tt0 = $tt;
WriteTable("f_store", $res_format, 0,  "1", "1", $C0, "1", "0");
$expDdz = EX(bcmul($D, bcmul($tm, $dz)), $eps, $big_scale);
$i_max = $te *$di;
$flag_end = false;
for ($i = 1; $i <= $i_max && !$flag_end; $i++)
{
    $dCz = f_dCz($i, $dz, $C2, $C3, $C4, $C5, $C6);
    $j = f_j($tm, $k, $N, $M, $Mm, $dCz, $Cz);
    $a = f_a($dz, $j);
    $b = f_b($a);
    $VekD = VFP($k,  $N,  $z,  $M,  $Cz,  $tm, $C0, $C2, $C3, $C4, $C5, $C6, $zi, $Ci, $zi, $Mm);
    $expDz = bcmul($expDz, $expDdz);
    $M = bcdiv($Mm, bcadd("1", bcdiv(bcsub($Mm, "1"), $expDz)));
    $N = bcadd($N, bcmul($dz, f_f($b, $VekD, $k, $dz)));
    if ($i % $di == 0)
    {
        $t = $i / $di;
        $R = Calc_k($t, $k_max, $ln_v, $h_order, $lndec, $M, $eps_k);
        if ($flag_concave['k'] >= 0) CalcConcav("k", $k_values, $flag_concave, $point_concave, $t);
        if ($flag_concave['R'] >= 0) CalcConcav("R", $k_values, $flag_concave, $point_concave, $t);
        WriteTable("f_store", $res_format, $t, $M, $N, $Cz, $k, (integer)$R);
    }
    if (trim(NormNum($M, 20)) == $Mm && trim(NormNum($N, 20)) == $Mm && NormNum($k, 16) == "3.") $flag_end = true;
}
$fh = fopen("f_store".".txt", "a");
fwrite($fh, "\r\nfor k = ".$point_concave['k']."   for R = ".$point_concave['R']);
fclose($fh);
?>

