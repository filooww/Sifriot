<?php

function GetAllConfigs($dbh, $conf_table)
{
    $arr_site = [];
    $res = mysqli_query($dbh, 'SELECT * FROM '.$conf_table.' ORDER BY config_name');
    if ($res) {
        $i = 0;
        while ($row = mysqli_fetch_row($res)) {
            $i++;
            $arr_site[(string) $i] = [false, false, false, false, false, $row[0], $row[1], $row[2], $row[3]];
        }
        mysqli_free_result($res);
    }
    if ($_SESSION['user_working_mode'] == 1) {
        $arr_site['new'] = [false, false, false, false, false, '', '', false, '', ''];
    }

    return $arr_site;
}
function ActionConfigExit($dbh, &$Mes)
{
    if ($_SESSION['user_working_mode'] == 1) {
        $arr_warn = TestConfigs($Mes, false);
        if (count($arr_warn['changed']) + count($arr_warn['illegal']) + count($arr_warn['numeric']) + count($arr_warn['doubled']) + count($arr_warn['deleted']) == 0) {
            $v = GetValueByConfigName('image_dir');
            if ($v !== false) {
                $_SESSION['image_dir'] = $v;
            }
            if ($_SESSION['common_config']) {
                if ($_SESSION['alarm']) {
                    SimpleSysTableCheck($dbh, 'db_s_configs', "config_name NOT REGEXP '^[a-z][a-z0-9_]*$' OR config_name = '' OR config_type > 1 OR config_type = 1 AND (config_value = '' OR config_value <> '' AND config_value NOT REGEXP '^[+-]?[0-9]*\\.?[0-9]+([eE][+-]?[0-9]+)?$')");

                    return '../Alarm/CommonAlarmForm';
                }

                return '../Administrator/MainForm';
            }

            return '../Administrator/DataBaseActions';
        } else {
            if (count($arr_warn['illegal']) + count($arr_warn['numeric']) + count($arr_warn['doubled']) > 0) {
                $Mes[] = '<b>'.Title(223).'</b>';
            } else {
                $Mes[] = '<b>'.Title(235).'</b>';
            }
            if ($_SESSION['alarm']) {
                return '../Alarm/CommonAlarmForm';
            }

            return '';
        }
    }
    if ($_SESSION['common_config']) {
        return '../Administrator/MainForm';
    }

    return '../Administrator/DataBaseActions';
}
function FindNumberByConfigName($conf_name)
{
    foreach ($_SESSION['config_list'] as $k => $v) {
        if ($v[5] == $conf_name) {
            return $k;
        }
    }

    return '';
}
function RewriteConfigs($dbh, $conf_table, &$Mes)
{
    mysqli_query($dbh, 'DELETE FROM '.$conf_table);
    $fl = false;
    $arr_list = [];
    $arr_sort = [];
    foreach ($_SESSION['config_list'] as $k => $v) {
        if ($v[5] != '') {
            mysqli_query($dbh, 'INSERT INTO '.$conf_table." VALUES ('".$v[5]."','".$v[6]."',".(int) $v[7].",'".$v[8]."')");
            $arr_sort[$v[5]] = [$v[6], $v[7], $v[8]];
            $fl = true;
        }
    }
    if ($fl) {
        ksort($arr_sort);
        $i = 0;
        foreach ($arr_sort as $k => $v) {
            $i++;
            $arr_list[(string) $i] = [false, false, false, false, false, $k, $v[0], $v[1], $v[2]];
        }
        $arr_list['new'] = [false, false, false, false, false, '', '', false, '', ''];
        $Mes[] = '<b>'.Title(192).'</b>';
        if ($_SESSION['common_config']) {
            SystemConfigs($dbh);
        } else {
            DBConfigs($dbh);
        }
    }
    $_SESSION['config_list_copy'] = ListCopy($arr_list);

    return $arr_list;
}
function ClearConfigFlags()
{
    foreach ($_SESSION['config_list'] as $k => $v) {
        for ($i = 1; $i < 4; $i++) {
            $_SESSION['config_list'][$k][$i] = false;
        }
    }
}
function TestConfigs(&$Mes, $init = false, $numeric_flag_key = '')
{
    if ($numeric_flag_key == '') {
        ClearConfigFlags();
    }
    if (! $init) {
        foreach ($_SESSION['config_list'] as $k => $v) {
            ChangeConfigFromPost('name', $k, $v, 5);
            ChangeConfigFromPost('value', $k, $v, 6);
            ChangeConfigFromPost('description', $k, $v, 8);
        }
    }
    foreach ($_SESSION['config_list'] as $k => $v) {
        if ($v[5] != '') {
            $_SESSION['config_list'][$k][1] = ! TestSysString($v[5]);
            if ($k == $numeric_flag_key) {
                if ($v[7] > 1) {
                    $_SESSION['config_list'][$k][7] = 1;
                } else {
                    $_SESSION['config_list'][$k][7] = 1 - $_SESSION['config_list'][$k][7];
                }
                $_SESSION['config_list'][$k][4] = true;
            }
            $_SESSION['config_list'][$k][2] = ($_SESSION['config_list'][$k][7] == 1) ? ! is_numeric($v[6]) : false;
        }
    }
    $dubl_codes = DoubleConfigSearch();
    foreach ($dubl_codes as $k => $v) {
        for ($i = 0; $i < count($v); $i++) {
            $_SESSION['config_list'][(string) $v[$i]][3] = true;
        }
    }
    $arr_warn = WarningConfigArrays();
    if (count($arr_warn['changed']) > 0) {
        $Mes[] = "<font color='#0000FF'><b>".((count($arr_warn['changed']) == 1) ? Title(116) : Title(108)).'</b></font>: <b>'.implode(', ', $arr_warn['changed']).'</b>';
    }
    if (count($arr_warn['illegal']) > 0) {
        $Mes[] = "<font color='#FF0000'><b>".Title(191).'</b></font> ('.((count($arr_warn['illegal']) == 1) ? Title(419) : Title(222)).' <b>'.implode(', ', $arr_warn['illegal']).'</b>)';
    }
    if (count($arr_warn['numeric']) > 0) {
        $Mes[] = "<font color='#FF0000'><b>".Title(410).' '.Title(77).'</b></font> ('.((count($arr_warn['numeric']) == 1) ? Title(419) : Title(222)).' <b>'.implode(', ', $arr_warn['numeric']).'</b>)';
    }
    if (count($arr_warn['doubled']) > 0) {
        DoubledMessages(190, $arr_warn['doubled'], [], 0, $Mes);
    }
    if (count($arr_warn['deleted']) > 0) {
        $Mes[] = "<font color='#FF00FF'><b>".Title(204).' '.((count($arr_warn['deleted']) == 1) ? Title(221) : Title(222)).'</font> '.implode(', ', $arr_warn['deleted']).'</b>';
    }

    return $arr_warn;
}
function ChangeConfigFromPost($conf_type, $k, $v, $param_str_ind)
{
    if ($_POST['conf_'.$conf_type.'-'.$k] != $v[$param_str_ind]) {
        $_SESSION['config_list'][$k][$param_str_ind] = $_POST['conf_'.$conf_type.'-'.$k];
        if ($k == 'new' && $_POST['conf_'.$conf_type.'-'.$k] == '') {
            $_SESSION['config_list'][$k][0] = false;
            $_SESSION['config_list'][$k][1] = false;
            $_SESSION['config_list'][$k][2] = false;
        } else {
            $_SESSION['config_list'][$k][0] = true;
        }
    }
}
function WarningConfigArrays()
{
    $arr_warn = ['changed' => [], 'illegal' => [], 'numeric' => [], 'doubled' => [], 'deleted' => []];
    foreach ($_SESSION['config_list'] as $k => $v) {
        if ($v[0] || $v[4]) {
            $arr_warn['changed'][] = $k;
        }
        if ($v[1]) {
            $arr_warn['illegal'][] = $k;
        }
        if ($v[2]) {
            $arr_warn['numeric'][] = $k;
        }
        if ($v[3]) {
            $arr_warn['doubled'][$v[5]][] = $k;
        }
        if ($v[5] == '' && $k != 'new') {
            $arr_warn['deleted'][] = $k;
        }
    }

    return $arr_warn;
}
function DoubleConfigSearch()
{
    $no_del = ['codes' => [], 'values' => []];
    foreach ($_SESSION['config_list'] as $k => $v) {
        if ($v[5] != '') {
            $no_del['codes'][] = $k;
            $no_del['values'][] = $v[5];
        }
    }
    $arr_groups = array_count_values($no_del['values']);
    $dubl_codes = [];
    for ($i = 0; $i < count($no_del['values']); $i++) {
        if ($arr_groups[$no_del['values'][$i]] > 1) {
            $dubl_codes[$no_del['values'][$i]][] = $no_del['codes'][$i];
        }
    }

    return $dubl_codes;
}
function NewConfigKey()
{
    end($_SESSION['config_list']);
    prev($_SESSION['config_list']);
    $k = key($_SESSION['config_list']);

    return (string) ((int) $k + 1);
}
function VV($s_str)
{
    $str = str_replace(chr(92), chr(92).chr(92), $s_str);
    $str = str_replace(chr(39), chr(96), $s_str);

    return $str;
}
function GetConfigValue($dbh, $conf_table, $config_name, $conf_type, $config_description = '', $default_value = '')
{
    $res = mysqli_query($dbh, 'SELECT config_value, config_description FROM '.$conf_table." WHERE config_name = '".$config_name."'");
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $gv = ($conf_type) ? (int) $row[0] : $row[0];
            if ($config_description != '' && $row[1] != $config_description) {
                SetConfigDescription($dbh, $conf_table, $config_name, $config_description);
            }
        } else {
            mysqli_query($dbh, 'INSERT INTO '.$conf_table." VALUES ('".$config_name."','".VV($default_value)."',".(($conf_type) ? '1' : '0').",'".VV($config_description)."')");
            if ($row[1] == '' && $config_description != '') {
                SetConfigDescription($dbh, $conf_table, $config_name, $config_description);
            }
            $gv = ($conf_type) ? (int) VV($default_value) : VV($default_value);
        }
        mysqli_free_result($res);
    } else {
        mysqli_query($dbh, 'INSERT INTO '.$conf_table." VALUES ('".$config_name."','".VV($default_value)."',".(($conf_type) ? '1' : '0').",'".VV($config_description)."')");
        if ($row[1] == '' && $config_description != '') {
            SetConfigDescription($dbh, $conf_table, $config_name, $config_description);
        }
        $gv = ($conf_type) ? (int) VV($default_value) : VV($default_value);
    }

    return $gv;
}
function SetConfigDescription($dbh, $conf_table, $config_name, $config_description)
{
    mysqli_query($dbh, 'UPDATE '.$conf_table." SET config_description = '".$config_description."' WHERE config_name = '".$config_name."'");
}
function SetConfigValue($dbh, $conf_table, $config_name, $conf_type, $config_value, $config_description = '')
{
    $f_set = 'config_name,config_value,config_type,config_description';
    $v_set = "'".$config_name."','".$config_value."',".(($conf_type) ? '1' : '0').",'".$config_description."'";
    $res = mysqli_query($dbh, 'SELECT * FROM '.$conf_table." WHERE config_name = '".$config_name."'");
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            mysqli_query($dbh, 'UPDATE '.$conf_table.' SET conf_type = '.(($conf_type) ? '1' : '0').", config_value = '".$config_value."', config_description = '".$config_description."' WHERE config_name = '".$config_name."'");
        } else {
            mysqli_query($dbh, 'INSERT INTO '.$conf_table." VALUES ('".$config_name."','".$config_value."','".$config_description."')");
        }
        mysqli_free_result($res);
    } else {
        mysqli_query($dbh, 'INSERT INTO '.$conf_table.' ('.$f_set.') VALUES ('.$v_set.')');
    }
}
function TitleConfig()
{
    if ($_SESSION['common_config']) {
        return Title(130);
    }

    return Title(202)." <font color='0000FF'>".$_SESSION['db_info']['name'].'</font>';
}
function GetValueByConfigName($c_n)
{
    foreach ($_SESSION['config_list'] as $v) {
        if ($v[5] == $c_n) {
            return $v[6];
        }
    }

    return false;
}
function FillConfigs($dbh, $dbh_sys, $conf_table, &$Mes)
{
    $fl = false;
    $res = mysqli_query($dbh_sys, 'SELECT * FROM db_s_configs');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $res_db = mysqli_query($dbh, 'SELECT * FROM '.$conf_table." WHERE config_name = '".$row[0]."'");
            if ($res_db) {
                if ($row_db = mysqli_fetch_row($res_db)) {
                    if ($row_db[1] != $row[1] || $row_db[2] != $row[2] || $row_db[3] != $row[3]) {
                        mysqli_query($dbh, 'UPDATE '.$conf_table." SET config_value = '".$row[1]."', config_type = ".(string) $row[2].", config_description = '".$row[3]."' WHERE config_name = '".$row[0]."'");
                        $fl = true;
                    }
                } else {
                    mysqli_query($dbh, 'INSERT INTO '.$conf_table." VALUES ('".$row[0]."','".$row[1]."',".(string) $row[2].",'".$row[3]."')");
                    $fl = true;
                }
            } else {
                mysqli_query($dbh, 'INSERT INTO '.$conf_table." VALUES ('".$row[0]."','".$row[1]."',".(string) $row[2].",'".$row[3]."')");
                $fl = true;
            }
        }
        mysqli_free_result($res);
    }
    if ($fl) {
        $_SESSION['config_list'] = GetAllConfigs($dbh, $conf_table);
        $Mes[] = Title(48);
    }
}
function BeforeSave(&$Mes, $init)
{
    $arr_warn = TestConfigs($Mes, $init);
    if (count($arr_warn['illegal']) + count($arr_warn['numeric']) + count($arr_warn['doubled']) > 0) {
        $Mes[] = "<br><font color='0000FF'><b>".Title(420).'</b></font>';
    }

    return $arr_warn;
}
function InvalidConfigType()
{
    $arr_err = [];
    foreach ($_SESSION['config_list'] as $k => $v) {
        if ($v[7] > 1) {
            $arr_err[] = $k;
        }
    }

    return $arr_err;
}
function SystemConfigs($dbh_sys)
{
    $_SESSION['start_year'] = GetConfigValue($dbh_sys, 'db_s_configs', 'start_year', true, 'System initial year', '1970');
    $_SESSION['char_group'] = (int) GetConfigValue($dbh_sys, 'db_s_configs', 'char_group', true, 'Code to separate of special symbol groups', '19');
    $_SESSION['apostrophe_replace'] = GetConfigValue($dbh_sys, 'db_s_configs', 'apostrophe_replace', false, 'Apostrophe replacement symbol', '`');
    $_SESSION['number_warn'] = (int) GetConfigValue($dbh_sys, 'db_s_configs', 'number_warning', true, 'Number of values in warning row', '10');
    $_SESSION['reminder_interval'] = GetConfigValue($dbh_sys, 'db_s_configs', 'reminder_interval', true, 'Time interval for a reminder to close the session (sec)', '10');
    ChangeTimerInterval($_SERVER['DOCUMENT_ROOT'].'/s/Utilities/SetTimerInterval.txt', $_SESSION['reminder_interval']);
    $_SESSION['close_delay'] = GetConfigValue($dbh_sys, 'db_s_configs', 'close_delay', true, 'Session close delay time (sec)', '300');
    $_SESSION['image_dir'] = GetConfigValue($dbh_sys, 'db_s_configs', 'image_dir', false, 'Directory of images', '/images');
    $_SESSION['try_limit'] = (int) GetConfigValue($dbh_sys, 'db_s_configs', 'try_limit', true, 'Maximum number of login attempts', '3');
    $_SESSION['max_table_columns'] = GetConfigValue($dbh_sys, 'db_s_configs', 'max_table_columns', true, 'Maximal count of columns in table', '13');
}
function DBConfigs($dbh)
{
    $_SESSION['conf']['list_title'] = GetConfigValue($dbh, 'db_configs', 'list_title', false, 'Main list title', 'Item list');
    $_SESSION['conf']['form_title'] = GetConfigValue($dbh, 'db_configs', 'form_title', false, 'Item form title', 'Item form');
    $_SESSION['conf']['file_list_title'] = GetConfigValue($dbh, 'db_configs', 'file_list_title', false, 'Item file list title', 'Item file list');
    $_SESSION['conf']['dest_dir'] = GetConfigValue($dbh, 'db_configs', 'dest_dir', false, 'Address and directory of file storage');
    //    $_SESSION['conf']['portion_item'] = (integer)GetConfigValue($dbh, "db_configs", "portion_item", true, "Number of records reading from the main list at one time", "100");
    $_SESSION['conf']['screen_saver'] = GetConfigValue($dbh, 'db_configs', 'screen_saver', false, 'Site screen saver');
    $_SESSION['conf']['screen_saver_height'] = (int) GetConfigValue($dbh, 'db_configs', 'screen_saver_height', true, 'Height of site screen saver');
    $_SESSION['conf']['w_01'] = GetConfigValue($dbh, 'db_configs', 'w_01', true, 'Size of fields TITLE of item', '200');
    $_SESSION['conf']['w_03'] = GetConfigValue($dbh, 'db_configs', 'w_03', false, 'Width of placeholder for parameter name inside table for upload settings', '9%');
    $_SESSION['conf']['w_04'] = GetConfigValue($dbh, 'db_configs', 'w_04', false, 'Widht of placeholder for button CALL CATALOG inside table for upload settings', '3%');
    $_SESSION['conf']['w_05'] = GetConfigValue($dbh, 'db_configs', 'w_05', false, 'Width of placeholder for catalog code', '5%');
    $_SESSION['conf']['w_06'] = GetConfigValue($dbh, 'db_configs', 'w_06', false, 'Width of catalog code', '60px');
    $_SESSION['conf']['w_07'] = GetConfigValue($dbh, 'db_configs', 'w_07', true, 'Size of selection criteria', '44');
    $_SESSION['conf']['w_08'] = GetConfigValue($dbh, 'db_configs', 'w_08', true, 'Size of catalog value', '44');
    $_SESSION['conf']['w_09'] = GetConfigValue($dbh, 'db_configs', 'w_09', false, 'Width of list settings pad', '64%');
}
