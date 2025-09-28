<?php
require_once("BaseFunctions.php");
require_once("NumbersNormalized.php");
require_once("ServiceFunctions.php");
require_once("ModelFunctions.php");

$res_format = "%6d %-21s";
$common_calc_scale = 200;
bcscale($common_calc_scale);
$time_limit = 200000;
set_time_limit($time_limit);
$h_order = 3;
$eps_log = pow(10, -3);
$eps_k = pow(10, -16);
$lndec = LN10($eps_k);
$ln_v = LN_values(1, 10, $h_order, $eps_log, $lndec, $eps_k);
$Mm = bcmul("6.513", bcpow("10", "184"));
$di = 1;
$tm = "854";
$te = 900;
$J = $di * $tm;
$dz = bcdiv("1", (string)$J);
$eps = "0.".str_repeat("0", 200)."1";
$big_scale = 200;
$D = "0.5";
$expDdz = EX(bcmul($D, bcmul($tm, $dz)), $eps, $big_scale);
$expDz = "1";
$M = 1;
$t_max = 900;
for ($t = 0; $t <= $t_max && trim(NormNum($M, 20)) != $Mm; $t++)
{
    $expDz = bcmul($expDz, $expDdz);
    $M = bcdiv($Mm, bcadd("1", bcdiv(bcsub($Mm, "1"), $expDz)));
    if ($t == 0) $f = "0";
    else
    {
        $ln_t = LN_number((string)(2*$t), $ln_v, $h_order, $lndec, $eps);
        $ln_m = LN_number($M, $ln_v, $h_order, $lndec, $eps);
        $f = bcsub($ln_t, $ln_m);
    }
    WriteKTable("k_store", $res_format, $t, $f);
}
?>
