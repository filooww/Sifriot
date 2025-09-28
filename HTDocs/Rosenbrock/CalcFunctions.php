<?php
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
function f_dCz($i, $dz, $C2, $C3, $C4, $C5, $C6)
{
    $z = bcmul((string)($i - 1), $dz);
    $x = bcmul("6", bcmul($C6, $z));
    $x = bcadd($x, bcmul("5", $C5));
    $x = bcmul($x, $z);
    $x = bcadd($x, bcmul("4", $C4));
    $x = bcmul($x, $z);
    $x = bcadd($x, bcmul("3", $C3));
    $x = bcmul($x, $z);
    $x = bcadd($x, bcmul("2", $C2));
    $x = bcmul($x, $z);
    return $x;
}
function f_j($tm, $k, $N, $M, $Mm, $dCz, $Cz)
{
    $j[1][1] = bcmul($tm, bcadd(bcsub(bcmul("2", $k), $N), "3"));
    $j[1][2] = bcmul($tm, $k);
    $j[1][3] = bcsub("1", bcdiv($M, $Mm));
    $j[1][3] = bcmul($M, $j[1][3]);
    $j[1][3] = bcmul("3", bcmul(bcpow($tm, 2), $j[1][3]));
    $j[2][1] = "0";
    $j[2][2] = bcmul($tm, bcmul($Cz, bcsub("1", bcdiv(bcmul("2", $N), $Mm))));
    $j[2][3] = bcmul($tm, bcmul($N, bcmul(bcsub("1", bcdiv($N, $Mm)), $dCz)));
    $j[3][1] = "0";
    $j[3][2] = "0";
    $j[3][3] = "0";
    return $j;
}
function f_a($dz, $j)
{
    $a[1][1] = bcsub("1", bcmul($dz, $j[1][1]));
    $a[1][2] = bcsub("0", bcmul($dz, $j[1][2]));
    $a[1][3] = bcsub("0", bcmul($dz, $j[1][3]));
    $a[2][1] = "0";
    $a[2][2] = bcsub("1", bcmul($dz, $j[2][2]));
    $a[2][3] = bcsub("0", bcmul($dz, $j[2][3]));
    $a[3][1] = "0";
    $a[3][2] = "0";
    $a[3][3] = bcsub("1", bcmul($dz, $j[3][3]));
    return $a;
}
function f_b($a)
{
    $b[1][1] = bcdiv("1", $a[1][1]);
    $b[1][2] = bcsub("0", bcdiv($a[1][2], bcmul($a[1][1], $a[2][2])));
    $b[1][3] = bcsub(bcdiv(bcmul($a[1][2], $a[2][3]), bcmul($a[1][1], $a[2][2])), bcdiv($a[1][3], $a[1][1]));
    $b[2][1] = "0";
    $b[2][2] = bcdiv("1", $a[2][2]);
    $b[2][3] = bcsub("0", bcdiv($a[2][3], $a[2][2]));
    $b[3][1] = "0";
    $b[3][2] = "0";
    $b[3][3] = "1";
    return $b;
}
function f_f($b, $VekD, &$k, $dz)
{
    $f = bcadd(bcmul($b[1][1], $VekD[1]), bcmul($b[1][2], $VekD[2]));
    $f = bcadd($f, bcmul($b[1][3], $VekD[3]));
    $k = bcadd($k, bcmul($dz, $f));
    $f = bcadd(bcmul($b[2][1], $VekD[1]), bcmul($b[2][2], $VekD[2]));
    return bcadd($f, bcmul($b[2][3], $VekD[3]));
}
function f_er3($ze, $ze2, $ze3, $ze4, $zi, $zi2, $zi3, $zi4)

{
    $er3 = bcsub($zi4, bcmul("4", bcmul($ze, $zi3)));
    $er3 = bcadd($er3, bcmul("6", bcmul($ze2, $zi2)));
    $er3 = bcsub($er3, bcmul("4", bcmul($ze3, $zi)));
    return bcadd($er3, $ze4);
}
function f_C6($de6, $er1, $er2, $ze, $ze2, $ze3, $zi, $zi2, $zi4)
{
    $s1 = bcsub(bcmul("5", $ze), bcmul("2", $zi));
    $s1 = bcmul($er1, bcmul($zi4, $s1));
    $s2 = bcsub(bcmul("10", $zi2), bcmul("10", bcmul($ze, $zi)));
    $s2 = bcadd($s2, bcmul("3", $ze2));
    $s2 = bcmul($er2, bcmul($ze3, $s2));
    return bcdiv(bcadd($s1, $s2), $de6);
}
function f_C5($de5, $er1, $er2, $ze, $ze2, $ze3, $zi, $zi2, $zi3, $zi4)
{
    $s1 = bcsub($zi2, bcmul("2", bcmul($ze, $zi)));
    $s1 = bcsub($s1, $ze2);
    $s1 = bcmul($er1, bcmul($zi4, $s1));
    $s2 = bcsub(bcmul($ze, $zi2), bcmul("4", $zi3));
    $s2 = bcadd($s2, bcmul("2", bcmul($ze2, $zi)));
    $s2 = bcsub($s2, $ze3);
    $s2 = bcmul($er2, bcmul($ze3, $s2));
    return bcmul("6", bcdiv(bcadd($s1, $s2), $de5));
}
function f_C4($de4, $er1, $er2, $ze, $ze2, $ze3, $ze4, $zi, $zi2, $zi3, $zi4, $zi5)
{
    $s1 = bcsub(bcmul("2", bcmul($ze, $zi)), bcmul("2", $zi2));
    $s1 = bcadd($s1, bcmul("6", $ze2));
    $s1 = bcmul($er1, bcmul($zi5, $s1));
    $s2 = bcadd(bcmul("5", $zi4), bcmul("10", bcmul($ze, $zi3)));
    $s2 = bcsub($s2, bcmul("12", bcmul($ze2, $zi2)));
    $s2 = bcadd($s2, bcmul("2", bcmul($ze3, $zi)));
    $s2 = bcadd($s2, $ze4);
    $s2 = bcmul($er2, bcmul($ze3, $s2));
    return bcmul("3", bcdiv(bcadd($s1, $s2), $de4));
}
function f_C3($de3, $er1, $er2, $ze, $ze2, $ze3, $ze4, $zi, $zi2, $zi3, $zi5)
{
    $s1 = bcadd($zi2, bcmul("2", bcmul($ze, $zi)));
    $s1 = bcsub($s1, bcmul("9", $ze2));
    $s1 = bcmul($er1, bcmul($zi5, $s1));
    $s2 = bcsub(bcmul("6", bcmul($ze, $zi2)), bcmul("15", $zi3));
    $s2 = bcadd($s2, bcmul("7", bcmul($ze2, $zi)));
    $s2 = bcsub($s2, bcmul("4", $ze3));
    $s2 = bcmul($er2, bcmul($ze4, $s2));
    return bcmul("2", bcdiv(bcadd($s1, $s2), $de3));
}
function f_C2($de2, $er1, $er2, $ze, $ze2, $ze4, $zi, $zi2, $zi5)
{
    $s1 = bcsub(bcmul("2", $ze), $zi);
    $s1 = bcmul($er1, bcmul($zi5, $s1));
    $s2 = bcsub(bcmul("5", $zi2), bcmul("6", bcmul($ze, $zi)));
    $s2 = bcadd($s2, bcmul("2", $ze2));
    $s2 = bcmul($er2, bcmul($ze4, $s2));
    return bcmul("3", bcdiv(bcadd($s1, $s2), $de2));
}
?>

