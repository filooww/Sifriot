<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.start_load {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>
	<style>.b_yes {color:#000000; background-color:#FFFFFF}</style>
	<style>.b_no {color:#FFFFFF; background-color:#FFFFFF}</style>
	<style>.catalog_exit {background-color:#00FFCC;}</style>
	<style>.data_num {text-align:right;}</style>
	<style>.odd_row {background-color:#FFFFFF;}</style>
	<style>.even_row {background-color:#F4F5E9;}</style>
	<style>.transparent {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.row_insert {background-color:#00CC99;}</style>
	<style>.action_off {color:#FFFFFF;}</style>
	<style>.set_visible {color:#0000FF; background-color:#0000FF;}</style>
	<style>.invisible_button {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin}</style>
	<style>.changed_text {background-color:#CCFFCC;}</style>
	<style>.error_text {background-color:#FF99FF;}</style>
	<style>.deleted_text {background-color:#98FB98;}</style>
	<style>.cur_text {background-color:#FFCC00;}</style>
	<style>.dis_text {color:#CCCCCC;}</style>
	<style>.OK_button {background-color:#CCFF00;}</style>
	<style>.cancel_button {background-color:#FF0000; color:#FF0000;}</style>
	<style>.new_txt {color:#FFFFFF;font:bold;}</style>
	<style>.vis_color {background-color:#CCFFFF}</style>
	<style>.found_text {color:#FFFFFF; background-color:#0066FF;}</style>
	<style>.copy_class {color:#FF0000;}</style>
	<style>.replace_text {background-color:#CCCCCC;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.catalog_form.user_lang_s.value = '*'; catalog_form.submit();}</SCRIPT>
<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once("MainUtilities.php");
require_once("SelectFromCatalog.php");
require_once("DoubleCatalog.php");
require_once("CopyPasteBranch.php");
require_once("Update.php");
require_once("Test.php");
require_once("Common.php");
require_once("Screen.php");
require_once("Filter.php");
require_once("Search.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tree/TreeNavigation.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tree/TreeCatalogs.php");
require_once("Navigation.php");
require_once("FormUtilities.php");
require_once("CatalogButtons.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/SetSession.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LogProcessing/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/SpecialTextsUtils.php");

session_start();
$Mes = array();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000");
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
$sel_value = ""; //???????
if (count($_POST) == 0)
{
	$_SESSION['mes'] = array("0"=>array(), "1"=>array(), "c"=>array());
	$_SESSION['view_err_mes'] = false;
	TestCatalogs($dbh, true);
	if ($_SESSION['priority'] < 11) ExitSession(Title(509)." <b>".$_SESSION['Catalog'][$n]['name']."</b> ".FTM(Title(296)).". ".Title(510)."."."|FF0000", $_SESSION['db_info']['id']);
	$_SESSION['cur_cat'] = "";
}
$line_code = array();
$sel = false;
foreach ($_POST as $k => $v)
{
	$sw_break = true;
	$ak = explode("|", $k);
	switch ($ak[0])
	{
	    case "idle_button"          : break;
		case "user_lang_s"			: CatalogLangChoice($dbh_sys, $dbh, $sw_break); break;
		case "cat_exit"				: CatExitAction($dbh_sys, $sel, $_SESSION['cat_view'], $_SESSION['block'], $_SESSION['cur_cat'], $_SESSION['PR']['var'], $_SESSION['cat_flag']); break;
		case "cat_rest"				: CatRestoreAction($dbh, $sel_value); break;
		case "cat_save"				: UpdateCatalogs($dbh); TestCatalogs($dbh, false, false); if ($_SESSION['user_working_mode'] == 1) $_SESSION['cat_arr_init'] = $_SESSION['cat_arr']; break;
		case "cat_test"				: TestCatalogs($dbh, false, true, ($_SESSION['Catalog']['1']['name'] == "") ? Title(215) : Title(126)); break;
		case "cat_height"			: ChangeScreenHeight($dbh, $ak[1]); break;
		case "cat_view_messages"	: $_SESSION['view_err_mes'] = !$_SESSION['view_err_mes']; TestCatalogs($dbh); break;
		case "answer_yes"			: CatYesDelAction($dbh); break;
		case "answer_no"			: CatNoDelAction($dbh, $_SESSION['block']); break;
		case "topos"				: GoToCatalogPosition($dbh, $ak[1], $ak[2]); $_SESSION['block']['cat_goto'] = false; break;
		case "cat_tree"				: $_SESSION['cat_tree'] = !$_SESSION['cat_tree']; break;
		case "cat_tree_collapse"	: SetAllCollapse($dbh, "+", $_SESSION['cat_arr']['0'], $_SESSION['set_pad']); break;
		case "cat_tree_extend"		: SetAllCollapse($dbh, "-", $_SESSION['cat_arr']['0'], $_SESSION['set_pad']); break;
		case "cat_filter"			: FFAction($dbh, "filter_on", "filter_compare", "filter_anywhere", $ak[1], Title(110), $_SESSION['set_pad']); break;
		case "cat_search"			: FFAction($dbh, "search_on", "search_compare", "search_anywhere", $ak[1], Title(111), $_SESSION['set_pad']); break;
		case "cat_search_move"		: SearchMoving($dbh, $ak[1], $ak[2], $_SESSION['set_pad']); break;
		case "cat_search_hide"		: $_SESSION['catalog_param'][$ak[1]]['view_search'] = !$_SESSION['catalog_param'][$ak[1]]['view_search']; break;
		case "cat_fill"				: CatFill($dbh); break;
		case "filter_start"			: ApplyFilter($dbh, $ak[1], $_POST["filter_text|".$ak[1]], $_POST["filter_mode|".$ak[1]], $_SESSION['set_pad']); break;
		case "search_start"			: StartSearch($dbh, $ak[1], $_POST["search_text|".$ak[1]], $_POST["search_mode|".$ak[1]], $_SESSION['set_pad']); break;
		case "cat_nav"				: NavigationActions($dbh, $ak[1], $ak[2], $_SESSION['set_pad']); break;
		case "cat_code"				: $line_code = (($ak[1] == "0") ? array("select", $ak[2]) : array("insert", $ak[2])); break; // new??
		case "collapse"				: SetCollapse($dbh, $ak[2], $ak[3], $_POST['collapse|0|'.$ak[2].'|'.$ak[3]], $_SESSION['set_pad']); break;
		case "set_vis"				: SetOnOffButton($_SESSION['cat_vis'], $ak[2]); break;
		case "cat_mark"				: SetOnOffButton($_SESSION['cat_mark'], $ak[2]); break;
		case "cat_copy"				: $_SESSION['copy_paste'] = CopyBranch($ak[3], $ak[2], $_SESSION['cat_arr']['0'][$ak[2]], $_SESSION['Catalog']['0']['separator']); break;
		case "cat_paste"			: CatBranchAction($dbh, $ak[2], $_SESSION['copy_paste']); break;
		case "cat_paste_root"		: CatBranchAction($dbh, "", $_SESSION['copy_paste']); break;
		case "del_hier_set"			: DelHierarchic($dbh, $ak[2]); break;
		case "del_from_set"			: if (is_numeric($ak[2])) DelFromSetList($dbh, $_SESSION['cat_vis'], $ak[2]); break; // new??
		default						: $sw_break = false;
	}
	if ($sw_break) break;
}

if ($line_code == "select") CatLineSelectAction($dbh, $line_code[1]);
elseif ($line_code == "insert") InsMarked($dbh, $_SESSION['cat_mark'], $line_code[1], $_POST["cat_text|1|".$line_code[1]]);

if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
/*
ToButtonOff($aA);
if (ToCopyBranch($aA)) $_SESSION['copy_paste'] = array("copy_id"=>"", "parent_value"=>"", "parent_value_text"=>"", "copy_value"=>"", "copy_text_value"=>"");
if (ToTest($aA)) TestCatalogs($dbh, $_SESSION['mes']);
if (count($aA) > 0) CatalogActions($dbh, (isset($aA['cat_num'])) ? $aA['cat_num'] : "", $aA, "", $_SESSION['cur_value'], $_SESSION['mes'], false, 0, true);

if (count($aA) > 0)
{
	$sw_break = true;
	switch ($aA['action'])
	{
		
		
		default:					$sw_break = false;
	}
	if ($sw_break && $user_id > 0) SaveCatalogUserSettings($dbh, $user_id);
*/

?>
<form method="post" id="catalog_form" name="catalog_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
	<table width="100%"><tr><td width="7%"><?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on");?></td></tr></table>
	<?php ViewNowMessages(); if ($_SESSION['view_err_mes']) ViewMessages(false, "", $_SESSION['number_warn'], $_SESSION['Catalog'], $_SESSION['del_row'], $_SESSION['block'], true);?>
	<table>
		<tr valign="top">
<!---			<td><?php //if ($view_err_mes) ViewMessages(false, "", $_SESSION['number_warn'], $_SESSION['Catalog'], $_SESSION['del_row'], $_SESSION['block'], ToSave($aA));?></td> -->
			<td><?php ViewCoupleCatalog();?></td>
		</tr>
	</table>
</form>
