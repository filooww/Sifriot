<?php

function SetUserFilterPosition($dbh)
{
    $old_filter = $_SESSION['user_filter'];
    $_SESSION['user_filter'] = GetUserListFilter();
    if ($_SESSION['user_filter'] != $old_filter) {
        GetUserCounts($dbh);
        if ($old_filter == '') {
            $_SESSION['start'] = 0;
        } else {
            $first_key = FindFirstUser();
            if ($first_key == '') {
                $_SESSION['start'] = 0;
            } else {
                GetUserStartPosition($dbh, $first_key);
            }
        }
    }
}
function FindFirstUser()
{
    foreach ($_SESSION['user_list'] as $k => $v) {
        if (UserRowComparison($k, $v)) {
            return $k;
        }
    }

    return '';
}
function UserRowComparison($k, $v)
{
    if ($_SESSION['filter_id'] != '' && $k != $_SESSION['filter_id']) {
        return false;
    }
    if ($_SESSION['filter_name'] != '' && strpos($v[0], FilterToUserName()) === false) {
        return false;
    }
    if ($_SESSION['category'][0] != 'all' && $_SESSION['category'][0] != 'invalid' && (int) $v[2] < $_SESSION['categories'][$_SESSION['category'][0]][1] && (int) $v[2] > $_SESSION['categories'][$_SESSION['category'][0]][2]) {
        return false;
    }
    if ($_SESSION['category'][0] == 'invalid' && (int) $v[2] >= 0 && (int) $v[2] <= 99) {
        return false;
    }
    if ($_SESSION['filter_error']) {
        return ComparisonErrors($k, $v);
    }

    return true;
}
function FilterToUserName()
{
    if (strpos($_SESSION['filter_name'], chr(39)) !== false) {
        return str_replace(chr(39), $_SESSION['apostrophe_replace'], $_SESSION['filter_name']);
    } else {
        return $_SESSION['filter_name'];
    }
}
function ComparisonErrors($k, $v)
{
    if ((int) ($k) <= 0) {
        return true;
    }
    if ((int) $v[2] < 0 || (int) $v[2] > 99) {
        return true;
    }
    if ((int) $v[2] = 0 && ($v[0] != '_'.$k || $v[1] != '#####'.$k)) {
        return true;
    }
    if (! TestUserName($v[0]) || ! TestUserPassword($v[1])) {
        return true;
    }
    if (! isset($_SESSION['user_langs'][$v[3]])) {
        return true;
    }
    if ((int) $v[4] < 1) {
        return true;
    }
    if ((int) $v[2] == 0 && gettype($v[5]) == 'string' || (int) $v[2] > 0 && ! isset($_SESSION['list_db'][$v[5]])) {
        return true;
    }
    if (substr($v[6], 1) != 'dmy' && substr($v[6], 1) != 'mdy' && substr($v[6], 1) != 'ydm' && substr($v[6], 1) != 'ymd') {
        return true;
    }
    if ($v[7] != 0 && $v[7] != 1) {
        return true;
    }
    if ($v[8] != 0 && $v[8] != 1) {
        return true;
    }

    return false;
}
function GetUserStartPosition($dbh, $k)
{
    $w_w = ($_SESSION['user_filter'] == '') ? 'WHERE' : 'AND';
    $ord_field = ($_SESSION['filter_id'] != '' && $_SESSION['filter_name'] == '') ? 'id_user' : 'name';
    $lim_value = ($_SESSION['filter_id'] != '' && $_SESSION['filter_name'] == '') ? $k : "'".$_SESSION['user_list'][$k][0]."'";
    $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM user_ident '.$_SESSION['user_filter'].' '.$w_w.' '.$ord_field.' < '.$lim_value.' ORDER BY '.$ord_field);
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $s_p = $row[0];
        }
        mysqli_free_result($res);
    }
    if (isset($s_p)) {
        $_SESSION['start'] = $s_p;
    }
    if ($_SESSION['update_replace'][$k][1]) {
        $_SESSION['start'] = max($_SESSION['start'] - 1, 0);
    }
}
function UserFilter($dbh)
{
    SetUserFilterPosition($dbh);
    $_SESSION['user_size'] = UserListSize($dbh);
    GetUserListPortion($dbh);
    foreach ($_SESSION['user_list'] as $k => $v) {
        TestUser($k, $v);
    }
}
function ChangeFilterString($spec_symb_code, &$str_filter)
{
    $str_filter = str_replace(chr($spec_symb_code), chr(33).chr($spec_symb_code), $str_filter);

    return true;
}
function UserFilterNameSettings()
{
    $w_str = $_SESSION['filter_name'];
    if ($_SESSION['double_quote_fix']) {
        str_replace(chr(34).chr(34), chr(34), $w_str);
    } // "
    $esc_flag = false;
    if (strpos($w_str, chr(37)) !== false) {
        $esc_flag = ChangeFilterString(37, $w_str);
    } // %
    if (strpos($w_str, chr(95)) !== false) {
        $esc_flag = ChangeFilterString(95, $w_str);
    } // _
    if (strpos($w_str, chr(92)) !== false) {
        $esc_flag = ChangeFilterString(92, $w_str);
    } // \
    if (strpos($w_str, chr(39)) !== false) {
        $w_str = str_replace(chr(39), chr(39).chr(39), $w_str);
    } // '
    $w_str = "name LIKE '%".$w_str."%'";
    if ($esc_flag) {
        $w_str .= " ESCAPE '!'";
    }

    return $w_str;
}
function GetUserListFilter()
{
    $arr_ww = [];
    if ($_SESSION['filter_id'] != '') {
        $arr_ww[] = 'id_user >= '.$_SESSION['filter_id'];
    }
    if ($_SESSION['filter_name'] != '') {
        $arr_ww[] = UserFilterNameSettings();
    }
    if ($_SESSION['category'][0] != 'all' && $_SESSION['category'][0] != 'invalid') {
        $arr_ww[] = 'user_priority >= '.$_SESSION['categories'][$_SESSION['category'][0]][1].' AND user_priority <='.$_SESSION['categories'][$_SESSION['category'][0]][2];
    } elseif ($_SESSION['category'][0] == 'invalid') {
        $arr_ww[] = '(user_priority < 0 OR user_priority > 99)';
    }
    if ($_SESSION['filter_error']) {
        $arr_ww[] = '('.UserListErrorCondition().')';
    }

    return (count($arr_ww) == 0) ? '' : ' WHERE '.implode(' AND ', $arr_ww);
}
function UserListErrorCondition()
{
    $str_where = 'id_user = 0 OR ';
    $str_where .= "user_priority = 0 AND (name <> CONCAT('_',id_user) OR pass <> CONCAT('#####',id_user)) OR ";
    $str_where .= 'user_priority > 0 AND user_priority < 100 AND (LENGTH(name) < 3 OR name NOT REGEXP '.UserRegExp(0).' OR LENGTH(pass) < 6 OR LENGTH(pass) > 12 OR pass REGEXP '.UserRegExp(1).') OR ';
    $str_where .= 'user_priority > 99 OR ';
    $str_where .= '(SELECT languages.id_language FROM languages WHERE languages.id_language = use_lang_id) IS NULL OR use_lang_id = 0 OR ';
    $str_where .= 'user_list_portion < 1 OR ';
    $str_where .= '(user_priority > 10 AND user_priority < 100 AND ((SELECT db_list.db_id FROM db_list WHERE db_list.db_id = user_ident.preffered_db LIMIT 1) IS NULL AND preffered_db IS NOT NULL)) OR ';
    $str_where .= '(user_priority >= 0 AND user_priority < 11 AND (((SELECT db_list.db_id FROM db_list WHERE db_list.db_id = user_ident.preffered_db LIMIT 1) IS NULL OR preffered_db = 0) AND preffered_db IS NOT NULL)) OR ';
    $str_where .= "SUBSTR(date_format, 2, 3) <> 'dmy' AND SUBSTR(date_format, 2, 3) <> 'mdy' AND SUBSTR(date_format, 2, 3) <> 'ydm' AND SUBSTR(date_format, 2, 3) <> 'ymd' OR ";
    $str_where .= 'hide_list > 1 OR ';
    $str_where .= 'match_case > 1';

    return $str_where;
}
function UserResetFilter()
{
    $_SESSION['filter_name'] = '';
    $_SESSION['filter_id'] = '';
    $_SESSION['filter_error'] = false;
    $_SESSION['edit_user'] = '';
    $_SESSION['category'] = ['all', Title(169)];
    $_SESSION['user_filter'] = '';
}
