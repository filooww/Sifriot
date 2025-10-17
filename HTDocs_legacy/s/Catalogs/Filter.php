<?php

function CancelFilter($dbh, $n, $settings_pad)
{
    if (! IsChangesAndErrors($n, Title(481).' ', '')) {
        $_SESSION['catalog_param'][$n]['filter_text'] = '';
        $_SESSION['catalog_param'][$n]['filter_where'] = '';
        $_SESSION['catalog_param'][$n]['filter_on'] = false;
        $_SESSION['catalog_param'][$n]['filter_count'] = 0;
        $_SESSION['catalog_param'][$n]['start_pos'] = 0;
        if ($_SESSION['catalog_param'][$n]['search_on']) {
            $_SESSION['catalog_param'][$n]['found_count'] = GetTableLimit($dbh, 'found', $n, $_SESSION['match_case'], 'f');
        }
        $_SESSION['cat_arr'][$n] = GetCatalogPortion($dbh, $n, $_SESSION['catalog_param'][$n]['start_pos'], $_SESSION['portion'], $settings_pad);
        TestCatalogs($dbh);
    }
}
function ApplyFilter($dbh, $n, $f_test, $f_radio, $settings_pad)
{
    if ($f_text == '') {
        $_SESSION['mes'][$n][] = ['time' => '', 'text' => Title(99).' '.Title(226), 'status' => 'error', 'now' => true];
    } elseif (! IsChangesAndErrors($n, Title(107).' ', '')) { // [] = array("time"=>"", "text"=>Title(107), "status"=>"warning", "log"=>false);
        if ($n == '0' && $_SESSION['Catalog']['1']['table'] != '' && $_SESSION['Catalog'][$n]['cat_type'] == 1) {
            SetAllCollapse($dbh, '-', $_SESSION['cat_arr'][$n], $settings_pad);
        }
        $_SESSION['catalog_param'][$n]['filter_text'] = $f_text;
        $_SESSION['catalog_param'][$n]['filter_compare'] = $f_radio;
        if ($_SESSION['Catalog']['1']['name'] == '' && $n == '0' || $n == '1') {
            $_SESSION['catalog_param'][$n]['filter_where'] = GetWhere(MCF($_SESSION['Catalog'][$n]['value'], $_SESSION['match_case']), MCV($f_text, $_SESSION['match_case']), $_SESSION['catalog_param'][$n]['filter_compare']);
        } else {
            $_SESSION['catalog_param'][$n]['filter_where'] = WhereSet($dbh, $_SESSION['match_case'], $f_text, $_SESSION['catalog_param'][$n]['filter_compare'], 'f');
        }
        $_SESSION['catalog_param'][$n]['start_pos'] = 0;
        $_SESSION['cat_arr'][$n] = GetCatalogPortion($dbh, $n, $_SESSION['catalog_param'][$n]['start_pos'], $_SESSION['portion'], $settings_pad);
        $_SESSION['catalog_param'][$n]['filter_count'] = GetTableLimit($dbh, 'filter', $n, $_SESSION['match_case'], 'f');
        if ($_SESSION['catalog_param'][$n]['search_on']) {
            $_SESSION['catalog_param'][$n]['found_count'] = GetTableLimit($dbh, 'found', $n, $_SESSION['match_case'], 'f');
        }
        TestCatalogs($dbh);
    }
}
function CatalogFilterTest($n, $filter_text, $filter_where)
{
    $filter_type = GetRadioType($filter_where);
    $out_list = [];
    foreach ($_SESSION['cat_arr'][$n] as $v_arr) {
        if ($v_arr[1] && ! $v_arr[2] && end($v_arr) != '') {
            if (strpos(end($v_arr), $filter_text) === false) {
                $out_list[] = end($v_arr);
            } elseif ($filter_type == 1 && end($v_arr) != $filter_text) {
                $out_list[] = end($v_arr);
            } elseif ($filter_type == 2 && strpos(end($v_arr), $filter_text) > 0) {
                $out_list[] = end($v_arr);
            }
        }
    }
    if (count($out_list) > 0) {
        $s = (count($out_list) == 1) ? ' is' : 's are';
        $_SESSION['mes'][$n][] = ['time' => '', 'text' => (((count($out_list) == 1)) ? Title(492) : Title(493)).': ', 'status' => 'warning', 'now' => true];
        foreach ($out_list as $z) {
            $_SESSION['mes'][$n][] = ['time' => '', 'text' => ' - '.$z, 'status' => 'warning', 'now' => true];
        }
        $_SESSION['mes'][$n][] = ['time' => '', 'text' => Title(494), 'status' => 'warning', 'now' => true];

        return false;
    } else {
        return true;
    }
}
