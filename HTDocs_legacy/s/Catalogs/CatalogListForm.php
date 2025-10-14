<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.start_load {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>
	<style>.b_yes {color:#000000; background-color:#FFFFFF}</style>
	<style>.b_no {color:#FFFFFF; background-color:#FFFFFF}</style>
	<style>.catalog_exit {background-color:#00FFCC;}</style>
	<style>.act_b {color:#0033FF; font-size:150%; font-weight:700; background-color:#FFFFFF; border:none;}</style>
	<style>.table_b {color:#000000; font-size:150%; font-weight:700; background-color:#FFFFFF; border:none;}</style>
	<style>.invisible_row {color:#FFFFFF; background-color:#FFFFFF;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.catalog_list_form.user_lang_s.value = '*'; catalog_list_form.submit();}</SCRIPT>

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
require_once("Navigation.php");
require_once("FormUtilities.php");
require_once("CatalogButtons.php");
require_once("SetSession.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/LogProcessing/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserList/UserlistUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserList/UserListTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");

session_start();
$Mes = array();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000");
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	$arr_key = explode("|", $str_key);
	switch ($arr_key[0])
	{
		case "user_lang_s"	: AfterLangChoice($dbh_sys, "user_lang_s", "user_lang", $sw_break); break;
		case "catalogs_exit": header("Location: ../Administrator/DataBaseActions.php"); break;
		case "catalog"		: ToSelectedCatalog($dbh, $arr_key[1]); header("Location: CatalogForm.php"); break;
		default				: $sw_break = false;
	}
	if ($sw_break) break;
}
if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
?>
<form method="post" id="catalog_list_form" name="catalog_list_form">
	<table width="100%">
		<tr>
			<td width="7%"><?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on");?></td>
			<td><button name='catalogs_exit' type='submit' value='*' class='exit_button'><?php echo Title(8);?></button></td>
		</tr>
	</table>
	<div class="invisible_row">X</div>
	<div class="invisible_row">X</div>
	<div class="invisible_row">X</div>
	<table width="100%">
		<tr>
			<td width="20%"></td>
			<td width="60%"><font class="table_b"><?php echo Title(375);?></font></td>
			<td width="20%"></td>
		</tr>
		<?php
		foreach ($_SESSION['catalog_list'] as $k => $v)
		{
			echo "<tr>";
				echo "<td width='20%'></td>";
				echo "<td width='60%'><button name='catalog|".$k."' type='submit' value='*' class='act_b' ".OnMouseOver("00FF00", "0033FF").">".$v[1]."</button></td>";
				echo "<td width='20%'></td>";
			echo "</tr>";
		}
		?>
	</table>
</form>
