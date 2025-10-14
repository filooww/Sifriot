<?php
function RowToFilter($value_from, $pad_id, &$p_var, &$block, &$cat_flag)
{
	if ($value_from != "") $p_var[$pad_id]['filter']['text'] = $value_from;
	$block['pad_cat'] = false;
	$cat_flag = false;
}
function CatAction($dbh, $s_n, $sel_value, &$cur_value, $user_id = "")
{
	$_SESSION['cat_view'] = true;
	$_SESSION['block']['cat'] = true;
	ClearCatalogs();
	$_SESSION['cur_cat'] = CatalogCall($dbh, $_SESSION['conf'], $s_n, $_SESSION['block'], $_SESSION['set_pad'], $sel_value, $cur_value);
	TestCatalogs($dbh);
	if ($user_id > 0 && $s_n != $_SESSION['cur_cat']) ResetCatalogSettings();
}
function CatSelectCancel($k, &$item_row)
{
	$item_row[$k] = "";
	$item_row[$k."_code"] = 0;
}
function CatRestoreAction($dbh, $sel_value)
{
//	$emp = "";
//	$_SESSION['cur_cat'] = CatalogCall($dbh, $_SESSION['conf'], "", $_SESSION['block'], $_SESSION['set_pad'], $sel_value, $emp);
    $_SESSION['cat_arr'] = $_SESSION['cat_arr_init'];
	TestCatalogs($dbh);
	$_SESSION['mes']['c'][] = array("time"=>"", "text"=>Title(507), "status"=>"statement", "now"=>true);
}
function CatExitAction($dbh, $sel, &$cat_view, &$block, $cur_cat, &$p_var, &$cat_flag)
{
	if (!IsChangesAndErrors("", Title(484)." ", "")) //[] = array("time"=>"", "text"=>"To exit the ".ExitMessage()." You must correct all errors.", "status"=>"warning", "log"=>false);
	{
		$_SESSION['del_row'] = array("0"=>array(), "1"=>array());
		$_SESSION['mes']['0'] = array();
		$_SESSION['mes']['1'] = array();
		$_SESSION['mes']['c'] = array();
		$cat_view = false;
		$block['cat'] = false;
		if ($sel) RowToFilter("", $cur_cat, $p_var, $block, $cat_flag);
		elseif (is_numeric($_POST['cat_height_input']) && (integer)$_POST['cat_height_input'] > 0) SaveUserScreenPortion($dbh, $_POST['cat_height_input']);
	}
}
function CatBranchAction($dbh, $act_id, &$copy_paste)
{
	if (PasteBranch($dbh, $act_id, $copy_paste))
	{
		$_SESSION['catalog_param']['0']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['0']['total_count']);
		TestCatalogs($dbh);
	}
}
function CatNoDelAction($dbh, &$block)
{
	foreach (array_keys($_SESSION['del_row']['0']) as $k) OldValuesToCat($k, "0");
	foreach (array_keys($_SESSION['del_row']['1']) as $k) OldValuesToCat($k, "1");
	$_SESSION['del_row'] = array("0"=>array(), "1"=>array());
	TestCatalogs($dbh);
	$block['cat_del'] = false;
}
function CatYesDelAction($dbh)
{
	$_SESSION['block']['cat_del'] = false;
	CatalogDeleting($dbh, "0");
	CatalogDeleting($dbh, "1");
	TestCatalogs($dbh, false, true, "saved");
}
function CatLineSelectAction($dbh, $ins_code)
{
	RowToFilter(end($_SESSION['cat_arr']['0'][$ins_code]), $_SESSION['cur_cat'], $_SESSION['PR']['var'], $_SESSION['block'], $_SESSION['cat_flag']);
	if (!IsChangesAndErrors("", "", "", false))
	{
		$_SESSION['cat_view'] = false;
		$_SESSION['block']['cat'] = false;
	}
}
function FFAction($dbh, $ff_on, $mode_checks, $default_check, $n, $mes_text, $settings_pad)
{
	if ($n == "0" && $_SESSION['Catalog'][$n]['cat_type'] == 1 && isset($_SESSION['collapse'][$_SESSION['Catalog'][$n]['table']]))
	{
		$_SESSION['mes']['0'][] = array("time"=>"", "text"=>Title(109)." ".$mes_text, "status"=>"warning", "now"=>true);
		$_SESSION['catalog_param'][$n][$ff_on] = false;
	}
	else
	{
		$fl = true;
		if ($_SESSION['catalog_param'][$n][$ff_on])
		{
			if ($ff_on == "filter_on") $fl = CancelFilter($dbh, $n, $settings_pad);
			else CancelSearch($n);
		}
		if ($fl) $_SESSION['catalog_param'][$n][$ff_on] = !$_SESSION['catalog_param'][$n][$ff_on];
	}
}
function CatalogActions($dbh, $n, $aA, $sel_value, &$cur_value, &$Mes, $sel = false, $user_id = "", $ign = false)
{
	$sw_break = true;
	switch ($aA['action'])
	{
		case "cat"					: CatAction($dbh, $aA['s_n'], $sel_value, $cur_value, $Mes, $user_id); break;
		case "cat_cancel"			: CatSelectCancel($aA['s_n'], $_SESSION['item_row']['e']); break;
		case "cat_restore"			: CatRestoreAction($dbh, $Mes, $sel_value); break;
		case "cat_exit"				: CatExitAction($dbh, $sel, $_SESSION['cat_view'], $_SESSION['block'], $_SESSION['cur_cat'], $_SESSION['PR']['var'], $_SESSION['cat_flag'], $Mes); break;
		case "cat_save"				: UpdateCatalogs($dbh); break;
		case "cat_test"				: TestCatalogs($dbh, false, true, "tested"); break;
		case "cat_tree"				: $_SESSION['cat_tree'] = !$_SESSION['cat_tree']; break;
		case "cat_tree_col"			: SetAllCollapse($dbh, "+", $_SESSION['cat_arr']['0'], $_SESSION['set_pad']); break;
		case "cat_tree_ext"			: SetAllCollapse($dbh, "-", $_SESSION['cat_arr']['0'], $_SESSION['set_pad']); break;
		case "cat_scr_height"		: ChangeScreenHeight($dbh, $aA['scr_corr'], $Mes); break;
		case "cat_filter_call"		: FFAction($dbh, "filter_on", "filter_mode_checks", "filter_anywhere", $n, Title(110), $_SESSION['set_pad']); break;
		case "cat_search"			: FFAction($dbh, "search_on", "search_mode_checks", "search_anywhere", $n, Title(111), $_SESSION['set_pad']); break;
		case "apply_filter"			: ApplyFilter($dbh, $ak[1], $_POST["filter_text|".$ak[1]], $_POST["filter_mode|".$ak[1]], $_SESSION['set_pad']); break;
		case "start_search"			: StartSearch($dbh, $ak[1], $_POST["search_text|".$ak[1]], $_POST["search_mode|".$ak[1]], $_SESSION['set_pad']); break;
		case "cat_search_move"		: SearchMoving($dbh, $aA['dir'], $n, $_SESSION['set_pad']); break;
		case "cat_search_hide"		: $_SESSION['catalog_param'][$n]['view_search'] = !$_SESSION['catalog_param'][$n]['view_search']; break;
		case "cat_navigation"		: NavigationActions($dbh, $aA['nv'], $n, $_SESSION['set_pad']); break;
		case "line_mark"			: SetOnOffButton($_SESSION['cat_mark'], $aA['code']); break;
		case "line_insert"			: InsMarked($dbh, $_SESSION['cat_mark'], $aA['code'], $aA['inserted']); break;
		case "copy_branch"			: $_SESSION['copy_paste'] = CopyBranch($aA['act_id'], $aA['act_ind'], $_SESSION['cat_arr']['0'][$aA['act_id']], $_SESSION['Catalog']['0']['separator']); break;
		
		case "paste_branch"			: CatBranchAction($dbh, $aA['act_id'], $_SESSION['copy_paste']); break;
		
		case "set_vis"				: SetOnOffButton($_SESSION['cat_vis'], $aA['code']); break;
		case "collapse"				: SetCollapse($dbh, $aA['code'], $aA['set'], $aA['value'], $_SESSION['set_pad']); break;
		case "del_from_set"			: DelFromSetList($dbh, $_SESSION['cat_vis'], $aA['code']); break;
		case "del_hier_set"			: DelHierarchic($dbh, $_SESSION['Catalog'], $aA['code']); break;
		case "cat_fill"				: CatFill($dbh); break;
		case "go_to_pos"			: CatGoToPosAction($dbh, $aA); break;
		case "answer_no"			: CatNoDelAction($dbh, $_SESSION['block']); break;
		case "answer_yes"			: CatYesDelAction($dbh, $_SESSION['mes']); break;
		case "line_select"			: if (!$ign) CatLineSelectAction($dbh, $sel, $aA, $user_id); break;
		default						: $sw_break = false;
	}
	if ($sw_break && $user_id != "") SaveCatalogUserSettings($dbh, $user_id);
	return $sw_break;
}
function ParseCatalogButtons($dbh)
{
	foreach ($_POST as $str_key => $str_v)
	{
		$sw_break = true;
		$arr_key = explode("|", $str_key);
		$s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
		switch ($s_k)
		{
			case "cat_rest"				: return array("action"=>"catalog_restore");
			case "selectPublishing"		: return array("action"=>"cat", "s_n"=>"Publishing");
			case "cancelPublishing"		: return array("action"=>"cat_cancel", "s_n"=>"Publishing");
			case "selectSeries"			: return array("action"=>"cat", "s_n"=>"Series");
			case "cancelseries"			: return array("action"=>"cat_cancel", "s_n"=>"Series");
			case "selectIssueType"		: return array("action"=>"cat", "s_n"=>"IssueType");
			case "cancelIssueType"		: return array("action"=>"cat_cancel", "s_n"=>"IssueType");
			case "selectMagazine"		: return array("action"=>"cat", "s_n"=>"Magazine");
			case "cancelMagazine"		: return array("action"=>"cat_cancel", "s_n"=>"Magazine");
			case "selectAuthors"		: return array("action"=>"cat", "s_n"=>"Authors");
			case "cancelAuthors"		: return array("action"=>"cat_cancel", "s_n"=>"Authors");
			case "selectThemes"			: return array("action"=>"cat", "s_n"=>"Themes");
			case "cancelThemes"			: return array("action"=>"cat_cancel", "s_n"=>"Themes");
			case "cat_exit"				: return array("action"=>"cat_exit", "change"=>"");
			case "cat_save"				: return array("action"=>"cat_save", "change"=>"");
			case "cat_test"				: return array("action"=>"cat_test");
			case "cat_tree"				: return array("action"=>"cat_tree");
			case "cat_tree_collapse"	: return array("action"=>"cat_tree_col");
			case "cat_tree_extend"		: return array("action"=>"cat_tree_ext");
			case "cat_height_minus"		: return array("action"=>"cat_scr_height", "scr_corr"=>-1);
			case "cat_height_plus"		: return array("action"=>"cat_scr_height", "scr_corr"=>1);
			case "cat_filter0"			: return array("action"=>"cat_filter_call", "cat_num"=>"0");
			case "cat_filter1"			: return array("action"=>"cat_filter_call", "cat_num"=>"1");
			case "cat_search0"			: return array("action"=>"cat_search", "cat_num"=>"0");
			case "cat_search1"			: return array("action"=>"cat_search", "cat_num"=>"1");
			case "cat_search_prev0"		: return array("action"=>"cat_search_move", "cat_num"=>"0", "dir"=>"back");
			case "cat_search_prev1"		: return array("action"=>"cat_search_move", "cat_num"=>"1", "dir"=>"back");
			case "cat_search_next0"		: return array("action"=>"cat_search_move", "cat_num"=>"0", "dir"=>"forward");
			case "cat_search_next1"		: return array("action"=>"cat_search_move", "cat_num"=>"1", "dir"=>"forward");
			case "cat_search_hide0"		: return array("action"=>"cat_search_hide", "cat_num"=>"0");
			case "cat_search_hide1"		: return array("action"=>"cat_search_hide", "cat_num"=>"1");
			case "filter_start0"		: return array("action"=>"apply_filter", "cat_num"=>"0", "text"=>$_POST['filter_text0'], "radio"=>"f_mode");
			case "filter_start1"		: return array("action"=>"apply_filter", "cat_num"=>"1", "text"=>$_POST['filter_text1'], "radio"=>"f_mode");
			case "search_start0"		: return array("action"=>"start_search", "cat_num"=>"0", "text"=>$_POST['search_text0'], "radio"=>"s_mode");
			case "search_start1"		: return array("action"=>"start_search", "cat_num"=>"1", "text"=>$_POST['search_text1'], "radio"=>"s_mode");
			case "cat_beg0"				: return array("action"=>"cat_navigation", "nv"=>"cat_beg", "cat_num"=>"0");
			case "cat_beg1"				: return array("action"=>"cat_navigation", "nv"=>"cat_beg", "cat_num"=>"1");
			case "cat_pg_up0"			: return array("action"=>"cat_navigation", "nv"=>"cat_pg_up", "cat_num"=>"0");
			case "cat_pg_up1"			: return array("action"=>"cat_navigation", "nv"=>"cat_pg_up", "cat_num"=>"1");
			case "cat_ln_up0"			: return array("action"=>"cat_navigation", "nv"=>"cat_ln_up", "cat_num"=>"0");
			case "cat_ln_up1"			: return array("action"=>"cat_navigation", "nv"=>"cat_ln_up", "cat_num"=>"1");
			case "cat_ln_dn0"			: return array("action"=>"cat_navigation", "nv"=>"cat_ln_dn", "cat_num"=>"0");
			case "cat_ln_dn1"			: return array("action"=>"cat_navigation", "nv"=>"cat_ln_dn", "cat_num"=>"1");
			case "cat_pg_dn0"			: return array("action"=>"cat_navigation", "nv"=>"cat_pg_dn", "cat_num"=>"0");
			case "cat_pg_dn1"			: return array("action"=>"cat_navigation", "nv"=>"cat_pg_dn", "cat_num"=>"1");
			case "cat_end0"				: return array("action"=>"cat_navigation", "nv"=>"cat_end", "cat_num"=>"0");
			case "cat_end1"				: return array("action"=>"cat_navigation", "nv"=>"cat_end", "cat_num"=>"1");
			case "cat_code0"			: return array("action"=>"line_select", "code"=>(integer)$arr_key[1]);
			case "cat_code1"			: return (!is_numeric($arr_key[1])) ? array() : array("action"=>"line_insert", "code"=>$arr_key[1], "inserted"=>$_POST['cat_text1|'.$arr_key[1]]); // new??
			case "cat_mark0"			: return array("action"=>"line_mark", "code"=>$arr_key[1]);
			case "set_vis0"				: return array("action"=>"set_vis", "code"=>$arr_key[1]);
			case "collapse0"			: return array("action"=>"collapse", "code"=>$arr_key[1], "set"=>$arr_key[2], "value"=>$_POST['collapse0|'.$arr_key[1].'|'.$arr_key[2]]);
			case "cat_copy0"			: return array("action"=>"copy_branch", "act_id"=>$arr_key[1], "act_ind"=>$arr_key[2]);
			case "cat_paste0"			: return array("action"=>"paste_branch", "act_id"=>$arr_key[1]);
			case "cat_paste_root"		: return array("action"=>"paste_branch", "act_id"=>"");
			
			case "del_hier_set0"		: return array("action"=>"del_hier_set", "code"=>$arr_key[1]);

			case "del_from_set0"		: return (!is_numeric($arr_key[1])) ? array() : array("action"=>"del_from_set", "code"=>$arr_key[1]); // new??
			case "cat_fill"				: return array("action"=>"cat_fill", "change"=>"", "table"=>$arr_key[1]);
			case "topos"				: return array("action"=>"go_to_pos", "pos"=>$arr_key[1], "cat_num"=>$arr_key[2]);
			case "answer_yes"			: return array("action"=>"answer_yes", "change"=>"");
			case "answer_no"			: return array("action"=>"answer_no", "change"=>"");
			default						: $sw_break = false;
		}
		if ($sw_break) break;
	}
	return array();
}
function ToTest($aA)
{
	if (count($aA) == 0 || !isset($aA['action'])) return true;
	switch ($aA['action'])
	{
		case "to_catalog"		: return false;
		case "cat_save"			: return false;
		case "del_hier_set"		: return false;
		case "cat"				: return false;
		case "cat_restore"		: return false;
		default					: return true;
	}
}
function ToSave($aA)
{
	if (count($aA) == 0 || !isset($aA['action'])) return false;
	switch ($aA['action'])
	{
		case "cat_exit"		: return true;
		case "cat_save"		: return true;
		case "answer_yes"	: return true;
		default				: return false;
	}
}
function ToButtonOff($aA)
{
	switch ($aA['action'])
	{
		case "cat_save"			: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "catalog_restore"	: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "cat_tree_col"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "cat_tree_ext"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "cat_filter_call"	: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "cat_search"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "cat_search_move"	: ButtonOff("cat_mark"); break;
		case "apply_filter"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "start_search"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "line_mark"		: ButtonOff("cat_vis"); break;
		case "collapse"			: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "copy_branch"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "paste_branch"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "cat_fill"			: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "go_to_pos"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "answer_yes"		: ButtonOff("cat_vis"); ButtonOff("cat_mark"); break;
		case "answer_no"		:
	}
}
function ToCopyBranch($aA)
{
	if (count($aA) > 0 && isset($aA['action']))
	{
		switch ($aA['action'])
		{
			case "paste_branch"			: return false;
		}
	}
	else return true;
}
function CatalogLangChoice($dbh_sys, $dbh, &$sw_break)
{
	if (AfterLangChoice($dbh_sys, "user_lang_s", "user_lang", $sw_break))
	{
		$_SESSION['compare_mode'] = GetSpecialTexts($dbh_sys, "compare_mode");
		TestCatalogs($dbh);
	}
}

?>
