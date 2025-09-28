<?php
function ToSelectedCatalog($dbh, $catalog_table_name)
{
	$_SESSION['to_sel_form_catalog'] = false;
	if (!isset($_SESSION['w_05'])) $_SESSION['w_05'] = GetConfigValue($dbh, "db_configs", "w_05", false, "Width of placeholder for catalog code", "5%");
	if (!isset($_SESSION['w_06'])) $_SESSION['w_06'] = GetConfigValue($dbh, "db_configs", "w_06", false, "Width of catalog code", "50px");
	if (!isset($_SESSION['w_07'])) $_SESSION['w_07'] = GetConfigValue($dbh, "db_configs", "w_07", true, "Size of selection criteria", "44");
	if (!isset($_SESSION['w_08'])) $_SESSION['w_08'] = GetConfigValue($dbh, "db_configs", "w_08", true, "Width of catalog value", "41");
	$_SESSION['del_row'] = array("0"=>array(), "1"=>array());
	$_SESSION['Catalog'] = GetCatalogDefinition($dbh, $catalog_table_name);
	$_SESSION['block'] = array("cat"=>false, "cat_del"=>false, "cat_goto"=>false, "item_del"=>false, "item_exit"=>false, "pad"=>false, "pad_cat"=>false);
	$_SESSION['cur_value'] = "";
	$_SESSION['cat_mark'] = array("b_on"=>false, "bc"=>"");
	$_SESSION['cat_vis'] = array("b_on"=>false, "bc"=>"");
	$ca = array();
	$Mes = array();
	if ($_SESSION['Catalog']['0']['cat_type'] == 1) SetAllCollapse($dbh, "", $ca, false);
	InitCatalogParameters("1");
	if ($_SESSION['Catalog']['1']['name'] == "") $_SESSION['cat_arr']['1'] = array();
	else
	{
		$_SESSION['cat_arr']['1'] = GetCatalogPortion($dbh, "1", $_SESSION['catalog_param']['1']['start_pos'], $_SESSION['portion']);
		$_SESSION['catalog_param']['1']['total_count'] = GetTableLimit($dbh, "total", "1");
	}
	InitCatalogParameters("0");
	$_SESSION['cat_arr']['0'] = GetCatalogPortion($dbh, "0", $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion']);
	if ($_SESSION['user_working_mode'] == 1) $_SESSION['cat_arr_init'] = $_SESSION['cat_arr'];
	$_SESSION['catalog_param']['0']['total_count'] = GetTableLimit($dbh, "total", "0");
	if ($_SESSION['Catalog']['0']['cat_type'] == 1) $_SESSION['catalog_param']['0']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['0']['total_count']);
}
function InitConfig($dbh)
{
	if (!isset($_SESSION['conf']['start_year'])) $_SESSION['conf']['start_year'] = (integer)GetConfigValue($dbh, "db_configs", "start_year", true, "Initial issue year of publication", "1900");
	if (!isset($_SESSION['conf']['screen_saver'])) $_SESSION['conf']['screen_saver'] = GetConfigValue($dbh, "db_configs", "screen_saver", false, "Site screen saver");
	if (!isset($_SESSION['conf']['screen_saver_height'])) $_SESSION['conf']['screen_saver_height'] = (integer)GetConfigValue($dbh, "db_configs", "screen_saver_height", true, "Height of site screen saver");
	if (!isset($_SESSION['conf']['w_05'])) $_SESSION['conf']['w_05'] = GetConfigValue($dbh, "db_configs", "w_05", false, "Width of placeholder for catalog code", "5%");
	if (!isset($_SESSION['conf']['w_06'])) $_SESSION['conf']['w_06'] = GetConfigValue($dbh, "db_configs", "w_06", false, "Width of catalog code", "60px");
	if (!isset($_SESSION['conf']['w_07'] = GetConfigValue($dbh, "db_configs", "w_07", true, "Size of selection criteria", "44");
	if (!isset($_SESSION['conf']['w_08'])) $_SESSION['conf']['w_07'] = GetConfigValue($dbh, "db_configs", "w_08", true, "Size of catalog value", "44");
}
function InitSession($dbh)
{
	InitConfig($dbh);
	$_SESSION['cat_flag'] = false;
	$_SESSION['main_params'] = ReadFieldDefinition($dbh);
	$_SESSION['del_row'] = array("0"=>array(), "1"=>array());
	$_SESSION['catalog_list'] = FormCatalogList($_SESSION['PR']['const']);
	ClearCatalogs();
	$_SESSION['block'] = array("cat"=>false, "cat_del"=>false, "cat_goto"=>false, "item_del"=>false, "item_exit"=>false, "pad"=>false, "pad_cat"=>false);
	$_SESSION['set_pad'] = false;
	$_SESSION['cat_tree'] = false;
	$_SESSION['cur_cat'] = "";
	$_SESSION['cur_value'] = "";
	$_SESSION['cat_view'] = false;
	$_SESSION['mes'] = array("0"=>array(), "1"=>array(), "c"=>array());
}
function FormCatalogList($p_con)
{
	$catalog_list = array();
	foreach ($p_con as $k => $v) if ($v['table'] != "") $catalog_list[$k] = $v['name'];
	return $catalog_list;
}
function ClearSession()
{
	if (isset($_SESSION['m_case'])) unset($_SESSION['m_case']);
	if (isset($_SESSION['conf']['w_05'])) unset($_SESSION['conf']['w_05']);
	if (isset($_SESSION['conf']['w_06'])) unset($_SESSION['conf']['w_06']);
	if (isset($_SESSION['conf']['w_07'])) unset($_SESSION['conf']['w_07']);
	if (isset($_SESSION['conf']['w_08'])) unset($_SESSION['conf']['w_08']);
	if (isset($_SESSION['cat_flag'])) unset($_SESSION['cat_flag']);
	if (isset($_SESSION['main_params'])) unset($_SESSION['main_params']);
	if (isset($_SESSION['del_row'])) unset($_SESSION['del_row']);
	if (isset($_SESSION['catalog_list'])) unset($_SESSION['catalog_list']);
	if (isset($_SESSION['Catalog'])) unset($_SESSION['Catalog']);
	if (isset($_SESSION['catalog_param'])) unset($_SESSION['catalog_param']);
	if (isset($_SESSION['cat_arr'])) unset($_SESSION['cat_arr']);
	if (isset($_SESSION['cat_mark'])) unset($_SESSION['cat_mark']);
	if (isset($_SESSION['cat_vis'])) unset($_SESSION['cat_vis']);
	if (isset($_SESSION['copy_paste'])) unset($_SESSION['copy_paste']);
	if (isset($_SESSION['block'])) unset($_SESSION['block']);
	if (isset($_SESSION['set_pad'])) unset($_SESSION['set_pad']);
	if (isset($_SESSION['cat_tree'])) unset($_SESSION['cat_tree']);
	if (isset($_SESSION['cur_cat'])) unset($_SESSION['cur_cat']);
	if (isset($_SESSION['cur_value'])) unset($_SESSION['cur_value']);
	if (isset($_SESSION['cat_view'])) unset($_SESSION['cat_view']);
	if (isset($_SESSION['mes'])) unset($_SESSION['mes']);

}
?>

