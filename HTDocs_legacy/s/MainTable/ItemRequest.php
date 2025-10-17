<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/CatalogButtons.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Calendar.php';
require_once 'ListSettings.php';

function IncludeToFrom($main_table, $k, $v_con, $view_flag, $w_arr, $sort_arr, $code_flag)
{
    $arr_fl = ['view' => 0, 'filter' => 0, 'sort' => 0, 'code' => 0];
    if ($view_flag && $v_con['screen_order'] > 0) {
        $arr_fl['view'] = 1;
    }
    if (count($w_arr) > 0 && array_key_exists($k, $w_arr)) {
        $arr_fl['filter'] = 1;
    }
    if (count($sort_arr) > 0 && array_key_exists($k, $sort_arr)) {
        $arr_fl['sort'] = 1;
    }
    if ($code_flag && $v_con['table'] != '') {
        $arr_fl['code'] = 1;
    }

    return array_sum($arr_fl) > 0;
}
function ReqDefault($f_type)
{
    switch ($f_type) {
        case 'integer': return '0';
        case 'string': return chr(34).chr(34);
        case 'date': return chr(34).chr(34);
        case 'select': return chr(34).chr(34);
        case 'check': return '0';
        case 'URL_file': return chr(34).chr(34);
        case 'URL_link': return chr(34).chr(34);
        case 'del_mark': return '0';
        case 'ref': return '0';
        default: return chr(34).chr(34);
    }
}
function FromList($p_con, $main_table, $arr_max_level, $call_from, $w_arr, $sort_arr)
{
    $ri = -1;
    $r_flag = false;
    $rf = [];
    $cc = -1;
    $ci = -1;
    if ($call_from == 'portion') {
        foreach ($p_con as $k => $v) {
            if (IncludeToFrom($main_table, $k, $v, true, $w_arr, $sort_arr, false)) {
                InsertField($main_table, $k, $v, $arr_max_level, $rf, $ri, $r_flag, $cc);
            }
        }
        if (! $_SESSION['match_case']) {
            foreach ($p_con as $k => $v) {
                if (IncludeToFrom($main_table, $k, $v, false, $w_arr, $sort_arr, false)) {
                    InsertField($main_table, $k, $v, $arr_max_level, $rf, $ri, $r_flag, $cc);
                }
            }
        }
        foreach ($p_con as $k => $v) {
            if (IncludeToFrom($main_table, $k, $v, false, [], [], true)) {
                InsertCodeField($main_table, $k, $v['ref'], $rf, $cc, $ci);
            }
        }
    } elseif ($call_from == 'offset') {
        foreach ($p_con as $k => $v) {
            if (IncludeToFrom($main_table, $k, $v, false, $w_arr, $sort_arr, false)) {
                InsertField($main_table, $k, $v, $arr_max_level, $rf, $ri, $r_flag, $cc);
            } else {
                foreach ($p_con as $k => $v) {
                    if (IncludeToFrom($main_table, $k, $v, false, $w_arr, [], false)) {
                        InsertField($main_table, $k, $v, $arr_max_level, $rf, $ri, $r_flag, $cc);
                    }
                }
            }
        }
    }
    if ($_SESSION['spec_fld'][1]['key'] == '') {
        return implode(',', $rf);
    } else {
        return ['codes' => $cc, 'key_ind' => $ri, 'inc_fields' => implode(',', $rf)];
    }
}
function InsertField($main_table, $k, $v, $mc, $arr_max_level, &$arr_field, &$f_ind, &$f_flag, &$cc)
{
    $add = (! $mc && $v['low']) ? '_low' : '';
    if (! array_key_exists($k.$add, $arr_field)) {
        $def_v = ReqDefault($v['type']);
        if ($v['table'] == '') {
            $arr_field[$k.$add] = VF(0, '', '', '', '', '', '', $main_table.'.'.$v['ref'].$add, $def_v, $k.$add);
        } elseif ($v['table1'] == '') {
            $arr_field[$k.$add] = VF($arr_max_level[$k], '', '', '', $v['table'], $v['c_id'], $v['c_v'].$add, $main_table.'.'.$v['ref'], $def_v, $k.$add);
        } else {
            $arr_field[$k.$add] = VF($arr_max_level[$k], $v['table'], $v['c_id'], $v['c_v'], $v['table1'], $v['id1'], $v['v1'].$add, $main_table.'.'.$v['ref'], $def_v, $k.$add);
        }
        if ($_SESSION['spec_fld'][1]['key'] != '') {
            if (! $f_flag) {
                $f_ind += (($v['table1'] == '') ? 1 : $arr_max_level[$k]);
            }
            if ($k == $_SESSION['spec_fld'][1]['key']) {
                $f_flag = true;
            }
            $cc += (($v['table1'] == '') ? 1 : $arr_max_level[$k]);
        }
    }
}
function InsertCodeField($main_table, $k, $v_ref, &$arr_field, $cc, &$ci)
{
    if ($ci == -1) {
        $ci = $cc;
    }
    $arr_field[$k.'_code'] = ' CASE WHEN '.$main_table.'.'.$v_ref.' IS NULL THEN 0 ELSE '.$main_table.'.'.$v_ref.' END AS '.$k.'_code';
}
function GetMaxLevels($dbh)
{
    $arr_names = [];
    $arr_table = [];
    foreach ($_SESSION['main_params']['const'][1] as $k => $v) {
        if ($v['table'] != '') {
            $arr_table[] = $v['ref'];
            $arr_names[] = $k;
        }
    }
    $arr = [];
    $res = mysqli_query($dbh, "SELECT table_name, max_level FROM table_definitions WHERE table_name IN ('".implode("','", $arr_table)."')");
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $ml = $row[1];
            $i = array_search($row[0], $arr_table);
            $arr[$arr_names[$i]] = ($row[1] == 0) ? 1 : $row[1];
        }
        mysqli_free_result($res);
    }

    return $arr;
}
function FieldsToArray(&$n, $row, $count_rows = 0, $test_s = '')
{
    if ($count_rows == 0) {
        $str = (string) $row[$n];
        $n++;
    } else {
        $str = '';
        $arr_v = [];
        for ($i = 0; $i < $count_rows; $i++) {
            if ($row[$n + $i] != '') {
                $arr_v[] = (string) $row[$n + $i];
            }
        }
        $str = implode($test_s, $arr_v);
        $n += $count_rows;
    }

    return $str;
}
function GetMTLimit($dbh, $main_table, $wh)
{
    if ($wh == 'total' || $wh == 'active') {
        if ($wh == 'active' && $_SESSION['spec_fld'][1]['del_mark'] != '') {
            $ww = ' WHERE '.$_SESSION['spec_fld'][1]['del_mark'].' = 0';
        } else {
            $ww = '';
        }
        $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM '.$main_table.$ww);
        if ($res) {
            if ($row = mysqli_fetch_row($res)) {
                $ctbl = $row[0];
            } else {
                $ctbl = 0;
            }
            mysqli_free_result($res);

            return $ctbl;
        }

        return 0;
    } else {
        if (FilterEmpty($_SESSION['main_params']['var'][1])) {
            return 0;
        }
        $ctbl = 0;
        $arr_max_level = GetMaxLevels($dbh);
        $w_arr = WhereList($_SESSION['main_params'], $arr_max_level, '');
        $w = (count($w_arr) == 0) ? '' : ' WHERE '.implode(' AND ', $w_arr);
        $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM (SELECT '.FromList($_SESSION['main_params']['const'], $main_table, $arr_max_level, 'limit', $w_arr, []).' FROM '.$main_table.') AS T'.$w);
        if ($res) {
            if ($row = mysqli_fetch_row($res)) {
                $ctbl = $row[0];
            }
            mysqli_free_result($res);
        }

        return $ctbl;
    }
}
function GetItemOffset($dbh, $main_table, &$PR, $p_code, $item_arr_row)
{
    $ctbl = -1;
    $arr_max_level = GetMaxLevels($dbh);
    $sort_arr = SortArray($arr_max_level);
    $w_arr = WhereList($PR, $arr_max_level, '');
    $w_arr[] = 'T.'.$nres['kf'].(($_SESSION['main_params']['var'][1][$nres['kf']]['sort']['sort_mode'] == 1) ? ' <= ' : ' => ').(string) $p_code;
    $where_str = (count($w_arr) == 0) ? '' : $w = ' WHERE '.implode(' AND ', $w_arr);
    $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM (SELECT '.FromList($PR['const'], $main_table, $arr_max_level, 'offset', $w_arr, $sort_arr).' FROM '.$main_table.') AS T'.$where_str.' ORDER BY '.implode(',', $sort_arr));
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $ctbl = $row[0] - 1;
        }
        mysqli_free_result($res);
    }

    return $ctbl;
}
function WhereList($PR, $arr_max_level, $md_active)
{
    $w_arr = [];
    foreach ($_SESSION['main_params']['const'][1] as $k => $v) {
        $dd = ($v['type'] != '' && $v['type'] != 'integer') ? "'" : '';
        $m_case = ($_SESSION['match_case'] || substr($v['ref'], -4) != '_low');
        if ($_SESSION['main_params']['var'][$k]['filter']['md'] < 0) {
            if ($_SESSION['main_params']['var'][$k]['filter']['to'] != '') {
                if ($_SESSION['main_params']['var'][$k]['filter']['iv']) {
                    GetByInterval($dd, $k, $PR['var'][$k]['filter']['text'], $PR['var'][$k]['filter']['to'], $w_arr);
                } elseif ($_SESSION['main_params']['var'][$k]['filter']['text'] != '') {
                    $w_arr[$k] = MCF('T.'.$k, $m_case).' = '.$dd.MCV($_SESSION['main_params']['var'][$k]['filter']['text'], $m_case).$dd;
                }
            } elseif ($_SESSION['main_params']['var'][$k]['filter']['text'] != '') {
                $w_arr[$k] = MCF('T.'.$k, $m_case).' = '.$dd.MCV($_SESSION['main_params']['var'][$k]['filter']['text'], $m_case).$dd;
            }
        } elseif ($_SESSION['main_params']['var'][$k]['filter']['text'] != '') {
            if ($v['table'] != '') {
                if ($v['table1'] != '') {
                    $w_arr[$k] = GetWhere(ConcatPart($arr_max_level[$k], $v['separator'], 'T.'.$k, $m_case), MCV($_SESSION['main_params']['var'][$k]['filter']['text'], $m_case), $_SESSION['main_params']['var'][$k]['filter']['md']);
                } else {
                    $w_arr[$k] = GetWhere(MCF('T.'.$k, $m_case), MCV($_SESSION['main_params']['var'][$k]['filter']['text'], $m_case), $_SESSION['main_params']['var'][$k]['filter']['md']);
                } // md???
            } else {
                $w_arr[$k] = GetWhere(MCF('T.'.$k, $m_case), MCV($_SESSION['main_params']['var'][$k]['filter']['text'], $m_case), $_SESSION['main_params']['var'][$k]['filter']['md']);
            }
        }
    }
    if ($_SESSION['spec_fld'][1]['del_mark'] != '') {
        if ($md_active == 'active') {
            $w_arr[$_SESSION['spec_fld'][1]['del_mark']] = 'T.'.$_SESSION['spec_fld'][1]['del_mark'].' = 0';
        } elseif ($md_active == 'inactive') {
            $w_arr[$_SESSION['spec_fld'][1]['del_mark']] = 'T.'.$_SESSION['spec_fld'][1]['del_mark'].' = 1';
        }
    }

    return $w_arr;
}
function GetByInterval($dd, $k, $f_text, $f_text_to, &$w_arr)
{
    if ($f_text == '' && $f_text_to != '') {
        $w_arr[$k] = MCF('T.'.$k, $_SESSION['match_case']).' <= '.$dd.MCV($f_text_to, $_SESSION['match_case']).$dd;
    } elseif ($f_text != '' && $f_text_to == '') {
        $w_arr[$k] = MCF('T.'.$k, $_SESSION['match_case']).' >= '.$dd.MCV($f_text, $_SESSION['match_case']).$dd;
    } elseif ($f_text != '' && $f_text_to != '') {
        $w_arr[$k] = MCF('T.'.$k, $_SESSION['match_case']).' >= '.$dd.MCV($f_text, $_SESSION['match_case']).$dd.' AND '.MCF('T.'.$k, $_SESSION['match_case']).' <= '.$dd.MCV($f_text_to, $_SESSION['match_case']).$dd;
    }
}
function AddSortKeyField($kf, $m_case, $max_so, &$arr_str)
{
    $_SESSION['main_params']['var'][1][$kf]['sort']['sort_mode'] = 1;
    $_SESSION['main_params']['var'][1][$kf]['sort']['sort_order'] = $max_so + 1;
    $arr_str[$kf] = 'T.'.MCF($kf, $m_case).' ';
}
function SortArray($arr_max_level)
{
    $arr_str = [];
    $arr_k = [];
    $key_fl = false;
    foreach ($_SESSION['main_params']['var'][1] as $k => $v) {
        if ($v['sort']['sort_order'] > 0) {
            if ($k == $_SESSION['spec_fld'][1]['key']) {
                $key_fl = true;
            }
            $arr_k[$k] = $v['sort']['sort_order'];
        }
    }
    if (count($arr_k) > 0) {
        asort($arr_k);
        $max_so = count($arr_k);
        foreach (array_keys($arr_k) as $k) {
            $m_case = ($_SESSION['match_case'] || substr($_SESSION['main_params']['const'][1][$k]['ref'], -4) != '_low');
            if ($_SESSION['main_params']['const'][1][$k]['table1'] != '') {
                $arr_str[$k] = OrderForSet($arr_max_level[$k], $_SESSION['main_params']['var'][1][$k]['sort']['sort_mode'], 'T.', $k, $m_case, $_SESSION['main_params']['const'][1][$k]['separator']);
            } else {
                $arr_str[$k] = 'T.'.MCF($_SESSION['main_params']['const'][1][$k]['ref'], $m_case).' '.(($_SESSION['main_params']['var'][1][$k]['sort']['sort_mode'] > 0) ? '' : 'DESC');
            }
        }
        if (! $key_fl) {
            AddSortKeyField($_SESSION['spec_fld'][1]['key'], $_SESSION['match_case'] || substr($_SESSION['main_params']['const'][1][$_SESSION['spec_fld'][1]['key']]['ref'], -4) != '_low', $max_so, $arr_str);
        }
    } else {
        AddSortKeyField($_SESSION['spec_fld'][1]['key'],
            $_SESSION['match_case'] || substr($_SESSION['main_params']['const'][1][$_SESSION['spec_fld'][1]['key']]['ref'], -4) != '_low', 0, $arr_str);
    }

    return $arr_str;
}
function FilterEmpty($p_var)
{
    foreach ($p_var as $v) {
        if ($v['filter']['text'] != '' || $v['filter']['to'] != '') {
            return false;
        }
    }

    return true;
}
function GetMTPortion($dbh, $main_table, $start_pos, $portion, &$PR, $md_active = 'all')
{
    $f_l = false;
    $item_arr = [];
    $arr_max_level = GetMaxLevels($dbh);
    $sort_arr = SortArray($arr_max_level);
    $w_arr = WhereList($PR, $arr_max_level, $md_active);
    $w = (count($w_arr) == 0) ? '' : $w = ' WHERE '.implode(' AND ', $w_arr);
    $rr = FromList($PR['const'], $main_table, $arr_max_level, 'portion', $w_arr, $sort_arr);
    $res = mysqli_query($dbh, 'SELECT * FROM (SELECT '.$rr['inc_fields'].' FROM '.$main_table.') AS T'.$w.' ORDER BY '.implode(',', $sort_arr).' LIMIT '.(string) $start_pos.','.(string) $portion);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $key_item_arr = (string) $row[$rr['key_ind']];
            $n = 0;
            foreach ($_SESSION['main_params']['const'] as $k => $v) {
                if ($v['table1'] != '') {
                    $item_arr[$key_item_arr][$k] = [FieldsToArray($n, $row, $arr_max_level[$k], $v['separator']), false];
                } elseif ($v['type'] == 'date') {
                    $item_arr[$key_item_arr][$k] = [ToDate(FieldsToArray($n, $row)), '.'];
                } // ???date delimiter
                else {
                    $item_arr[$key_item_arr][$k] = [FieldsToArray($n, $row), false];
                }
            }
            $n = $rr['codes'] + 1;
            SetDeletedLinks($PR['const'], $main_table, $row, $key_item_arr, $item_arr, $n);
        }
        mysqli_free_result($res);
    }

    return $item_arr;
}
function SetDeletedLinks($p_con, $main_table, $row, $key_item_arr, &$item_arr, &$n)
{
    foreach ($p_con as $k => $v) {
        if (IncludeToFrom($main_table, $k, $v, false, [], [], true)) {
            if (array_key_exists($k, $item_arr[$key_item_arr]) && $item_arr[$key_item_arr][$k][0] == '' && $row[$n] > 0) {
                $item_arr[$key_item_arr][$k][1] = true;
                $fl = true;
            }
            $n++;
        }
    }
}
