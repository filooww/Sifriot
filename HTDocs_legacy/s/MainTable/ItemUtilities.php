<?php

require_once '../../Utilities/TestScripts.php';
require_once '../../LogProcessing/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/MainTable/CommonUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Calendar.php';
require_once 'MTRequests.php';

function InitItem($dbh, $cury, $item_arr, $k)
{
    $item_row = ['e' => [], 'i' => []];
    if ($k == 0) {
        $item_row['e'] = EmptyRow($cury);
        $item_row['i'] = EmptyRow($cury);
    } else {
        $item_row['e'] = CopyRow($k, $item_arr);
        $item_row['i'] = CopyRow($k, $item_arr);
    }

    return $item_row;
}
function InitFiles($dbh, $URL_path, $p_id, $p_con, &$Mes)
{
    $p_files = ['e' => [], 'i' => []];
    $p_files['i'] = ReadItemFiles($dbh, 'files', $p_id, $URL_path, $p_con);
    foreach ($p_files['i'] as $k => $v) {
        foreach ($p_con as $k_c => $v_c) {
            $p_files['e'][$k][$k_c] = $v[$k_c];
        }
    }
    DeleteDeleted($dbh, $URL_path, $p_id, $p_files['i'], $Mes);

    return $p_files;
}
function EmptyRow($cury)
{
    $c_row = [];
    foreach ($p_con as $k => $v) {
        if ($v['type'] == 'integer') {
            $c_row[$k] = 0;
        } elseif ($v['type'] == 'date') {
            $c_row[$k] = GetCurrentDate();
        } else {
            $c_row[$k] = '';
        }
        if ($v['table'] != '') {
            $c_row[$k.'_code'] = 0;
        }
    }

    return $c_row;
}
function CopyRow($k, $item_arr)
{
    $c_row = [];
    $v = $item_arr[$k][0];
    foreach ($v as $k0 => $v0) {
        $c_row[$k0] = $v[$k0];
    }

    return $c_row;
}
function PostValue($post_value, $p_con_type)
{
    if ($post_value != '') {
        return $post_value;
    } elseif ($p_con_type == 'integer') {
        return '0';
    } else {
        return '';
    }
}
function AddRefCatalog($act, $ref_field, $ref_value, &$arr_fields, &$arr_values)
{
    if ($act == 'insert') {
        $arr_fields[] = $ref_field;
        $arr_values[] = (string) $ref_value;
    } else {
        $arr_values[] = $ref_field.' = '.(string) $ref_value;
    }
}
function AddPostValue($act, $source_post_value, $k, $p_con, $ref_field, &$arr_fields, &$arr_values)
{
    $post_value = PostValue($source_post_value, $p_con[$k]['type']);
    $ref_value = GetQueryValue($p_con, $k, $post_value);
    if ($act == 'insert') {
        $arr_fields[] = $ref_field;
        $arr_values[] = $ref_value;
    } else {
        $arr_values[] = $ref_field.' = '.$ref_value;
    }
    if ($p_con[$k]['low']) {
        $ref_value = GetQueryValue($p_con, $k, MCV($post_value, false));
        if ($act == 'insert') {
            $arr_fields[] = $ref_field.'_low';
            $arr_values[] = $ref_value;
        } else {
            $arr_values[] = $ref_field.'_low = '.$ref_value;
        }
    }
}
function ItemQueryText($act, $item_row, $p_con)
{
    $arr_fields = [];
    $arr_values = [];
    foreach ($item_row['e'] as $k => $v_row) {
        if (strpos($k, '_code') === false) {
            $source_f = (isset($_POST[$k])) ? $_POST[$k] : $v_row;
            $v = (isset($p_con[$k]['default']) && $source_f == '') ? $p_con[$k]['default'] : $source_f;
            if (isset($p_con[$k]['table']) && ($act == 'insert' || $item_row['e'][$k] != $item_row['i'][$k])) {
                AddRefCatalog($act, $p_con[$k]['ref'], $item_row['e'][$k], $arr_fields, $arr_values);
            } elseif ($act == 'insert' || $v != $item_row['i'][$k]) {
                AddPostValue($act, $v, $k, $p_con, $p_con[$k]['ref'], $arr_fields, $arr_values);
            }
        }
    }
    if (count($arr_values) > 0) {
        if ($act == 'insert') {
            return '('.implode(', ', $arr_fields).') VALUES ('.implode(',', $arr_values).')';
        } else {
            return implode(', ', $arr_values);
        }
    } else {
        return '';
    }
}
function GetQueryValue($p_con, $k, $item_row_value)
{
    $col_type = (isset($p_con[$k]['type'])) ? $p_con[$k]['type'] : '';
    switch ($col_type) {
        case 'integer': return (string) $item_row_value;
        case 'date': return "'".GetDBDate($item_row_value)."'";
        default: return "'".$item_row_value."'";
    }
}
function AfterItemEdit($dbh)
{
    $in_filter = TestPubForFilter($_SESSION['PR'], (string) $_SESSION['p_code'], $_SESSION['item_row']['e'], $_SESSION['mes']['m']);
    $no_sort = NoSort($_SESSION['PR']['var']);
    if (! $in_filter) {
        ResetSettings($dbh, [false, true], $_SESSION['PR']['var']);
    }
    $pos_p_code = GetItemOffset($dbh, $_SESSION['match_case'], $_SESSION['db_info']['t_main'], $_SESSION['PR'], $_SESSION['p_code'], $_SESSION['item_row']['e']);
    $i_pos = array_search((string) $_SESSION['p_code'], array_keys($_SESSION['item_arr']));
    $_SESSION['p_start'] = ($pos_p_code == -1) ? 0 : max($pos_p_code - $i_pos, 0);
    $_SESSION['item_arr'] = GetMTPortion($dbh, $_SESSION['db_info']['t_main'], $_SESSION['p_start'], $_SESSION['portion'], $_SESSION['PR'], $_SESSION['match_case']);
    if (! $in_filter) {
        $_SESSION['mes']['m'] = ['time' => '', 'text' => 'The item (<b>'.(string) $_SESSION['p_code'].'</b>) does not meet the selection criteria. The selection was canceled.', 'status' => 'warning'];
    }
    if (! $no_sort) {
        $_SESSION['mes']['m'] = ['time' => '', 'text' => 'The list on the screen has changes due to editing. Sorting by name is set.', 'status' => 'warning'];
    }
}
function NoSort($p_var)
{
    foreach ($p_var as $v) {
        if ($v['sort']['sort_order'] > 0) {
            return false;
        }
    }

    return true;
}
function ItemFormExit($dbh, &$_SESSION, $exit_mode = '')
{
    if ($exit_mode == 'save') {
        if (SaveItem($dbh, $_SESSION)) {
            AfterItemEdit($dbh);
            $_SESSION['block']['item_exit'] = false;
            $_SESSION['old_p_code'] = 0;

            return true;
        } else {
            $_SESSION['block']['item_exit'] = false;

            return false;
        }
    } else {
        if ($_SESSION['p_code'] == 0) {
            $_SESSION['p_code'] = $_SESSION['old_p_code'];
        }
        $_SESSION['block']['item_exit'] = false;
        $_SESSION['mes']['m'] = [];

        return true;
    }
}
function PubMarkDelete($dbh, &$_SESSION)
{
    $_SESSION['item_arr'][$_SESSION['p_code']]['del_mark'][0] = ! $_SESSION['item_arr'][$_SESSION['p_code']]['del_mark'][0];
    $res = mysqli_query($dbh, 'UPDATE '.$_SESSION['db_info']['t_main'].' SET '.$_SESSION['spec_fld'][1]['del_mark'].' = '.(($_SESSION['item_arr'][$_SESSION['p_code']]['del_mark'][0]) ? '1' : '0').' WHERE '.$_SESSION['spec_fld'][1]['key'].' = '.(string) $_SESSION['p_code']);
    if ($_SESSION['item_arr'][$_SESSION['p_code']]['del_mark'][0]) {
        $_SESSION['p_count']['active']--;
        $_SESSION['p_count']['inactive']++;
    } else {
        $_SESSION['p_count']['active']++;
        $_SESSION['p_count']['inactive']--;
    }
    if ($_SESSION['item_view_mode'] != 'all') {
        $_SESSION['item_arr'] = GetMTPortion($dbh, $_SESSION['db_info']['t_main'], $_SESSION['p_start'], $_SESSION['portion'], $_SESSION['PR'], $_SESSION['item_view_mode']);
    }
}
function SetViewMode($dbh, &$_SESSION, $post_view_mode)
{
    if ($_SESSION['item_view_mode'] != $post_view_mode) {
        $old_mode = $_SESSION['item_view_mode'];
        $_SESSION['item_view_mode'] = $post_view_mode;
        if ($_SESSION['p_count']['total'] > 0 && $_SESSION['p_count']['active'] > 0) {
            $_SESSION['item_arr'] = GetMTPortion($dbh, $_SESSION['db_info']['t_main'], $_SESSION['p_start'], $_SESSION['portion'], $_SESSION['PR'], $_SESSION['item_view_mode']);
            if ($old_mode == 'all' && $_SESSION['item_view_mode'] == 'active') {
                $_SESSION['p_code'] = FindNewPubID($_SESSION['item_arr'], false);
            } elseif ($old_mode == 'all' && $_SESSION['item_view_mode'] == 'inactive') {
                $_SESSION['p_code'] = FindNewPubID($_SESSION['item_arr'], true);
            } elseif ($old_mode == 'active' && $_SESSION['item_view_mode'] == 'inactive') {
                $_SESSION['p_code'] = FindNewPubID($_SESSION['item_arr'], true);
            } elseif ($old_mode == 'inactive' && $_SESSION['item_view_mode'] == 'active') {
                $_SESSION['p_code'] = FindNewPubID($_SESSION['item_arr'], false);
            }
        }
    }
}
function FindNewPubID($item_arr, $del_mark_flag)
{
    foreach ($item_arr as $k => $v) {
        if ($v['del_mark'][0] == $del_mark_flag) {
            return (int) $k;
        }
    }
    if (count($item_arr) == 0) {
        return 0;
    }

    return (int) GetFirstKey($item_arr);
}
