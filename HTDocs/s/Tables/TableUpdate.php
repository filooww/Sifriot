<?php
function TableValuesString($table_name, $table_params)
{
	$arr_values = array();
	$arr_values[] = "'".$table_name."'";
	$arr_values[] = (string)$table_params['use_type'];
	$arr_values[] = "'".$table_params['illegals']."'";
    $arr_values[] = (string)$table_params['max_level'];
    $arr_values[] = "'".$table_params['separators']."'";
    $arr_values[] = "'".$table_params['catalog_id']."'";
    $arr_values[] = "'".$table_params['catalog_value']."'";
	$arr_values[] = (string)$table_params['group_type'];
	$arr_values[] = "'".$table_params['second_catalog']."'";
	$arr_values[] = "'".$table_params['table_title']."'";
	return implode(",", $arr_values);
}
function RewriteDefinition($dbh, &$Mes)
{
	$arr_insert = array();
	$_SESSION['single_catalogs'] = array();
	foreach ($_SESSION['table_definitions'] as $table_name => $table_params)
	{
		if ($table_params['separators'] != "" && $table_params['second_catalog'] != "") InsertToIllegals($table_params['separators'], $table_params['second_catalog']);
		$arr_insert[] = "(".TableValuesString($table_name, $table_params).")";
        if ($table_params['use_type'] == 3 && $table_params['separators'] == "") $_SESSION['single_catalogs'][] = $table_name;
        if (!in_array($table_name, array_keys($_SESSION['all_field_list']))) $_SESSION['table_definitions'][$table_name]['tab_err'][] = "<font color='#FF0000'>".Title(608)."</font>";
	}
    foreach (array_keys($_SESSION['all_field_list']) as $table_name) if (!isset($_SESSION['table_definitions'][$table_name])) $_SESSION['table_definitions'][$table_name] = array("use_type"=>0, "illegals"=>'', "separators"=>'', "catalog_id"=>"", "catalog_value"=>"", "group_type"=>0, "second_catalog"=>"", "table_title"=>$table_name, "tab_err"=>array(FTM(Title(365))." <b>".$table_name."</b> - ".Title(409)." ".Title(705)));
	if (count($arr_insert) > 0)
    {
        mysqli_query($dbh, "DELETE FROM table_definitions");
        mysqli_query($dbh, "ALTER TABLE table_definitions AUTO_INCREMENT = 1");
        mysqli_query($dbh, "INSERT INTO table_definitions VALUES ".implode(",", $arr_insert));
	    $Mes[] = "<b><font color='#0000FF'>".Title(193)."</font></b>";
    }
}

function AutoIncrementAll($dbh)
{
	$res = mysqli_query($dbh, "SHOW TABLES");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
        {
            mysqli_query($dbh, "ALTER TABLE ".$row[0]." AUTO_INCREMENT = 1");
        }
		mysqli_free_result($res);
	}
}
function InsertToIllegals($separators, $second_catalog)
{
	if ($_SESSION['table_definitions'][$second_catalog]['illegals'] == "") $_SESSION['table_definitions'][$second_catalog]['illegals'] = $separators;
	elseif (strpos($_SESSION['table_definitions'][$second_catalog]['illegals'], $separators) === false) $_SESSION['table_definitions'][$second_catalog]['illegals'] .= $_SESSION['char_group'].$separators;
}

?>
