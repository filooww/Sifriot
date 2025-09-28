<?php
require_once("BaseFunctions.php");
require_once("NumbersNormalized.php");
require_once("ServiceFunctions.php");
require_once("ModelFunctions.php");

function VFP($kl, $Nl, $zl, $Ml, &$Cz, $tm, $C0, $C2, $C3, $C4, $C5, $C6, $ze, $Ci, $zi, $Mm)
{
//  VF1D = tm*(kl*kl-(Nl+3)*kl+3*Ml)
    $VF1D = bcmul($kl, $kl);
    $VF1D = bcsub($VF1D, bcmul(bcadd($Nl, "3"), $kl));
    $VF1D = bcadd($VF1D, bcmul("3", $Ml));
    $VF1D = bcmul($tm, $VF1D);

//  Cz=((((C6*zl+C5)*zl+C4)*zl+C3)*zl+C2)*zl^2+C0
    $Cz = bcadd(bcmul($C6, $zl), $C5);
    $Cz = bcadd(bcmul($Cz, $zl), $C4);
    $Cz = bcadd(bcmul($Cz, $zl), $C3);
    $Cz = bcadd(bcmul($Cz, $zl), $C2);
    $Cz = bcadd(bcmul($Cz, bcmul($zl, $zl)), $C0);

    if (bccomp($zl, $ze) == 1 && bccomp($Cz, $Ci) == 1) $Cz = $Ci;
    if (bccomp($zl, $zi) == 1) $Cz = $Ci;

//  VF2D = tm*Cz*Nl*(1-Nl/Mm)
    $VF2D = bcsub("1", bcdiv($Nl, $Mm));
    $VF2D = bcmul($Nl, $VF2D);
    $VF2D = bcmul($Cz, $VF2D);
    $VF2D = bcmul($tm, $VF2D);

//  $Vf3D = 1
    return array(1=>$VF1D, 2=>$VF2D, 3=>"1");

}
//===============================================
//==MainRegion
//===============================================
//$res_format = "%10d %-6s %-"."28"."s %-"."28"."s %-"."28"."s %-"."28"."s %-"."20"."s   %5d";
$res_format = "%6d %-21s %-21s %-21s %-17s %3d";
$eps = "0.".str_repeat("0", 200)."1";
$common_calc_scale = 200;
bcscale($common_calc_scale);
$time_limit = 200000;
set_time_limit($time_limit);
$big_scale = 200;
$Mm = bcmul("6.513", bcpow("10", "184"));
$tm = "854"; //$tm = "843";
$te = 900;
$D = "0.5";
$di = 8192; //$di = 4096;
$C0 = "0.4995"; //$C0 = "0.49";
$Ce = "0.4933"; //$Ce = "0.47"; //$Ce = "0.45"; //$Ce = "0.49003"; //$Ce = "0.47"; //$Ce = "0.48"; //$Ce = 0.48747595;
$Ci = "0.5";
$er1 = bcsub($C0, $Ce);
$er2 = bcsub($Ci, $C0);
$ze = "1.0";
$zi = "3.0";
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
//C6=(er1*zi4*(-2*zi+5*ze)   +er2*ze3*(10*zi2-10*ze*zi+3*ze2)  )/de6
$s1 = bcsub(bcmul("5", $ze), bcmul("2", $zi));
$s1 = bcmul($er1, bcmul($zi4, $s1));
$s2 = bcsub(bcmul("10", $zi2), bcmul("10", bcmul($ze, $zi)));
$s2 = bcadd($s2, bcmul("3", $ze2));
$s2 = bcmul($er2, bcmul($ze3, $s2));
$C6 = bcdiv(bcadd($s1, $s2), $de6);
//C5=6*(er1*zi4*(zi2-2*ze*zi-ze2)+    er2*ze3*(-4*zi3 + ze*zi2 + 2*ze2*zi - ze3) )/de5
$s1 = bcsub($zi2, bcmul("2", bcmul($ze, $zi)));
$s1 = bcsub($s1, $ze2);
$s1 = bcmul($er1, bcmul($zi4, $s1));
$s2 = bcsub(bcmul($ze, $zi2), bcmul("4", $zi3));
$s2 = bcadd($s2, bcmul("2", bcmul($ze2, $zi)));
$s2 = bcsub($s2, $ze3);
$s2 = bcmul($er2, bcmul($ze3, $s2));
$C5 = bcmul("6", bcdiv(bcadd($s1, $s2), $de5));
//C4=3*(er1*zi5*(-2*zi2+2*ze*zi+6*ze2)+    er2*ze3*(5*zi4+10*ze*zi3-12*ze2*zi2+2*ze3*zi+ze4) )/de4
$s1 = bcsub(bcmul("2", bcmul($ze, $zi)), bcmul("2", $zi2));
$s1 = bcadd($s1, bcmul("6", $ze2));
$s1 = bcmul($er1, bcmul($zi5, $s1));
$s2 = bcadd(bcmul("5", $zi4), bcmul("10", bcmul($ze, $zi3)));
$s2 = bcsub($s2, bcmul("12", bcmul($ze2, $zi2)));
$s2 = bcadd($s2, bcmul("2", bcmul($ze3, $zi)));
$s2 = bcadd($s2, $ze4);
$s2 = bcmul($er2, bcmul($ze3, $s2));
$C4 = bcmul("3", bcdiv(bcadd($s1, $s2), $de4));
//C3=2*(er1*zi5*(zi2+2*ze*zi-9*ze2) +er2*ze4*(-15*zi3+6*ze*zi2+7*ze2*zi-4*ze3)  )/de3
$s1 = bcadd($zi2, bcmul("2", bcmul($ze, $zi)));
$s1 = bcsub($s1, bcmul("9", $ze2));
$s1 = bcmul($er1, bcmul($zi5, $s1));
$s2 = bcsub(bcmul("6", bcmul($ze, $zi2)), bcmul("15", $zi3));
$s2 = bcadd($s2, bcmul("7", bcmul($ze2, $zi)));
$s2 = bcsub($s2, bcmul("4", $ze3));
$s2 = bcmul($er2, bcmul($ze4, $s2));
$C3 = bcmul("2", bcdiv(bcadd($s1, $s2), $de3));
//C2=3*(er1*zi5*(-zi+2*ze)+er2*ze4*(5*zi2-6*ze*zi+2*ze2))/de2
//echo "<br> 00 ".$ze."===".$zi."===".$er1."===".$zi5."===".$zi2."===".$ze2."===".$er2."===".$ze4."===".$de2."===";
$s1 = bcsub(bcmul("2", $ze), $zi);
$s1 = bcmul($er1, bcmul($zi5, $s1));
$s2 = bcsub(bcmul("5", $zi2), bcmul("6", bcmul($ze, $zi)));
$s2 = bcadd($s2, bcmul("2", $ze2));
$s2 = bcmul($er2, bcmul($ze4, $s2));
$C2 = bcmul("3", bcdiv(bcadd($s1, $s2), $de2));
$Cz = $C0;
$k = 1;
$N = 1;
$z = 0;
$M = 1;
$expDz = "1";
$J = $di * $tm; //$J = 3497984; //$J = 109312; //$J = 81984; //$J = 54656;
$fh = fopen("f_store".".txt", "a");
fwrite($fh, "\r\nC0 = ".$C0."   Ce = ".$Ce."   Ci = ".$Ci."   C6 = ".NormNum($C6, 30)."   C5 = ".NormNum($C5, 25)."   C4 = ".NormNum($C4, 25)."   C3 = ".NormNum($C3, 25)."   C2 = ".NormNum($C2, 25)."   zi = ".$zi."   ze = ".$ze."   J = ".$J);
fwrite($fh, "\r\n     t M                     N                     C                     k");
fclose($fh);
$tt = time();
$tt0 = $tt;
require_once("Analitics.php");
exit();
$dz = bcdiv("1", (string)$J);
$expDdz = EX(bcmul($D, bcmul($tm, $dz)), $eps, $big_scale);
$i_max = $te *$di; //$i_max = 2 * $J; //$i_max = 5 * $J + 1;
$max_info = array(0, "0");
$flag_end = false;
$k_values = array("0", "0", "0");
$flag_concave = "0";
$point_concave = array();
for ($i = 1; $i <= $i_max && !$flag_end; $i++)
{
    $z = bcmul((string)($i - 1), $dz);
    $dCz = bcmul("6", bcmul($C6, $z));
    $dCz = bcadd($dCz, bcmul("5", $C5));
    $dCz = bcmul($dCz, $z);
    $dCz = bcadd($dCz, bcmul("4", $C4));
    $dCz = bcmul($dCz, $z);
    $dCz = bcadd($dCz, bcmul("3", $C3));
    $dCz = bcmul($dCz, $z);
    $dCz = bcadd($dCz, bcmul("2", $C2));
    $dCz = bcmul($dCz, $z);
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
    $VekD = VFP($k, $N, $z, $M, $Cz, $tm, $C0, $C2, $C3, $C4, $C5, $C6, $ze, $Ci, $zi, $Mm);
    $expDz = bcmul($expDz, $expDdz);
    $M = bcdiv($Mm, bcadd("1", bcdiv(bcsub($Mm, "1"), $expDz)));
    $f = bcadd(bcmul($b11, $VekD[1]), bcmul($b12, $VekD[2]));
    $f = bcadd($f, bcmul($b13, $VekD[3]));
    $k = bcadd($k, bcmul($dz, $f));
    if (bccomp($max_info[1], $k) < 0)
    {
        $max_info[0] = $i;
        $max_info[1] = $k;
    }
    $f = bcadd(bcmul($b21, $VekD[1]), bcmul($b22, $VekD[2]));
    $f = bcadd($f, bcmul($b23, $VekD[3]));
    $N = bcadd($N, bcmul($dz, $f));
    CalcConcav($i, $k_values, $flag_concave, $point_concave, $k, $di);
    if ($i % $di == 0)
    {
        $t = $i / $di;
        $df = bcsub(time(), $tt);
        $tt = time();
        WriteTable("f_store", $res_format, $t, $M, $N, $Cz, $k, $df);
    }
    if (trim(NormNum($M, 20)) == $Mm && trim(NormNum($N, 20)) == $Mm && NormNum($k, 16) == "3.") $flag_end = true;
}
$dif_t = (integer)bcsub((string)time(), (string)$tt0);
$sec = (integer)($max_info[0] / $di);
$fh = fopen("f_store".".txt", "a");
fwrite($fh, "\r\n".(string)$dif_t);
fwrite($fh, "\r\n".(string)$max_info[0]."   ".(string)$sec."   ".(string)(float)$max_info[1]."    time = ".time());
fwrite($fh, "\r\nconcave points:");
foreach ($point_concave as $z) fwrite($fh, "\r\n = ".$z);
fclose($fh);
?>
