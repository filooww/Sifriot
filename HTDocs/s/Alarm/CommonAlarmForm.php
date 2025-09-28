<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.table_color {background-color:#CCCCCC;}</style>
	<style>.group_color {background-color:#CCFFCC;}</style>
	<style>.invis_color {background-color:#FFFFFF; color:#FFFFFF;}</style>
	<style>.d_color {background-color:#CCFFFF; text-align:center;}</style>
	<style>.db_l {font-size:120%; font-weight:700;}</style>
	<style>.act_b {color:#0033FF; font-size:100%; font-weight:700; background-color:#FFFFFF; border:none;}</style>
	<style>.exit_button {color:#FFFFFF; font-size:120%; font-weight:700; border:1px solid rgb(250,172,17); border-radius:7px; background:rgb(56,81,202) linear-gradient(rgb(56,81,202), rgb(55,180,202));}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.alarm_form.user_lang_s.value = '*'; alarm_form.submit();}</SCRIPT>
<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Administrator/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleServices.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/SpecialTextsUtils.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitlePortion.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");
require_once("AlarmManagerUtilities.php");
require_once("AlarmReferences.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$Mes = array();
$dbh = GetOnlyDB("db_manager");
if (!$dbh) ExitSession(Title(252)."|FF0000");
foreach (array_keys($_POST) as $str_key)
{
	$sw_break = true;
	switch ($str_key)
	{
	    case "user_lang_s"		: AfterLangChoice($dbh, "user_lang_s", "user_lang", $sw_break); break;
		case "sys_exit"			: ExitSession(); break;
		case "coding_table"		: $_SESSION['language_messages'] = array(); header("Location: ../Codings/CodingForm.php"); break;
		case "interface_texts"	: GoToTitles($dbh); header("Location: ../Titles/TitleForm.php"); break;
		case "languages"		: header("Location: ../Languages/LanguageForm.php"); break;
		case "translate_table"	: header("Location: ../LocalLanguages/LocalForm.php"); break;
		case "user_ident"		: header("Location: ../UserList/UserListForm.php"); break;
		case "visits"			: header("Location: ../Visits/VisitForm.php"); break;
		case "db_list"			: $_SESSION['lang_param'] = ReadLanguages($dbh, true); header("Location: ../DataBases/DataBasesForm.php"); break;
		case "db_s_configs"		: $_SESSION['old_number_warn'] = $_SESSION['number_warn']; $_SESSION['common_config'] = true; $_SESSION['config_list'] = GetAllConfigs($dbh, "db_s_configs"); header("Location: ../Configuration/ConfigForm.php"); break;
		case "algorithms"	    : header("Location: ../Algorithms/AlgorithmForm.php"); break;
		default					: $sw_break = false;
	}
	if ($sw_break) break;
}
if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
if (count($Mes) > 0) foreach ($Mes as $z) echo "<div><font color='#FF0000'><b>".$z."</b></font></div>";
?>
<form method="post" id="alarm_form" name="alarm_form">
	<table width="100%">
        <tr>
            <td width="20%"><button name='sys_exit' type='submit' value='*' class='exit_button'><?php echo Title(8);?></button></td>
            <td width="70%"></td>
            <td width="10%"><?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on");?></td>
	    </tr>
    </table>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr><td><font class="db_l"><?php echo Title(220);?></font></td></tr>
		<tr><td><button name="coding_table" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo FTM(Title(281), true);?><?php echo SysTableError("coding_table");?></button></td></tr>
		<tr><td><button name="languages" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(124);?><?php echo SysTableError("languages");?></button></td></tr>
		<tr><td><button name="db_s_configs" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(130);?><?php echo SysTableError("db_s_configs");?></button></td></tr>
		<tr><td><button name="db_list" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(127);?><?php echo SysTableError("db_list");?></button></td></tr>
		<tr><td><button name="interface_texts" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(128);?><?php echo SysTableError("interface_texts");?></button></td></tr>
		<tr><td><button name="translate_table" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(125);?><?php echo SysTableError("translate_table");?></button></td></tr>
		<tr><td><button name="user_ident" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(12);?><?php echo SysTableError("user_ident");?></button></td></tr>
		<tr><td><button name="visits" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(167);?><?php echo SysTableError("visits");?></button></td></tr>
        <tr><td><button name="algorithms" type="submit" value="*" class="act_b" <?php echo OnMouseOver("00FF00", "0033FF");?>><?php echo Title(615);?><?php echo SysTableError("algorithms");?></button></td></tr>
	</table>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<?php if (IsInvalidReferences()) require_once("ReferenceAlarmHTML.php"); ?>
</form>

