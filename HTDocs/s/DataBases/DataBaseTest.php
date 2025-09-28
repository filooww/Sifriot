<?php
function GetDBTestFlag($k)
{
    if (count($_SESSION['arr_db'][$k]['db_err']) == 0) return true;
    elseif (count($_SESSION['arr_db'][$k]['db_err']) == 1 && isset($_SESSION['pre_db_err'][$k]) && $_SESSION['arr_db'][$k]['db_err'][0] == "<font color='#0000FF'>".$_SESSION['pre_db_err'][$k]."</font>") return true;
    return false;
}
function TestInitDBList()
{
    $fl_test = true;
    foreach ($_SESSION['arr_db'] as $k => $v)
    {
        $_SESSION['arr_db'][$k]['db_err'] = TestDatabase($v['db_name'], $v['db_coding'], (string)$k, $v['del']);
        if (!GetDBTestFlag($k)) $fl_test = false;
    }
    return $fl_test;
}
function TestDBList(&$Mes, $mes_err = false)
{
    $fl_test = TestInitDBList();
    if (!$fl_test && $mes_err) $Mes[] = "<font color='#FF0000'>".Title(471)."</font>";
    return ($_SESSION['alarm'] || $fl_test);
}
function TestDatabase($db_name, $db_coding, $db_id, $db_del = false)
{
    $err_arr = array();
	$dbh = GetDB($db_name, $err_arr, $db_coding);
	for ($i = 0; $i < count($err_arr); $i++)
    {
        if ($err_arr[$i] == Title(1)) $err_arr[$i] = "<font color='#FF0000'>".FTM($err_arr[$i])."</font>";
        else $err_arr[$i] = "<font color='#FF0000'>".$err_arr[$i]."</font>";
    }
	if ((integer)$db_id < 0) $err_arr[] = "<font color='#FF0000'>".Title(532)." (".Title(589)." <b>".$db_id."</b>, ".Title(591).") <button name='correct_id|".$db_id."' type='submit' class='i_h' title='".Title(590)." ".FTM(Title(147))."'>...</button>";
	if (isset($_SESSION['del_ref'][$db_id]))
    {
        $err_arr[] = $_SESSION['del_ref'][$db_id];
        $fl_test = false;
	}
    if (isset($_SESSION['pre_db_err'][$db_id])) $err_arr[] = "<font color='#0000FF'>".$_SESSION['pre_db_err'][$db_id]."</font>";
	return $err_arr;
}
function TestUserDB($db_name, $db_coding, $db)
{
    $_SESSION['structure_errors'] = array();
    $_SESSION['db_errors'] = array();
	$dbh = GetDB($db_name, $_SESSION['db_errors'], $db_coding);
	if ($dbh)
	{
        $db_pre_flags = array();
	    $add_tables = TestDataBaseTablesExist($dbh, $db);
        $_SESSION['all_field_list'] = GetAllFieldList($dbh, $_SESSION['db_errors'], $db);
        if (count($add_tables) > 0)
        {
            $dbh = GetOnlyDB($db_name);
            if (!$dbh) ExitSession(Title(1)."|FF0000");
        }
        $_SESSION['structure_errors'] = TestUserTableStructure($dbh, UserDataBaseStructureDefinition());
        if (count($_SESSION['structure_errors']) == 0)
        {
            TestDBServiceTableCheck($dbh, "db_configs", "config_name NOT REGEXP '^[a-z][a-z0-9_]*$' AND config_name <> '' OR config_name = '' OR config_type > 1 OR config_type = 1 AND (config_value = '' OR config_value <> '' AND config_value NOT REGEXP '^[+-]?[0-9]*\\.?[0-9]+([eE][+-]?[0-9]+)?$')", $db_pre_flags);
            TestDBFieldCheck($dbh, $db_pre_flags);
            TestDBServiceTableCheck($dbh, "table_definitions", "table_name NOT REGEXP '^[a-z][a-z0-9_]*$' AND table_name <> '' OR table_name = '' OR use_type < 1 OR use_type > ".(string)count($_SESSION['table_types'])." OR group_catalog_type > ".(string)count($_SESSION['group_types'])." OR second_catalog_name NOT REGEXP '^[a-z][a-z0-9_]*$' AND second_catalog_name <> ''", $db_pre_flags);
            if (count($db_pre_flags) > 0) $_SESSION['db_errors'][] = FTM(Title(592))." ".((count($db_pre_flags) == 1) ? Title(184) : Title(186))." <b>".implode(", ", $db_pre_flags)."</b>";
            $_SESSION['table_definitions'] = GetTableDefinitions($dbh, $db);
            GetUserDBTableStructure($db, $_SESSION['db_errors']);
            $t_flags = TestTables(true);
            $_SESSION['field_definitions'] = GetFieldParameters($dbh, $t_flags['mandatory']);
            if ($t_flags['empty'] || IsFieldsErrors() || $t_flags['errors'])
            {
                MessageOnNonUniqueSecondaryTables($t_flags['d_second_catalogs'], $_SESSION['db_errors']);
                MessageOnMandatoryDBTables($t_flags['mandatory'], $_SESSION['db_errors']);
            }
        }
    }
}
function TestDBServiceTableCheck($dbh, $table, $req_str, &$db_pre_flags)
{
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$table." WHERE ".$req_str);
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if ($row[0] > 0 && !in_array($table, $db_pre_flags)) $db_pre_flags[] = $table;
		}
		mysqli_free_result($res);
	}
}
function TestDBFieldCheck($dbh, &$db_pre_flags)
{
	$req_str = "own_table > 2 OR ";
	$req_str .= "f_ID NOT REGEXP '^[a-z][a-z0-9_]*$' OR f_ID = '' OR ";
	$req_str .= "f_key > 1 OR ";
    $req_str .= "f_name = '' AND (screen_order > 0 OR load_order > 0) OR ";
    $req_str .= "f_type > ".(string)(count($_SESSION['field_types']) - 1)." OR ";
	$req_str .= "f_interval > 1 OR ";
	$req_str .= "f_blank > 1 OR ";
	$req_str .= "f_unique > 1 OR ";
	$req_str .= "f_s_mode > 1 OR ";
	$req_str .= "NOT (f_type = 8 AND (SELECT table_definitions.table_name FROM table_definitions WHERE table_definitions.table_name = field_config.f_table) IS NOT NULL OR f_type <> 8 AND  f_table = '') OR ";
	$req_str .= "f_check > 1 OR ";
	$req_str .= "comm > 1 OR ";
	$req_str .= "f_filter_md > ".(string)(count($_SESSION['compare_mode']) - 1)." OR ";
	$req_str .= "f_sort_sm > ".(string)(count($_SESSION['sort_mode']) - 1)." OR ";
    $req_str .= "f_using <> '' AND ".TestUsing()." OR ";
	$req_str .= "f_align > ".(string)(count($_SESSION['field_align']) - 1)." OR ";
	$req_str .= "NOT (table_percent = '' OR table_percent REGEXP '^[1-9][0-9]*%$' OR table_percent REGEXP '^[1-9][0-9]*px$' OR table_percent REGEXP '^[1-9][0-9]*$');";
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM field_config WHERE ".$req_str);
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if ($row[0] > 0 && !in_array("field_config", $db_pre_flags)) $db_pre_flags[] = "field_config";
		}
		mysqli_free_result($res);
	}
}
function TestUsing()
{
    $permutation_arr = SetUsings();
    $str_arr = array();
    foreach ($permutation_arr as $z) $str_arr[] = "f_using <> '".$z."'";
    return implode(" AND ", $str_arr);
}
function SetUsings()
{
    $a = array();
    for ($i = 1; $i < count($_SESSION['field_using']); $i++) $a[] = $i;
    $usings_arr = array();
    $permutations = array();
    heapPermutation($a, count($a), $permutations);
    foreach ($permutations as $permutation) $usings_arr[count($_SESSION['field_using'])] = $permutation;
    for ($p_size = count($_SESSION['field_using']) - 1; $p_size > 0; $p_size--)
    {
        $usings_arr[$p_size] = array();
        foreach ($permutations as $permutation)
        {
            $p_arr = explode(",", $permutation);
            $p_s = array_slice($p_arr, 0, $p_size);
            $x = implode(",", $p_s);
            if (!in_array($x, $usings_arr[$p_size])) $usings_arr[$p_size][] = $x;
        }
    }
    $permutation_arr = $permutations;
    for ($p_size = 3; $p_size > 0; $p_size--) $permutation_arr = array_merge($permutation_arr, $usings_arr[$p_size]);
    return $permutation_arr;
}
function heapPermutation(&$a, $size, &$result)
{
    if ($size == 1) $result[] = implode(',', $a);
    else
    {
        for ($i = 0; $i < $size; $i++)
        {
            heapPermutation($a, $size - 1, $result);
            if ($size % 2 == 0) ChangeElements($a, $i, $size - 1);
            else ChangeElements($a, 0, $size - 1);
        }
    }
}
function ChangeElements(&$a, $i1, $i2)
{
    $z = $a[$i1];
    $a[$i1] = $a[$i2];
    $a[$i2] = $z;
}

?>
