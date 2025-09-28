<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.header_button {background-color:#CCCCCC; color:#CCCCCC;}</style>
	<style>.header_text {background-color:#CCCCCC;}</style>
</head>
<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/UserList.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/UserSettings.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/_DBM/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/SelectFromCatalog.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/DoubleCatalog.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Screen.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Filter.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Search.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Forms.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/FormPubSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Navigation.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LogProcessing/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/_MainTable/CommonUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/_DBM/VisitUtilities.php");
//require_once("MTRequests.php");
//require_once("SetSession.php");
//require_once("ListForms.php");
//require_once("ListSettings.php");
//require_once("Utilities.php");

session_start();
$Mes = array();
$dbh_sys = GetDB_file("DB_manager", $_SERVER['DOCUMENT_ROOT']."/_DBM/_Credentials.txt");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000");
$_SESSION['db_info'] = SelectUserDB($dbh_sys, $_SESSION['user_db_name']);
$dbh = GetUserDB($_SESSION['db_info']['db_conn'], $Mes);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['db_conn']['db_name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);

// sleep --- time_sleep_until --- usleep
$_SESSION['mes'] = array("0"=>array(), "1"=>array(), "c"=>array(), "m"=>array(), "u"=>array());
if (GetUpdateFlag($dbh) == 1 && (isset($_POST['u_id'])))
{
	ExitFromViewMode($dbh);
	ExitSession(Title(53), (integer)$_SESSION['db_key']);
}
if (count($_POST) == 0) 
{

	
	InitUserSettings($dbh, (integer)$_SESSION['login']['id']);
	GetUserSettings($dbh, (integer)$_SESSION['login']['id']);
	$_SESSION['p_count']['total'] = GetMTLimit($dbh, $_SESSION['db_info']['t_main'], "active", $_SESSION['PR']);
	$_SESSION['item_arr'] = GetMTPortion($dbh, $_SESSION['db_info']['t_main'], $_SESSION['p_start'], $_SESSION['conf']['pt_p'], $_SESSION['PR'], $_SESSION['conf']['match_case'], "active");
	VisitParameters($dbh_sys, $_SESSION['login']['id'], $_SESSION['db_info']['id'], 0, $_SESSION['enter_mode'][1], $Mes);
}
else GetUserSettings($dbh, (integer)$_SESSION['login']['id']);
$_SESSION['p_files']['e'] = array();
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	$arr_key = explode("-", $str_key);
	$s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
	switch ($s_k)
	{
		case "db_view_exit"   :	ExitFromViewMode($dbh); break;
		case "list_minus"     :	ChangeMTScreenHeight($dbh, -1, $_SESSION['mes']['m'], (integer)$_SESSION['user_id']); break;
		case "list_plus"      :	ChangeMTScreenHeight($dbh, 1, $_SESSION['mes']['m'], (integer)$_SESSION['user_id']); break;
		case "list_height_b"  :	ChangeMTScreenHeight($dbh, $_POST['list_height_v'], $_SESSION['mes']['m'], (integer)$_SESSION['user_id']); break;
		case "match_case_find":	$_SESSION['item_arr'] = ChangeMatchCase($dbh, (integer)$_SESSION['user_id']); break;
		case "hide_list_flag" :	$_SESSION['hide_list'] = !$_SESSION['hide_list']; break;
		case "sort_find"      :	SortFind(); break;
		case "multi_col"      :	ChangeColumnsView($dbh, $_SESSION['m_col_v'], $_SESSION['f_view'], $_SESSION['p_code'], $_SESSION['URL_p'], (integer)$_SESSION['login']['id'], $_SESSION['p_files']['e'], $_SESSION['PR']['con']); break;
		case "item_beg"       :	MTNavigation($dbh, "beg", $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], (integer)$_SESSION['login']['id']); break;
		case "item_pg_up"     :	MTNavigation($dbh, "pgup", $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], (integer)$_SESSION['login']['id']); break;
		case "item_ln_up"     :	MTNavigation($dbh, "lnup", $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], (integer)$_SESSION['login']['id']); break;
		case "item_ln_dn"     :	MTNavigation($dbh, "lndn", $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], (integer)$_SESSION['login']['id']); break;
		case "item_pg_dn"     :	MTNavigation($dbh, "pgdn", $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], (integer)$_SESSION['login']['id']); break;
		case "item_end"       :	MTNavigation($dbh, "end", $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], (integer)$_SESSION['login']['id']); break;
		case "view_item"      :	ViewItem($dbh, $_SESSION['URL_p'], $_SESSION['f_view'], $_SESSION['p_code'], (integer)$arr_key[1], $_SESSION['p_files']['e'], (integer)$_SESSION['login']['id'], $_SESSION['PR']['con']); break;
		case "sel_lang"       :	SetLanguage($dbh, $_POST['view_language'], $_SESSION['langs'], (integer)$_SESSION['login']['id'], $_SESSION['cur_lang'], $_SESSION['el_titles']); break;
		default               :	$sw_break = false;
	}
	$sw_cat_break = ListSettingActions($dbh, $arr_key, $_SESSION, $_SESSION['mes']['m'], (integer)$_SESSION['login']['id']);
	if ($sw_cat_break) break;
}
?>
<!-- <form action="List.php" method="post"> -->
<form method="post">
	<input type="hidden" name="u_id" value="<?php echo (string)(integer)$_SESSION['login']['id'];?>"/>
	<?php echo Title(54)." <b>".$_SESSION['user_name']."</b>";?>
	<font class="invisible_button">X</font>
	<select name="view_language">
		<?php foreach ($_SESSION['langs'] as $v) echo OptionTag($_SESSION['cur_lang'][1], $v);?>
	</select>
	<button name="sel_lang" type="submit" value="*">...</button>
	<div align="center"><h2><b><?php echo Title(55)." ".$_SESSION['dbName'];?></b></h2></div>
	<hr align="left" size="2" noshade="noshade" color="#000000" >
	<?php
	require_once("MainMenu.php");
	if ($_SESSION['set_pad']) ListSettingsPad((integer)$_SESSION['login']['id']);
	if ($_SESSION['conf']['hide_list'])
	{
		if (!$_SESSION['set_pad']) require_once("ItemPortion.php");
		ViewMessages($_SESSION['mes'], false, "", $_SESSION['conf']['number_warning'], array(), array(), $_SESSION['block'], false);
	}
	else
	{
		ViewMessages($_SESSION['mes'], false, "", $_SESSION['conf']['number_warning'], array(), array(), $_SESSION['block'], false);
		require_once("ItemPortion.php");
	}
	?>
</form>

<?php // anti sleep?>
