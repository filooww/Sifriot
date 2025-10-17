<?php

function TransformActionText($action)
{
    $new_act = str_replace('drop the field', Title(283).' '.Title(282), $action);
    $new_act = str_replace('change the field', FTM(Title(284)).' '.Title(282), $new_act);
    $new_act = str_replace('add the field', Title(286).' '.Title(282), $new_act);
    $new_act = str_replace('parameter', Title(428), $new_act);
    $new_act = str_replace(' to ', ' >> ', $new_act);

    return $new_act;
}
function UserDataBaseStructureDefinition()
{
    $arr['db_configs']['config_name'] = ['varchar(31)', 'utf8mb3_bin', 'NO', 'PRI', null, ''];
    $arr['db_configs']['config_value'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['db_configs']['config_type'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['db_configs']['config_description'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['field_config']['own_table'] = ['tinyint unsigned', null, 'NO', 'PRI', '0', ''];
    $arr['field_config']['f_ID'] = ['varchar(255)', 'utf8mb3_bin', 'NO', 'PRI', null, ''];
    $arr['field_config']['f_key'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['f_name'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['field_config']['f_type'] = ['tinyint unsigned', null, 'NO', '', null, ''];
    $arr['field_config']['f_size'] = ['smallint unsigned', null, 'YES', '', '0', ''];
    $arr['field_config']['f_interval'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['f_blank'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['f_unique'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['f_s_mode'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['f_table'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['field_config']['f_illegals'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['field_config']['f_default'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['field_config']['f_check'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['comm'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['f_filter_md'] = ['tinyint unsigned', null, 'NO', '', '1', ''];
    $arr['field_config']['f_sort_sm'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['f_using'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['field_config']['f_align'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['table_percent'] = ['varchar(5)', 'utf8mb3_bin', 'NO', '', '', ''];
    $arr['field_config']['screen_order'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['field_config']['load_order'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['table_definitions']['table_name'] = ['varchar(31)', 'utf8mb3_bin', 'NO', 'PRI', null, ''];
    $arr['table_definitions']['use_type'] = ['tinyint unsigned', null, 'NO', '', null, ''];
    $arr['table_definitions']['illegal_symbols'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['table_definitions']['group_catalog_type'] = ['tinyint unsigned', null, 'NO', '', null, ''];
    $arr['table_definitions']['separators'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['table_definitions']['max_level'] = ['tinyint unsigned', null, 'NO', '', '0', ''];
    $arr['table_definitions']['second_catalog_name'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];
    $arr['table_definitions']['table_title'] = ['varchar(255)', 'utf8mb3_bin', 'NO', '', null, ''];

    return $arr;
}
function GetUserDBTableStructure($db)
{
    foreach (array_keys($_SESSION['all_field_list']) as $table) {
        if (isset($_SESSION['table_definitions'][$table])) {
            $arr_struct = [];
            $row_count = 0;
            $del_flag = '';
            $f_low = '';
            foreach ($_SESSION['all_field_list'][$table] as $k_field => $f_params) {
                $row_count++;
                switch ($_SESSION['table_definitions'][$table]['use_type']) {
                    case 1: TestMainTableStructure($row_count, $f_params, $k_field, $table, $f_low, $del_flag);
                        break;
                    case 2: TestAttachFileStructure($row_count, $k_field, $f_params, $table, $f_low);
                        break;
                    case 3: TestCatalogStructure($row_count, $_SESSION['table_definitions'][$table]['second_catalog'], $f_params, $table, $k_field);
                        break;
                    case 5: TestPrimaryFileStructure($row_count, $f_params, $table, $k_field);
                        break;
                }
            }
            if ($_SESSION['table_definitions'][$table]['use_type'] == 1) {
                DelFlagTest($del_flag, $table, $_SESSION['all_field_list'][$table][$del_flag]);
                FieldLowTest($f_low, $table, $_SESSION['all_field_list'][$table][$f_low]);
            }
            if ($_SESSION['table_definitions'][$table]['use_type'] == 2) {
                FieldLowTest($f_low, $table, $_SESSION['all_field_list'][$table][$f_low]);
            }
        }
    }
}
function FieldLowTest($f_low, $table, $f_params)
{
    if ($f_low == '') {
        $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.Title(433);
    } elseif (f_Err($f_params, ['varchar(255)', 'utf8mb3_bin', 'NO', '', '*', ''])) {
        $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.Title(648);
    }
}
function DelFlagTest($del_flag, $table, $f_params)
{
    if ($del_flag == '') {
        $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.Title(377);
    } elseif (f_Err($f_params, ['tinyint unsigned', '*', 'NO', '', '0', ''])) {
        $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.Title(647);
    }
}
function f_Err($params, $v_arr)
{
    for ($i = 0; $i < count($params); $i++) {
        if (is_null($params[$i])) {
            if ($v_arr[$i] != '*') {
                return true;
            }
        } else {
            if ($params[$i] != $v_arr[$i]) {
                return true;
            }
        }
    }

    return false;
}
function TestMainTableStructure($row_count, $params, $k_field, $table, &$f_low, &$del_flag)
{
    if ($row_count == 1 && f_Err($params, ['int unsigned', '*', 'NO', 'PRI', '*', 'auto_increment'])) {
        $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(646);
    }
    if ($k_field == '_del_mark') {
        $del_flag = $k_field;
    }
    if (substr($k_field, -4) == '_low') {
        $f_low = $k_field;
    }
}
function TestAttachFileStructure($row_count, $k_field, $params, $table, &$f_low)
{
    if ($row_count == 1) {
        if (f_Err($params, ['int unsigned', '*', 'NO', 'PRI', '*', ''])) {
            $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(649);
        }
    } elseif ($row_count == 2) {
        if (f_Err($params, ['varchar(255)', 'utf8mb3_bin', 'NO', 'PRI', '*', ''])) {
            $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(650);
        }
    }
    if (substr($k_field, -4) == '_low') {
        $f_low = $k_field;
    }
}
function TestCatalogStructure($row_count, $second_catalog, $params, $table, $k_field)
{
    switch ($row_count) {
        case 1: if (f_Err($params, ['int unsigned', '*', 'NO', 'PRI', '*', 'auto_increment'])) {
            $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(637);
        } break;
        case 2: if (f_Err($params, ['varchar(255)', 'utf8mb3_bin', 'NO', 'UNI', '*', ''])) {
            $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(638);
        } break;
        case 3: if ($second_catalog == '' && (f_Err($params, ['varchar(255)', 'utf8mb3_bin', 'NO', '', '*', '']))) {
            $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(638).' ('.Title(645).')';
        } break;
    }
}
function TestPrimaryFileStructure($row_count, $params, $table, $k_field)
{
    switch ($row_count) {
        case 1: if (f_Err($params, ['int unsigned', '*', 'NO', '', '*', ''])) {
            $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(651);
        } break;
        case 2: if (f_Err($params, ['varchar(8191)', 'utf8mb3_bin', 'NO', '', '*', ''])) {
            $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(652);
        } break;
        case 3: if (f_Err($params, ['varchar(8191)', 'utf8mb3_bin', 'NO', '', '*', ''])) {
            $_SESSION['db_errors'][] = FTM(Title(365)).' <b>'.$table.'</b> : '.FTM(Title(403)).' <b>'.$k_field.'</b> : '.Title(652).' ('.Title(645).')';
        } break;
    }
}
/*
    $ins = array();
    if (!$serv_tables['db_configs'])
    {
        $t_def['db_configs'] = array("use_type"=>4, "illegals"=>"", "max_level"=>0, "separators"=>"", "catalog_id"=>"", "catalog_value"=>"", "group_type"=>0, "second_catalog"=>"", "table_title"=>"Data base congifurations", "tab_err"=>array(Title(536)));
        $ins[] = "('db_configs',4,'',0,'',0,'','Data base congifurations')";
    }
    if (!$serv_tables['field_config'])
    {
        $t_def['field_config'] = array("use_type"=>4, "illegals"=>"", "max_level"=>0, "separators"=>"", "catalog_id"=>"", "catalog_value"=>"", "group_type"=>0, "second_catalog"=>"", "table_title"=>"Field configurations", "tab_err"=>array(Title(536)));
        $ins[] = "('field_config',4,'',0,'',0,'','Field configurations')";
    }
    if (!$serv_tables['table_definitions'])
    {
        $t_def['table_definitions'] = array("use_type"=>4, "illegals"=>"", "max_level"=>0, "separators"=>"", "catalog_id"=>"", "catalog_value"=>"", "group_type"=>0, "second_catalog"=>"", "table_title"=>"Table definitions", "tab_err"=>array(Title(536)));
        $ins[] = "('table_definitions',4,'',0,'',0,'','Table definitions')";
    }
    if (count($ins) > 0)
    {
        $str_ins = "INSERT INTO table_definitions VALUES ".implode(",", $ins);
        mysqli_query($dbh, "INSERT INTO table_definitions VALUES ".implode(",", $ins));
//$lf = fopen("D:\\Test.txt", "a"); fwrite($lf, "\r\ntest InsertServiceTableDefinitions 00 ***"); fclose($lf);
        $db_err[] = ((count($ins) == 1) ? FTM(Title(365)) : FTM(Title(115)))." <b>".implode(", ", array_keys($serv_tables))."</b> ".((count($ins) == 1) ? Title(536) : Title(712));
    }
*/
// }
/*
function TestServiceConfigs()
{
    if ($f_params[0] != "varchar(31)" || $f_params[1] != "NO" || $f_params[2] != "PRI" || !is_null($f_params[3]) || !is_null($f_params[4])

    $f_params[4] != "auto_increment")) $db_err[] = Title(646); break;

    !is_null($f_params[2]) || !is_null($f_params[3]) || !is_null($f_params[4])) $db_err[] = Title(650); break;

    $arr['db_configs']['config_name'] = array("varchar(31)", "NO", "PRI", null, "");
    $arr['db_configs']['config_value'] = array("varchar(255)", "NO", "", null, "");
    $arr['db_configs']['config_type'] = array("int(1)", "NO", "", "0", "");
    $arr['db_configs']['config_description'] = array("varchar(255)", "NO", "", null, "");
    $arr['field_config']['own_table'] = array("int(1)", "NO", "PRI", "0", "");
    $arr['field_config']['f_ID'] = array("varchar(255)", "NO", "PRI", null, "");
    $arr['field_config']['f_key'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['f_name'] = array("varchar(255)", "NO", "", null, "");
    $arr['field_config']['f_type'] = array("int(2)", "NO", "", null, "");
    $arr['field_config']['f_size'] = array("int(3)", "YES", "", "0", "");
    $arr['field_config']['f_interval'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['f_blank'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['f_unique'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['f_s_mode'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['f_table'] = array("varchar(255)", "NO", "", null, "");
    $arr['field_config']['f_illegals'] = array("varchar(255)", "NO", "", null, "");
    $arr['field_config']['f_default'] = array("varchar(255)", "NO", "", null, "");
    $arr['field_config']['f_check'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['comm'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['f_filter_md'] = array("int(1)", "NO", "", "1", "");
    $arr['field_config']['f_sort_sm'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['f_using'] = array("varchar(255)", "NO", "", null, "");
    $arr['field_config']['f_align'] = array("int(1)", "NO", "", "0", "");
    $arr['field_config']['table_percent'] = array("varchar(5)", "NO", "", "", "");
    $arr['field_config']['screen_order'] = array("int(2)", "NO", "", "0", "");
    $arr['field_config']['load_order'] = array("int(2)", "NO", "", "0", "");
    $arr['table_definitions']['table_name'] = array("varchar(255)", "NO", "PRI", null, "");
    $arr['table_definitions']['use_type'] = array("int(2)", "NO", "", "0", "");
    $arr['table_definitions']['illegal_symbols'] = array("varchar(255)", "NO", "", null, "");
    $arr['table_definitions']['group_catalog_type'] = array("int(2)", "NO", "", "0", "");
    $arr['table_definitions']['separators'] = array("varchar(255)", "NO", "", null, "");
    $arr['table_definitions']['max_level'] = array("int(2)", "NO", "", "0", "");
    $arr['table_definitions']['second_catalog_name'] = array("varchar(255)", "NO", "", null, "");
    $arr['table_definitions']['table_title'] = array("varchar(255)", "NO", "", null, "");
*/

function NoMatchUserFieldDef($f_params, $t_def)
{
    $arr_par = [];
    for ($i = 0; $i < 6; $i++) {
        if ($f_params[$i] != $t_def[$i]) {
            $arr_par[] = Title(428).' # <b>'.(string) ($i + 1).'</b> -- <b>'.SetFieldDefValue($f_params[$i]).'</b> to <b>'.SetFieldDefValue($t_def[$i]).'</b>';
        }
    }

    return $arr_par;
}
function TestUserTableStructure($dbh, $arr_table)
{
    $structure_errors = [];
    foreach (array_keys($arr_table) as $table) {
        $arr_struct = [];
        foreach ($_SESSION['all_field_list'][$table] as $k_field => $f_params) {
            if (! isset($arr_table[$table][$k_field])) {
                $structure_errors[$table][$k_field] = Title(283).' '.Title(282).' <b>'.$k_field.'</b>';
            } else {
                $arr_par = NoMatchUserFieldDef($f_params, $arr_table[$table][$k_field]);
                if (count($arr_par) > 0) {
                    $structure_errors[$table][$k_field] = Title(284).' '.Title(282).' <b>'.$k_field.'</b>: '.implode(', ', $arr_par);
                }
            }
            $arr_struct[$k_field] = $f_params;
        }
        foreach (array_keys($arr_table[$table]) as $field) {
            if (! isset($arr_struct[$field])) {
                $structure_errors[$table][$field] = Title(286).' '.Title(282).' <b>'.$field.'</b>';
            }
        }
    }

    return $structure_errors;
}
