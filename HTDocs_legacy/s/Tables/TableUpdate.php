<?php

function TableValuesString($table_name, $table_params)
{
    $arr_values = [];
    $arr_values[] = "'".$table_name."'";
    $arr_values[] = (string) $table_params['use_type'];
    $arr_values[] = "'".$_SESSION['table_definitions'][$table_name]['illegals']."'";
    $arr_values[] = (string) $table_params['group_type'];
    $arr_values[] = "'".$table_params['separators']."'";
    $arr_values[] = (string) $table_params['max_level'];
    $arr_values[] = "'".$table_params['second_catalog']."'";
    $arr_values[] = "'".$table_params['table_title']."'";

    return implode(',', $arr_values);
}
function RewriteDefinition($dbh, &$Mes)
{
    $arr_insert = [];
    $_SESSION['single_catalogs'] = [];
    foreach ($_SESSION['table_definitions'] as $table_name => $table_params) {
        if ($table_params['separators'] != '' && $table_params['second_catalog'] != '') {
            InsertToIllegals($table_params['separators'], $table_params['second_catalog']);
        }
        if ($table_params['illegals'] != '' && substr($table_params['illegals'], -1) == chr($_SESSION['char_group'])) {
            $_SESSION['table_definitions'][$table_name]['illegals'] = substr($_SESSION['table_definitions'][$table_name]['illegals'], 0, -1);
        }
        $arr_insert[] = '('.TableValuesString($table_name, $table_params).')';
        if (! in_array($table_name, array_keys($_SESSION['all_field_list']))) {
            $_SESSION['table_definitions'][$table_name]['tab_err'][] = "<font color='#FF0000'>".Title(608).'</font>';
        }
    }
    foreach (array_keys($_SESSION['all_field_list']) as $table_name) {
        if (! isset($_SESSION['table_definitions'][$table_name])) {
            $_SESSION['table_definitions'][$table_name] = ['use_type' => 0, 'illegals' => '', 'separators' => '', 'catalog_id' => '', 'catalog_value' => '', 'group_type' => 0, 'second_catalog' => '', 'table_title' => $table_name, 'tab_err' => [FTM(Title(365)).' <b>'.$table_name.'</b> - '.Title(409).' '.Title(705)]];
        }
    }
    if (count($arr_insert) > 0) {
        mysqli_query($dbh, 'DELETE FROM table_definitions');
        mysqli_query($dbh, 'ALTER TABLE table_definitions AUTO_INCREMENT = 1');
        mysqli_query($dbh, 'INSERT INTO table_definitions VALUES '.implode(',', $arr_insert));
        $Mes[] = "<b><font color='#0000FF'>".Title(193).'</font></b>';
    }
}
function GetMaxLevel($dbh, $table)
{
    $max_level = 0;
    $res = mysqli_query($dbh, 'SELECT * FROM '.$table);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $n = substr_count($row[1], ',');
            if ($n > $max_level) {
                $max_level = $n;
            }
        }
        mysqli_free_result($res);
    }

    return $max_level;
}
function ReduceMaxLevelAll($dbh)
{
    $fl = false;
    foreach ($_SESSION['table_definitions'] as $table => $t_params) {
        if (in_array(Title(427), $t_params['tab_err']) !== false) {
            $max_level_arr = [];
            if ($t_params['use_type'] == 3) {
                $max_level = GetMaxLevel($dbh, $table);
            } else {
                $max_level = 0;
            }
            $res = mysqli_query($dbh, "SELECT max_level FROM table_definitions WHERE table_name = '".$table."'");
            if ($res) {
                if ($row = mysqli_fetch_row($res_def)) {
                    if ($row[0] != $max_level) {
                        mysqli_query($dbh, 'UPDATE table_definitions SET max_level = '.(string) $max_level." WHERE table_name = '".$table."'");
                        $fl = true;
                    }
                }
                mysqli_free_result($res);
            }
        }
    }

    return $fl;
}
function AutoIncrementAll($dbh)
{
    $res = mysqli_query($dbh, 'SHOW TABLES');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            mysqli_query($dbh, 'ALTER TABLE '.$row[0].' AUTO_INCREMENT = 1');
        }
        mysqli_free_result($res);
    }
}
function InsertToIllegals($separators, $second_catalog)
{
    if ($_SESSION['table_definitions'][$second_catalog]['illegals'] == '') {
        $_SESSION['table_definitions'][$second_catalog]['illegals'] = $separators;
    } elseif (strpos($_SESSION['table_definitions'][$second_catalog]['illegals'], $separators) === false) {
        $_SESSION['table_definitions'][$second_catalog]['illegals'] .= $_SESSION['char_group'].$separators;
    }
}
function PostToSessionParameters()
{
    foreach ($_SESSION['table_definitions'] as $t_name => $v) {
        if ($v['table_title'] != $_POST['table_title-'.$t_name]) {
            $_SESSION['table_definitions'][$t_name]['table_title'] = $_POST['table_title-'.$t_name];
        }
        if ($v['use_type'] == 3) {
            if ($v['second_catalog'] == '' && isset($_POST['illegals-'.$t_name]) && $_SESSION['table_definitions'][$t_name]['illegals'] != $_POST['illegals-'.$t_name]) {
                $_SESSION['table_definitions'][$t_name]['illegals'] = $_POST['illegals-'.$t_name];
            } elseif (isset($_POST['separators-'.$t_name]) && $_SESSION['table_definitions'][$t_name]['separators'] != $_POST['separators-'.$t_name]) {
                $_SESSION['table_definitions'][$t_name]['separators'] = $_POST['separators-'.$t_name];
            }
            if ($_SESSION['group_types'][$v['group_type']] != $_POST['group_type-'.$t_name]) {
                $i = array_search($_POST['group_type-'.$t_name], $_SESSION['group_types']);
                if ($i !== false) {
                    $_SESSION['table_definitions'][$t_name]['group_type'] = $i;
                }
            }
        }
    }
}
