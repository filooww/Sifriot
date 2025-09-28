<?php
$k_max = 100;
$h_order = 3;
$eps_log = pow(10, -$h_order);
$h_k = 13;
$eps_k = pow(10, -$h_k);
$lndec = LN10($eps_k);
$ln_v = LN_values(1, 10, $h_order, $eps_log, $lndec, $eps_k);
$Mm1 = bcsub($Mm, "1");
WriteTable("f_store", $res_format, 0, "1", "1", $C0, "1", 0);
$expDdz = EX(bcmul($D, "1"), $eps, $big_scale);
$t_max = 19;
for ($t = 1; $t <= $t_max; $t++)
{
    $Cz = bcadd(bcmul($C6, $z), $C5);
    $Cz = bcadd(bcmul($Cz, $z), $C4);
    $Cz = bcadd(bcmul($Cz, $z), $C3);
    $Cz = bcadd(bcmul($Cz, $z), $C2);
    $Cz = bcadd(bcmul($Cz, bcmul($z, $z)), $C0);
    if (bccomp($z, $ze) == 1 && bccomp($Cz, $Ci) == 1) $Cz = $Ci;
    if (bccomp($z, $zi) == 1) $Cz = $Ci;
    $expDz = bcmul($expDz, $expDdz);
    $M = bcdiv($Mm, bcadd("1", bcdiv(bcsub($Mm, "1"), $expDz)));
    $N = Calc_N($Mm, $Mm1, $D, $Cz, $expDz, $ln_v, $h_order, $h_k, $lndec, $eps, $big_scale);
    $k = Calc_k($t, $k_max, $ln_v, $h_order, $lndec, $M, $eps_k);
    $df = bcsub(time(), $tt);
    $tt = time();
    WriteTable("f_store", $res_format, $t, $M, $N, $Cz, $k, $df);
}
?>
