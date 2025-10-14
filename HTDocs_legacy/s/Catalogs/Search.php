<?php
function StartSearch($dbh, $n, $s_test, $s_radio, $portion, $settings_pad)
{
	if ($s_text == "") $_SESSION['mes'][$n][] = array("time"=>"", "text"=>Title(83)." ".Title(226), "status"=>"error", "now"=>true);
	else
	{
		$_SESSION['catalog_param'][$n]['view_search'] = true;
		$_SESSION['catalog_param'][$n]['search_text'] = $s_text;
		$_SESSION['catalog_param'][$n]['search_compare'] = $s_radio;
		if ($_SESSION['Catalog']['1']['name'] == "" && $n == "0" || $n == "1") $_SESSION['catalog_param'][$n]['search_where'] = GetWhere(MCF($_SESSION['Catalog'][$n]['value'], $_SESSION['match_case']), MCV($s_text, $_SESSION['match_case']), $_SESSION['catalog_param'][$n]['search_compare']);
		else $_SESSION['catalog_param'][$n]['search_where'] = WhereSet($dbh, $_SESSION['match_case'], $s_text, $_SESSION['catalog_param'][$n]['search_compare'], "f");
		$_SESSION['catalog_param'][$n]['found_count'] = GetTableLimit($dbh, "found", $n, $_SESSION['match_case'], "f");
		if ($_SESSION['catalog_param'][$n]['found_count'] > 0)
		{
			$first_value = GetFirstSearchValue($dbh, $n);
			if ($first_value['id'] != "")
			{
				if ($n == "0" && $_SESSION['Catalog']['1']['table'] != "" && $_SESSION['Catalog'][$n]['cat_type'] == 1) SetAllCollapse($dbh, "-", $_SESSION['cat_arr']['0'], $settings_pad);
				if (array_key_exists($first_value['id'], $_SESSION['cat_arr'][$n])) GetSearchPosOut($dbh, $n);
				else
				{
					$_SESSION['catalog_param'][$n]['start_pos'] = GetOffsetInTable($dbh, $n, $first_value['value']);
					$_SESSION['cat_arr'][$n] = GetCatalogPortion($dbh, $n, $_SESSION['catalog_param'][$n]['start_pos'], $_SESSION['portion'], $settings_pad);
				}
				TestCatalogs($dbh);
			}
		}
	}
}
function GetFirstSearchValue($dbh, $n)
{
	$first_value = array("id"=>"", "value"=>"");
	$single = ($_SESSION['Catalog']['1']['table'] == "" || $_SESSION['Catalog']['1']['table'] != "" && $n == "1");
	if ($single)
	{
		if ($_SESSION['catalog_param'][$n]['filter_text'] != "") $_SESSION['catalog_param'][$n]['search_where'] .= " AND ".$_SESSION['catalog_param'][$n]['filter_where'];
		$res = mysqli_query($dbh, "SELECT ".$_SESSION['Catalog'][$n]['id'].",".$_SESSION['Catalog'][$n]['value']." FROM ".$_SESSION['Catalog'][$n]['table']." WHERE ".$_SESSION['catalog_param'][$n]['search_where']." ORDER BY ".MCF($_SESSION['Catalog'][$n]['value'], $_SESSION['match_case'])." LIMIT 1");
		if (mysqli_errno($dbh) > 0) $_SESSION['mes'][$n][] = array("time"=>"", "text"=>mysqli_error($dbh), "status"=>"error", "now"=>true);
	}
	else 
	{
		$s_arr = array(MCV($_SESSION['catalog_param'][$n]['search_text'], $_SESSION['match_case']), $_SESSION['catalog_param'][$n]['search_compare']);
		$f_arr = array(MCV($_SESSION['catalog_param'][$n]['filter_text'], $_SESSION['match_case']), $_SESSION['catalog_param'][$n]['filter_compare']);
		$q_text = QueryTextForSet($dbh, $_SESSION['match_case'], 1, $f_arr, $s_arr, "f", "", "", "1");
		if (mysqli_errno($dbh) > 0) $_SESSION['mes'][$n][] = array("time"=>"", "text"=>mysqli_error($dbh), "status"=>"error", "now"=>true);
		if ($q_text == "") $res = false;
		else $res = mysqli_query($dbh, $q_text);
	}
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if ($single) $first_value = array("id"=>$row[0], "value"=>$row[1]);
			else $first_value = array("id"=>$row[0], "value"=>GetQueryValueSet(2, $row, GetMaxSetLevel($dbh, $_SESSION['Catalog']['0']['table'])));
		}
		mysqli_free_result($res);
	}
	return $first_value;
}
function CancelSearch($n)
{
	$_SESSION['catalog_param'][$n]['search_text'] = "";
	$_SESSION['catalog_param'][$n]['search_where'] = "";
	$_SESSION['catalog_param'][$n]['found_count'] = 0;
	$_SESSION['catalog_param'][$n]['view_search'] = false;
}
function SearchMoving($dbh, $dir, $n, $portion, $settings_pad)
{
	if (!IsChangesAndErrors($n, Title(480)." ", " ".Title(470))) //[] = array("time"=>"", "text"=>"To perform searching You must save changes on this page", "status"=>"warning", "log"=>false);
	{
		$_SESSION['catalog_param'][$n]['start_pos'] = GetOffsetInTable($dbh, $n, ($dir == "back") ? $_SESSION['catalog_param'][$n]['prev_search_out'] : $_SESSION['catalog_param'][$n]['next_search_out']);
		if ($dir == "back") $_SESSION['catalog_param'][$n]['start_pos'] = max($_SESSION['catalog_param'][$n]['start_pos'] - $_SESSION['portion'] + 1, 0);
		$_SESSION['cat_arr'][$n] = GetCatalogPortion($dbh, $n, $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion'], $settings_pad);
		TestCatalogs($dbh);
	}
}
function GetSearchPosOut($dbh, $n)
{
	$single = ($_SESSION['Catalog']['1']['table'] == "" || $_SESSION['Catalog']['1']['table'] != "" && $n == "1");
	$_SESSION['catalog_param'][$n]['prev_search_out'] = "";
	$_SESSION['catalog_param'][$n]['next_search_out'] = "";
	$p = GetLimitsPosOut($n);
	$q_prev = "";
	$q_next = "";
	if ($single)
	{
		$waw = ($_SESSION['catalog_param'][$n]['filter_text'] == "") ? "" : " AND ".$_SESSION['catalog_param'][$n]['filter_where'];
		if ($p[0] != "") $q_prev = "SELECT ".$_SESSION['Catalog'][$n]['value']." FROM ".$_SESSION['Catalog'][$n]['table']." WHERE ".WhereSingle($n, $_SESSION['match_case'], $p, $waw, "<")." ORDER BY ".MCF($_SESSION['Catalog'][$n]['value'], $_SESSION['match_case'])." DESC LIMIT 1";
		if ($p[1] != "") $q_next = "SELECT ".$_SESSION['Catalog'][$n]['value']." FROM ".$_SESSION['Catalog'][$n]['table']." WHERE ".WhereSingle($n, $_SESSION['match_case'], $p, $waw, ">")." ORDER BY ".MCF($_SESSION['Catalog'][$n]['value'], $_SESSION['match_case'])." LIMIT 1";
	}
	else
	{
		$f_a = array(MCV($_SESSION['catalog_param'][$n]['filter_text'], $_SESSION['match_case']), $_SESSION['catalog_param'][$n]['filter_compare']);
		$s_a = array(MCV($_SESSION['catalog_param'][$n]['search_text'], $_SESSION['match_case']), $_SESSION['catalog_param'][$n]['search_compare']);
		if ($p[0] != "") $q_prev = QueryTextForSet($dbh, $_SESSION['match_case'], -1, $f_a, $s_a, "f", "<", MCV($p[0], $_SESSION['match_case']), "1");
		if ($p[1] != "") $q_next = QueryTextForSet($dbh, $_SESSION['match_case'], 1, $f_a, $s_a, "f", ">", MCV($p[1], $_SESSION['match_case']), "1");
	}
	$_SESSION['catalog_param'][$n]['prev_search_out'] = GetValueOut($dbh, $single, $q_prev);
	$_SESSION['catalog_param'][$n]['next_search_out'] = GetValueOut($dbh, $single, $q_next);
}
function GetValueOut($dbh, $single, $rq)
{
	$v_out = "";
	if ($rq != "")
	{
		$res = mysqli_query($dbh, $rq);
		if ($res)
		{
			if ($row = mysqli_fetch_row($res))
            {
                $v_out = ($single) ? $row[0] : GetQueryValueSet(2, $row, GetMaxSetLevel($dbh, $_SESSION['Catalog']['0']['table']));
            }
			mysqli_free_result($res);
		}
	}
	return $v_out;
}
function GetLimitsPosOut($n)
{
	if (count($_SESSION['cat_arr'][$n]) == 0) return array();
	$lim_arr = reset($_SESSION['cat_arr'][$n]);
	if (!is_numeric(key($_SESSION['cat_arr'][$n]))) return array(); // new??
	$res_lim[] = end($lim_arr);
	$lim_arr = end($_SESSION['cat_arr'][$n]);
	if (!is_numeric(key($_SESSION['cat_arr'][$n]))) // new??
	{
		$lim_arr = prev($_SESSION['cat_arr'][$n]);
	 	$res_lim[] = end($lim_arr);
	}
	else $res_lim[] = end($_SESSION['cat_arr'][$n]);
	return $res_lim;
}
function WhereSingle($n, $mc, $p, $waw, $search_compare)
{
	return MCF($_SESSION['Catalog'][$n]['value'], $mc)." ".$search_compare."< '".MCV($p[0], $mc)."' AND ".$_SESSION['catalog_param'][$n]['search_where'].$waw;
}

?>
