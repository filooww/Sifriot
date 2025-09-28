<?php
function UpdateCatalogs($dbh)
{
	TestCatalogs($dbh);
	if ($_SESSION['Catalog']['1']['table'] != "") CatalogUpdating($dbh, "1");
	CatalogUpdating($dbh, "0");
	$_SESSION['mes']['c'][] = array("time"=>"", "text"=>CatalogMessage(($_SESSION['Catalog']['1']['name'] == "") ? Title(131) : Title(139)), "status"=>"statement", "now"=>true);
}
function CatalogUpdating($dbh, $n)
{
	if ($_SESSION['catalog_param'][$n]['filter_text'] != "" && !CatalogFilterTest($n, $_SESSION['catalog_param'][$n]['filter_text'], $_SESSION['catalog_param'][$n]['filter_where'])) ChangeCatParam($dbh, $n);
	$edit_values = UpdateRows($dbh, $n);
	if ($n == "0" && $_SESSION['Catalog']['1']['name'] != "") SetMaxLevel($dbh, $_SESSION['Catalog']['0']['table']);
	$_SESSION['cat_arr'][$n] = GetCatalogPortion($dbh, $n, $_SESSION['catalog_param'][$n]['start_pos'], $_SESSION['portion']);
	if (count($_SESSION['del_row'][$n]) == 0) PositionOutScreen($dbh, $n, $edit_values);
	$_SESSION['catalog_param'][$n]['total_count'] = GetTableLimit($dbh, "total", $n);
	if ($n == "0" && $_SESSION['Catalog'][$n]['cat_type'] == 1) $_SESSION['catalog_param'][$n]['tree_total_count'] = TreeCounts($_SESSION['catalog_param'][$n]['total_count']);
	$_SESSION['catalog_param'][$n]['filter_count'] = GetTableLimit($dbh, "filter", $n, $_SESSION['match_case'], "f");
	if ($_SESSION['catalog_param'][$n]['search_on']) $_SESSION['catalog_param'][$n]['found_count'] = GetTableLimit($dbh, "found", $n, $_SESSION['match_case'], "f");
}
function CatalogDeleting($dbh, $n)
{
	DeleteSelectedRows($dbh, $n);
	$_SESSION['cat_arr'][$n] = GetCatalogPortion($dbh, $n, $_SESSION['catalog_param'][$n]['start_pos'], $_SESSION['portion']);
	$_SESSION['catalog_param'][$n]['total_count'] = GetTableLimit($dbh, "total", $n);
	if ($n == "0" && $_SESSION['Catalog'][$n]['cat_type'] == 1) $_SESSION['catalog_param'][$n]['tree_total_count'] = TreeCounts($_SESSION['catalog_param'][$n]['total_count']);
	$_SESSION['catalog_param'][$n]['filter_count'] = GetTableLimit($dbh, "filter", $n, $_SESSION['match_case'], "f");
	if ($_SESSION['catalog_param'][$n]['search_on']) $_SESSION['catalog_param'][$n]['found_count'] = GetTableLimit($dbh, "found", $n, $_SESSION['match_case'], "f");
}
function DeleteSelectedRows($dbh, $n)
{
	foreach (array_keys($_SESSION['del_row'][$n]) as $k) 
	{
		mysqli_query($dbh, "DELETE FROM ".$_SESSION['Catalog'][$n]['table']." WHERE ".$_SESSION['Catalog'][$n]['id']." = ".$k);
		mysqli_query($dbh, "ALTER TABLE ".$_SESSION['Catalog'][$n]['table']." AUTO_INCREMENT = 1");
		unset($_SESSION['del_row'][$n][$k]);
		if (isset($_SESSION['cat_arr'][$n][$k])) unset($_SESSION['cat_arr'][$n][$k]);
	}
}
function ChangeCatParam($dbh, $n)
{
	$_SESSION['catalog_param'][$n]['filter_text'] = "";
	$_SESSION['catalog_param'][$n]['filter_where'] = "";
	$_SESSION['catalog_param'][$n]['filter_on'] = false;
	if ($_SESSION['catalog_param'][$n]['search_on']) $_SESSION['catalog_param'][$n]['found_count'] = GetTableLimit($dbh, "found", $n, $_SESSION['match_case'], "f");
}
function UpdateRows($dbh, $n)
{
	$edit_values = array();
	foreach ($_SESSION['cat_arr'][$n] as $c_key => $v_arr)
	{
		if ($v_arr[1] && !$v_arr[2] && end($v_arr) != "")
		{
			if ($_SESSION['Catalog']['1']['name'] == "" && $n == "0" || $n == "1") $db_t = GetCorrectEditedValue($_SESSION['Catalog'][$n]['illegal_symbols'], $v_arr[3]);
			else $db_t = $v_arr[3];
			$res_arr = SaveCatalogRow($dbh, $c_key, $db_t, $n);
			if ($res_arr['err'])
			{
				$_SESSION['cat_arr'][$n][$c_key][1] = false;
				$_SESSION['cat_arr'][$n][$c_key][2] = true;
			}
			if ($c_key != "") $edit_values[$c_key] = end($v_arr);
			if ($res_arr['n_code'] != "")
			{
				if ($_SESSION['Catalog']['1']['name'] == "" && $n == "0" || $n == "1") $_SESSION['cat_arr'][$n][$res_arr['n_code']] = array($v_arr[0], false, false, $v_arr[3]);
				else $_SESSION['cat_arr'][$n][$res_arr['n_code']] = array($v_arr[0], false, false, $v_arr[3], $v_arr[4]);
				if (isset($_SESSION['cat_arr'][$n]['N'])) unset($_SESSION['cat_arr'][$n]['N']); // new??
				$edit_values[$res_arr['n_code']] = end($v_arr);
			}
		}
		if (is_numeric($c_key) && $v_arr[1] && end($v_arr) != "") $_SESSION['cat_arr'][$n][$c_key][1] = false; // new??
	}
	//IsBranch
	return $edit_values;
}
function SaveCatalogRow($dbh, $cat_key, $db_t, $n)
{
	$res_arr = array("err"=>false, "n_code"=>"");
	if (!is_numeric($cat_key)) // new??
	{
	    mysqli_query($dbh, "ALTER TABLE ".$_SESSION['Catalog'][$n]['table']." AUTO_INCREMENT = 1");
		if ($n == 0 && $_SESSION['Catalog']['1']['table'] != "") mysqli_query($dbh, "INSERT INTO ".$_SESSION['Catalog'][$n]['table']." (".$_SESSION['Catalog'][$n]['value'].") VALUES ('".$db_t."')");
		else mysqli_query($dbh, "INSERT INTO ".$_SESSION['Catalog'][$n]['table']." (".$_SESSION['Catalog'][$n]['value'].", ".$_SESSION['Catalog'][$n]['value']."_low) VALUES ('".$db_t."','".MCV($db_t, false)."')");
		if (mysqli_errno($dbh) == 0) $res_arr['n_code'] = GetAutoIncrement($dbh);
		else
		{
			$_SESSION['mes'][$n][] = array("time"=>"", "text"=>mysqli_error($dbh), "status"=>"error");
			$res_arr['err'] = true;
		}
	}
	else
	{
		if ($n == 0 && $_SESSION['Catalog']['1']['table'] != "") mysqli_query($dbh, "UPDATE ".$_SESSION['Catalog'][$n]['table']." SET ".$_SESSION['Catalog'][$n]['value']." = '".$db_t."'  WHERE ".$_SESSION['Catalog'][$n]['id']." = ".$cat_key);
		else mysqli_query($dbh, "UPDATE ".$_SESSION['Catalog'][$n]['table']." SET ".$_SESSION['Catalog'][$n]['value']." = '".$db_t."', ".$_SESSION['Catalog'][$n]['value']."_low = '".MCV($db_t, false)."' WHERE ".$_SESSION['Catalog'][$n]['id']." = ".$cat_key);
		if (mysqli_errno($dbh) > 0)
		{
			$_SESSION['mes'][$n][] = array("time"=>"", "text"=>mysqli_error($dbh), "status"=>"error");
			$res_arr['err'] = true;
		}
	}
	return $res_arr;
}
function PositionOutScreen($dbh, $n, $edit_values)
{
	$arr_pos_out = array();
	foreach ($edit_values as $k => $v)
	{
		if (!array_key_exists($k, $_SESSION['cat_arr'][$n]))
		{
			$np = GetOffsetInTable($dbh, $n, $v);
			$arr_pos_out[$k] = array($np, $v);
		}
	}
	if (count($arr_pos_out) > 0) SetGotoMessages($arr_pos_out, $n);
}
function SetGotoMessages($arr_pos_out)
{
	$s = (count($arr_pos_out) == 1) ? " is" : "s are";
	$_SESSION['mes'][$n][] = array("time"=>"", "text"=>((count($arr_pos_out) == 1) ? Title(487) : Title(488)).": ", "status"=>"warning", "now"=>true);
	foreach ($arr_pos_out as $k => $arr_v) $_SESSION['mes'][$n][] = array("time"=>"", "text"=>" -- <b>".$arr_v[1]."</b> (<b>".$k."</b>) ==>", "status"=>"warning", "now"=>true, "goto"=>$arr_v[0]);
}
function SetMaxLevel($dbh)
{
	$max_level = 0;
	foreach ($_SESSION['cat_arr']['0'] as $v_arr)
	{
		$n = substr_count($v_arr[3], ",") + 1;
		if ($n > $max_level) $max_level = $n;
	}
	SaveMaxLevel($dbh, $max_level);
}

?>
