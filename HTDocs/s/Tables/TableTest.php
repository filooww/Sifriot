<?php
function PostToSessionParameters($table_name, $table_params)
{
	if ($_SESSION['table_definitions'][$table_name]['table_title'] != $_POST["table_title-".$table_name]) $_SESSION['table_definitions'][$table_name]['table_title'] = $_POST["table_title-".$table_name];
	if ($table_params['use_type'] == 3)
	{
		if ($table_params['second_catalog'] == "")
		{
			if (isset($_POST["illegals-".$table_name]) && $_SESSION['table_definitions'][$table_name]['illegals'] != $_POST["illegals-".$table_name])
			{
				$_SESSION['table_definitions'][$table_name]['illegals'] = $_POST["illegals-".$table_name];
				TestTableIllegals($table_name);
			}
		}
		elseif (isset($_POST["separators-".$table_name]) && $_SESSION['table_definitions'][$table_name]['separators'] != $_POST["separators-".$table_name]) $_SESSION['table_definitions'][$table_name]['separators'] = $_POST["separators-".$table_name];
	}
}
function TestTableIllegals($table_name)
{
    $arr_illegals = explode($_SESSION['char_group'], $_SESSION['table_definitions'][$table_name]['illegals']);
    $new_illegals = array();
    foreach ($arr_illegals as $ill) if ($ill != "") $new_illegals[] = $ill;
    $_SESSION['table_definitions'][$table_name]['illegals'] = implode($_SESSION['char_group'], $new_illegals);
}
function ChangeApostrophe($table_name, $par_name, $title_number, &$fl)
{
	if (strpos($_SESSION['table_definitions'][$table_name][$par_name], chr(39)) !== false)
	{
        $_SESSION['table_definitions'][$table_name]['tab_err'][] = FTM(Title(403))." <b>".Title($title_number)."</b> ".Title(318)." (".Title(328)." <font color='#990000' size='+2'><b>".$_SESSION['apostrophe_replace']."</b></font>)";
		$_SESSION['table_definitions'][$table_name][$par_name] = str_replace(chr(39), $_SESSION['apostrophe_replace'], $_SESSION['table_definitions'][$table_name][$par_name]);
		$fl = true;
	}
}
function SetTableError($t_name, $err_text, &$fl)
{
    $_SESSION['table_definitions'][$t_name]['tab_err'][] = "<font color='#FF0000'>".$err_text."</font>";
    $fl = true;
}
function IsMandatoryTableCorrect($mnd_tables)
{
    foreach ($mnd_tables as $v) if (count($v) != 1) return false;
    return true;
}
function TestTables($init)
{
    $flags = array("mandatory" => TestTableTypes(), "d_second_catalogs" => TestSecondCatalogs(), "empty" => false, "errors" => false);
    $fl = true;
    if (count($_SESSION['table_definitions']) == 0) $flags['empty'] = true;
    else
    {
	    foreach ($_SESSION['table_definitions'] as $t_name => $v)
	    {
            $_SESSION['table_definitions'][$t_name]['tab_err'] = array();
		    if (!$init)
            {
                PostToSessionParameters($t_name, $v);
		        ChangeApostrophe($t_name, "table_title", 311, $flags['errors']);
          	    ChangeApostrophe($t_name, "illegals", 313, $flags['errors']);
		        ChangeApostrophe($t_name, "separators", 315, $flags['errors']);
            }
            if ($_SESSION['table_definitions'][$t_name]['table_title'] == "") SetTableError($t_name, Title(308), $flags['errors']);
            if (!in_array($t_name, array_keys($_SESSION['all_field_list']))) SetTableError($t_name, Title(427), $flags['errors']);
            if (!isset($_SESSION['table_types'][$v['use_type']])) SetTableError($t_name, FTM(Title(602))." ".FTM(Title(312))." (<b>".$v['use_type']."</b>)", $flags['errors']);
            if (!isset($_SESSION['group_types'][$v['group_type']])) SetTableError($t_name, FTM(Title(602))." ".FTM(Title(233))." (<b>".$v['group_type']."</b>)", $flags['errors']);
		    if ($v['use_type'] == 3 && $v['second_catalog'] != "")
            {
                if ($v['separators'] == "") SetTableError($t_name, Title(168), $flags['errors']);
                if (!in_array($v['second_catalog'], array_keys($_SESSION['all_field_list']))) SetTableError($t_name, Title(639), $flags['errors']);
            }
        }
	}
	return $flags;
}
function SetPreliminaryTableErrors($flags)
{
    $flag_errors = false;
    if ($flags['mandatory'][1] != 1 || $flags['mandatory'][2] != 1 || $flags['mandatory'][5] != 1) $flag_errors = true;
    if (count($flags['d_second_catalogs']) > 0) $flag_errors = true;
    if ($flags['empty'] || $flags['errors']) $flag_errors = true;
    return $flag_errors;
}
function TestThisType($table_name, $n_type, &$Mes)
{
	$cnt = count($Mes);
	$arr = array();
	foreach ($_SESSION['table_definitions'] as $table_name => $table_params) if ($table_params['use_type'] == $n_type) $arr[] = $table_name;
	if (count($arr) > 1)
	{
		switch ($n_type)
		{
			case 1: $Mes[] = "<font color='#FF0000'>".Title(320)." <b>(".implode(", ", $arr).")</b></font>"; break;
			case 2: $Mes[] = "<font color='#FF0000'>".Title(322)." <b>(".implode(", ", $arr).")</b></font>"; break;
			case 5: $Mes[] = "<font color='#FF0000'>".Title(324)." <b>(".implode(", ", $arr).")</b></font>"; break;
		}
	}
	if (count($Mes) == $cnt) return true;
	$Mes[] = "<font color='#0000FF'><b>".Title(329)."</b></font>";
}
function TestTableTypes()
{
    $mandatory_db_tables = array(1 => array(), 2 => array(), 5 => array());
    foreach ($_SESSION['table_definitions'] as $table_name => $v) if ($v['use_type'] == 1 || $v['use_type'] == 2 || $v['use_type'] == 5) $mandatory_db_tables[$v['use_type']][] = $table_name;
	return $mandatory_db_tables;
}
function TestSecondCatalogs()
{
	$second_list = array("tables"=>array(), "seconds"=>array());
	foreach ($_SESSION['table_definitions'] as $table_name => $table_params)
	{
		if ($table_params['use_type'] == 3 && $table_params['second_catalog'] != "")
		{
			$second_list['tables'][] = $table_name;
			$second_list['seconds'][] = $table_params['second_catalog'];
		}
	}
	$second_groups = array_count_values($second_list['seconds']);
	$d_second_catalogs = array();
	for ($i = 0; $i < count($second_list['seconds']); $i++) if ($second_groups[$second_list['seconds'][$i]] > 1) $d_second_catalogs[$second_list['seconds'][$i]][] = $second_list['tables'][$i];
	return $d_second_catalogs;
}
function SetMandatoryDBTables($flags_mandatory)
{
    $mandatory_db_tables = array();
    TestTableTypes();
    if (IsMandatoryTableCorrect($flags_mandatory)) foreach ($flags_mandatory as $k => $v) $mandatory_db_tables[$k] = $v[0];
    return $mandatory_db_tables;
}
function MessageOnMandatoryDBTables($mandatory_db_tables, &$db_err)
{
    $arr_messages = array(1 => array(319, 320), 2 => array(321, 322), 5 => array(323, 324));
    foreach ($mandatory_db_tables as $table_type => $mandatory_tables)
    {
        if (count($mandatory_tables) == 0) $db_err[] = Title($arr_messages[$table_type][0]);
        elseif (count($mandatory_tables) > 1) $db_err[] = Title($arr_messages[$table_type][1]).":</font> <b>".implode(", ", $mandatory_tables)."</b>";
    }
}
function MessageOnNonUniqueSecondaryTables($d_second_catalogs, &$db_err)
{
    if (count($d_second_catalogs) > 0)
    {
        $tab_list = array();
        foreach ($d_second_catalogs as $k => $v) $tab_list[] = "<b>".$k."</b> (".FTM(Title(115))." <b>".implode(", ", $v)."</b>)";
        $db_err[] = FTM(Title(309)).": ".implode("; ", $tab_list);
    }
}
function IsTablesErrors()
{
    foreach ($_SESSION['table_definitions'] as $table_name => $table_params) if (count($table_params['tab_err']) > 0) return true;
    return false;
}

?>
