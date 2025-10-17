<head> <style>.replace_text {background-color:#CCCCCC</style> </head>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tree/Collapse.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Fields/FieldRead.php';

function GetCatalogPortion($dbh, $n, $start_pos, $d_portion)
{
    $single = ($_SESSION['Catalog']['1']['table'] == '' || $_SESSION['Catalog']['1']['table'] != '' && $n == '1');
    if ($n == '0' && $_SESSION['Catalog'][$n]['cat_type'] == 1) {
        $cat_arr = GetTreeCatalogPortion($dbh, $start_pos, $d_portion);
    } else {
        $cat_arr = GetNormalCatalogPortion($dbh, $single, $n, $start_pos, $d_portion);
    }
    if ($_SESSION['user_working_mode'] == 1) {
        AddNewRow($single, $cat_arr);
    }
    if ($_SESSION['catalog_param'][$n]['search_on']) {
        GetSearchPosOut($dbh, $n);
    }

    return $cat_arr;
}
function AddNewRow($single, &$cat_arr)
{
    if (isset($cat_arr['N'])) { // new??
        $new_row = $cat_arr['N']; // new??
        unset($cat_arr['N']); // new??
        $cat_arr['N'] = $new_row; // new??
    } else {
        if ($single) {
            $cat_arr['N'] = [false, false, false, ''];
        } // new??
        else {
            $cat_arr['N'] = [false, false, false, '', ''];
        } // new??
    }
}
function GetRequest($dbh, $single, $n, $start_pos, $d_portion)
{
    if ($single) {
        $str_common = MCF($_SESSION['Catalog'][$n]['value'], $_SESSION['match_case']).','.$_SESSION['Catalog'][$n]['id'].' LIMIT '.(string) $start_pos.','.$d_portion;
        if ($_SESSION['catalog_param'][$n]['filter_text'] == '') {
            $res = mysqli_query($dbh, 'SELECT * FROM '.$_SESSION['Catalog'][$n]['table'].' ORDER BY '.$str_common);
        } else {
            $res = mysqli_query($dbh, 'SELECT * FROM '.$_SESSION['Catalog'][$n]['table'].' WHERE '.$_SESSION['catalog_param'][$n]['filter_where'].' ORDER BY '.str_common);
        }
    } else {
        $f_arr = [MCV($_SESSION['catalog_param'][$n]['filter_text'], $_SESSION['match_case']), $_SESSION['catalog_param'][$n]['filter_compare']];
        $q_text = QueryTextForSet($dbh, $_SESSION['match_case'], 1, $f_arr, [''], 'f', '', '', (string) $start_pos.','.$d_portion);
        if ($q_text == '') {
            $res = false;
        } else {
            $res = mysqli_query($dbh, $q_text);
        }
    }

    return $res;
}
function GetNormalCatalogPortion($dbh, $single, $n, $start_pos, $d_portion)
{
    $cat_arr = [];
    $res = GetRequest($dbh, $single, $n, $start_pos, $d_portion);
    if ($res) {
        if (! $single) {
            $max_lev = GetMaxSetLevel($dbh, $_SESSION['Catalog'][$n]['table']);
        }
        while ($row = mysqli_fetch_row($res)) {
            if ($single) {
                $cat_arr[(string) $row[0]] = [false, false, false, $row[1]];
            } else {
                $cat_arr[(string) $row[0]] = [false, false, false, $row[1], GetQueryValueSet(2, $row, $max_lev)];
            }
        }
        mysqli_free_result($res);
    }

    return $cat_arr;
}
function GetOffsetInTable($dbh, $n, $value)
{
    $ctbl = -1;
    $v = MCV($value, $_SESSION['match_case']);
    $vx = MCF($_SESSION['Catalog'][$n]['value'], $_SESSION['match_case']);
    $single = ($_SESSION['Catalog']['1']['table'] == '' || $_SESSION['Catalog']['1']['table'] != '' && $n == '1');
    if ($single) {
        if ($_SESSION['catalog_param'][$n]['filter_where'] == '') {
            $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM '.$_SESSION['Catalog'][$n]['table'].' WHERE '.$vx." < '".$v."' ORDER BY ".$vx);
        } else {
            $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM '.$_SESSION['Catalog'][$n]['table'].' WHERE '.$vx." < '".$v."' AND ".$_SESSION['catalog_param'][$n]['filter_where'].' ORDER BY '.$vx);
        }
        if ($res) {
            if ($row = mysqli_fetch_row($res)) {
                $ctbl = $row[0];
            }
            mysqli_free_result($res);
        }
    } else {
        $q_text = QueryTextForSet($dbh, $_SESSION['match_case'], 1, [MCV($_SESSION['catalog_param'][$n]['filter_text'], $_SESSION['match_case']), $_SESSION['catalog_param'][$n]['filter_compare']], [''], 'f', '<', $v, '', 'COUNT(*)');
        if ($q_text != '') {
            $res = mysqli_query($dbh, $q_text);
            if ($res) {
                if ($row = mysqli_fetch_row($res)) {
                    $ctbl = $row[0];
                }
                mysqli_free_result($res);
            }
        }
    }

    return $ctbl;
}
function ClearCatalogs()
{
    $_SESSION['Catalog'] = ['0' => ['name' => '', 'tbl' => '', 'id' => '', 'vl' => '', 'in_pub' => '', 's_n' => ''], '1' => ['name' => '', 'tbl' => '', 'id' => '', 'vl' => '']];
    InitCatalogParameters('0');
    InitCatalogParameters('1');
    $_SESSION['cat_arr'] = ['0' => [], '1' => []];
    $_SESSION['cat_mark'] = ['b_on' => false, 'bc' => ''];
    $_SESSION['cat_vis'] = ['b_on' => false, 'bc' => ''];
    $_SESSION['copy_paste'] = ['copy_id' => '', 'parent_value' => '', 'parent_value_text' => '', 'copy_value' => '', 'copy_text_value' => ''];
}
function InitCatalogParameters($catns)
{
    $_SESSION['catalog_param'][$catns]['search_text'] = '';
    $_SESSION['catalog_param'][$catns]['search_compare'] = 1;
    $_SESSION['catalog_param'][$catns]['search_where'] = '';
    $_SESSION['catalog_param'][$catns]['search_on'] = false;
    $_SESSION['catalog_param'][$catns]['filter_text'] = '';
    $_SESSION['catalog_param'][$catns]['filter_compare'] = 1;
    $_SESSION['catalog_param'][$catns]['filter_where'] = '';
    $_SESSION['catalog_param'][$catns]['filter_on'] = false;
    $_SESSION['catalog_param'][$catns]['filter_count'] = 0;
    $_SESSION['catalog_param'][$catns]['total_count'] = 0;
    $_SESSION['catalog_param']['tree_total_count'] = 0;
    $_SESSION['catalog_param'][$catns]['found_count'] = 0;
    $_SESSION['catalog_param'][$catns]['start_pos'] = 0;
    $_SESSION['catalog_param'][$catns]['prev_search_out'] = '';
    $_SESSION['catalog_param'][$catns]['next_search_out'] = '';
    $_SESSION['catalog_param'][$catns]['view_search'] = false;
}
function CatalogCall($dbh, $conf, $s_n, &$block, $settings_pad, $sel_value, &$cur_value)
{
    $_SESSION['del_row'] = ['0' => [], '1' => []];
    if ($s_n != '') {
        $_SESSION['Catalog'] = ReadCatalogFieldDefinitions($dbh, $s_n);
    }
    $block['cat'] = true;
    $ca = [];
    if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
        SetAllCollapse($dbh, '', $ca, false);
    }
    if ($_SESSION['Catalog']['1']['name'] == '') {
        $_SESSION['cat_arr']['1'] = [];
    } else {
        $_SESSION['cat_arr']['1'] = GetCatalogPortion($dbh, '1', $_SESSION['catalog_param']['1']['start_pos'], $_SESSION['portion'], $settings_pad);
        $_SESSION['catalog_param']['1']['total_count'] = GetTableLimit($dbh, 'total', '1');
    }
    if ($sel_value != '') {
        $np = GetOffsetInTable($dbh, '0', $sel_value);
        if ($np > -1 && ($np < $_SESSION['catalog_param']['0']['start_pos'] || $np > $_SESSION['catalog_param']['0']['start_pos'] + (int) $_SESSION['portion'] - 1)) {
            $_SESSION['catalog_param']['0']['start_pos'] = $np;
        }
    }
    $_SESSION['cat_arr']['0'] = GetCatalogPortion($dbh, '0', $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion'], $settings_pad);
    $_SESSION['catalog_param']['0']['total_count'] = GetTableLimit($dbh, 'total', '0');
    if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
        $_SESSION['catalog_param']['0']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['0']['total_count']);
    }
    $cur_value = $sel_value;

    return $_SESSION['Catalog']['0']['s_n']; // !!!!!!!!!!!!! s_n - catalog name !!!!!!!!!!!!!!!!!!!!
}
function CatalogMessage($common_mes)
{
    if ($_SESSION['Catalog']['1']['name'] == '') {
        return Title(399).' <b>'.$_SESSION['Catalog']['0']['name'].'</b> '.$common_mes;
    } else {
        return Title(375).' <b>'.$_SESSION['Catalog']['0']['name'].'</b> '.Title(82).' <b>'.$_SESSION['Catalog']['1']['name'].'</b> '.$common_mes;
    }
}
function ExitMessage()
{
    if ($_SESSION['Catalog']['1']['name'] == '') {
        return FTM(Title(399)).' <b>'.$_SESSION['Catalog']['0']['name'].'</b>';
    } else {
        return FTM(Title(375)).' <b>'.$_SESSION['Catalog']['0']['name'].'</b>, <b>'.$_SESSION['Catalog']['1']['name'].'</b>';
    }
}
function ButtonOff($button_name)
{
    if ($_SESSION[$button_name]['bc'] != '' && array_key_exists($_SESSION[$button_name]['bc'], $_SESSION['cat_arr']['0']) !== false) {
        $_SESSION[$button_name]['bc'] = '';
        $_SESSION[$button_name]['b_on'] = false;
    }
}
function GoToCatalogPosition($dbh, $pos_go_to, $n)
{
    if (! IsChangesAndErrors($n, Title(483).' ', ' '.Title(470))) { // [] = array("time"=>"", "text"=>"To go to this position You must save changes on this page", "status"=>"warning", "log"=>false);
        $_SESSION['catalog_param'][$n]['start_pos'] = (int) $pos_go_to;
        $cat_arr[$n] = GetCatalogPortion($dbh, $n, $_SESSION['catalog_param'][$n]['start_pos'], $_SESSION['portion']);
        TestCatalogs($dbh);
    }
}
?>
