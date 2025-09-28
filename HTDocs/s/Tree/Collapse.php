<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");

function GetCollapseCount($dbh, $collapse_set)
{
	$col_count = 0;
	$res_count = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$_SESSION['Catalog']['0']['table']." WHERE ".$_SESSION['Catalog']['0']['value']." LIKE '".$collapse_set.",%'");
	if ($res_count)
	{
		if ($row_count = mysqli_fetch_row($res_count))
        {
            $col_count = $row_count[0];
        }
		mysqli_free_result($res_count);
	}
	return $col_count;
}
function ArrayCollapse()
{
	$arr_collapse = array("n_set"=>array(), "n_count"=>array());
	foreach ($_SESSION['collapse'][$_SESSION['Catalog']['0']['table']] as $k => $v)
	{
		$arr_collapse['n_set'][$k] = $v['set'];
		$arr_collapse['n_count'][$k] = $v['count'];
	}
	return $arr_collapse;
}
function SingleNode($dbh, $value)
{
	$y = true;
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$_SESSION['Catalog']['0']['table']." WHERE ".$_SESSION['Catalog']['0']['value']." LIKE '".$value.",%'");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
        {
            $y = ($row[0] == 0);
        }
		mysqli_free_result($res);
	}
	return $y;
}
function SetCollapse($dbh, $collapse_id, $collapse_set, $collapse_value, $settings_pad)
{
	$arr_err = array();	
	foreach ($_SESSION['cat_arr']['0'] as $k => $v) if (($v[1] || $v[2])) $arr_err[] = $k;
	if (count($arr_err) > 0) $_SESSION['mes']['0'][] = array("time"=>"", "text"=>IsChangesAndErrors("0", Title(486)." ", " ".Title(470)), "status"=>"error", "now"=>true);
	else
	{
		$str_where = " WHERE ".$_SESSION['Catalog']['0']['value']." LIKE '".$collapse_set.",%' AND INSTR(SUBSTR(".$_SESSION['Catalog']['0']['value'].", LENGTH('".$collapse_set."') + 2), ',') = 0";
		if ($collapse_value == "-")
		{
			if (!SingleNode($dbh, $collapse_set))
			{
				$res = mysqli_query($dbh, "SELECT ".$_SESSION['Catalog']['0']['id']." FROM ".$_SESSION['Catalog']['0']['table'].$str_where);
				if ($res)
				{
					while ($row = mysqli_fetch_row($res))
                    {
                        unset($_SESSION['collapse'][$_SESSION['Catalog']['0']['table']][(string)$row[0]]);
                    }
					mysqli_free_result($res);
				}
				$_SESSION['collapse'][$_SESSION['Catalog']['0']['table']][(string)$collapse_id] = array("set"=>$collapse_set, "count"=>GetCollapseCount($dbh, $collapse_set));
				$_SESSION['catalog_param']['0']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['0']['total_count']);
				$_SESSION['cat_arr']['0'] = GetCatalogPortion($dbh, "0", $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion'], $settings_pad);
			}
		}
		else
		{
			unset($_SESSION['collapse'][$_SESSION['Catalog']['0']['table']][(string)$collapse_id]);
			$res = mysqli_query($dbh, "SELECT ".$_SESSION['Catalog']['0']['id'].", ".$_SESSION['Catalog']['0']['value']." FROM ".$_SESSION['Catalog']['0']['table'].$str_where);
			if ($res)
			{
				while ($row = mysqli_fetch_row($res))
                {
                    if ($son_count > 0) $_SESSION['collapse'][$_SESSION['Catalog']['0']['table']][(string)$row[0]] = array("set"=>$row[1], "count"=>GetCollapseCount($dbh, $row[1]));
                }
				mysqli_free_result($res);
				$_SESSION['catalog_param']['0']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['0']['total_count']);
			}
			$_SESSION['cat_arr']['0'] = GetCatalogPortion($dbh, "0", $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion'], $settings_pad);
		}
	}
}
function SetAllCollapse($dbh, $tree_mode, &$cat_arr, $settings_pad)
{
	$arr_err = array();
	foreach ($cat_arr as $k => $v) if (($v[1] || $v[2])) $arr_err[] = "<b>".$k."</b>";
	if (count($arr_err) > 0) $_SESSION['mes']['0'][] = array("time"=>"", "text"=>IsChangesAndErrors("0", Title(486)." ", " ".Title(470)), "status"=>"error", "now"=>true);
	else
	{
		unset($_SESSION['collapse'][$_SESSION['Catalog']['0']['table']]);
		if ($tree_mode == "+" || $tree_mode == "")
		{
			$res = mysqli_query($dbh, "SELECT ".$_SESSION['Catalog']['0']['id'].", ".$_SESSION['Catalog']['0']['value']." FROM ".$_SESSION['Catalog']['0']['table']." WHERE ".$_SESSION['Catalog']['0']['value']." NOT LIKE '%,%'");
			if ($res)
			{
				while ($row = mysqli_fetch_row($res))
                {
                    $_SESSION['collapse'][$_SESSION['Catalog']['0']['table']][(string)$row[0]] = array("set"=>$row[1], "count"=>GetCollapseCount($dbh, $row[1]));
                }
				mysqli_free_result($res);
				$_SESSION['catalog_param']['0']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['0']['total_count']);
			}
		}
		else $_SESSION['catalog_param']['0']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['0']['total_count']);
		if ($tree_mode != "")
		{
			$_SESSION['catalog_param']['0']['start_pos'] = 0;
			$cat_arr = GetCatalogPortion($dbh, "0", $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion'], $settings_pad);
		}
	}
}
function NodeCollapse($node_id, $ref_ids, $arr_collapse)
{
	if (in_array($node_id, array_keys($arr_collapse))) return 1;
	$arr = explode(",", $ref_ids);
	for ($i = 0; $i < count($arr) - 1; $i++) if (in_array(implode(",", array_slice($arr, 0, $i + 1)), $arr_collapse)) return -1;
	return 0;
}
function TreeCounts($total_count)
{
	$arr_collapse = ArrayCollapse();
	return $total_count - array_sum($arr_collapse['n_count']);
}
function GetSonsArray($cat_arr, $v_set)
{
	$sons_inner = array();
	foreach ($cat_arr as $k => $v) if (strpos($v[3], $v_set.",") !== false && strpos($v[3], $v_set.",") == 0) $sons_inner[$k] = array($v[3], $v[4]);	
	return $sons_inner;
}
function GetNodeSons($dbh, $sons_inner)
{
	$sons_outer = array();
	$screen_sons = array_keys($sons_inner);
	$max_lev = GetMaxSetLevel($dbh, $_SESSION['Catalog']['0']['table']);
	$q_text = VF($max_lev, $_SESSION['Catalog']['0']['table'], $_SESSION['Catalog']['0']['id'], $_SESSION['Catalog']['0']['value'], $_SESSION['Catalog']['1']['table'],  $_SESSION['Catalog']['1']['id'], $_SESSION['Catalog']['1']['value'], "", chr(34).chr(34), "f");
	$q_text = "SELECT * FROM (SELECT ".$_SESSION['Catalog']['0']['id'].", ".$_SESSION['Catalog']['0']['value'].", ".$q_text." FROM ".$_SESSION['Catalog']['0']['table'].") AS T WHERE ".$_SESSION['Catalog']['0']['value']." LIKE '".$_SESSION['Catalog']['0']['value'].",%'";
	if (count($screen_sons) > 0) $q_text .= " AND ".$_SESSION['Catalog']['0']['id']." NOT IN (".implode(",", $screen_sons).")";
	$res = mysqli_query($dbh, $q_text);
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
        {
            $sons_outer[(string)$row[0]] = array($row[1], GetQueryValueSet(2, $row, $max_lev));
        }
		mysqli_free_result($res);
	}
	return $sons_outer;
}

?>
