<?php
function GetFieldParameters($dbh, $mandatory_db_tables) // new fields
{
	$f_param = array();
	$flag_repeat = false;
    $res = mysqli_query($dbh, "SELECT * FROM field_config");
    if ($res)
    {
	    while ($row = mysqli_fetch_row($res))
	    {
            $test_err = FieldInitialTest($row, $mandatory_db_tables);
//                                             0             1                     2         3         4        5        6             7         8        9             10            11            12        13        14             15             16        17        18        19        20
//                                             f_key                               screen_   f_using   f_name   f_type   f_unique      f_default f_size   f_interval    f_blank       f_s_mode      f_table   f_        f_check        comm           f_        f_sort_sm f_align   table_
//                                                                                 order                                                                                                                      illegals                                filter_md                     percent
            $f_param[$row[0]][$row[1]] = array($row[2] == 1, $test_err['ind'] + 1, $row[20], $row[17], $row[3], $row[4], $row[8] == 1, $row[12], $row[5], $row[6] == 1, $row[7] == 1, $row[9] == 1, $row[10], $row[11], $row[13] == 1, $row[14] == 1, $row[15], $row[16], $row[18], $row[19], $test_err['err']);
	    }
	    mysqli_free_result($res);
	}
    $flag_repeat = SetRepeatFieldValues($f_param);
    if (count($f_param) > 0)
    foreach (array_keys($_SESSION['all_field_list']) as $k_own) foreach (array_keys($_SESSION['all_field_list'][$k_own]) as $k_field) if (!isset($f_param[$k_own][$k_field])) $f_param[$k_own][$k_field] = array(false, 0, /*count($f_param[$k_own]),*/0, "", $k_field, 0, false, "", 0, false, false, false, "", "", false, false, 0, 0, 0, "", array());
	return $f_param;
}
function FieldInitialTest($row, $mandatory_db_tables)
{
    $test_err = array("ind" => -1, "err" => array());
    if (count($mandatory_db_tables[1]) == 1 && count($mandatory_db_tables[2]) == 1)
    {
        if (isset($_SESSION['all_field_list'][$mandatory_db_tables[$row[0]][0]]))
        {
            $test_ind = array_search($row[1], $_SESSION['all_field_list'][$row[0]]);
            if ($test_ind === false) $test_err['err'][] = Title(653)." <b>".$mandatory_db_tables[$row[0]]."/b>";
            else $test_err['ind'] = $test_ind;
        }
        else $test_err['err'][] = Title(654)." <b>".$mandatory_db_tables[$row[0]][0]."</b>)";
    }
    if ($row[0] < 0 || $row[0] > 2) $test_err['err'][] = FTM(Title(602))." ".Title(342)." (<b>".$row[0]."</b>)"; // invalid table type
    if (!TestSysString($row[1])) $test_err['err'][] = FTM(Title(602))." ".Title(342)." (<b>".$row[0]."</b>)"; // invalid field name
    if ($row[2] < 0 || $row[2] > 1) $test_err['err'][] = FTM(Title(602))." ".Title(342)." (<b>".$row[2]."</b>)";
    if ($row[3] == "" && $row[20] > 0 /*&&$row[21] > 0*/) $test_err['err'][] = FTM(Title(602))." ".Title(342)." (<b>".$row[2]."</b>)"; // invalid name   Title(341)
    if (!isset($_SESSION['field_types'][$row[4]])) $test_err['err'][] = Title(596)." <b>".(string)$row[4]."/b>";
    if ($row[5] < 0) $test_err['err'][] = Title(490)." ".Title(350)." (<b>".$row[5]."</b>)";
    if ($row[6] < 0 || $row[6] > 1) $test_err['err'][] = Title(596)." <b>".(string)$row[6]."/b> (".Title(577). "<b>0</b>".Title(567)."<b>1</b>)";
    if ($row[7] < 0 || $row[7] > 1) $test_err['err'][] = Title(596)." <b>".(string)$row[7]."/b> (".Title(577). "<b>0</b>".Title(567)."<b>1</b>)";
    if ($row[8] < 0 || $row[8] > 1) $test_err['err'][] =  Title(596)." <b>".(string)$row[8]."/b> (".Title(577). "<b>0</b>".Title(567)."<b>1</b>)";
    if ($row[9] < 0 || $row[9] > 1) $test_err['err'][] =  Title(596)." <b>".(string)$row[9]."/b> (".Title(577). "<b>0</b>".Title(567)."<b>1</b>)";
    if (!($row[4] == 8 && isset($_SESSION['reference_catalogs'][$row[10]]) || $row[4] != 8 && $row[10] == "")) $test_err['err'][] = Title(596)." <b>".(string)$row[10]."/b>";
    if ($row[13] < 0 || $row[13] > 1) $test_err['err'][] = Title(596)." <b>".(string)$row[13]."/b> (".Title(577). "<b>0</b>".Title(567)."<b>1</b>)";
    if ($row[14] < 0 && $row[14] > 1) $test_err['err'][] = Title(596)." <b>".(string)$row[14]."/b> (".Title(577). "<b>0</b>".Title(567)."<b>1</b>)";
    if (!isset($_SESSION['compare_mode'][$row[15]])) $test_err['err'][] = Title(596)." <b>".(string)$row[15]."/b>";
    if (!isset($_SESSION['sort_mode'][$row[16]])) $test_err['err'][] = Title(596)." <b>".(string)$row[16]."/b>";
    if ($row[17] != "")
    {
        $arr = explode(",", $row[17]);
        $arr_err = array();
        foreach ($arr as $z) if (!isset($_SESSION['field_using'][$z])) $arr_err[] = $z;
        if (count($arr_err) > 0) $test_err['err'][] = Title(596)." (<b>".implode("; ", $arr_err)."</b>)";
    }
    if (!isset($_SESSION['field_align'][$row[18]])) $test_err['err'][] = Title(596)." <b>".(string)$row[18]."/b>";
    if (!TestPercent($row[19], array("", "%", "px"))) $test_err['err'][] = Title(596)." <b>".(string)$row[19]."/b>"; // invalid percent
    if ($row[20] < 0) $test_err['err'][] = Title(490)." ".Title(350)." (<b>".$row[20]."</b>)";
    return $test_err;
}
function TestPercent($perc, $end_arr)
{
    if ($perc == "") return true;
    foreach ($end_arr as $z) if (RegularComparison($reg_exp = "'^[1-9][0-9]*".$z."$'", $perc)) return true;
    return false;
}
function SetRepeatFieldValues(&$f_param)
{
    $arr_rep_name = array(1=>array(), 2=>array());
    $arr_rep_screen = array(1=>array(), 2=>array());
    if (isset($f_param[1]))
    {
        $arr_rep_name[1] = RepeatFieldValues($f_param[1], 1, 4, "");
        $arr_rep_screen[1] = RepeatFieldValues($f_param[1], 1, 2, 0);
    }
    if (isset($f_param[2]))
    {
        $arr_rep_name[2] = RepeatFieldValues($f_param[2], 2, 4, "");
        $arr_rep_screen[2] = RepeatFieldValues($f_param[2], 2, 2, 0);
    }
    if (count($arr_rep_name[1]) > 0) foreach ($arr_rep_name[1] as $rep_value => $rep_list) foreach ($rep_list as $rep_id => $rep_ids) $f_param[1][$rep_id][20][] = Tilte(490)." <b>".Title(347)." (".$rep_value.")</b> ".Title(587)." ".((count($rep_ids) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $rep_ids);
    if (count($arr_rep_name[2]) > 0) foreach ($arr_rep_name[2] as $rep_value => $rep_list) foreach ($rep_list as $rep_id => $rep_ids) $f_param[2][$rep_id][20][] = Tilte(490)." <b>".Title(347)." (".$rep_value.")</b> ".Title(587)." ".((count($rep_ids) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $rep_ids);
    if (count($arr_rep_screen[1]) > 0) foreach ($arr_rep_screen[1] as $rep_value => $rep_list) foreach ($rep_list as $rep_id => $rep_ids) $f_param[1][$rep_id][20][] = Tilte(490)." <b>".Title(333)." (".$rep_value.")</b> ".Title(587)." ".((count($rep_ids) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $rep_ids);
    if (count($arr_rep_screen[2]) > 0) foreach ($arr_rep_screen[2] as $rep_value => $rep_list) foreach ($rep_list as $rep_id => $rep_ids) $f_param[2][$rep_id][20][] = Tilte(490)." <b>".Title(333)." (".$rep_value.")</b> ".Title(587)." ".((count($rep_ids) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $rep_ids);
    return (count($arr_rep_name[1]) + count($arr_rep_name[2]) + count($arr_rep_screen[1]) + count($arr_rep_screen[2]) > 0);
}
function RepeatFieldValues($tested_arr, $table_number, $par_name, $empty_value)
{
    $arr_values = array();
    foreach ($tested_arr as $f => $f_params) if ($f_params[$par_name] != $empty_value) $arr_values[$f] = $f_params[$par_name];
    $arr_value_groups = array_count_values($arr_values);
    foreach ($arr_value_groups as $id => $value_count) if ($value_count == 1) unset($arr_value_groups[$id]);
    $rep_values = array();
    foreach ($tested_arr as $f => $f_params) if (isset($arr_value_groups[$f_params[$par_name]])) $rep_values[$f_params[$par_name]][] = $f;
    $arr_rep = array();
    foreach ($rep_values as $id => $arr_field) $arr_rep[$id] = WriteRepeatError($arr_field);
    return $arr_rep;
}
function WriteRepeatError($arr_field)
{
    $arr_rep = array();
    for ($i = 0; $i < count($arr_field); $i++) for ($j = 0; $j < count($arr_field); $j++) if ($j != $i) $arr_rep[$arr_field[$i]][] = $arr_field[$j];
    return $arr_rep;
}
function SortColumnValue($sort_by, $table_code, $field_code, $f_def)
{
    switch ($sort_by)
    {
		case "table_field": return (string)$table_code.strtolower($field_code);
		case "field"	  : return strtolower($field_code);
		case "table_name" : return (string)$table_code.strtolower($f_def[4]);
		case "in_table"	  : return (string)$table_code.sprintf("%'.02d", $f_def[1]);
		case "in_screen"  : return (string)$table_code.sprintf("%'.02d", $f_def[2]);
		default           : return "";
    }
}
function SortFieldDefinitions($sort_by)
{
	$arr_sort = array();
	foreach (array_keys($_SESSION['field_definitions']) as $t) foreach ($_SESSION['field_definitions'][$t] as $f_c => $f_def) $arr_sort[(string)$t."|".$f_c] = SortColumnValue($sort_by, $t, $f_c, $f_def);
    asort($arr_sort);
    $_SESSION['sorted_column'] = $sort_by;
    return $arr_sort;
}
function ViewFieldUsing($str_using)
{
	if ($str_using == "") return "";
	$arr = explode(",", $str_using);
	$arr_view = array();
	foreach ($arr as $z) if (isset($_SESSION['field_using'][$z])) $arr_view[] = $_SESSION['field_using'][$z];
	return implode(", ", $arr_view);
}
function GetAllFieldList($dbh, &$db_err, $db = -1)
{
    $arr_table = GetTableList($dbh);
    if (count($arr_table) == 0 && $db != -1)
    {
        if ($_SESSION['priority'] < 11) ExitSession(Title(632)."|FF0000`[".$_SESSION['arr_db'][$db]['db_name']."]|0000FF`".Title(613), $db);
        $db_err[] = Title(163)." <b>".$_SESSION['arr_db'][$db]['db_name']."</b> ".Title(160);
    }
    $arr_all_field = array();
    foreach ($arr_table as $table)
    {
        $res_field = mysqli_query($dbh, "SHOW FULL COLUMNS FROM ".$table); // one time
	    if ($res_field)
	    {
		    while ($row_field = mysqli_fetch_row($res_field))
		    {
				$arr_all_field[$table][$row_field[0]] = array($row_field[1], $row_field[2], $row_field[3], $row_field[4], $row_field[5], $row_field[6]);
			}
		}
		mysqli_free_result($res_field);
	}
    return $arr_all_field;
}
function CatalogFieldDefinitions($table, $separators, &$t_def)
{
    $arr_field = array("", "");
    if (isset($_SESSION['all_field_list'][$table]))
    {
        $arr_k = array_keys($_SESSION['all_field_list'][$table]);
        if (count($arr_k) > 0) $arr_field[0] = $arr_k[0];
        if (count($arr_k) > 1) $arr_field[1] = $arr_k[1];
    }
    if ($arr_field[0] == "") $t_def[$table]['tab_err'][] = Title(351);
    else $t_def[$table]['catalog_id'] = $arr_field[0];
    if ($arr_field[1] == "") $t_def[$table]['tab_err'][] = Title(363);
    $t_def[$table]['catalog_value'] = $arr_field[1];
    if ($arr_field[0] != "" && $arr_field[1] != "" && $separators == "") $_SESSION['single_catalogs'][] = $table;
}
function IsFieldsErrors()
{
    foreach (array_keys($_SESSION['field_definitions']) as $t)
    {
        foreach ($_SESSION['field_definitions'][$t] as $f_c => $f_def)
        {
            if (!isset($f_def['err'])) return true;
            if (count($f_def['err']) > 0) return true;
        }
    }
    return false;
}
?>
