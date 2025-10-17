<?php

function ChangeUserListCategory()
{
    $_SESSION['categories']['all'][0] = Title(169);
    $_SESSION['categories']['admins'][0] = Title(135);
    $_SESSION['categories']['users'][0] = Title(136);
    $_SESSION['categories']['guests'][0] = Title(137);
    $_SESSION['categories']['invalid'][0] = Title(232);
}
function TestUser($k, $v)
{
    if (! is_numeric($k)) {
        $_SESSION['user_flag'][$k][0] = true;
    } elseif ((int) $k == 0) {
        $_SESSION['user_flag'][$k][0] = true;
    }
    if (! is_numeric($v[2]) || is_numeric($v[2]) && (strpos($v[2], '.') !== false || (int) $v[2] < 0 || (int) $v[2] > 99)) {
        $_SESSION['user_flag'][$k][3] = true;
    }
    $_SESSION['user_flag'][$k][1] = ! UserParamTest($v[0], $k, $v[2], 0, 1);
    $_SESSION['user_flag'][$k][2] = ! UserParamTest($v[1], $k, $v[2], 1, 2);
    if (! isset($_SESSION['user_langs'][$v[3]])) {
        $_SESSION['user_flag'][$k][4] = true;
    }
    if (! is_numeric($v[4]) || is_numeric($v[4]) && (strpos($v[2], '.') !== false || (int) $v[4] < 1)) {
        $_SESSION['user_flag'][$k][5] = true;
    }
    $_SESSION['user_flag'][$k][6] = PrefDBTest($k, $v[2], $v[5]);
    $_SESSION['user_flag'][$k][7] = ! PrefDateTest($v[6], $k);
    if ($v[7] != 0 && $v[7] != 1) {
        ReplaceUserCheck($k, 7, 8, $v[7]);
    }
    if ($v[8] != 0 && $v[8] != 1) {
        ReplaceUserCheck($k, 8, 9, $v[8]);
    }
}
function ReplaceUserApostrophe($k, $param_num, $flag_num)
{
    $_SESSION['user_list'][$k][$param_num] = str_replace(chr(39), $_SESSION['apostrophe_replace'], $_SESSION['user_list'][$k][$param_num]);
    $_SESSION['update_replace'][$k][$flag_num] = true;
}
function ReplaceUserCheck($k, $param_num, $flag_num, $check_value)
{
    $_SESSION['user_list'][$k][$param_num] = 0;
    $_SESSION['user_flag'][$k][$flag_num] = true;
    $_SESSION['update_replace'][$k][$flag_num] = (string) $check_value;
}
function PrefDBTest($k, $prior, $db)
{
    $fl = false;
    if ($_SESSION['user_flag'][$k][3]) {
        if (! isset($_SESSION['list_db'][$db])) {
            $fl = true;
        }
    } else {
        if ($prior > 10 && $prior < 100) {
            if (! isset($_SESSION['list_db'][$db])) {
                $fl = true;
            }
        } elseif (! isset($_SESSION['list_db'][$db]) || $db == '0') {
            $fl = true;
        }
    }

    return $fl;
}
function PrefDateTest($pref_date, $k)
{
    if (strpos($pref_date, chr(39)) !== false) {
        ReplaceUserApostrophe($k, 6, 7);
    }
    switch (substr($_SESSION['user_list'][$k][6], 1)) {
        case 'dmy': return true;
        case 'mdy': return true;
        case 'ydm': return true;
        case 'ymd': return true;
        default: return false;
    }
}
function UserParamTest($param, $k, $priority, $param_num, $flag_num)
{
    if (strpos($param, chr(39)) === false) {
        if ($priority == 0) {
            if ($param_num == 0) {
                return $param == '_'.$k;
            } else {
                return $param == '#####'.$k;
            }
        } else {
            if ($param_num == 0) {
                return TestUserName($param);
            } else {
                return TestUserPassword($param);
            }
        }
    } else {
        ReplaceUserApostrophe($k, $param_num, $flag_num);

        return false;
    }
}
function TestDoubledUser($dbh, $k_edit, $param_num, $param_name, $param_value)
{
    foreach ($_SESSION['user_list'] as $k => $v) {
        if ($k != $k_edit && $v[$param_num] == $param_value) {
            return $k;
        }
    }
    $test_result = '';
    $res = mysqli_query($dbh, 'SELECT id_user FROM user_ident WHERE '.$param_name." = '".$param_value."' AND id_user <> ".$k_edit);
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $test_result = (string) $row[0];
        }
        mysqli_free_result($res);
    }

    return $test_result;
}
