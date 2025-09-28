<?php
function CasePlus($dbh)
{
	if ($_SESSION['Catalog']['0']['cat_type'] == 1) $_SESSION['cat_arr']['0'] = GetCatalogPortion($dbh, "0", $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion']);
	else EnlargeScreenTable($dbh, "0");
	EnlargeScreenTable($dbh, "1");
	$_SESSION['portion']++;
	TestCatalogs($dbh);
}
function CaseMinus($dbh)
{
	$max_count = max(count($_SESSION['cat_arr']['0']), count($_SESSION['cat_arr']['1']));
	if ($max_count < $_SESSION['portion']) $_SESSION['portion']--;
	else
	{
		$k0 = ToReduce("0", $max_count);
		$k1 = ToReduce("1", $max_count);
		if ($k0 != "" && $k1 != "")
		{
			if ($k0 != "-") ReduceScreenTable("0", $k0);
			if ($k1 != "-") ReduceScreenTable("1", $k1);
			$_SESSION['portion']--;
		}
		else $_SESSION['mes']['c'][] = array("time"=>"", "text"=>IsChangesAndErrors("", Title(237)." ", " ".Title(470)), "status"=>"warning", "now"=>true);
	}
}
function CasePost($dbh)
{
	if (!is_numeric($_POST['cat_height_input'])) $_SESSION['mes']['c'][] = array("time"=>"", "text"=>"<font color='#FF0000'>".Title(177)." ".Title(77)."</font>", "status"=>"error", "now"=>true);
	elseif ((integer)$_POST['cat_height_input'] < 1) $_SESSION['mes']['c'][] = array("time"=>"", "text"=>"<font color='#FF0000'>".Title(178)."</font>", "status"=>"error", "now"=>true);
	elseif ((integer)$_POST['cat_height_input'] != $_SESSION['portion'])
	{
		$fl = ((integer)$_POST['cat_height_input'] < $_SESSION['portion']) ? !IsChangesAndErrors("", Title(237)." ", "") : true;
		if ($fl)
		{
			$_SESSION['portion'] = (integer)$_POST['cat_height_input'];
			$_SESSION['cat_arr']['0'] = GetCatalogPortion($dbh, "0", $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion']);
			if ($_SESSION['Catalog']['1']['name'] != "") $_SESSION['cat_arr']['1'] = GetCatalogPortion($dbh, "1", $_SESSION['catalog_param']['1']['start_pos'], $_SESSION['portion']);
			TestCatalogs($dbh);
		}
	}
}
function ChangeScreenHeight($dbh, $offs)
{
	switch ($offs)
	{
		case "plus"		: CasePlus($dbh); break;
		case "minus"	: if ($_SESSION['portion'] > 1) CaseMinus($dbh); break;
		case "button"	: if (isset($_POST['cat_height_input'])) CasePost($dbh); break;
	}
}
function ToReduce($n, $max_count)
{
	if (count($_SESSION['cat_arr'][$n]) < $max_count) return "-";
	if (isset($_SESSION['cat_arr'][$n]['N']) && $_SESSION['cat_arr'][$n]['N'][3] != "") return ""; // new??
	if (count($_SESSION['cat_arr'][$n]) == 1) return "";
	$arr = array_keys($_SESSION['cat_arr'][$n]);
	$nn = count($arr) - ((isset($_SESSION['cat_arr'][$n]['N'])) ? 2 : 1); // new??
	$k = $arr[$nn];
	if (!$_SESSION['cat_arr'][$n][$k][1] && !$_SESSION['cat_arr'][$n][$k][2]) return $k;
	else return "";
}
function ReduceScreenTable($n, $arr_key)
{
	if ($_SESSION['catalog_param'][$n]['search_on']) $_SESSION['catalog_param'][$n]['next_search_out'] = ScreenNextOutPos($_SESSION['cat_arr'][$n][$arr_key], $_SESSION['catalog_param'][$n]['search_text'], GetRadioType($_SESSION['catalog_param'][$n]['search_where']));
	unset($_SESSION['cat_arr'][$n][$arr_key]);
}
function EnlargeScreenTable($dbh, $n)
{
	$offset_last = (isset($_SESSION['cat_arr'][$n]['N'])) ? -1 : 0; // new??
	$next_arr = array();
	$next_arr = GetCatalogPortion($dbh, $n, $_SESSION['catalog_param'][$n]['start_pos'] + count($_SESSION['cat_arr'][$n]) + $offset_last, 1);
	if (count($next_arr) > 0) 
	{
		$next_row = reset($next_arr);
		$next_key = key($next_arr);
		if ($next_row[3] != "")
		{
			if (isset($_SESSION['cat_arr'][$n]['N'])) // new??
			{
				$new_row = array();
				foreach ($_SESSION['cat_arr'][$n]['N'] as $z) $new_row[] = $z; // new??
				unset($_SESSION['cat_arr'][$n]['N']); // new??
				$_SESSION['cat_arr'][$n][$next_key] = $next_row;
				$_SESSION['cat_arr'][$n]['N'] = $new_row; // new??
			}
			else $_SESSION['cat_arr'][$n][$next_key] = $next_row;
		}
	}
}
function ScreenNextOutPos($cat_row, $search_text, $s_type)
{
	$ss_sv = ($_SESSION['match_case']) ? end($cat_row) : strtolower(end($cat_row));
	$ss_s_text = ($_SESSION['match_case']) ? $search_text : strtolower($search_text);
	if ($s_type == 1)
	{
		if ($ss_sv == $ss_s_text) return end($cat_row);
		else return "";
	}
	else
	{
		$pos = strpos($ss_sv, $ss_s_text);
		if ($pos === false) return "";
		else
		{
			if ($s_type == 2 && $pos == 0 || $s_type == 3) return end($cat_row);
			else return "";
		}
	}
}
?>
