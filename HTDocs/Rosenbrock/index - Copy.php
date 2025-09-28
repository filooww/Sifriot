<?php
require_once("BaseFunctions.php");
require_once("NumbersNormalized.php");
require_once("ServiceFunctions.php");
require_once("ModelFunctions.php");
require_once("CalcFunctions.php");

function VFP($kl, $Nl, $zl, $Ml, &$Cz, $tm, $C0, $C2, $C3, $C4, $C5, $C6, $ze, $Ci, $zi, $Mm)
{
    $VF1D = bcmul($kl, $kl);
    $VF1D = bcsub($VF1D, bcmul(bcadd($Nl, "3"), $kl));
    $VF1D = bcadd($VF1D, bcmul("3", $Ml));
    $VF1D = bcmul($tm, $VF1D);
    $Cz = bcadd(bcmul($C6, $zl), $C5);
    $Cz = bcadd(bcmul($Cz, $zl), $C4);
    $Cz = bcadd(bcmul($Cz, $zl), $C3);
    $Cz = bcadd(bcmul($Cz, $zl), $C2);
    $Cz = bcadd(bcmul($Cz, bcmul($zl, $zl)), $C0);
    if (bccomp($zl, $ze) == 1 && bccomp($Cz, $Ci) == 1) $Cz = $Ci;
    if (bccomp($zl, $zi) == 1) $Cz = $Ci;
    $VF2D = bcsub("1", bcdiv($Nl, $Mm));
    $VF2D = bcmul($Nl, $VF2D);
    $VF2D = bcmul($Cz, $VF2D);
    $VF2D = bcmul($tm, $VF2D);
    return array(1=>$VF1D, 2=>$VF2D, 3=>"1");
}

$k_max = 100;
$eps_log = .001;
$h_order = 3;
$eps_k = .0000000000000001;
$lndec = LN10($eps_k);
$ln_v = LN_values(1, 10, $h_order, $eps_log, $lndec, $eps_k);
$k_values = array(0, 0, 0);
$R_values = array(0, 0, 0);
$k_flag_concave = 0;
$R_flag_concave = 0;
$k_point_concave = 0;
$R_point_concave = 0;

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
$er3 = bcsub($zi4, bcmul("4", bcmul($ze, $zi3)));
$er3 = bcadd($er3, bcmul("6", bcmul($ze2, $zi2)));
$er3 = bcsub($er3, bcmul("4", bcmul($ze3, $zi)));
$er3 = bcadd($er3, $ze4);
$de2 = bcmul($ze2, bcmul($zi2, $er3));
$de3 = bcmul($ze, bcmul($zi, $de2));
$de4 = bcmul($zi, $de3);
$de5 = $de4;
$de6 = $de5;

$s1 = bcsub(bcmul("5", $ze), bcmul("2", $zi));
$s1 = bcmul($er1, bcmul($zi4, $s1));
$s2 = bcsub(bcmul("10", $zi2), bcmul("10", bcmul($ze, $zi)));
$s2 = bcadd($s2, bcmul("3", $ze2));
$s2 = bcmul($er2, bcmul($ze3, $s2));
$C6 = bcdiv(bcadd($s1, $s2), $de6);

$s1 = bcsub($zi2, bcmul("2", bcmul($ze, $zi)));
$s1 = bcsub($s1, $ze2);
$s1 = bcmul($er1, bcmul($zi4, $s1));
$s2 = bcsub(bcmul($ze, $zi2), bcmul("4", $zi3));
$s2 = bcadd($s2, bcmul("2", bcmul($ze2, $zi)));
$s2 = bcsub($s2, $ze3);
$s2 = bcmul($er2, bcmul($ze3, $s2));
$C5 = bcmul("6", bcdiv(bcadd($s1, $s2), $de5));

$s1 = bcsub(bcmul("2", bcmul($ze, $zi)), bcmul("2", $zi2));
$s1 = bcadd($s1, bcmul("6", $ze2));
$s1 = bcmul($er1, bcmul($zi5, $s1));
$s2 = bcadd(bcmul("5", $zi4), bcmul("10", bcmul($ze, $zi3)));
$s2 = bcsub($s2, bcmul("12", bcmul($ze2, $zi2)));
$s2 = bcadd($s2, bcmul("2", bcmul($ze3, $zi)));
$s2 = bcadd($s2, $ze4);
$s2 = bcmul($er2, bcmul($ze3, $s2));
$C4 = bcmul("3", bcdiv(bcadd($s1, $s2), $de4));

$s1 = bcadd($zi2, bcmul("2", bcmul($ze, $zi)));
$s1 = bcsub($s1, bcmul("9", $ze2));
$s1 = bcmul($er1, bcmul($zi5, $s1));
$s2 = bcsub(bcmul("6", bcmul($ze, $zi2)), bcmul("15", $zi3));
$s2 = bcadd($s2, bcmul("7", bcmul($ze2, $zi)));
$s2 = bcsub($s2, bcmul("4", $ze3));
$s2 = bcmul($er2, bcmul($ze4, $s2));
$C3 = bcmul("2", bcdiv(bcadd($s1, $s2), $de3));

$s1 = bcsub(bcmul("2", $ze), $zi);
$s1 = bcmul($er1, bcmul($zi5, $s1));
$s2 = bcsub(bcmul("5", $zi2), bcmul("6", bcmul($ze, $zi)));
$s2 = bcadd($s2, bcmul("2", $ze2));
$s2 = bcmul($er2, bcmul($ze4, $s2));
$C2 = bcmul("3", bcdiv(bcadd($s1, $s2), $de2));
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
WriteTable("f_store", $res_format, 0, "1", "1", "1", $C0, "1", 0);
$expDdz = EX(bcmul($D, bcmul($tm, $dz)), $eps, $big_scale);
$i_max = $te *$di;
$flag_end = false;

for ($i = 1; $i <= $i_max && !$flag_end; $i++)
{
    $dCz = dCz($i, $dz, $C2, $C3, $C4, $C5, $C6);
    
    $j11 = bcmul($tm, bcadd(bcsub(bcmul("2", $k), $N), "3"));
    $j12 = bcmul($tm, $k);
    $j13 = bcsub("1", bcdiv($M, $Mm));
    $j13 = bcmul($M, $j13);
    $j13 = bcmul("3", bcmul(bcpow($tm, 2), $j13)); //???
    $j21 = "0";
    $j22 = bcmul($tm, bcmul($Cz, bcsub("1", bcdiv(bcmul("2", $N), $Mm))));
    $j23 = bcmul($tm, bcmul($N, bcmul(bcsub("1", bcdiv($N, $Mm)), $dCz)));
    $j31 = "0";
    $j32 = "0";
    $j33 = "0";
    $a11 = bcsub("1", bcmul($dz, $j11));
    $a12 = bcsub("0", bcmul($dz, $j12));
    $a13 = bcsub("0", bcmul($dz, $j13));
    $a21 = "0";
    $a22 = bcsub("1", bcmul($dz, $j22));
    $a23 = bcsub("0", bcmul($dz, $j23));
    $a31 = "0";
    $a32 = "0";
    $a33 = bcsub("1", bcmul($dz, $j33));
    $b11 = bcdiv("1", $a11);
    $b12 = bcsub("0", bcdiv($a12, bcmul($a11, $a22)));
    $b13 = bcsub(bcdiv(bcmul($a12, $a23), bcmul($a11, $a22)), bcdiv($a13, $a11));
    $b21 = "0";
    $b22 = bcdiv("1", $a22);
    $b23 = bcsub("0", bcdiv($a23, $a22));
    $b31 = "0";
    $b32 = "0";
    $b33 = "1";
    $VekD = VFP($k,  $N,  $z,  $M,  $Cz,  $tm, $C0, $C2, $C3, $C4, $C5, $C6, $zi, $Ci, $zi, $Mm);
    $expDz = bcmul($expDz, $expDdz);
    $M = bcdiv($Mm, bcadd("1", bcdiv(bcsub($Mm, "1"), $expDz)));
    $f = bcadd(bcmul($b11, $VekD[1]), bcmul($b12, $VekD[2]));
    $f = bcadd($f, bcmul($b13, $VekD[3]));
    $k = bcadd($k, bcmul($dz, $f));
    $f = bcadd(bcmul($b21, $VekD[1]), bcmul($b22, $VekD[2]));
    $f = bcadd($f, bcmul($b23, $VekD[3]));
    $N = bcadd($N, bcmul($dz, $f));
    if ($i % $di == 0)
    {
        $t = $i / $di;
        $R = Calc_k($t, $k_max, $ln_v, $h_order, $lndec, $M, $eps_k);
        $k_arr[] = $k;
        $R_arr[] = $R;
        if ($t > 2)
        {
echo "<br> 00 ***t = ".$t."==="; print_r($a_k);
echo "<br> 01 ***"; print_r($a_R);
            if ($flag_kR[0] == 0)
            $a_k[$t-1] =
            bcsub(bcdiv(bcadd(
            $k_arr[$t],
            $k_arr[$t-2],
            $eps_k), "2",
            $eps_k),
            $k_arr[$t-1],
            $eps_k);
            if ($flag_kR[1] == 0) $a_R[$t-1] = bcsub(bcdiv(bcadd($R_arr[$t], $R_arr[$t-2], $eps_k), "2", $eps_k), $R_arr[$t-1], $eps_k);
            if (bccomp($a_k[$t-2], $a_k[$t-1], $eps_k) >= 0 && bccomp($a_k[$t-1], $a_k[$t], $eps_k) < 0) $flag_kR[0] = $t;
            if (bccomp($a_R[$t-2], $a_R[$t-1], $eps_k) >= 0 && bccomp($a_R[$t-1], $a_k[$t], $eps_k) < 0) $flag_kR[1] = $t;
        }
        WriteTable("f_store", $res_format, $t, $M, $N, $Cz, $k, $R);
    }
    if (trim(NormNum($M, 20)) == $Mm && trim(NormNum($N, 20)) == $Mm && NormNum($k, 16) == "3.") $flag_end = true;
}
$fh = fopen("f_store".".txt", "a");
fwrite($fh, "\r\nk = ".(string)$flag_kR[0]."   R = ".(string)$flag_kR[1]);
fclose($fh);
?>

