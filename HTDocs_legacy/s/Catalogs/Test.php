<?php

function TestCatalogs($dbh, $init = false, $clear_mes = true, $common_mes = '')
{
    if ($clear_mes) {
        $_SESSION['mes']['0'] = [];
        $_SESSION['mes']['1'] = [];
        $_SESSION['mes']['c'] = [];
    }
    if (! $init) {
        if ($_SESSION['Catalog']['1']['table'] != '') {
            CatalogNewContent('1');
        } else {
            CatalogNewContent('0');
        }
    }
    TestCatalogRows('0');
    TestCatalogRows('1');
    DublicateSearch($dbh, '0');
    if ($_SESSION['Catalog']['1']['table'] != '') {
        DublicateSearch($dbh, '1');
    }
    if (count($_SESSION['del_row']['0']) > 0) {
        PublicationReferences($dbh, $_SESSION['Catalog']['0']['in_pub']);
    }
    if (count($_SESSION['del_row']['1']) > 0) {
        CoupledReferences($dbh);
    }
    if ($common_mes != '') {
        $_SESSION['mes']['c'][] = ['time' => '', 'text' => CatalogMessage($common_mes), 'status' => 'statement', 'now' => true];
        $_SESSION['view_err_mes'] = true;
    }
}
function CatalogNewContent($n)
{
    foreach ($_SESSION['cat_arr'][$n] as $arr_key => $arr_el) {
        $post_key = 'cat_text'.$n.'|'.$arr_key;
        if (isset($_POST[$post_key]) && $_POST[$post_key] != end($arr_el)) {
            if ($_POST[$post_key] == '' && is_numeric($arr_key) && end($arr_el) != '') {
                $_SESSION['del_row'][$n][$arr_key] = ['old_id' => $arr_key, 'old_value' => end($arr_el)];
            } // new??
            $_SESSION['cat_arr'][$n][$arr_key][3] = $_POST[$post_key];
            $_SESSION['cat_arr'][$n][$arr_key][1] = true;
        }
    }
}
function TestCatalogRows($n)
{
    foreach ($_SESSION['cat_arr'][$n] as $k => $v) {
        if (is_numeric($k) && end($v) == '') {
            $_SESSION['mes'][$n][] = ['time' => '', 'text' => Title(225).' <b>'.$k.'</b> '.Title(160), 'status' => 'warning'];
        }
        $rr = StringTest($_SESSION['table_definitions'][$_SESSION['Catalog'][$n]['table']]['illegals'], end($v), 'replace_text');
        $_SESSION['cat_arr'][$n][$k][2] = $rr['error'];
        $illegal_mes = SetTestMessages($rr, end($v), $k);
        if ($illegal_mes != '') {
            $_SESSION['mes'][$n][] = ['time' => '', 'text' => $illegal_mes, 'status' => 'error'];
        }
        if ($n == 0 && $_SESSION['Catalog']['1']['table'] != '' && $v[3] != '' && is_numeric($k) && ! $_SESSION['cat_arr']['0'][$k][2] && TestSetValue($k, $v[3], $v[4])) {
            $_SESSION['cat_arr']['0'][$k][2] = true;
        } // new??
        if (is_numeric($k) && (int) $k = 0) {
            $_SESSION['mes'][$n][] = ['time' => '', 'text' => Title(225).' <b>'.$k.'</b>: '.Title(627), 'status' => 'error'];
            $_SESSION['cat_arr'][$n][$k][2] = true;
        }
    }
}
function TestSetValue($k, $code_str, $value_str)
{
    $fl = false;
    $arr_codes = explode(',', $code_str);
    $arr_values = explode($_SESSION['Catalog']['0']['separator'], $value_str);
    $arr_empty = [];
    for ($i = 0; $i < count($arr_values); $i++) {
        if ($arr_values[$i] == '') {
            $arr_empty[] = $arr_codes[$i];
        }
    }
    if (count($arr_empty) > 0) {
        $title_link = (count($arr_empty) == 1) ? Title(445) : Title(446);
        $title_del = (count($arr_empty) == 1) ? Title(447) : Title(448);
        $_SESSION['mes']['0'][] = ['time' => '', 'text' => Title(443).' <b>'.$value_str.'</b> (<b>'.$k.'</b>) '.$title_link.' <b>'.implode(', ', $arr_empty).'</b> '.$title_del.' <b>'.$_SESSION['Catalog']['1']['name'].'</b>.', 'status' => 'error'];
        $fl = true;
    }
    if ($_SESSION['Catalog']['0']['cat_type'] == 2) {
        $arr_test = $arr_values;
        asort($arr_test);
        if (implode($_SESSION['Catalog']['0']['separator'], $arr_test) != $value_str) {
            $_SESSION['mes']['0'][] = ['time' => '', 'text' => Title(443).' <b>'.$value_str.'</b> (<b>'.$k.'</b>) '.Title(475), 'status' => 'error'];
            $fl = true;
        }
    }

    return $fl;
}
function DublicateSearch($dbh, $n)
{
    $single = ($_SESSION['Catalog']['1']['table'] == '' || $_SESSION['Catalog']['1']['table'] != '' && $n == '1');
    $f_name = ($single) ? MCF($_SESSION['Catalog'][$n]['value'], $_SESSION['match_case']) : $_SESSION['Catalog'][$n]['value'];
    $doubled = [];
    if (! $single) {
        $pre_ids = [];
    }
    $req = mysqli_query($dbh, 'SELECT '.$_SESSION['Catalog'][$n]['value'].', GROUP_CONCAT('.$_SESSION['Catalog'][$n]['id'].') FROM '.$_SESSION['Catalog'][$n]['table'].' WHERE '.$f_name." <> '' GROUP BY ".$f_name.' HAVING COUNT(*) > 1');
    if ($req) {
        while ($row = mysqli_fetch_row($req)) {
            $arr = explode(',', $row[1]);
            foreach ($arr as $z) {
                if ($single) {
                    $doubled[$row[0]][] = $z;
                } else {
                    $pre_ids[$row[0]][] = $z;
                }
            }
        }
        mysqli_free_result($req);
        if (! $single) {
            $doubled = GetSetValues($dbh, $pre_ids);
        }
        if (count($doubled) > 0) {
            DublicateMessages($dbh, $doubled, $n);
        }
    }
}
function DublicateMessages($dbh, $doubled, $n)
{
    $_SESSION['mes'][$n][] = ['time' => '', 'text' => Title(489).':', 'status' => 'error'];
    foreach ($doubled as $doubled_value => $doubled_id_arr) {
        foreach ($doubled_id_arr as $z) {
            if (array_key_exists($z, $_SESSION['cat_arr'][$n])) {
                $_SESSION['cat_arr'][$n][$z][2] = true;
            }
        }
        $doubled_id_arr = array_unique($doubled_id_arr);
        sort($doubled_id_arr);
        $_SESSION['mes'][$n][] = ['time' => '', 'text' => '-- '.Title(490).' <b>'.$doubled_value.'</b> '.Title(491).' <b>'.implode(', ', $doubled_id_arr).'</b>', 'status' => 'error'];
    }
}
function GetSetValues($dbh, $pre_ids)
{
    $doubled = [];
    $set_keys = [];
    foreach ($pre_ids as $k => $v) {
        $arr[$k] = explode(',', $k);
        foreach ($arr[$k] as $z) {
            if (! in_array($z, $set_keys)) {
                $set_keys[] = $z;
            }
        }
    }
    $set_values = [];
    $res = mysqli_query($dbh, 'SELECT '.$_SESSION['Catalog']['1']['id'].','.$_SESSION['Catalog']['1']['value'].' FROM '.$_SESSION['Catalog']['1']['table'].' WHERE '.$_SESSION['Catalog']['1']['id'].' IN ('.implode(',', $set_keys).')');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $set_values[(string) $row[0]] = $row[1];
        }
        mysqli_free_result($res);
    }
    $arr_del = [];
    foreach ($pre_ids as $k => $v) {
        $arr_new = [];
        for ($i = 0; $i < count($arr[$k]); $i++) {
            $arr_new[] = $set_values[$arr[$k][$i]];
        }
        $doubled[implode($_SESSION['Catalog']['0']['separator'], $arr_new)] = $v;
    }

    return $doubled;
}

function PublicationReferences($dbh, $in_pub)
{
    $item_for_del = [];
    $res = mysqli_query($dbh, 'SELECT '.$in_pub.', GROUP_CONCAT(id_publication) FROM publication WHERE '.$in_pub.' IN ('.implode(',', array_keys($_SESSION['del_row']['0'])).') GROUP BY '.$in_pub);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            CancelDeletion((string) $row[0], $row[1], $item_for_del);
            unset($_SESSION['del_row']['0'][(string) $row[0]]);
        }
        mysqli_free_result($res);
    }
    if (count($item_for_del) > 0) {
        $_SESSION['mes']['c'][] = ['time' => '', 'text' => 'The references to deleted values found in publications.', 'status' => 'error'];
        foreach ($item_for_del as $k => $v) {
            $_SESSION['cat_arr']['0'][$k][2] = true;
            $s = (count($v) == 1) ? '' : 's';
            $_SESSION['mes']['c'][] = ['time' => '', 'text' => '-- reference to value <b>'.end($_SESSION['cat_arr']['0'][$k]).'</b> (<b>'.$k.'</b>) found in publication'.$s.':', 'status' => 'error'];
            foreach ($v as $z) {
                $_SESSION['mes']['c'][] = ['time' => '', 'text' => '-- -- <b>'.GetFieldByID($dbh, 'publication', 'title', 'id_publication', $z).'</b> (<b>'.$z.'</b>)', 'status' => 'error'];
            }
        }
        $_SESSION['mes']['c'][] = ['time' => '', 'text' => '<b>-- Deletion canceled</b>', 'status' => 'warning'];
    }
    foreach ($_SESSION['del_row']['0'] as $k => $v) {
        $_SESSION['mes']['c'][] = ['time' => '', 'text' => 'Row <b>'.$v['old_value'].'</b> (<b>'.$k.'</b>) will be deleted', 'status' => 'warning'];
    }
}
function CancelDeletion($key_del, $id_row, &$item_for_del)
{
    OldValuesToCat($key_del, '0');
    $arr_pub = explode(',', $id_row);
    $item_for_del[$key_del] = [];
    foreach ($arr_pub as $z) {
        if ($z != '') {
            $item_for_del[$key_del][] = $z;
        }
    }
}
function CoupledReferences($dbh)
{
    $del_row_keys = array_keys($_SESSION['del_row']['1']);
    $del_values = [];
    DelScreenValues('0', $del_row_keys, $del_values);
    DelOutScreenValues($dbh, $del_row_keys, KeysForReq('0'), $del_values);
    if (count($del_values) > 0) {
        $_SESSION['mes']['c'][] = ['time' => '', 'text' => 'The following deleting values have links to catalog <b>'.$_SESSION['Catalog']['0']['name'].'</b>:', 'status' => 'error'];
        foreach ($del_values as $k_del => $v_del) {
            OldValuesToCat($k_del, '0');
            $_SESSION['cat_arr']['1'][$k_del][2] = true;
            $_SESSION['mes']['c'][] = ['time' => '', 'text' => '-- row <b>'.end($_SESSION['cat_arr']['1'][$k_del]).'</b> (<b>'.$k_del.'</b>) refers to', 'status' => 'error'];
            foreach ($v_del as $k => $v) {
                $_SESSION['mes']['c'][] = ['time' => '', 'text' => '-- -- <b>'.$v.'</b> (<b>'.$k.')</b>', 'status' => 'error'];
            }
            unset($_SESSION['del_row']['1'][$k_del]);
        }
        if (count($del_values) > 0) {
            $_SESSION['mes']['c'][] = ['time' => '', 'text' => '<b>-- Deletion canceled</b>. You must first make the appropriate changes to the catalog <b>'.$_SESSION['Catalog']['0']['name'].'</b>', 'status' => 'warning'];
        }
        foreach ($_SESSION['del_row']['1'] as $k => $v) {
            $_SESSION['mes']['c'][] = ['time' => '', 'text' => 'Row <b>'.$v['old_value'].'</b> (<b>'.$k.'</b>) will be deleted', 'status' => 'warning'];
        }
    }
}
function DelScreenValues($n, $del_row_keys, &$test_values)
{
    foreach ($_SESSION['cat_arr'][$n] as $k => $v) {
        SetDelValues($del_row_keys, $k, $v[3], $v[4], $test_values);
    }

    return $test_values;
}
function DelOutScreenValues($dbh, $del_row_keys, $screen_ids, &$test_values)
{
    $adel = [];
    foreach ($del_row_keys as $k) {
        $adel[] = $_SESSION['Catalog']['0']['value']." = '".$k."' OR ".$_SESSION['Catalog']['0']['value']." LIKE '".$k.",%' OR ".$_SESSION['Catalog']['0']['value']."  LIKE '%,".$k.",%' OR ".$_SESSION['Catalog']['0']['value']." LIKE '%,".$k."'";
    }
    if (count($adel) > 0) {
        $ml = GetMaxSetLevel($dbh, $_SESSION['Catalog']['0']['table']);
        $qt = VF($ml, $_SESSION['Catalog']['0']['table'], $_SESSION['Catalog']['0']['id'], $_SESSION['Catalog']['0']['value'], $_SESSION['Catalog']['1']['table'], $_SESSION['Catalog']['1']['id'], $_SESSION['Catalog']['1']['value'], '', chr(34).chr(34), 'f');

        if (! $_SESSION['match_case']) {
            $qt .= ', '.VF($ml, $_SESSION['Catalog']['0']['table'], $_SESSION['Catalog']['0']['id'], $_SESSION['Catalog']['0']['value'], $_SESSION['Catalog']['1']['table'], $_SESSION['Catalog']['1']['id'], $_SESSION['Catalog']['1']['value'].'_low', '', chr(34).chr(34), 'f_l');
        }
        $qt = 'SELECT * FROM (SELECT '.$_SESSION['Catalog']['0']['id'].', '.$_SESSION['Catalog']['0']['value'].', '.$qt.' FROM '.$_SESSION['Catalog']['0']['table'].') AS T ';
        $qt .= 'WHERE ('.implode(' OR ', $adel).') AND '.$_SESSION['Catalog']['0']['id'].' NOT IN ('.$screen_ids.')';
        $qt .= ' ORDER BY '.OrderForSet($ml, 1, 'f', '', $_SESSION['match_case'], $_SESSION['Catalog']['0']['separator']).', '.$_SESSION['Catalog']['0']['id'];
        $res = mysqli_query($dbh, $qt);
        if ($res) {
            while ($row = mysqli_fetch_row($res)) {
                SetDelValues($del_row_keys, (string) $row[0], $row[1], GetQueryValueSet(2, $row, $ml), $test_values);
            }
            mysqli_free_result($res);
        }
    }

    return $test_values;
}
function SetDelValues($del_row_keys, $k, $str_codes, $str_value, &$test_values)
{
    $ed_codes = explode(',', $str_codes);
    foreach ($del_row_keys as $k_del) {
        if (in_array($k_del, $ed_codes)) {
            $test_values[$k_del][$k] = $str_value;
        }
    }
}

function OldValuesToCat($key_cat_arr, $n)
{
    $last_ind = count($_SESSION['cat_arr'][$n][$key_cat_arr]) - 1;
    $_SESSION['cat_arr'][$n][$key_cat_arr][$last_ind - 1] = $_SESSION['del_row'][$n][$key_cat_arr]['old_id'];
    $_SESSION['cat_arr'][$n][$key_cat_arr][$last_ind] = $_SESSION['del_row'][$n][$key_cat_arr]['old_value'];
    $_SESSION['cat_arr'][$n][$key_cat_arr][1] = false;
    unset($_SESSION['del_row'][$n][$key_cat_arr]);
}
function KeysForReq($n)
{
    $cat_keys = array_keys($_SESSION['cat_arr'][$n]);
    $i = array_search('N', $cat_keys); // new??
    if ($i !== false) {
        unset($cat_keys[$i]);
    }

    return implode(',', $cat_keys);
}

function IsChangesAndErrors($n, $pref_text, $post_text, $set_mes = true)
{
    $flag_test = 0;
    if ($n != '') {
        $flag_test = ChangesAndErrorsTest($_SESSION['cat_arr'][$n]);
    } else {
        $flag0 = ChangesAndErrorsTest($_SESSION['cat_arr']['0']);
        $flag1 = ChangesAndErrorsTest($_SESSION['cat_arr']['1']);
        $flag_test = max($flag0, $flag1);
    }
    if ($flag_test == 0) {
        return false;
    } else {
        if ($set_mes) {
            if ($flag_test == 1) {
                $_SESSION['mes']['c'][] = ['time' => '', 'text' => $pref_text.Title(239).$post_text, 'status' => 'warning', 'now' => true];
            } else {
                $_SESSION['mes']['c'][] = ['time' => '', 'text' => $pref_text.' '.Title(471).$post_text, 'status' => 'warning', 'now' => true];
            }
        }

        return true;
    }
}
function ChangesAndErrorsTest($arr_tested)
{
    $flag_test = 0;
    $v = reset($arr_tested);
    while ($v !== false && $flag_test < 2) {
        if ($v[1] && ! $v[2] && $flag_test == 0) {
            $flag_test = 1;
        } elseif ($v[2]) {
            $flag_test = 2;
        }
        $v = next($arr_tested);
    }

    return $flag_test;
}
