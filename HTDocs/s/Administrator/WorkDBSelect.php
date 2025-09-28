<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.exit_button {color:#FFFFFF; font-size:120%; font-weight:700; border:1px solid rgb(250,172,17); border-radius:7px; background:rgb(56,81,202) linear-gradient(rgb(56,81,202), rgb(55,180,202));}</style>
	<style>.invisible_row {color:#FFFFFF; background-color:#FFFFFF;}</style>
	<style>.label_button {color:#0033FF; font-size:150%; font-weight:700; background-color:#FFFFFF; border:none;}</style>
	<style>.label_title {color:#000000; font-size:200%; font-weight:800; background-color:#FFFFFF; border:none;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.work_db_select.user_lang_s.value = '*'; work_db_select.submit();}</SCRIPT>
<?php
require_once("Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserList/UserListUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserList/UserListTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserEnter/UserEnterUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Fields/FieldUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/UserDBcreate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmDBUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmManagerUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableTest.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$Mes = array();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000");
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	$arr_key = explode("-", $str_key);
	switch ($arr_key[0])
	{
		case "user_lang_s"			: AfterLangChoice($dbh_sys, "user_lang_s", "user_lang", $sw_break); break;
		case "work_db_select_exit"	: DBSelectExit(); break;
		case "db_list"				: $goto = GoToPrefferedDB($dbh_sys, $arr_key[1], $Mes); if ($goto != "") header("Location: ".$goto.".php"); break;
		default						: $sw_break = false;
	}
	if ($sw_break) break;
}
if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
if (count($_SESSION['db_pre_flags']['no_existed_tables']) > 0) echo "<div align='center'><h3><b>".Title(632)." ".Title(613)." ".Title(631).".</b></h3></div>";
if (count($Mes) > 0) foreach ($Mes as $z) echo "<br><b>".$z."</b>";
?>
<form method="post" id="work_db_select" name="work_db_select">
	<table width="100%">
		<tr>
			<td width="20%"><?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on");?></td>
			<td width="60%"></td>
			<td width="20%" align="right"><button name="work_db_select_exit" type="submit" value="*" class="exit_button"><?php echo Title(8);?></button></td>
		</tr>
		<tr><td width="20%"><font class="invisible_row">X</font></td><td width="60%"></td><td width="20%"></td></tr>
		<tr><td width="20%"><font class="invisible_row">X</font></td><td width="60%"></td><td width="20%"></td></tr>
		<tr>
			<td width="20%"></td>
			<td width="60%"><font class="label_title"><?php echo Title(127).":";?></font></td>
			<td width="20%"></td>
		</tr>
		<tr><td width="20%"><font class="invisible_row">X</font></td><td width="60%"></td><td width="20%"></td></tr>
		<?php
		foreach ($_SESSION['arr_db'] as $k => $v)
		{
            if ($k > 0)
            {
		        echo "<tr valign='top'>";
			        echo "<td width='20%'></td>";
                    echo "<td width='60%'><button name='db_list-".(string)$k."' type='submit' value='*' class='label_button' ".OnMouseOver("00FF00", "0033FF").">".$v['db_comment']."</button></td>";
			        echo "<td width='20%'></td>";
                echo "</tr>";
			}
		}
		?>
	</table>
<form>
