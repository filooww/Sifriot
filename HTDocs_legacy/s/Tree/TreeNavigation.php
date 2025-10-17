<?php

function NavigationTree($dbh, $act, $arr_collapse, $settings_pad, &$s_p)
{
    switch ($act) {
        case 'beg': $s_p = 0;
            break;
        case 'pg_up': MoveUp($dbh, 'pg_up', $_SESSION['portion'], $arr_collapse, $s_p);
            break;
        case 'ln_up': MoveUp($dbh, 'ln_up', 1, $arr_collapse, $s_p);
            break;
        case 'ln_dn': MoveDown($dbh, 'ln_dn', $s_p);
            break;
        case 'pg_dn': MoveDown($dbh, 'pg_dn', $s_p);
            break;
        case 'end': MoveEnd($dbh, $s_p, $arr_collapse, $s_p);
            break;
    }
    if ($s_p != $_SESSION['catalog_param']['0']['start_pos']) {
        if (TestNavigation($_SESSION['cat_arr']['0'], $s_p - $_SESSION['catalog_param']['0']['start_pos'])) {
            $_SESSION['catalog_param']['0']['start_pos'] = $s_p;
            GetNewCatalogPortion($dbh, '0', $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['cat_arr']['0'], $settings_pad);
            TestCatalogs($dbh);
        } else {
            $_SESSION['mes']['0'][] = ['time' => '', 'text' => IsChangesAndErrors('0', Title(485).' ', ' '.Title(470)), 'status' => 'warning', 'now' => true];
        }
    }
}
function MoveUp($dbh, $act, $portion, $arr_collapse, &$s_p)
{
    $cur = reset($_SESSION['cat_arr']['0']);
    if ($cur !== false && gettype(key($_SESSION['cat_arr']['0'])) == 'integer') {
        $prev_value = NewNavigationValue($dbh, ($act == 'ln_up') ? 0 : $portion - 1, $arr_collapse, $cur[4]);
        if ($prev_value != '') {
            $s_p = GetOffsetInTable($dbh, '0', $prev_value);
        }
    }
}
function MoveDown($dbh, $act, &$s_p)
{
    $cur = reset($_SESSION['cat_arr']['0']);
    if ($cur !== false && gettype(key($_SESSION['cat_arr']['0'])) == 'integer') {
        if ($act == 'ln_dn') {
            $cur = next($_SESSION['cat_arr']['0']);
        } else {
            $cur = end($_SESSION['cat_arr']['0']);
            if (gettype(key($_SESSION['cat_arr']['0'])) == 'string') {
                $cur = prev($_SESSION['cat_arr']['0']);
            }
        }
        if ($cur !== false && gettype(key($_SESSION['cat_arr']['0'])) == 'integer') {
            $s_p = GetOffsetInTable($dbh, '0', $cur[4]);
        }
    }
}
function MoveEnd($dbh, &$s_p, $arr_collapse, &$s_p)
{
    if (isset($_SESSION['cat_arr']['0']['N']) && count($_SESSION['cat_arr']['0']) - 1 < $_SESSION['portion'] || ! isset($_SESSION['cat_arr']['0']['N']) && count($_SESSION['cat_arr']['0']) < $_SESSION['portion']) {
        $cur = end($_SESSION['cat_arr']['0']);
        if (gettype(key($_SESSION['cat_arr']['0'])) == 'string') {
            $cur = prev($_SESSION['cat_arr']['0']);
        }
        if ($cur !== false) {
            $s_p = GetOffsetInTable($dbh, '0', $cur[4]);
        }
    } else {
        $end_value = NewNavigationValue($dbh, 0, $arr_collapse, '');
        if ($end_value != '') {
            $s_p = GetOffsetInTable($dbh, '0', $end_value);
        }
    }
}
