<?php

require_once $_SERVER['DOCUMENT_ROOT'].'../s/Catalogs/CatalogButtons.php';
require_once $_SERVER['DOCUMENT_ROOT'].'../s/Catalogs/CatalogButtons.php';
require_once $_SERVER['DOCUMENT_ROOT'].'../s/Catalogs/Test.php';
require_once $_SERVER['DOCUMENT_ROOT'].'../s/Utilities/Calendar.php';
require_once 'ListSettings.php';

function GetFieldList($own_table, $p_con, $ord_list)
{
    $f_list_arr = ['arr_list' => [], 'key' => -1, 'URL' => -1];
    $i = 0;
    foreach ($p_con as $k => $v) {
        if ($v['own_table'] == 1) {
            $f_list_arr['arr_list'][$k] = $v[$ord_list];
            if ($v['key']) {
                $f_list_arr['key'] = $i;
            }
            if ($v['type'] == 'URL_file') {
                $f_list_arr['key'] = $i;
            }
            $i++;
        }
    }
    asort($f_list_arr['arr_list']);

    return $f_list_arr;
}
function ReadItemFiles($dbh, $main_table, $p_code, $URL_path, $p_con)
{
    $arr = [];

    $f_list_arr = GetFieldList($main_table, $p_con, 'table_order');
    $s_list_arr = GetFieldList($main_table, $p_con, 'screen_order');
    $res = mysqli_query($dbh, 'SELECT '.implode(', ', array_keys($f_list_arr['arr_list'])).' FROM '.$main_table.' WHERE '.$_SESSION['spec_fld'][2]['URL_link'].' = '.(string) $p_code.' ORDER BY '.implode(', ', array_keys($s_list_arr['arr_list'])));
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            foreach ($f_list_arr['arr_list'] as $k => $v) {
                $arr[(string) $row[$f_list_arr['key']]][$k] = $row[$v];
            }
            $arr[(string) $row[$f_list_arr['key']]][$_SESSION['spec_fld'][2]['URL_link']] = $URL_path.'/'.(string) $p_code.'-'.(string) $row[$f_list_arr['key']].'.'.pathinfo((string) $row[$f_list_arr['URL']], PATHINFO_EXTENSION); // ~~url
        }
        mysqli_free_result($res);
    }

    return $arr;
}
function GetItemTotal($item_view_mode, $p_count)
{
    if ($item_view_mode == 'all') {
        return $p_count['total'];
    } elseif ($item_view_mode == 'active') {
        return $p_count['active'];
    } else {
        return $p_count['inactive'];
    }
}
function MTNavigation($dbh, $nav, $t_main, $p_count, &$start_pos, &$item_arr, &$p_code, $user_id, $item_view_mode = '')
{
    if (count($item_arr) > 0) {
        $item_total = ($item_view_mode == '') ? $p_count['total'] : GetItemTotal($item_view_mode, $p_count);
        $s_p = $start_pos;
        switch ($nav) {
            case 'beg': $s_p = 0;
                break;
            case 'pgup': $s_p = max($start_pos - $_SESSION['portion'] + 1, 0);
                break;
            case 'lnup': $s_p = max($start_pos - 1, 0);
                break;
            case 'lndn': $s_p = min($start_pos + 1, (FilterEmpty($_SESSION['main_params']['var'])) ? $item_total - 1 : $p_count['filter'] - 1);
                break;
            case 'pgdn': $s_p = min($start_pos + $_SESSION['portion'] - 1, (FilterEmpty($_SESSION['main_params']['var'])) ? $item_total - 1 : $p_count['filter'] - 1);
                break;
            case 'end': $s_p = (FilterEmpty($_SESSION['main_params']['var'])) ? $item_total - 1 : $p_count['filter'] - 1;
                break;
        }
        if ($s_p != $start_pos) {
            $item_keys = array_keys($item_arr);
            if ($nav == 'end' && $s_p - $start_pos <= $_SESSION['portion']) {
                $p_code = (int) end($item_keys);
            } else {
                $start_pos = $s_p;
                $item_arr = GetMTPortion($dbh, $t_main, $start_pos, $_SESSION['portion'], $_SESSION['main_params'], ($user_id == '') ? $item_view_mode : 'active');
                $item_keys = array_keys($item_arr);
                if (! in_array((string) $p_code, $item_keys)) {
                    $p_code = (int) $item_keys[0];
                }
            }
        }
    }
}
function ChangeMTScreenHeight($dbh, $offs, &$Mes, $user_id = '', $act_mode = 'all')
{
    if (! is_numeric($offs)) {
        $Mes[] = ['time' => '', 'text' => Title(177).' '.Title(77), 'status' => 'error'];
    } elseif ($offs < 1) {
        $Mes[] = ['time' => '', 'text' => Title(178), 'status' => 'error'];
    } else {
        if (abs($offs) == 1) {
            if ($_SESSION['portion'] > 1 || $offs == 1) {
                $_SESSION['portion'] += $offs;
                if ($offs == 1) {
                    $arr_add = GetMTPortion($dbh, $t_main, $_SESSION['p_start'] + $_SESSION['portion'] - 1, 1, $_SESSION['main_params'], ($user_id == '') ? $act_mode : 'active');
                    if (count($arr_add) > 0) {
                        $k = key($arr_add);
                        foreach (array_keys($arr_add[$k]) as $k0) {
                            $_SESSION['item_arr'][$k][$k0][0] = $arr_add[$k][$k0][0];
                        }
                    }
                } elseif (count($_SESSION['item_arr']) == $_SESSION['portion'] + 1) {
                    array_pop($_SESSION['item_arr']);
                }
            }
        } else {
            $_SESSION['portion'] = (int) $offs;
            $_SESSION['item_arr'] = GetMTPortion($dbh, $t_main, $_SESSION['p_start'], $_SESSION['portion'], $_SESSION['main_params'], ($user_id == '') ? $act_mode : 'active');
        }
    }
}
function ChangeMatchCase($dbh, $user_id = '')
{
    $_SESSION['match_case'] = ! $_SESSION['match_case'];
    $_SESSION['p_count']['filter'] = GetMTLimit($dbh, $main_table, 'filter');

    return GetMTPortion($dbh, $main_table, $_SESSION['p_start'], $_SESSION['portion'], $_SESSION['main_params'], ($user_id == '') ? 'all' : 'active');
}
function ParseForSettingActions($arr_key)
{
    switch ($arr_key[0]) {
        case 'cat_exit': return ['act' => 'cat_exit', 'change' => ''];
        case 'cat_height_minus': return ['act' => 'cat_scr_height', 'scr_corr' => -1];
        case 'cat_height_plus': return ['act' => 'cat_scr_height', 'scr_corr' => 1];
        case 'cat_tree': return ['act' => 'cat_tree'];
        case 'cat_tree_collapse': return ['act' => 'cat_tree_col'];
        case 'cat_tree_extend': return ['act' => 'cat_tree_ext'];
        case 'cat_filter0': return ['act' => 'cat_filter_call', 'cat_num' => '0'];
        case 'cat_search0': return ['act' => 'cat_search', 'cat_num' => '0'];
        case 'cat_search_prev0': return ['act' => 'cat_search_move', 'cat_num' => '0', 'dir' => 'back'];
        case 'cat_search_next0': return ['act' => 'cat_search_move', 'cat_num' => '0', 'dir' => 'forward'];
        case 'cat_search_hide0': return ['act' => 'cat_search_hide', 'cat_num' => '0'];
        case 'filter_start0': return ['act' => 'apply_filter', 'cat_num' => '0', 'text' => $_POST['filter_text0'], 'radio' => 'f_mode'];
        case 'search_start0': return ['act' => 'start_search', 'cat_num' => '0', 'text' => $_POST['search_text0'], 'radio' => 's_mode'];
        case 'cat_beg0': return ['act' => 'cat_navigation', 'nv' => 'cat_beg', 'cat_num' => '0'];
        case 'cat_pg_up0': return ['act' => 'cat_navigation', 'nv' => 'cat_pg_up', 'cat_num' => '0'];
        case 'cat_ln_up0': return ['act' => 'cat_navigation', 'nv' => 'cat_ln_up', 'cat_num' => '0'];
        case 'cat_ln_dn0': return ['act' => 'cat_navigation', 'nv' => 'cat_ln_dn', 'cat_num' => '0'];
        case 'cat_pg_dn0': return ['act' => 'cat_navigation', 'nv' => 'cat_pg_dn', 'cat_num' => '0'];
        case 'cat_end0': return ['act' => 'cat_navigation', 'nv' => 'cat_end', 'cat_num' => '0'];
        case 'cat_code0': return ['act' => 'line_select', 'code' => $arr_key[1]];
        case 'set_vis0': return ['act' => 'set_vis', 'code' => $arr_key[1]];
        case 'collapse0': return ['act' => 'collapse', 'code' => $arr_key[1], 'set' => $arr_key[2], 'value' => $_POST['collapse0-'.$arr_key[1].'-'.$arr_key[2]]];
    }

    return [];
}
function ListSettingActions($dbh, $arr_key, &$s, &$Mes, $user_id = '')
{
    $sw_break = true;
    switch ($arr_key[0]) {
        case 'apply_settings':	ApplyAllSettings($dbh, $Mes, $user_id);
            break;
        case 'cancel_sort':		CancelAllSettings($dbh, true, false, $user_id);
            break;
        case 'cancel_filter':	CancelAllSettings($dbh, false, true, $user_id);
            break;
        case 'sort_order':		SetSortOrder($dbh, $arr_key[1], $user_id);
            break;
        case 'filter_to':		FilterMaxValue($dbh, $arr_key[1], $user_id);
            break;
        case 'to_catalog':		CallCatalogForSelect($dbh, $arr_key[1], $Mes, $user_id);
            break;
        default:				$sw_break = false;
    }
    if (! $sw_break) {
        $aA = ParseForSettingActions($arr_key);
        if (count($aA) > 0) {
            $cur_text = ($s['cur_cat'] == '') ? '' : $s['main_params']['var'][$s['cur_cat']]['filter']['text'];
            $sw_break = CatalogActions($dbh, '0', $aA, $cur_text, $s['cur_value'], $Mes, true, $user_id);
        } elseif (strpos($arr_key[0], 'calendar') !== false) {
            $sw_break = CalendarSelect($arr_key, $s['conf']['start_year'], $s['calend_date'], $s['PR']['var'], $Mes);
        }
    }

    return $sw_break;
}
function ApplyAllSettings($dbh, &$Mes, $user_id)
{
    $Mes = TestSettings($user_id);
    if (count($Mes) == 0) {
        $_SESSION['set_pad'] = false;
        $_SESSION['block']['pad'] = false;
        RememberSettings($_SESSION['PR']['var']);
        ApplySettings($dbh, $_SESSION['db_info']['t_main'], $_SESSION['p_code'], $_SESSION['item_arr'], $_SESSION['p_start'], $_SESSION['p_count']['filter'], $Mes, $user_id);
        $_SESSION['cur_cat'] = '';
        if ($user_id != '') {
            SaveFieldsSettings($dbh, $_SESSION['PR']['var'], $user_id);
        }
    } else {
        $Mes[] = ['time' => '', 'text' => Title(75), 'status' => 'error'];
    }

    return $Mes;
}
function TestDateFilter($p_name, $v)
{
    $mm = '';
    $date_err = CalendTestDate($v);
    if (count($date_err) > 0) {
        $mm = 'The <b>'.$p_name.'</b>: '.implode('; ', $date_err);
    }

    return $mm;
}
function TestSettings($user_id)
{
    $Mes = [];
    foreach ($main_params['const'] as $k => $v) {
        if ($user_id == '' || $user_id != '' && strpos($v['using'], 'view') !== false) {
            $mm = '';
            if ($v['type'] == 'integer' || $v['type'] == 'date') {
                if (isset($_POST['filter_max-'.$k])) {
                    if ($_POST['filter_text-'.$k] != '' && $_POST['filter_max-'.$k] == '' || $_POST['filter_text-'.$k] == '' && $_POST['filter_max-'.$k] != '') {
                        if ($v['type'] == 'integer' && (! is_numeric($_POST['filter_text-'.$k]) || ! is_numeric($_POST['filter_max-'.$k]))) {
                            $mm = 'The <b>'.$v['name'].'</b> '.Title(77);
                        } elseif ($v['type'] == 'date') {
                            if ($_POST['filter_text-'.$k] != '') {
                                $mm = TestDateFilter($v['name'], $_POST['filter_text-'.$k]);
                            } elseif ($_POST['filter_max-'.$k] != '') {
                                $mm = TestDateFilter($v['name'], $_POST['filter_max-'.$k]);
                            }
                        }
                    } elseif ($_POST['filter_text-'.$k] != '' && $_POST['filter_max-'.$k] != '') {
                        if ($v['type'] == 'integer') {
                            if (! is_numeric($_POST['filter_text-'.$k]) && is_numeric($_POST['filter_max-'.$k])) {
                                $mm = '<b>'.$v['name'].'</b>: '.Title(78).' '.Title(77);
                            } elseif (is_numeric($_POST['filter_text-'.$k]) && ! is_numeric($_POST['filter_max-'.$k])) {
                                $mm = '<b>'.$v['name'].'</b>: '.Title(78).' '.Title(77);
                            } elseif (! is_numeric($_POST['filter_text-'.$k]) && ! is_numeric($_POST['filter_max-'.$k])) {
                                $mm = '<b>'.$v['name'].'</b>: '.Title(80).' '.Title(77);
                            }
                        } else {
                            $m1 = TestDateFilter($v['name'], $_POST['filter_text-'.$k]);
                            $m2 = TestDateFilter($v['name'], $_POST['filter_max-'.$k]);
                            if ($m1 != '' && $m2 == '') {
                                $mm = '<b>'.$v['name'].'</b>: '.Title(78).' '.Title(81);
                            } elseif ($m1 == '' && $m2 != '') {
                                $mm = '<b>'.$v['name'].'</b>: '.Title(79).' '.Title(81);
                            } elseif ($m1 != '' && $m2 != '') {
                                $mm = '<b>'.$v['name'].'</b>: '.Title(80).' '.Title(81);
                            }
                        }
                    }
                } elseif ($_POST['filter_text-'.$k] != '') {
                    if ($v['type'] == 'integer' && ! is_numeric($_POST['filter_text-'.$k])) {
                        $mm = '<b>'.$v['name'].'</b>: '.Title(79).' '.Title(77);
                    } elseif ($v['type'] == 'date' && $_POST['filter_text-'.$k] != '') {
                        $mm = TestDateFilter($v['name'], $v['filter']['text']);
                    }
                }
            }
            if ($mm != '') {
                $Mes[] = ['time' => '', 'text' => $mm, 'status' => 'error'];
            }
        }
    }

    return $Mes;
}
function CancelAllSettings($dbh, $f_sort, $f_filter, $user_id)
{
    RememberSettings($_SESSION['PR']['var']);
    ResetSettings($dbh, [$f_sort, $f_filter], $_SESSION['PR']['var']);
    if ($user_id != '') {
        SaveFieldsSettings($dbh, $_SESSION['PR']['var'], $user_id);
    }
}
function SetSortOrder($dbh, $sort_field_ID, $user_id)
{
    OrderSortingButton($sort_field_ID, $_SESSION['PR']['var']);
    RememberSettings($_SESSION['PR']['var']);
    if ($user_id != '') {
        SaveFieldsSettings($dbh, $_SESSION['PR']['var'], $user_id);
    }
}
function FilterMaxValue($dbh, $filter_field_ID, $user_id)
{
    $_SESSION['main_params']['var'][1][$filter_field_ID]['filter']['iv'] = ! $_SESSION['main_params']['var'][1][$filter_field_ID]['filter']['iv'];
    RememberSettings($_SESSION['PR']['var']);
    if ($user_id != '') {
        SaveFieldsSettings($dbh, $_SESSION['PR']['var'], $user_id);
    }
}
function CallCatalogForSelect($dbh, $filterField_ID, &$Mes, $user_id)
{
    if ($_SESSION['cur_cat'] != $filterField_ID) {
        InitCatalogParameters('0');
        if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
            SetAllCollapse($dbh, '', $_SESSION['cat_arr']['0'], true);
        }
    }
    $_SESSION['block']['pad_cat'] = true;
    $_SESSION['cur_cat'] = $filterField_ID;
    $_SESSION['cat_flag'] = CatalogSelectCall($dbh, $_SESSION['conf'], $filterField_ID, $user_id, $_SESSION['PR']['var'][$_SESSION['cur_cat']]['filter']['text'], $_SESSION['cur_value']);
    TestCatalogs($dbh);
    RememberSettings($_SESSION['PR']['var']);
    if ($user_id != '') {
        SaveFieldsSettings($dbh, $_SESSION['PR']['var'], $user_id);
    }
}
function DeleteFilesDir($del_dir, $item_id, $p_files, $file_id = '')
{
    $dir = opendir($del_dir);
    if ($dir) {
        while (($file = readdir($dir)) !== false) {
            $arr_f = explode('-', pathinfo($file, PATHINFO_FILENAME));
            $cnt = count($arr_f);
            if ($cnt > 1 && $arr_f[0] == (string) $item_id) {
                if (count($p_files) == 0) {
                    if ($file_id == '') {
                        unlink($del_dir.'/'.$file);
                    } elseif ($arr_f[1] == $file_id) {
                        unlink($del_dir.'/'.$file);
                    }
                } elseif (($file_id != '' && $arr_f[1] == $file_id || $file_id == '') && ! array_key_exists($arr_f[1], $p_files)) {
                    unlink($del_dir.'/'.$file);
                }
            }
        }
        closedir($dir);
    }
}
