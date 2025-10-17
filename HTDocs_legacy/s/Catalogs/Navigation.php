<?php

function NavigationActions($dbh, $nav, $n, $settings_pad)
{
    $s_p = $_SESSION['catalog_param'][$n]['start_pos'];
    if ($n == '0' && $_SESSION['Catalog'][$n]['cat_type'] == 1) {
        $arr_collapse = ArrayCollapse();
        if (count($arr_collapse['n_count']) == 0) {
            NavigationNormal($dbh, $nav, $n, $settings_pad, $s_p);
        } else {
            NavigationTree($dbh, $nav, $arr_collapse, $settings_pad, $s_p);
        }
    } else {
        NavigationNormal($dbh, $nav, $n, $settings_pad, $s_p);
    }
}
function GetNewCatalogPortion($dbh, $n, $start_pos, &$cat_arr, $settings_pad)
{
    $old_arr = [];
    foreach ($cat_arr as $k => $v) {
        if (is_numeric($k)) {
            $old_arr[$k] = $v;
        }
    } // new??
    if (isset($cat_arr['0']) && $cat_arr['0'][3] != '') {
        $new_row = $cat_arr['0'];
    }
    $cat_arr = GetCatalogPortion($dbh, $n, $start_pos, $_SESSION['portion'], $settings_pad);
    foreach (array_keys($cat_arr) as $k) {
        if (isset($old_arr[$k])) {
            $cat_arr[$k] = $old_arr[$k];
        }
    }
    if (isset($cat_arr['0']) && isset($new_row)) {
        $cat_arr['0'] = $new_row;
    }
}
function TestNavigation($cat_arr, $dif)
{
    $cur = reset($cat_arr);
    $i = 0;
    while ($cur !== false) {
        if (is_numeric(key($cat_arr)) && ($cur[1] || $cur[2])) { // new??
            if ($dif > 0) {
                if ($i - $dif < 0) {
                    return false;
                }
            } else {
                if ($i - $dif > $_SESSION['portion'] - 1) {
                    return false;
                }
            }
        }
        $cur = next($cat_arr);
        $i++;
    }

    return true;
}
function NavigationNormal($dbh, $act, $n, $settings_pad, &$s_p)
{
    switch ($act) {
        case 'beg': $s_p = 0;
            break;
        case 'pg_up': $s_p = max($_SESSION['catalog_param'][$n]['start_pos'] - $_SESSION['portion'] + 1, 0);
            break;
        case 'ln_up': $s_p = max($_SESSION['catalog_param'][$n]['start_pos'] - 1, 0);
            break;
        case 'ln_dn': $s_p = min($_SESSION['catalog_param'][$n]['start_pos'] + 1, ($_SESSION['catalog_param'][$n]['filter_text'] == '') ? $_SESSION['catalog_param'][$n]['total_count'] - 1 : $_SESSION['catalog_param'][$n]['filter_count'] - 1);
            break;
        case 'pg_dn': $s_p = min($_SESSION['catalog_param'][$n]['start_pos'] + $_SESSION['portion'] - 1, ($_SESSION['catalog_param'][$n]['filter_text'] == '') ? $_SESSION['catalog_param'][$n]['total_count'] - 1 : $_SESSION['catalog_param'][$n]['filter_count'] - 1);
            break;
        case 'end': $s_p = ($_SESSION['catalog_param'][$n]['filter_text'] == '') ? $_SESSION['catalog_param'][$n]['total_count'] - 1 : $_SESSION['catalog_param'][$n]['filter_count'] - 1;
            break;
    }
    if ($s_p != $_SESSION['catalog_param'][$n]['start_pos']) {
        if (TestNavigation($_SESSION['cat_arr'][$n], $s_p - $_SESSION['catalog_param'][$n]['start_pos'])) {
            $_SESSION['catalog_param'][$n]['start_pos'] = $s_p;
            GetNewCatalogPortion($dbh, $n, $_SESSION['catalog_param'][$n]['start_pos'], $_SESSION['cat_arr'][$n], $settings_pad);
            TestCatalogs($dbh);
        } else {
            $_SESSION['mes'][$n][] = ['time' => '', 'text' => IsChangesAndErrors('', Title(237).' ', ''), 'status' => 'warning'];
        }
    }
}
function NewNavigationValue($dbh, $skip_count, $arr_collapse, $start_value)
{
    $new_value = '';
    $lim_area = $skip_count + 1 + array_sum($arr_collapse['n_count']);
    if ($start_value == '') {
        $q_new = QueryTextForSet($dbh, $_SESSION['match_case'], -1, [''], [''], 'f', '', '', (string) $lim_area);
    } else {
        $q_new = QueryTextForSet($dbh, $_SESSION['match_case'], -1, [''], [''], 'f', '<', MCV($start_value, $_SESSION['match_case']), (string) $lim_area);
    }
    $res = mysqli_query($dbh, $q_new);
    if ($res) {
        $new_value = SearchNewValue($dbh, $res, $arr_collapse['n_set'], $skip_count);
        mysqli_free_result($res);
    }

    return $new_value;
}
function SearchNewValue($dbh, $res, $arr_collapse, $skip_count)
{
    $max_lev = GetMaxSetLevel($dbh, $_SESSION['Catalog']['0']['table']);
    $i = 0;
    $new_value = '';
    while ($row = mysqli_fetch_row($res)) {
        if (NodeCollapse((string) $row[0], $row[1], $arr_collapse) > -1) {
            if ($skip_count == 0) {
                return GetQueryValueSet(2, $row, $max_lev);
            } else {
                $i++;
                $new_value = GetQueryValueSet(2, $row, $max_lev);
                if ($i == $skip_count) {
                    return $new_value;
                }
            }
        }
    }
    if ($i < $skip_count) {
        return $new_value;
    }

    return '';
}
