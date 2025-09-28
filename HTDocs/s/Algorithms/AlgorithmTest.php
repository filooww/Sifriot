<?php
function AlgorithmErrors()
{
    $w_str = "id_algorithm = 0 OR ";
    $w_str .= "del_from_source > 1 OR ";
    $w_str .= "beg_limit_set REGEXP '['']' OR ";
    $w_str .= "beg_inc < 0 OR beg_inc > 1 OR ";
    $w_str .= "beg_scr > 1 OR ";
    $w_str .= "inner_limit_set REGEXP '['']' OR ";
    $w_str .= "end_limit_set REGEXP '['']' OR ";
    $w_str .= "beg_number > 0 AND end_number > 0 AND end_number < beg_number OR ";
    $w_str .= "end_inc > 1 OR ";
    $w_str .= "end_scr > 1 OR ";
    $w_str .= "del_symbols REGEXP '['']' OR ";
    $w_str .= "ins_symbols REGEXP '['']' OR ";
    $w_str .= "field_only > 1 OR ";
    $w_str .= "reg_expression REGEXP '['']' OR ";
    $w_str .= "reg_scr > 1 OR ";
    $w_str .= "alg_remarks = '' OR alg_remarks REGEXP '['']'";
    return $w_str;
}
function TestAlgorithm($k, $v)
{
	$_SESSION['alg_flag'][$k]['id'] = ((integer)$k == 0);
	$_SESSION['alg_flag'][$k]['offset'] = (!is_numeric($v['offset']) || is_numeric($v['offset']) && (integer)$v['offset'] < 0);
    TestAlgorithmCheck($k, "del_from_source", $v['del_from_source']);
    AlgorithmStringTest($k, "beg_del", $v['beg_del']);
    $_SESSION['alg_flag'][$k]['beg_num'] = ($v['beg_num'] < 0);
    TestAlgorithmCheck($k, "beg_inc", $v['beg_inc']);
    TestAlgorithmCheck($k, "beg_scr", $v['beg_scr']);
    AlgorithmStringTest($k, "inn_del", $v['inn_del']);
    AlgorithmStringTest($k, "end_del", $v['end_del']);
    $_SESSION['alg_flag'][$k]['end_num'] = ($v['end_num'] < 0 || $v['beg_num'] > 0 && $v['end_num'] > 0 && $v['end_num'] < $v['beg_num']);
    TestAlgorithmCheck($k, "end_inc", $v['end_inc']);
    TestAlgorithmCheck($k, "end_scr", $v['end_scr']);
    AlgorithmStringTest($k, "del_sym", $v['del_sym']);
    AlgorithmStringTest($k, "ins_sym", $v['ins_sym']);
    TestAlgorithmCheck($k, "field_only", $v['field_only']);
    AlgorithmStringTest($k, "reg_exp", $v['reg_exp']);
    TestAlgorithmCheck($k, "reg_scr", $v['reg_scr']);
    $_SESSION['alg_flag'][$k]['remarks'] = AlgorithmRemarkTest($k, $v['remarks']);
}
function AlgorithmRemarkTest($k, $check_value)
{
    $fl_empty = ($check_value == "");
    if (strpos($check_value, chr(39)) === false) $fl_apostr = false;
    else
    {
        $_SESSION['arr_alg'][$k]['remarks'] = str_replace(chr(39), $_SESSION['apostrophe_replace'], $_SESSION['arr_alg'][$k]['remarks']);
        $fl_apostr = true;
        $_SESSION['update_replace'][$k]['remarks'] = $_SESSION['apostrophe_replace'];
    }
    return ($fl_empty || $fl_apostr);
}
function TestAlgorithmCheck($k, $param_name, $check_value)
{
    if ($check_value == 0 || $check_value == 1) $_SESSION['alg_flag'][$k][$param_name] = false;
    else
    {
        $_SESSION['arr_alg'][$k][$param_name] = 0;
        $_SESSION['alg_flag'][$k][$param_name] = true;
        $_SESSION['update_replace'][$k][$param_name] = (string)$check_value;
    }
}
function AlgorithmStringTest($k, $param_name, $check_value)
{
    if (strpos($check_value, chr(39)) === false) $_SESSION['alg_flag'][$k][$param_name] = false;
    else
    {
        $_SESSION['arr_alg'][$k][$param_name] = str_replace(chr(39), $_SESSION['apostrophe_replace'], $_SESSION['arr_alg'][$k][$param_name]);
        $_SESSION['alg_flag'][$k][$param_name] = true;
        $_SESSION['update_replace'][$k][$param_name] = $_SESSION['apostrophe_replace'];
    }
}
function AlgorithmMarkDelete($k)
{
    $i = array_search($k, $_SESSION['alg_del']);
    if ($i === false) $_SESSION['alg_del'][] = $k;
    else unset($_SESSION['alg_del'][$i]);
}

?>
