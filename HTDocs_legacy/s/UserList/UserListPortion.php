<?php

function GetUserListPortion($dbh)
{
    $_SESSION['user_list'] = [];
    $_SESSION['user_flag'] = [];
    $_SESSION['update_replace'] = [];
    $_SESSION['update_double'] = [];
    $ord = ($_SESSION['filter_id'] != '' && $_SESSION['filter_name'] == '') ? 'id_user' : 'name';
    if ($_SESSION['start'] >= $_SESSION['user_size']) {
        $_SESSION['start'] = 0;
    }
    $res = mysqli_query($dbh, 'SELECT * FROM user_ident '.$_SESSION['user_filter'].' ORDER BY '.$ord.' LIMIT '.(string) $_SESSION['start'].','.(string) $_SESSION['portion']);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $_SESSION['user_list'][(string) $row[0]] = [$row[1], $row[2], (string) $row[3], $row[4], (string) $row[5], is_null($row[6]) ? 'nu' : (string) $row[6], $row[7], $row[8], $row[9]];
            $_SESSION['user_flag'][(string) $row[0]] = [false, false, false, false, false, false, false, false, false, false, false];
            $_SESSION['update_replace'][(string) $row[0]] = [1 => false, 2 => false, 7 => false, 8 => '', 9 => ''];
            $_SESSION['update_double'][(string) $row[0]] = [1 => '', 2 => ''];
        }
        mysqli_free_result($res);
    }
}
function UserListSize($dbh)
{
    $user_count = 0;
    $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM user_ident '.$_SESSION['user_filter']);
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $user_count = $row[0];
        }
        mysqli_free_result($res);
    }

    return $user_count;
}
function UserListNavigation($dbh, $act, &$Mes)
{
    if ($_SESSION['edit_user'] == '') {
        SetUserFilterPosition($dbh);
        $s_p = ListNewStartPosition($act, $_SESSION['user_size']);
        if ($s_p != $_SESSION['start']) {
            $_SESSION['start'] = $s_p;
            GetUserListPortion($dbh);
            foreach ($_SESSION['user_list'] as $k => $v) {
                TestUser($k, $v);
            }
        }
    } else {
        $Mes[] = "<font color='#FF0000'><b>".Title(240).' '.Title(239).'</b></font>';
    }
}
function StrG($k)
{
    if ($_SESSION['categories'][$k][1] == $_SESSION['categories'][$k][2]) {
        return 'user_priority = '.(string) $_SESSION['categories'][$k][1];
    }

    return 'user_priority >= '.(string) $_SESSION['categories'][$k][1].' AND user_priority <= '.(string) $_SESSION['categories'][$k][2];
}
function StrN($user_category)
{
    $cases = [];
    $cases[] = 'id_user = 0'; // id_user
    if ($user_category != 'guests') {
        $cases[] = 'LENGTH(name) < 3 OR name NOT REGEXP '.UserRegExp(0);
    } // name
    else {
        $cases[] = "name <> CONCAT('_',id_user)";
    } // name
    if ($user_category != 'guests') {
        $cases[] = 'LENGTH(pass) < 6 OR LENGTH(pass) > 12 OR pass REGEXP '.UserRegExp(1);
    } // pass
    else {
        $cases[] = "pass <> CONCAT('#####',id_user)";
    } // pass
    $cases[] = '(SELECT languages.id_language FROM languages WHERE languages.id_language = user_ident.use_lang_id) IS NULL OR user_ident.use_lang_id = 0'; // use_lang_id
    $cases[] = 'user_list_portion < 1'; // user_list_portion
    if ($user_category == 'admins') {
        $cases[] = '(SELECT db_list.db_id FROM db_list WHERE db_list.db_id = user_ident.preffered_db LIMIT 1) IS NULL AND preffered_db IS NOT NULL';
    } // preffered_db
    else {
        $cases[] = '((SELECT db_list.db_id FROM db_list WHERE db_list.db_id = user_ident.preffered_db LIMIT 1) IS NULL OR preffered_db = 0) AND preffered_db IS NOT NULL';
    } // preffered_db
    $cases[] = "SUBSTR(date_format, 2, 3) <> 'dmy' AND SUBSTR(date_format, 2, 3) <> 'mdy' AND SUBSTR(date_format, 2, 3) <> 'ydm' AND SUBSTR(date_format, 2, 3) <> 'ymd'"; // date_format
    $cases[] = 'hide_list > 1';
    $cases[] = 'match_case > 1';

    return '('.implode(' OR ', $cases).')';
}
function GetUserCounts($dbh)
{
    $cat_cond = ['admins' => StrG('admins'), 'users' => StrG('users'), 'guests' => StrG('guests')];
    $arr_counts['admins'] = [$cat_cond['admins'], $cat_cond['admins'].' AND '.StrN('admins')];
    $arr_counts['users'] = [$cat_cond['users'], $cat_cond['users'].' AND '.StrN('users')];
    $arr_counts['guests'] = [$cat_cond['guests'], $cat_cond['guests'].' AND '.StrN('guests')];
    $str_req = "SELECT COUNT(*), 'admins', '3' FROM user_ident WHERE ".$arr_counts['admins'][0]." UNION SELECT COUNT(*), 'admins', '4' FROM user_ident WHERE ".$arr_counts['admins'][1].' ';
    $str_req .= 'UNION ';
    $str_req .= "SELECT COUNT(*), 'users', '3' FROM user_ident WHERE ".$arr_counts['users'][0]." UNION SELECT COUNT(*), 'users', '4' FROM user_ident WHERE ".$arr_counts['users'][1].' ';
    $str_req .= 'UNION ';
    $str_req .= "SELECT COUNT(*), 'guests', '3' FROM user_ident WHERE ".$arr_counts['guests'][0]." UNION SELECT COUNT(*), 'guests', '4' FROM user_ident WHERE ".$arr_counts['guests'][1].' ';
    $str_req .= 'UNION ';
    $str_req .= "SELECT COUNT(*), 'invalid', '3' FROM user_ident WHERE user_priority > 99";
    $res = mysqli_query($dbh, $str_req);
    $_SESSION['categories']['all'][3] = 0;
    $_SESSION['categories']['all'][4] = 0;
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $_SESSION['categories'][$row[1]][(int) $row[2]] = $row[0];
            if ((int) $row[2] == 3) {
                $_SESSION['categories']['all'][3] += $row[0];
            } else {
                $_SESSION['categories']['all'][4] += $row[0];
            }
        }
        mysqli_free_result($res);
        $_SESSION['categories']['all'][4] += $_SESSION['categories']['invalid'][3];
        $_SESSION['categories']['invalid'][4] = $_SESSION['categories']['invalid'][3];
    }
}
function SetUserListPortionSize($int_offs)
{
    $mes_screen = '';
    $f = end($_SESSION['user_flag']);
    $k = key($_SESSION['user_flag']);
    $_SESSION['portion'] = count($_SESSION['user_flag']);
    for ($i = count($_SESSION['user_flag']) - 1; $i >= $int_offs && UserErrorsFree($k, [1, 2, 7, 8, 9], [1, 2]); $i--) {
        array_pop($_SESSION['user_list']);
        array_pop($_SESSION['user_flag']);
        $_SESSION['portion']--;
        $f = end($_SESSION['user_flag']);
        $k = key($_SESSION['user_flag']);
    }
    if ($i >= $int_offs) {
        $mes_screen = "<font color='#FF0000'>".Title(563).' '.Title(513).'</font>';
    }

    return $mes_screen;
}
function SetAlarmUserListPortionSize($int_offs)
{
    $_SESSION['user_list'] = array_slice($_SESSION['user_list'], 0, $int_offs, true);
    $_SESSION['user_flag'] = array_slice($_SESSION['user_flag'], 0, $int_offs, true);
    $_SESSION['portion'] = $int_offs;
}
function UserErrorsFree($k, $r_ind, $d_ind, $f_beg = 0, $f_end = 10)
{
    for ($i = $f_beg; $i < $f_end; $i++) {
        if ($_SESSION['user_flag'][$k][$i]) {
            return false;
        }
    }
    for ($i = 0; $i < count($r_ind); $i++) {
        if ($_SESSION['update_replace'][$k][$r_ind[$i]] || $_SESSION['update_replace'][$k][$r_ind[$i]] != '') {
            return false;
        }
    }
    for ($i = 0; $i < count($d_ind); $i++) {
        if ($_SESSION['update_double'][$k][$d_ind[$i]] != '') {
            return false;
        }
    }

    return true;
}
function UserRegExp($field_number)
{
    if ($field_number == 0) {
        return "'^[a-z0-9 ]+$'";
    } else {
        return "'[''".chr(34)." ]'";
    }
}
function UserListEmpty($dbh_sys)
{
    $fl = true;
    $res = mysqli_query($dbh_sys, 'SELECT COUNT(*) FROM user_ident');
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            if ($row[0] > 0) {
                $fl = false;
            }
        }
    }
    mysqli_free_result($res);

    return $fl;
}
