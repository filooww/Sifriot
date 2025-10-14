<?php

function TreeViewRow($cat_row)
{
	$arr_row = explode($_SESSION['Catalog']['0']['separator'], $cat_row);
	for ($i = 0; $i < count($arr_row) - 1; $i++) $arr_row[$i] = " ";
	return implode("-- ", $arr_row);
}
function GetTreeCatalogPortion($dbh, $start_pos, $d_portion)
{
	$cat_arr = array();
	$arr_collapse = ArrayCollapse();
	$total_portion = $d_portion + array_sum($arr_collapse['n_count']);
	$res = 	GetRequest($dbh, false, "0", $start_pos, $total_portion);
	if ($res)
	{
		$max_lev = GetMaxSetLevel($dbh, $_SESSION['Catalog']['0']['table']);
		while ($row = mysqli_fetch_row($res))
		{
			$fl_row = NodeCollapse((string)$row[0], $row[1], $arr_collapse['n_set']);
			if ($fl_row > -1) $cat_arr[(string)$row[0]] = array($fl_row == 1, false, false, $row[1], GetQueryValueSet(2, $row, $max_lev));
			if (count($cat_arr) == $d_portion) break;
		}
		mysqli_free_result($res);
	}
	return $cat_arr;
}

?>
