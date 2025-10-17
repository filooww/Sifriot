<?php

function GetAlgorithmPortion($dbh)
{
    $_SESSION['arr_alg'] = [];
    $_SESSION['alg_flag'] = [];
    $res = mysqli_query($dbh, 'SELECT * FROM algorithms ORDER BY id_algorithm LIMIT '.(string) $_SESSION['start'].','.(string) $_SESSION['portion']);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            AddAlg($row);
            TestAlgorithm((string) $row[0], $_SESSION['arr_alg'][(string) $row[0]]);
        }
        mysqli_free_result($res);
    }
}
function AddAlg($row)
{
    $_SESSION['arr_alg'][(string) $row[0]]['offset'] = $row[1];
    $_SESSION['arr_alg'][(string) $row[0]]['del_from_source'] = $row[2];
    $_SESSION['arr_alg'][(string) $row[0]]['beg_del'] = $row[3];
    $_SESSION['arr_alg'][(string) $row[0]]['beg_num'] = $row[4];
    $_SESSION['arr_alg'][(string) $row[0]]['beg_inc'] = $row[5];
    $_SESSION['arr_alg'][(string) $row[0]]['beg_scr'] = $row[6];
    $_SESSION['arr_alg'][(string) $row[0]]['inn_del'] = $row[7];
    $_SESSION['arr_alg'][(string) $row[0]]['end_del'] = $row[8];
    $_SESSION['arr_alg'][(string) $row[0]]['end_num'] = $row[9];
    $_SESSION['arr_alg'][(string) $row[0]]['end_inc'] = $row[10];
    $_SESSION['arr_alg'][(string) $row[0]]['end_scr'] = $row[11];
    $_SESSION['arr_alg'][(string) $row[0]]['del_sym'] = $row[12];
    $_SESSION['arr_alg'][(string) $row[0]]['ins_sym'] = $row[13];
    $_SESSION['arr_alg'][(string) $row[0]]['field_only'] = $row[14];
    $_SESSION['arr_alg'][(string) $row[0]]['reg_exp'] = $row[15];
    $_SESSION['arr_alg'][(string) $row[0]]['reg_scr'] = $row[16];
    $_SESSION['arr_alg'][(string) $row[0]]['remarks'] = $row[17];
}
function AlgorithmSize($dbh)
{
    $alg_count = 0;
    $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM algorithms');
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $alg_count = $row[0];
        }
        mysqli_free_result($res);
    }

    return $alg_count;
}
function AlgorithmNavigation($dbh, $act, &$Mes)
{
    if ($_SESSION['edit_algorithm'] == '') {
        $s_p = ListNewStartPosition($act, $_SESSION['algorithm_size']);
        if ($s_p != $_SESSION['start']) {
            $_SESSION['start'] = $s_p;
            GetAlgorithmPortion($dbh);
            foreach ($_SESSION['arr_alg'] as $k => $v) {
                TestAlgorithm($k, $v);
            }
        }
    } else {
        $Mes[] = "<font color='#FF0000'><b>".Title(240).' '.Title(239).'</b></font>';
    }
}
function SetAlgorithmPortionSize($int_offs)
{
    $mes_screen = '';
    $f = end($_SESSION['alg_flag']);
    $k = key($_SESSION['alg_flag']);
    $_SESSION['portion'] = count($_SESSION['alg_flag']);
    for ($i = count($_SESSION['alg_flag']) - 1; $i >= $int_offs && AlgorithmErrorsFree($k, 'full'); $i--) {
        array_pop($_SESSION['arr_alg']);
        array_pop($_SESSION['alg_flag']);
        $_SESSION['portion']--;
        $f = end($_SESSION['alg_flag']);
        $k = key($_SESSION['alg_flag']);
    }
    if ($i >= $int_offs) {
        $mes_screen = "<font color='#FF0000'>".Title(563).' '.Title(513).'</font>';
    }

    return $mes_screen;
}
function SetAlarmAlgorithmPortionSize($int_offs)
{
    $_SESSION['arr_alg'] = array_slice($_SESSION['arr_alg'], 0, $int_offs, true);
    $_SESSION['alg_flag'] = array_slice($_SESSION['alg_flag'], 0, $int_offs, true);
    $_SESSION['portion'] = $int_offs;
}
function AlgorithmErrorsFree($k, $err_types)
{
    foreach ($_SESSION['alg_flag'][$k] as $v_name => $flag) {
        if (in_array($v_name, $_SESSION['algorithm_err'][$err_types][0]) && $flag) {
            return false;
        }
    }
    if (isset($_SESSION['update_replace'][$k])) {
        foreach ($_SESSION['update_replace'][$k] as $v_name => $f_name) {
            if (in_array($v_name, $_SESSION['algorithm_err'][$err_types][1]) && $f_name) {
                return false;
            }
        }
    }

    return true;
}
