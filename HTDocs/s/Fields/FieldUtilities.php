<?php
/*
 0    f_key
 1    *# in DB table
 2    screen_order
 3    f_using
 4    f_name
 5    f_type
 6    f_unique
 7    f_default
 8    f_size
 9    f_interval
10    f_blank
11    f_s_mode
12    f_table
13    f_illegals
14    f_check
15    comm
16    f_filter_md
17    f_sort_sm
18    f_align
19    table_percent
20    *errors
*/
function GetFieldParameters($dbh, $mandatory_db_tables)
{
	$f_param = array();
	$flag_repeat = false;
    $res = mysqli_query($dbh, "SELECT * FROM field_config");
    if ($res)
    {
	    while ($row = mysqli_fetch_row($res))
	    {
            $test_err = FieldInitialTest($row, $mandatory_db_tables);
            $f_param[$row[0]][$row[1]] = array("f_key"=>($row[2] == 1), "ind_in_t"=>$test_err['ind'] + 1, "screen_order"=>$row[20], "load_order"=>$row[21], "f_using"=>$row[17], "f_name"=>$row[3], "f_type"=>$row[4], "f_unique"=>($row[8] == 1), "f_default"=>$row[12], "f_size"=>$row[5], "f_interval"=>($row[6] == 1), "f_blank"=>($row[7] == 1), "f_s_mode"=>($row[9] == 1), "f_table"=>$row[10], "f_illegals"=>$row[11], "f_check"=>($row[13] == 1), "comm"=>($row[14] == 1), "f_filter_md"=>$row[15], "f_sort_sm"=>$row[16], "f_align"=>$row[18], "table_percent"=>$row[19], "errors"=>$test_err['err'], "auto"=>0);
            if (!isset($_SESSION['all_field_list'][$row[0]][$row[1]])) $f_param[$row[0]][$row[1]]['auto'] = -1;
	    }
	    mysqli_free_result($res);
	}
    $flag_repeat = SetRepeatFieldValues($f_param);
    SetFieldAuto($mandatory_db_tables, 1, $f_param);
    SetFieldAuto($mandatory_db_tables, 2, $f_param);
	return $f_param;
}
function SetFieldAuto($mandatory_db_tables, $k_own, &$f_param)
{
    if (count($mandatory_db_tables[$k_own]) == 1 && isset($_SESSION['all_field_list'][$mandatory_db_tables[$k_own][0]]))
    {
        $i = 1;
        foreach (array_keys($_SESSION['all_field_list'][$mandatory_db_tables[$k_own][0]]) as $k_field)
        {
            if (!isset($f_param[$k_own][$k_field])) $f_param[$k_own][$k_field] = array("f_key"=>false, "ind_in_t"=>$i, "screen_order"=>0, "f_using"=>"", "f_name"=>$k_field, "f_type"=>0, "f_unique"=>false, "f_default"=>"", "f_size"=>0, "f_interval"=>false, "f_blank"=>false, "f_s_mode"=>false, "f_table"=>"", "f_illegals"=>"", "f_check"=>false, "comm"=>false, "f_filter_md"=>0, "f_sort_sm"=>0, "f_align"=>0, "table_percent"=>"", "errors"=>array(), "auto"=>1);
            $i++;
        }
    }
}
function FieldInitialTest($row, $mandatory_db_tables)
{
//echo "<br><br>test FieldInitialTest 00 ***"; print_r($row);
//echo "<br>test FieldInitialTest 01 ***"; print_r($mandatory_db_tables);
    $test_err = array("ind" => -1, "err" => array());
    if (count($mandatory_db_tables[1]) == 1 && count($mandatory_db_tables[2]) == 1)
    {
//echo "<br>test FieldInitialTest 02 ***".TestBool(isset($_SESSION['all_field_list'][$mandatory_db_tables[$row[0]][0]]))."===";
        if (isset($_SESSION['all_field_list'][$mandatory_db_tables[$row[0]][0]]))
        {
            if (isset($_SESSION['all_field_list'][$row[0]]))
            {
                $test_ind = array_search($row[1], $_SESSION['all_field_list'][$row[0]]);
                if ($test_ind !== false) $test_err['ind'] = $test_ind;
             }
        }
    }
/*
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
*/
    if ($row[0] > 2) $test_err['err'][] = Title(383); // invalid own table
    if (!isset($_SESSION['all_field_list'][$row[0]][$row[1]]) && !TestSysString($row[1])) $test_err['err'][] = Title(529); // incorrect field name
    if ($row[2] > 1) $test_err['err'][] = FTM(Title(602))." ".Title(342)." (<b>".$row[2]."</b>)"; // invalid key field
    if ($row[3] == "" && ($row[20] > 0 || $row[21] > 0)) $test_err['err'][] = Title(630); // invalid field name
    if (!isset($_SESSION['field_types'][$row[4]])) $test_err['err'][] = Title(596)." <b>".(string)$row[4]."/b>"; // invalid field type
    if ($row[6] > 1) $test_err['err'][] = Title(596)." <b>".(string)$row[6]."/b> (".Title(577). "<b>0</b>".Title(567)."<b>1</b>)";
    
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
        $arr_rep_name[1] = RepeatFieldValues($f_param[1], "f_name", "");
        $arr_rep_screen[1] = RepeatFieldValues($f_param[1], "screen_order", 0);
    }
    if (isset($f_param[2]))
    {
        $arr_rep_name[2] = RepeatFieldValues($f_param[2], "f_name", "");
        $arr_rep_screen[2] = RepeatFieldValues($f_param[2], "screen_order", 0);
    }
    if (count($arr_rep_name[1]) > 0) foreach ($arr_rep_name[1] as $rep_value => $rep_list) foreach ($rep_list as $rep_id => $rep_ids) $f_param[1][$rep_id][20][] = Tilte(490)." <b>".Title(347)." (".$rep_value.")</b> ".Title(587)." ".((count($rep_ids) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $rep_ids);
    if (count($arr_rep_name[2]) > 0) foreach ($arr_rep_name[2] as $rep_value => $rep_list) foreach ($rep_list as $rep_id => $rep_ids) $f_param[2][$rep_id][20][] = Tilte(490)." <b>".Title(347)." (".$rep_value.")</b> ".Title(587)." ".((count($rep_ids) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $rep_ids);
    if (count($arr_rep_screen[1]) > 0) foreach ($arr_rep_screen[1] as $rep_value => $rep_list) foreach ($rep_list as $rep_id => $rep_ids) $f_param[1][$rep_id][20][] = Tilte(490)." <b>".Title(333)." (".$rep_value.")</b> ".Title(587)." ".((count($rep_ids) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $rep_ids);
    if (count($arr_rep_screen[2]) > 0) foreach ($arr_rep_screen[2] as $rep_value => $rep_list) foreach ($rep_list as $rep_id => $rep_ids) $f_param[2][$rep_id][20][] = Tilte(490)." <b>".Title(333)." (".$rep_value.")</b> ".Title(587)." ".((count($rep_ids) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $rep_ids);
    return (count($arr_rep_name[1]) + count($arr_rep_name[2]) + count($arr_rep_screen[1]) + count($arr_rep_screen[2]) > 0);
}
function RepeatFieldValues($tested_arr, $par_name, $empty_value)
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
		case "table_name" : return (string)$table_code.strtolower($f_def['f_name']);
		case "in_table"	  : return (string)$table_code.sprintf("%'.02d", $f_def['ind_in_t']);
		case "in_screen"  : return (string)$table_code.sprintf("%'.02d", $f_def['screen_order']);
		default           : return "";
    }
}
function SortFieldDefinitions($sort_by) //??
{
	$arr_sort = array();
	foreach (array_keys($_SESSION['field_definitions']) as $t) foreach ($_SESSION['field_definitions'][$t] as $f_c => $f_def) $arr_sort[(string)$t."|".$f_c] = SortColumnValue($sort_by, $t, $f_c, $f_def);
    asort($arr_sort);
    $_SESSION['sorted_column'] = $sort_by;
    return $arr_sort;
}
function ViewFieldUsing($str_using) //??
{
	if ($str_using == "") return "";
	$arr = explode(",", $str_using);
	$arr_view = array();
	foreach ($arr as $z) if (isset($_SESSION['field_using'][$z])) $arr_view[] = $_SESSION['field_using'][$z];
	return implode(", ", $arr_view);
}
function GetAllFieldList($dbh, &$db_err, $db = -1) //??
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
function CatalogFieldDefinitions($table, $separators, &$t_def) //??
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
}
function IsFieldsErrors() //??
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
function GetAutoClass($v_auto)
{
    if ($v_auto == -1) return array(" class='field_no'", Title(213));
    if ($v_auto == 1) return array(" class='field_auto'", Title(258));
    return array("", "");
}
?>
