<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.header_button {background-color:#CCCCCC; color:#CCCCCC;}</style>
	<style>.header_text {background-color:#CCCCCC;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
</head>
<SCRIPT language=JavaScript>
var form_timer = setInterval(t_curr, Number(document.getElementById("t_interval").value) * 1000);
function t_curr()
{
	var d = new Date();
	document.getElementById("t_curr").value = d.getTime();
	if (document.getElementById("t_start").value == '')
	{
		document.getElementById("t_start").value = document.getElementById("t_curr").value;
		document.getElementById("t_stop").value = String(Number(document.getElementById("t_start").value) + Number(document.getElementById("t_delay").value));
	}
	if (Number(document.getElementById("t_curr").value) > Number(document.getElementById("t_stop").value))
	{
		document.getElementById("end_flag").value = '*';
		list_form.submit();
	}
	else
	{
		document.getElementById("t_flag").value = '*';
		list_form.submit();
	}
}
function user_lang_on() {document.list_form.user_lang_s.value = '*'; list_form.submit();}
</SCRIPT>

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Calendar.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/UserList.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/UserSettings.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/SelectFromCatalog.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/DoubleCatalog.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tree/TreeCatalogs.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Screen.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Filter.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Search.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/FormUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/FormPubSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Navigation.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LogProcessing/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/CommonUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");

session_start();
$Mes = array();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000");
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
$_SESSION['mes'] = array("0"=>array(), "1"=>array(), "c"=>array(), "m"=>array(), "u"=>array());
if (count($_POST) == 0)
{
    $_SESSION['main_params'] = ReadFieldDefinition($dbh);
	$_SESSION['p_count']['total'] = GetMTLimit($dbh, $_SESSION['db_info']['t_main'], "active");
	$_SESSION['item_arr'] = GetMTPortion($dbh, $_SESSION['db_info']['t_main'], $_SESSION['p_start'], $_SESSION['portion'], $_SESSION['PR'], "active");
}
$_SESSION['p_files']['e'] = array();
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	$arr_key = explode("-", $str_key);
	$s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
	switch ($s_k)
	{
		case "db_view_exit"		: ExitFromViewMode($dbh); break;
		case "list_minus"		: $sw_break = false; ChangeMTScreenHeight($dbh, $_SESSION['db_info']['t_main'], -1, $_SESSION['item_arr'], $_SESSION['p_start'], $_SESSION['PR'], $_SESSION['mes']['m'], (integer)$_SESSION['user_id']); break;
		case "list_plus"		: $sw_break = false; ChangeMTScreenHeight($dbh, $_SESSION['db_info']['t_main'], 1, $_SESSION['item_arr'], $_SESSION['p_start'], $_SESSION['PR'], $_SESSION['mes']['m'], (integer)$_SESSION['user_id']); break;
		case "list_height_b"	: $sw_break = false; ChangeMTScreenHeight($dbh, $_SESSION['db_info']['t_main'], $_POST['list_height_v'], $_SESSION['item_arr'], $_SESSION['p_start'], $_SESSION['PR'], $_SESSION['mes']['m'], (integer)$_SESSION['user_id']); break;
		case "match_case_find"	: $sw_break = false; $_SESSION['item_arr'] = ChangeMatchCase($dbh, (integer)$_SESSION['user_id']); break;
		case "hide_list_flag"	: $sw_break = false; $_SESSION['hide_list'] = !$_SESSION['hide_list']; break;
		case "sort_find"		: $sw_break = false; SortFind(); break;
		case "multi_col"		: $sw_break = false; ChangeColumnsView($dbh, $_SESSION['m_col_v'], $_SESSION['f_view'], $_SESSION['p_code'], $_SESSION['URL_p'], (integer)$_SESSION['user_id'], $_SESSION['p_files']['e'], $_SESSION['PR']['const']); break;
		case "item_beg"			: $sw_break = false; MTNavigation($dbh, "beg", $_SESSION['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['p_code'], (integer)$_SESSION['user_id']); break;
		case "item_pg_up"		: $sw_break = false; MTNavigation($dbh, "pgup", $_SESSION['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['p_code'], (integer)$_SESSION['user_id']); break;
		case "item_ln_up"		: $sw_break = false; MTNavigation($dbh, "lnup", $_SESSION['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['p_code'], (integer)$_SESSION['user_id']); break;
		case "item_ln_dn"		: $sw_break = false; MTNavigation($dbh, "lndn", $_SESSION['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['p_code'], (integer)$_SESSION['user_id']); break;
		case "item_pg_dn"		: $sw_break = false; MTNavigation($dbh, "pgdn", $_SESSION['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['p_code'], (integer)$_SESSION['user_id']); break;
		case "item_end"			: $sw_break = false; MTNavigation($dbh, "end", $_SESSION['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['p_code'], (integer)$_SESSION['user_id']); break;
		case "view_item"		: $sw_break = false; ViewItem($dbh, $_SESSION['URL_p'], $_SESSION['f_view'], $_SESSION['p_code'], (integer)$arr_key[1], $_SESSION['p_files']['e'], (integer)$_SESSION['user_id'], $_SESSION['PR']['const']); break;
		case "sel_lang"			: $sw_break = false; SetLanguage($dbh, $_POST['view_language'], $_SESSION['langs'], (integer)$_SESSION['user_id'], $_SESSION['cur_lang'], $_SESSION['el_titles']); break;
		case "t_flag"			: $sw_break = false; $suspend = TestSessionSuspend($dbh_sys, $_SESSION['db_info']['id'], $_SESSION['user_id']); break;
		case "end_flag"			: $sw_break = false; ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
		
		ExitFromViewMode($dbh, $_SESSION['user_id']); break;
		default					: $sw_break = false;
	}
	$sw_cat_break = ListSettingActions($dbh, $arr_key, $_SESSION, $_SESSION['mes']['m'], (integer)$_SESSION['user_id']);
	if ($sw_cat_break) break;
}
?>
<body onLoad="start_prompt_exit()">
	<?php //if ($suspend) echo ;?>
	<form method="post" id="list_form" name="list_form">
		<input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'];?>"/>
		<?php echo Title(54)." <b>".$_SESSION['user_name']."</b>";?>
		<font class="invisible_button">X</font>
		<select name="view_language"> <!-- // SelectTag -->
			<?php foreach ($_SESSION['langs'] as $v) echo OptionTag($_SESSION['cur_lang'][1], $v);?>
		</select>
		<button name="sel_lang" type="submit" value="*">...</button>
		<div align="center"><h2><b><?php echo Title(55)." ".$_SESSION['dbName'];?></b></h2></div>
		<hr align="left" size="2" noshade="noshade" color="#000000" >
		<?php
		require_once("MainMenu.php");
		if ($_SESSION['set_pad']) ListSettingsPad((integer)$_SESSION['user_id']);
		if ($_SESSION['hide_list'])
		{
			if (!$_SESSION['set_pad']) require_once("ItemPortion.php");
			ViewMessages(false, "", $_SESSION['number_warn'], array(), array(), $_SESSION['block'], false);
		}
		else
		{
			ViewMessages(false, "", $_SESSION['number_warn'], array(), array(), $_SESSION['block'], false);
			require_once("ItemPortion.php");
		}
		?>
		<input type="hidden" id="t_curr" name="t_curr">
		<input type="hidden" id="t_start" name="t_start">
		<input type="hidden" id="t_stop" name="t_stop">
		<input type="hidden" id="t_delay" name="t_delay" value="<?php echo $_SESSION['close_delay'];?>">
		<input type="hidden" id="t_flag" name="t_flag" value="">
		<input type="hidden" id="end_flag" name="end_flag" value="">
	</form>
</body>

