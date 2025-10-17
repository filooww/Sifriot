<?php

function CreateDBArray($dbh)
{
    $arr_db = [];
    $res = mysqli_query($dbh, 'SELECT * FROM db_list ORDER BY db_id');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $arr_db[$row[0]] = ['db_name' => $row[1], 'db_coding' => $row[2], 'db_comment' => $row[3], 'del' => false, 'db_err' => TestDatabase($row[1], $row[2], (string) $row[0])];
        }
        mysqli_free_result($res);
    }

    return $arr_db;
}
function CorrectDBID($dbh, $db_id)
{
    $new_row = [$_SESSION['arr_db'][$db_id]['db_name'], $_SESSION['arr_db'][$db_id]['db_coding'], $_SESSION['arr_db'][$db_id]['db_comment']];
    $new_id = NewTableId($_SESSION['arr_db'], 0);
    unset($_SESSION['arr_db'][$db_id]);
    $_SESSION['arr_db'][$new_id] = ['db_name' => $new_row[0], 'db_coding' => $new_row[1], 'db_comment' => $new_row[2], 'del' => false, 'db_err' => TestDatabase($new_row[0], $new_row[1], (string) $new_id)];
    ksort($_SESSION['arr_db']);
}
function RestoreDBHandler($work_db)
{
    $db_mes = [];
    $dbh = GetDB($_SESSION['arr_db'][$work_db]['db_name'], $db_mes, $_SESSION['arr_db'][$work_db]['db_coding']);
    if (! $dbh) {
        ExitSession(Title(1).' <b>'.$_SESSION['arr_db'][$work_db]['db_name'].'</b>`'.implode('`', $db_mes).'|FF0000', $work_db);
    }

    return $dbh;
}
function SetDBNameArray($first_element = '')
{
    if ($first_element == '') {
        $arr_db_names = [];
    } else {
        $arr_db_names['nu'] = $first_element;
    }
    foreach ($_SESSION['arr_db'] as $k => $v) {
        if ((int) $k >= 0) {
            $arr_db_names[$k] = $v['db_name'];
        }
    }
    ksort($arr_db_names);

    return $arr_db_names;
}
function RestDBValues()
{
    if (isset($_SESSION['pre_db_err'])) {
        unset($_SESSION['pre_db_err']);
    }
    $_SESSION['db_sel'] = '';
}
function DBInfoSelect($work_db)
{
    $db_info = ['id' => $work_db, 'name' => $_SESSION['arr_db'][$work_db]['db_name'], 'coding' => $_SESSION['arr_db'][$work_db]['db_coding']];
    $arr_table = GetDBMandatoryTables($_SESSION['arr_db'][$work_db]['db_name'], $_SESSION['arr_db'][$work_db]['db_coding'], $work_db);
    $db_info['t_main'] = $arr_table['t_main'];
    $db_info['t_attach'] = $arr_table['t_attach'];
    $db_info['t_primary'] = $arr_table['t_primary'];

    return $db_info;
}
function GetDBMandatoryTables($db_name, $db_coding, $db_id)
{
    $arr_table = ['err' => TestDatabase($db_name, $db_coding, $db_id), 't_main' => '', 't_attach' => '', 't_primary' => ''];
    if (count($arr_table['err']) == 0) {
        $Mes = [];
        $dbh = GetDB($db_name, $Mes, $db_coding);
        if (! $dbh) {
            ExitSession(Title(1).' <b>'.$db_name.'</b>`'.implode('`', $Mes).'|FF0000', $_SESSION['db_info']['id']);
        }
        $res = mysqli_query($dbh, 'SELECT table_name, use_type FROM table_definitions WHERE use_type = 1 OR use_type = 2 OR use_type = 5');
        if ($res) {
            while ($row = mysqli_fetch_row($res)) {
                if ($row[1] == 1) {
                    $arr_table['t_main'] = $row[0];
                } elseif ($row[1] == 2) {
                    $arr_table['t_attach'] = $row[0];
                } elseif ($row[1] == 5) {
                    $arr_table['t_primary'] = $row[0];
                }
                if ($arr_table['t_main'] != '' && $arr_table['t_attach'] != '' && $arr_table['t_primary'] != '') {
                    break;
                }
            }
            mysqli_free_result($res);
        }
    }

    return $arr_table;
}
