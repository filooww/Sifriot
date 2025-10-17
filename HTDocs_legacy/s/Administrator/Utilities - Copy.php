<?php
function SwitchWorkingMode($dbh_sys, $db_id, $work_mode_setted, &$sw_break)
{
    if ($_POST['w_mode_s'] == '') {
        $sw_break = false;
    } else {
        $_SESSION['admin_mes'] = SetWorkingMode($dbh_sys, $db_id, $work_mode_setted, true);
        if ($_SESSION['admin_mes'][0] == 0) {
            $_SESSION['user_working_mode'] = (int) $_POST['user_working_mode'];
        } elseif ($_SESSION['admin_mes'][0] != 1 && ($_SESSION['admin_mes'][0] <= 1 || $_SESSION['priority'] < 99)) {
            ExitSession($_SESSION['admin_mes'][1].'|FF0000');
        }
    }
}
function ListCopy($source_list)
{
    $arr = [];
    foreach ($source_list as $k => $v) {
        $arr[$k] = $v;
    }

    return $arr;
}
function GoToTitles($dbh_sys)
{
    if (count($_SESSION['no_title']) > 0) {
        FillNoTitles();
    }
    $_SESSION['title_langs_all'] = SetLanguageList(0);
    $_SESSION['title_langs'] = array_slice($_SESSION['title_langs_all'], 1, null, true);
    $_SESSION['selected_title_lang'] = [0, '--'.Title(441).'--'];
    $_SESSION['start'] = 0;
    $_SESSION['title_filter_id'] = '';
    $_SESSION['title_filter_text'] = '';
    $_SESSION['no_title_filter'] = '';
    $_SESSION['title_filter'] = '';
    $_SESSION['title_count'] = TitleCounts($dbh_sys);
    $_SESSION['title_insert'] = 0;
    $_SESSION['title_modes_all'] = [Title(435), Title(437), Title(438), Title(442), FTM(Title(592))];
    $_SESSION['view_title_mode'] = [0, Title(435)];
    $_SESSION['title_param'] = GetTitlePortion($dbh_sys);
    TestTitles($dbh_sys);
}
function SysImage($img_name, $w, $h, $block = false)
{
    $after_img = ($block) ? 'Block' : '';

    return "<img src='".$_SESSION['image_dir'].'/'.$img_name.$after_img.".bmp' width='".(string) $w."' height='".(string) $h."'>";
}
function DBMDelQuestion($m_class, $buttons_arr, $arr_del, $q_num, $u_num = 0, $v_num = 0, $add_txt = '')
{
    $tn = (count($arr_del) < 2) ? $u_num : $v_num;
    $arr_quest[] = Title($q_num).(($u_num == 0) ? '' : ' '.FTM(Title($tn))).'?';
    if (count($arr_del) > 0) {
        $arr_quest[] = "<font size='+2'>".implode(', ', $arr_del).'</font>';
    }
    if ($add_txt != '') {
        $arr_quest[] = $add_txt;
    }
    QuestionForm($m_class, $arr_quest, $buttons_arr, [Title(209), Title(210)]);
}
function SetUserCategories(&$arr_options, $title_ns)
{
    $arr = array_keys($arr_options);
    for ($i = 0; $i < count($arr); $i++) {
        $arr_options[$arr[$i]][0] = ($title_ns[$i] < 0) ? FTM(Title(-$title_ns[$i])) : Title($title_ns[$i]);
    }
}
function SavePostSelect(&$arr_options, $post_select, $title_nums)
{
    $k = GetIDArraySelect($arr_options, $post_select, 0);
    SetUserCategories($arr_options, $title_nums);

    return [$k, $arr_options[$k][0]];
}
function DoubledMessages($tn, $arr_double, $arr_double_out, $tn_out, &$Mes)
{
    $Mes[] = "<font color='#FF0000'><b>".Title($tn).'</b></font>:';
    foreach ($arr_double as $k => $v) {
        $Mes[] = ' -- <b>'.$k.'</b> ('.((count($v) == 1) ? Title(419) : Title(222)).' <b>'.implode(', ', $v).'</b>)';
        if (isset($arr_double_out[$k]) && count($arr_double_out[$k]) > 0) {
            $Mes[count($Mes) - 1] .= ' -- '.((count($arr_double_out[$k]) == 1) ? Title(419) : Title(222)).' <b>'.implode(', ', $arr_double_out[$k]).'</b> '.Title($tn_out);
        }
    }
}
function ListNewStartPosition($act, $list_size)
{
    switch ($act) {
        case 'beg': return 0;
        case 'pgup': return max($_SESSION['start'] - $_SESSION['portion'] + 1, 0);
        case 'lnup': return max($_SESSION['start'] - 1, 0);
        case 'lndn': return min($_SESSION['start'] + 1, $list_size - 1);
        case 'pgdn': return min($_SESSION['start'] + $_SESSION['portion'] - 1, $list_size - 1);
        case 'end': return $list_size - 1;
        default: return $_SESSION['start'];
    }
}
function DBConfiguration($dbh_sys)
{
    $Mes = [];
    $_SESSION['site'] = ['return' => '../Administrator/DataBaseActions.php', 'db' => 'db_configs', 'title' => 207, 'common_mode' => false];
    $dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
    if (! $dbh) {
        ExitSession(Title(1).' <b>'.$_SESSION['db_info']['name'].'</b>`'.implode('`', $Mes).'|FF0000', $_SESSION['db_info']['id']);
    }
    $_SESSION['config_list'] = GetAllConfigs($dbh, 'db_configs');
}
function DataBaseActionExit($dbh)
{
    VisitParameters($dbh, $_SESSION['db_info']['id'], -1, $_SESSION['priority']);
    VisitParameters($dbh, 0, $_SESSION['user_working_mode'], $_SESSION['priority']);
}
function GoToAdminDB($dbh, &$Mes)
{
    $_SESSION['table_definitions'] = GetTableDefinitions($dbh, $Mes);
    $t_flags = DBTesting($dbh);
    if ($t_flags['empty'] || IsTablesErrors() || count($t_flags['errors']) + count($_SESSION['field_definitions']) + count($_SESSION['db_pre_flags']['table_errors']) + count($_SESSION['no_table']['no_definition']) + count($_SESSION['no_table']['no_DB']) + count($_SESSION['db_pre_flags']['table_errors']) > 0) {
        MessageOnNonUniqueSecondaryTables($t_flags['d_second_catalogs'], $Mes);
        MessageOnMandatoryDBTables($t_flags['mandatory'], $Mes); // !!!!!!!!!!!!
    }
    $_SESSION['db_info'] = DBInfoSelect($work_db);
}

function GoToUserDB($dbh, $work_db)
{
    $Mes = [];
    $_SESSION['table_definitions'] = GetTableDefinitions($dbh, $Mes);
    $t_flags = DBTesting($dbh);
    if ($t_flags['empty'] || IsTablesErrors() || count($t_flags['errors']) + count($_SESSION['field_definitions']) + count($_SESSION['db_pre_flags']['table_errors']) + count($_SESSION['no_table']['no_definition']) + count($_SESSION['no_table']['no_DB']) > 0) {
        ExitSession(Title(632).'|FF0000`['.$_SESSION['arr_db'][$work_db]['db_name'].']|0000FF`'.Title(613), $work_db);
    }
    if (count($Mes) == 0) {
        $_SESSION['db_info'] = DBInfoSelect($work_db);
        if ($_SESSION['priority'] < 11) {
            return '../MainTable/List';
        }
    }
    ExitSession(Title(632).'|FF0000`['.$_SESSION['arr_db'][$work_db]['db_name'].']|0000FF`'.Title(613), $work_db);
}

function GoToDB($dbh_sys, $work_db, &$Mes)
{
    $db_mes = [];
    $dbh = GetDB($_SESSION['arr_db'][$work_db]['db_name'], $db_mes, $_SESSION['arr_db'][$work_db]['db_coding']);
    if (! $dbh) {
        ExitSession(Title(1).' <b>'.$_SESSION['arr_db'][$work_db]['db_name'].'</b>`'.implode('`', $db_mes).'|FF0000', $work_db);
    }
    $_SESSION['db_pre_flags'] = ['table_errors' => [], 'no_existed_tables' => []];
    TestDataBaseTablesExist($dbh);
    if (count($_SESSION['db_pre_flags']['no_existed_tables']) > 0) {
        if ($_SESSION['priority'] < 11) {
            ExitSession(Title(632).'|FF0000`['.$_SESSION['arr_db'][$work_db]['db_name'].']|0000FF`'.Title(613).((count($_SESSION['arr_db']) == 2) ? '' : ' '.Title(631)), $work_db);
        } else {
            $dbh = RestoreDBHandler($work_db);
        }
    }

    if ($_SESSION['priority'] > 10) {
        VisitParameters($dbh_sys, 0, -1, $_SESSION['priority']);
    }
    VisitParameters($dbh_sys, $work_db, $_SESSION['user_working_mode'], $_SESSION['priority']);
    $_SESSION['admin_mes'] = SetWorkingMode($dbh_sys, $work_db, $_SESSION['user_working_mode']);

    $_SESSION['all_field_list'] = GetAllFieldList($dbh);
    $_SESSION['structure_errors'] = TestUserTableStructure($dbh, UserDataBaseStructureDefinition());
    if (count($_SESSION['structure_errors']) > 0) {
        if ($_SESSION['priority'] < 11) {
            ExitSession(Title(632).'|FF0000`['.$_SESSION['arr_db'][$work_db]['db_name'].']|0000FF`'.Title(613), $work_db);
        }
        $_SESSION['user_structure_form'] = $work_db;

        return '../Alarm/UserStructureForm';
    }
    $_SESSION['db_info'] = DBInfoSelect($_SESSION['arr_db'][$work_db]);
    if ($_SESSION['priority'] < 11) {
        return GoToUserDB($dbh, $work_db);
    }

    return '../Administrator/DataBaseActions';
    //    return GoToAdminDB($dbh, $Mes);
}

function GoToPrefferedDB($dbh_sys, $db_id, &$Mes)
{
    $goto = GoToDB($dbh_sys, (int) $db_id, $Mes);
    if ($count($Mes) == 0) {
        SetPrefferedDB($dbh_sys, $db_id);
    }

    return $goto;
}
function DBSelectExit()
{
    if (isset($_SESSION['db_info'])) {
        ExitSession('', $_SESSION['db_info']['id']);
    } else {
        ExitSession();
    }
}
function GoToFields()
{
    if (isset($_SESSION['return_mes']) && count($_SESSION['return_mes']) > 0) {
        return '../Administrator/DataBaseActions.php';
    }
    $_SESSION['field_change'] = false;
    $_SESSION['field_sort'] = SortFieldDefinitions('table_field');
    $_SESSION['f_k'] = [0, ''];
    $_SESSION['mandatory_db_tables'] = SetMandatoryDBTables($t_flags['mandatory']);
    $_SESSION['reference_catalogs'] = GetRefCatalogs();

    return '../Fields/FieldForm.php';
}
function SetPrefferedDB($dbh_sys, $db_id)
{
    mysqli_query($dbh_sys, 'UPDATE user_ident SET preffered_db = '.(string) $db_id.' WHERE id_user = '.$_SESSION['user_id']);
    $_SESSION['db_info'] = DBInfoSelect($db_id);
}
function ChangeCategory($k, $old_priority, $new_priority, $old_err, $new_err)
{
    $old_category = GetUserCategoryByPriority($old_priority);
    if (isset($_POST['user_priority|'.$k])) {
        $new_category = GetUserCategoryByPriority($_POST['user_priority|'.$k]);
    } else {
        $new_category = $old_category;
    }
    if ($old_category != $new_category) {
        $_SESSION['categories'][$old_category][3]--;
        $_SESSION['categories'][$new_category][3]++;
        if ($old_err) {
            $_SESSION['categories'][$old_category][4]--;
        }
        if ($new_err) {
            $_SESSION['categories'][$new_category][4]++;
        }
    } else {
        if ($old_err && ! $new_err) {
            $_SESSION['categories'][$old_category][4]--;
        } elseif (! $old_err && $new_err) {
            $_SESSION['categories'][$old_category][4]++;
        }
    }
    $_SESSION['categories']['all'][4] = SumErrors();
}
function SumErrors()
{
    $s = 0;
    foreach ($_SESSION['categories'] as $k => $v) {
        if ($k != 'all') {
            $s += $_SESSION['categories'][$k][4];
        }
    }

    return $s;
}
function GetUserCategoryByName($category_name)
{
    foreach ($_SESSION['categories'] as $k => $v) {
        if ($v[0] == $category_name) {
            return $k;
        }
    }

    return 'invalid';
}
function GetUserCategoryByPriority($user_priority)
{
    if (! is_numeric($user_priority)) {
        return 'invalid';
    }
    if ($user_priority < 0 || $user_priority > 99) {
        return 'invalid';
    }
    foreach ($_SESSION['categories'] as $k => $v) {
        if ($k != 'all' && $user_priority >= $v[1] && $user_priority <= $v[2]) {
            return $k;
        }
    }

    return 'invalid';
}
/*
function TestUserDB($db_name, $db_coding, $db, &$db_err)
{
    $db_mes = array();
    $dbh = GetDB($db_name, $db_mes, $db_coding);
    if (!$dbh) ExitSession(Title(1)." <b>".$db_name."</b>`".implode("`", $db_mes)."|FF0000", $db);
    $_SESSION['preliminary_flags'] = array("table_errors"=>array(), "no_existed_tables"=>array());
    $dbh = TestDataBaseTablesExist($dbh, $db);
    $_SESSION['all_field_list'] = GetAllFieldList($dbh, $db_err, $db);
    $_SESSION['structure_errors'] = TestUserTableStructure($dbh, UserDataBaseStructureDefinition());
    if (count($_SESSION['structure_errors']) > 0)
    {
//        if ($_SESSION['priority'] < 11) ExitSession(Title(632)."|FF0000`[".$db_name."]|0000FF`".Title(613), $db);
//        $_SESSION['user_structure_form'] = $db;
//        return "../Alarm/UserStructureForm";
    }
    SimpleSysTableCheck($dbh, "db_configs", "config_name = '' OR config_type < 0 OR config_type > 1");
    if (count($_SESSION['preliminary_flags']['table_errors']) > 0)
    {
//        if ($_SESSION['priority'] < 11) ExitSession(Title(632)."|FF0000`[".$db_name."]|0000FF`".Title(613), $db);
        $db_err[] = Title(592)." ".((count($_SESSION['preliminary_flags']['table_errors']) == 1) ? Title(184) : Title(186))." <b>".implode(", ", $_SESSION['preliminary_flags']['table_errors'])."/b>";
    }
    $_SESSION['table_definitions'] = GetTableDefinitions($dbh, $db_err);
    if (count($_SESSION['table_definitions']) == 0 || IsTablesErrors())
    {
//        if ($_SESSION['priority'] < 11) ExitSession(Title(632)."|FF0000`[".$db_name."]|0000FF`".Title(613), $db);
        if (count($_SESSION['table_definitions']) == 0) $db_err[] = Title(365)." <b>table_definitions</b> ".Title(160);
        else foreach ($_SESSION['table_definitions'] as $t => $t_arr) $db_err[] = Title(365)." <b>".$t.": ".implode("; ", $t_arr['tab_err'])."</b>";
    }
    $_SESSION['no_table'] = TestUserTablesExist($db, $db_err);
    GetUserDBTableStructure($db, $db_err);
    $t_flags = TestTables(true);
    $_SESSION['field_definitions'] = GetFieldParameters($dbh, $t_flags['mandatory']);
    if ($t_flags['empty'] || IsFieldsErrors() || count($t_flags['errors']) > 0)
    {
//        if ($_SESSION['priority'] < 11) ExitSession(Title(632)."|FF0000`[".$db_name."]|0000FF`".Title(613), $db);
        MessageOnNonUniqueSecondaryTables($t_flags['d_second_catalogs'], $db_err);
        MessageOnMandatoryDBTables($t_flags['mandatory'], $db_err);
    }
    return "";
}
*/
?>

