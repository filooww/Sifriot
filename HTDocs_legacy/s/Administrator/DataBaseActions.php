<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.hidden_button {visibility:hidden;}</style>
	<style>.invisible_td {color:#FFFFFF; background-color:#FFFFFF;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.db_l {font-size:150%; font-weight:700;}</style>
	<style>.db_x {font-size:200%; font-weight:700; color:#0000FF;}</style>
	<style>.action_button {color:#0033FF; font-size:150%; font-weight:700; background-color:#FFFFFF; border:none;}</style>
	<style>.db_text {color:#0033FF; font-size: 150%; font-weight: 700; background-color:#FFFFFF; border:none;}</style>
</head>

<SCRIPT language=JavaScript>
function user_lang_on() {document.DB_actions.user_lang_s.value = '*'; DB_actions.submit();}
function working_mode_on() {var rc = document.querySelector('input[name="user_working_mode"]:checked').value; document.DB_actions.w_mode_s.value = rc; DB_actions.submit();}
</SCRIPT>

<?php
require_once("Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserList/UserListTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/SpecialTextsUtils.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserEnter/UserEnterUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/UserDBCreate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Fields/FieldUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Calendar.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Fields/FieldEditUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmManagerUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmDBUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmReferences.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000");
$Mes = array();
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	switch ($str_key)
	{
		case "user_lang_s"			: if (AfterLangChoice($dbh_sys, "user_lang_s", "user_lang", $sw_break)) TestUserDB($_SESSION['db_info']['name'], $_SESSION['db_info']['coding'], $_SESSION['db_info']['id']); break;
		case "w_mode_s"				: SwitchWorkingMode($dbh_sys, $_SESSION['db_info']['id'], (integer)$_POST['w_mode_s'], $sw_break); break;
		case "db_action_exit"		: DataBaseActionExit($dbh_sys); header("Location: ../Administrator/MainForm.php"); break;
		case "db_action_configs"	: $_SESSION['common_config'] = false; DBConfiguration($dbh_sys); header("Location: ../Configuration/ConfigForm.php"); break;
		case "db_action_tables"		: header("Location: ../Tables/TableForm.php"); break;
		case "db_action_fields"		: header("Location: ".GoToFields()); break;
		case "db_action_catalogs"	: $_SESSION['catalog_list'] = GetCatalogList($dbh_sys); header("Location: ../Catalogs/CatalogListForm.php"); break;
		case "db_action_primary"	: header("Location: ../Primary/PrimaryMain.php"); break;
		case "db_action_update"		: $_SESSION['p_start'] = 0; header("Location: ../MainTable/List.php"); break;
		case "db_action_delete"		: header("Location: ../DeleteMarked/List.php"); break;
		default						: $sw_break = false;
	}
	if ($sw_break) break;
}
if (isset($_SESSION['table_fatal_errors']) && count($_SESSION['table_fatal_errors']) > 0)
{
	echo "<div align='center'><font color='#FF0000'><h3><b>".Title(379)." ".implode("; ", $_SESSION['table_fatal_errors'])."</b></h3></font></div>";
	$_SESSION['table_fatal_errors'] = array();
}
if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
if (count($Mes) > 0) foreach( $Mes as $z) echo "<font color='#FF0000'>".$z."</font>";
?>
<form method="post" id="DB_actions" name="DB_actions">
	<table width="100%">
		<tr>
			<td width="7%"><?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on");?></td>
			<td width="20%" valign="middle"><?php RadioTag("user_working_mode", $_SESSION['user_working_mode'], array(Title(154), Title(155)), "hidden_button", false, "working_mode_on", "w_mode_s");?></td>
			<td align="right"><button name="db_action_exit" type="submit" value="*" class="exit_button"><?php echo Title(8);?></button></td>
		</tr>
	</table>
    <?php if (isset($_SESSION['db_errors']) && count($_SESSION['db_errors']) > 0) ViewDBErrors(); ?>
	<table width="100%"><tr valign="bottom"><td align="center"><font class="db_l"><?php echo Title(163);?></font> <font class="db_x"><?php echo $_SESSION['arr_db'][$_SESSION['db_info']['id']]['db_comment'];?></font></td></tr></table>
	<table width="100%">
		<tr valign="top">
			<td width="5%"><font class='invisible_td'>X</font></td>
			<td width="40%">
				<table>
					<tr valign="top"><td><button name="db_action_configs" type="submit" value="*" class="action_button" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(216);?></button></td></tr>
					<tr valign="top"><td><button name="db_action_tables" type="submit" value="*" class="action_button" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(115);?></button></td></tr>
					<tr valign="top"r><td><button name="db_action_fields" type="submit" value="*" class="action_button" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(214);?></button></td></tr>
				</table>
			</td>
			<td width="5%"><font class='invisible_td'>X</font></td>
			<td>
				<table>
					<tr valign="top"><td><button name="db_action_catalogs" type="submit" value="*" class="action_button" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(375);?></button></td></tr>
					<tr valign="top"><td><button name="db_action_primary" type="submit" value="*" class="action_button" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(218);?></button></td></tr>
					<tr valign="top"><td><button name="db_action_update" type="submit" value="*" class="action_button" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(219);?></button></td></tr>
					<tr valign="top"><td><button name="db_action_delete" type="submit" value="*" class="action_button" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(217);?></button></td></tr>
				</table>
			</td>
		</tr>
	</table>
<form>
