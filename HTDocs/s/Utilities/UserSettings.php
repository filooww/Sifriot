<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/ItemRequest.php");

function InitUserSettings($dbh, $u_id)
{
	$res = mysqli_query($dbh, "SELECT user_language, user_screen_portion, user_match_case, user_hide_list, cat_portion FROM userlist WHERE id_user = ".$u_id." LIMIT 1");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			$arr_data = InitReqUserSettings($dbh, $row);
			mysqli_query($dbh, "UPDATE userlist SET ".implode(",", $arr_data)." WHERE id_user = ".$u_id);
		}
		else
		{
			$arr_data = InitReqUserSettings($dbh, array());
			mysqli_query($dbh, "INSERT INTO userlist VALUES (".$u_id.",".implode(",", $arr_fields).")");
		}
		mysqli_free_result($res);
	}
	CreateFieldsSettings($dbh, $u_id);
}
function InitReqUserSettings($dbh, $row)
{
	$arr_data = array();
	$arr_data[] = (count($row) == 0) ? "1" : "user_active = 1";
	$arr_data[] = (count($row) == 0) ? "NOW()" : "work_start = NOW()";
	$arr_data[] = (count($row) == 0) ? "1" : "visit_count = ".(string)($row[3] + 1);
	if (count($row) == 0)
	{
		$arr_data[] = (string)$_SESSION['cur_lang'][0];
		$arr_data[] = (string)$_SESSION['conf']['portion_item'];
		$arr_data[] = (($_SESSION['m_case']) ? "1" : "0");
		$arr_data[] = (($_SESSION['conf']['hide_list']) ? "1" : "0");
	}
	else
	{
		if ($row[0] == 0) $arr_data[] = "user_language = ".(string)$_SESSION['cur_lang'][0];
		if ($row[1] == 0) $arr_data[] = "user_screen_portion = ".(string)$_SESSION['conf']['portion_item'];
		if ($row[2] == -1) $arr_data[] = "user_match_case = ".(($_SESSION['m_case']) ? "1" : "0");
		if ($row[3] == -1) $arr_data[] = "user_hide_list = ".(($_SESSION['conf']['hide_list']) ? "1" : "0");
	}
	$arr_data[] = (count($row) == 0) ? "0" : "start_pos = 0";
	$arr_data[] = (count($row) == 0) ? "0" : "settings_pad = 0";
	$arr_data[] = (count($row) == 0) ? "0" : "p_count_filter = 0";
	$arr_data[] = ((count($row) > 0) ? "p_code = " : "").(string)GetInitItemCode($dbh, $_SESSION['db_info']['t_main'], $_SESSION['main_params']['const']);
	$arr_data[] = (count($row) == 0) ? "0" : "view_on = 0";
	$arr_data[] = (count($row) == 0) ? "0" : "view_multi_columns = 0";
	$arr_data[] = (count($row) == 0) ? "''" : "cat_search_text = ''";
	$arr_data[] = (count($row) == 0) ? "1" : "cat_search_mode_checks = 1";
	$arr_data[] = (count($row) == 0) ? "''" : "cat_search_where = ''";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_search_on = 0";
	$arr_data[] = (count($row) == 0) ? "''" : "cat_filter_text = ''";
	$arr_data[] = (count($row) == 0) ? "1" : "cat_filter_mode_checks = 1";
	$arr_data[] = (count($row) == 0) ? "''" : "cat_filter_where = ''";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_filter_on = 0";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_filter_count = 0";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_found_count = 0";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_start_pos = 0";
	$arr_data[] = (count($row) == 0) ? "''" : "cat_prev_search_out = ''";
	$arr_data[] = (count($row) == 0) ? "''" : "cat_next_search_out = ''";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_view_search = 0";
	if (count($row) == 0) $arr_data[] = (string)$_SESSION['portion'];
	elseif ($row[4]) $arr_data[] = "cat_portion = ".(string)$_SESSION['portion'];
	$arr_data[] = (count($row) == 0) ? "0" : "cat_tree = 0";
	$arr_data[] = (count($row) == 0) ? "''" : "cat_current = ''";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_block = 0";
	$arr_data[] = (count($row) == 0) ? "''" : "cat_sel_current = ''";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_flag = 0";
	$arr_data[] = (count($row) == 0) ? "0" : "cat_tree_count = 0";
	return $arr_data;
}
function CreateFieldsSettings($dbh, $u_id)
{
	foreach (array_keys($_SESSION['main_params']['var']) as $k) $arr_value[] = "(".$u_id.",'".$k."',0,0,'','',1,0)";
	mysqli_query($dbh, "INSERT INTO user_settings VALUES ".implode(",", $arr_value));
}
function GetUserSettings($dbh, $u_id)
{
	$res = mysqli_query($dbh, "SELECT * FROM userlist WHERE id_user = ".$u_id." LIMIT 1");
	if ($row = mysqli_fetch_row($res))
	{
		FillUserSettings($row, $u_id);
		mysqli_free_result($res);
		GetFieldsSettings($dbh, $u_id);
	}
}
function GetFieldsSettings($dbh, $u_id)
{
	foreach (array_keys($_SESSION['main_params']['var']) as $k)
	{
		$res = mysqli_query($dbh, "SELECT sort_order, sort_mode, text, to_text, md, iv FROM user_settings WHERE id_user = ".(string)$u_id." AND field_name ='".$k."' LIMIT 1");
		if ($res)
		{
			if ($row = mysqli_fetch_row($res)) FillUserFieldsSettings($row, $k);
			mysqli_free_result($res);
		}
	}
}
function GetCheckFromArray($ch_arr)
{
	for ($i = 0; $i < count($ch_arr); $i++) if ($ch_arr[$i]) return $i;
	return 1;
}
function FillChecksArray($n)
{
	$chk = array(false, false, false);
	$chr[$n] = true;
	return $chk;
}
function FillUserSettings($row, $u_id)
{
	$_SESSION['user_name'] = $row[1];
	$_SESSION['pri'] = $row[3];
	$_SESSION['cur_lang'] = array($row[7], $_SESSION['langs'][(string)$row[7]]);
	$_SESSION['conf']['portion_item'] = $row[8];
	$_SESSION['m_case'] = ($row[9] == 1);
	$_SESSION['conf']['hide_list'] = ($row[10] == 1);
	$_SESSION['p_start'] = $row[11];
	$_SESSION['set_pad'] = ($row[12] == 1);
	$_SESSION['p_count']['filter'] = $row[13];
	$_SESSION['p_code'] = $row[14];
	$_SESSION['f_view'] = ($row[15] == 1);
	$_SESSION['m_col_v'] = ($row[16] == 1);
	$_SESSION['c_p']['0']['search_text'] = $row[17];
	$_SESSION['c_p']['0']['search_mode_checks'] = FillChecksArray($row[18]);
	$_SESSION['c_p']['0']['search_where'] = $row[19];
	$_SESSION['c_p']['0']['search_on'] = ($row[20] == 1);
	$_SESSION['c_p']['0']['filter_text'] = $row[21];
	$_SESSION['c_p']['0']['filter_mode_checks'] = FillChecksArray($row[22]);
	$_SESSION['c_p']['0']['filter_where'] = $row[23];
	$_SESSION['c_p']['0']['filter_on'] = ($row[24] == 1);
	$_SESSION['c_p']['0']['filter_count'] = $row[25];
	$_SESSION['c_p']['0']['found_count'] = $row[26];
	$_SESSION['c_p']['0']['start_pos'] = $row[27];
	$_SESSION['c_p']['0']['prev_search_out'] = $row[28];
	$_SESSION['c_p']['0']['next_search_out'] = $row[29];
	$_SESSION['c_p']['0']['view_search'] = ($row[30] == 1);
	$_SESSION['portion'] = $row[31];
	$_SESSION['cat_tree'] = ($row[32] == 1);
	$_SESSION['cur_cat'] = $row[33];
	$_SESSION['block']['cat'] = ($row[34] == 1);
	$_SESSION['cur_value'] = $row[35];
	$_SESSION['cat_flag'] = ($row[36] == 1);
	$_SESSION['c_p']['0']['tree_total_count'] = $row[37];
}
function FillUserFieldsSettings($row, $k)
{
	$_SESSION['main_params']['var'][$k]['sort']['sort_order'] = $row[0];
	$_SESSION['main_params']['var'][$k]['sort']['sort_mode'] = $row[1];
	$_SESSION['main_params']['var'][$k]['filter']['text'] = $row[2];
	$_SESSION['main_params']['var'][$k]['filter']['to'] = $row[3];
	$_SESSION['main_params']['var'][$k]['filter']['md'] = $row[4];
	$_SESSION['main_params']['var'][$k]['filter']['iv'] = ($row[5] == 1);
}
function ResetUserSettings()
{
	$arr_data[] = "user_active = 0";
	$arr_data[] = "work_start = NULL";
	$arr_data[] = "start_pos = 0";
	$arr_data[] = "settings_pad = 0";
	$arr_data[] = "p_count_filter = 0";
	$arr_data[] = "p_code = 0";
	$arr_data[] = "cat_search_text = ''";
	$arr_data[] = "cat_search_mode_checks = 1";
	$arr_data[] = "cat_search_where = ''";
	$arr_data[] = "cat_search_on = 0";
	$arr_data[] = "cat_filter_text = ''";
	$arr_data[] = "cat_filter_mode_checks = 1";
	$arr_data[] = "cat_filter_where = ''";
	$arr_data[] = "cat_filter_on = 0";
	$arr_data[] = "cat_filter_count = 0";
	$arr_data[] = "cat_found_count = 0";
	$arr_data[] = "cat_start_pos = 0";
	$arr_data[] = "cat_prev_search_out = ''";
	$arr_data[] = "cat_next_search_out = ''";
	$arr_data[] = "cat_view_search = 0";
	$arr_data[] = "cat_current = ''";
	$arr_data[] = "cat_block = 0";
	$arr_data[] = "cat_sel_current = ''";
	$arr_data[] = "cat_flag = 0";
	$arr_data[] = "cat_tree_count = 0";
	$_SESSION['user_name'] = $row[1];
	$_SESSION['pri'] = $row[3];
	$_SESSION['cur_lang'] = array($row[7], $_SESSION['langs'][(string)$row[7]]);
	$_SESSION['conf']['portion_item'] = $row[8];
	$_SESSION['m_case'] = ($row[9] == 1);
	$_SESSION['conf']['hide_list'] = ($row[10] == 1);
	$_SESSION['p_start'] = $row[11];
	$_SESSION['set_pad'] = ($row[12] == 1);
	$_SESSION['p_count']['filter'] = $row[13];
	$_SESSION['p_code'] = $row[14];
	$_SESSION['f_view'] = ($row[15] == 1);
	$_SESSION['m_col_v'] = ($row[16] == 1);
	$_SESSION['c_p']['0']['search_text'] = $row[17];
	$_SESSION['c_p']['0']['search_mode_checks'] = FillChecksArray($row[18]);
	$_SESSION['c_p']['0']['search_where'] = $row[19];
	$_SESSION['c_p']['0']['search_on'] = ($row[20] == 1);
	$_SESSION['c_p']['0']['filter_text'] = $row[21];
	$_SESSION['c_p']['0']['filter_mode_checks'] = FillChecksArray($row[22]);
	$_SESSION['c_p']['0']['filter_where'] = $row[23];
	$_SESSION['c_p']['0']['filter_on'] = ($row[24] == 1);
	$_SESSION['c_p']['0']['filter_count'] = $row[25];
	$_SESSION['c_p']['0']['found_count'] = $row[26];
	$_SESSION['c_p']['0']['start_pos'] = $row[27];
	$_SESSION['c_p']['0']['prev_search_out'] = $row[28];
	$_SESSION['c_p']['0']['next_search_out'] = $row[29];
	$_SESSION['c_p']['0']['view_search'] = ($row[30] == 1);
	$_SESSION['portion'] = $row[31];
	$_SESSION['cat_tree'] = ($row[32] == 1);
	$_SESSION['cur_cat'] = $row[33];
	$_SESSION['block']['cat'] = ($row[34] == 1);
	$_SESSION['cur_value'] = $row[35];
	$_SESSION['cat_flag'] = ($row[36] == 1);
	$_SESSION['c_p']['0']['tree_total_count'] = $row[37];

	return implode(",", $arr_data);
}
function SaveUserSettings($dbh, $u_id)
{
	$arr_data[] = "user_language = ".(string)$_SESSION['cur_lang'][0];
	$arr_data[] = "user_screen_portion = ".(string)$_SESSION['conf']['portion_item'];
	$arr_data[] = "user_match_case = ".(($_SESSION['m_case']) ? "1" : "0");
	$arr_data[] = "user_hide_list = ".(($_SESSION['conf']['hide_list']) ? "1" : "0");
	$arr_data[] = "start_pos = ".(string)$_SESSION['p_start'];
	$arr_data[] = "settings_pad = ".(($_SESSION['set_pad']) ? "1" : "0");
	$arr_data[] = "p_count_filter = ".(string)$_SESSION['p_count']['filter'];
	$arr_data[] = "p_code = ".(string)$_SESSION['p_code'];
	$arr_data[] = "view_on = ".(($_SESSION['f_view']) ? "1" : "0");
	$arr_data[] = "view_multi_columns = ".(($_SESSION['m_col_v']) ? "1" : "0");
	$arr_data[] = "cat_search_text = '".$_SESSION['c_p']['0']['search_text']."'";
	$arr_data[] = "cat_search_mode_checks = ".(string)GetCheckFromArray($_SESSION['c_p']['0']['search_mode_checks']);
	$arr_data[] = "cat_search_where = '".$_SESSION['c_p']['0']['search_where']."'";
	$arr_data[] = "cat_search_on = ".(($_SESSION['c_p']['0']['search_on']) ? "1" : "0");
	$arr_data[] = "cat_filter_text = '".$_SESSION['c_p']['0']['filter_text']."'";
	$arr_data[] = "cat_filter_mode_checks = ".(string)GetCheckFromArray($_SESSION['c_p']['0']['filter_mode_checks']);
	$arr_data[] = "cat_filter_where = '".$_SESSION['c_p']['0']['filter_where']."'";
	$arr_data[] = "cat_filter_on = ".(($_SESSION['c_p']['0']['filter_on']) ? "1" : "0");
	$arr_data[] = "cat_filter_count = ".(string)$_SESSION['c_p']['0']['filter_count'];
	$arr_data[] = "cat_found_count = ".(string)$_SESSION['c_p']['0']['found_count'];
	$arr_data[] = "cat_start_pos = ".(string)$_SESSION['c_p']['0']['start_pos'];
	$arr_data[] = "cat_prev_search_out = '".(string)$_SESSION['c_p']['0']['prev_search_out']."'";
	$arr_data[] = "cat_next_search_out = '".(string)$_SESSION['c_p']['0']['next_search_out']."'";
	$arr_data[] = "cat_view_search = ".(($_SESSION['c_p']['0']['view_search']) ? "1" : "0");
	$arr_data[] = "cat_portion = ".(string)$_SESSION['portion'];
	$arr_data[] = "cat_tree = ".(($_SESSION['cat_tree']) ? "1" : "0");
	$arr_data[] = "cat_current = '".$_SESSION['cur_cat']."'";
	$arr_data[] = "cat_block = ".(($_SESSION['block']['cat']) ? "1" : "0");
	$arr_data[] = "cat_sel_current = '".$_SESSION['cur_value']."'";
	$arr_data[] = "cat_flag = ".(($_SESSION['cat_flag']) ? "1" : "0");
	$arr_data[] = "cat_tree_count = ".(string)$_SESSION['c_p']['0']['tree_total_count'];
	mysqli_query($dbh, "UPDATE userlist SET ".implode(",", $arr_data)." WHERE id_user = ".$u_id); 
}
function SaveFieldsSettings($dbh, $p_var, $u_id)
{
	foreach ($p_var as $k => $v)
	{
		$arr_value = array();
		$arr_value[] = "sort_order = ".(string)$v['sort']['sort_order'];
		$arr_value[] = "sort_mode = ".(string)$v['sort']['sort_mode'];
		$arr_value[] = "text = '".$v['filter']['text']."'";
		$arr_value[] = "to_text = '".$v['filter']['to']."'";
		$arr_value[] = "md = ".$v['filter']['md'];
		$arr_value[] = "iv = ".(($v['filter']['iv']) ? "1" : "0");
		mysqli_query($dbh, "UPDATE user_settings SET ".implode(",", $arr_value)." WHERE id_user = ".$u_id." AND field_name ='".$k."'");
	}
}
function SaveCatalogUserSettings($dbh, $u_id)
{
	$arr_data[] = "cat_search_text = '".$_SESSION['c_p']['0']['search_text']."'";
	$arr_data[] = "cat_search_mode_checks = ".(string)GetCheckFromArray($_SESSION['c_p']['0']['search_mode_checks']);
	$arr_data[] = "cat_search_where = '".$_SESSION['c_p']['0']['search_where']."'";
	$arr_data[] = "cat_search_on = ".(($_SESSION['c_p']['0']['search_on']) ? "1" : "0");
	$arr_data[] = "cat_filter_text = '".$_SESSION['c_p']['0']['filter_text']."'";
	$arr_data[] = "cat_filter_mode_checks = ".(string)GetCheckFromArray($_SESSION['c_p']['0']['filter_mode_checks']);
	$arr_data[] = "cat_filter_where = '".$_SESSION['c_p']['0']['filter_where']."'";
	$arr_data[] = "cat_filter_on = ".(($_SESSION['c_p']['0']['filter_on']) ? "1" : "0");
	$arr_data[] = "cat_filter_count = ".(string)$_SESSION['c_p']['0']['filter_count'];
	$arr_data[] = "cat_found_count = ".(string)$_SESSION['c_p']['0']['found_count'];
	$arr_data[] = "cat_start_pos = ".(string)$_SESSION['c_p']['0']['found_count'];
	$arr_data[] = "cat_prev_search_out = '".(string)$_SESSION['c_p']['0']['prev_search_out']."'";
	$arr_data[] = "cat_next_search_out = '".(string)$_SESSION['c_p']['0']['next_search_out']."'";
	$arr_data[] = "cat_view_search = ".(($_SESSION['c_p']['0']['view_search']) ? "1" : "0");
	$arr_data[] = "cat_portion = ".(string)$_SESSION['portion'];
	$arr_data[] = "cat_tree = ".(($_SESSION['cat_tree']) ? "1" : "0");
	$arr_data[] = "cat_tree_count = ".(string)$_SESSION['c_p']['0']['tree_total_count'];
	mysqli_query($dbh, "UPDATE userlist SET ".implode(",", $arr_data)." WHERE id_user = ".$u_id); 
}
function DeleteFieldsSettings($dbh, $u_id)
{
	mysqli_query($dbh, "DELETE FROM user_settings WHERE id_user = ".$u_id);
}
?>
