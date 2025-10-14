<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.save_button {background-color:#CCFF00;}</style>
	<style>.add_button {background-color:#00FF00;}</style>
	<style>.del_button {background-color:#CCCCFF;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
	<style>.i_r {background-color:#01CCFF;}</style>
	<style>.invisible_text {background-color:#FFFFFF; color:#FFFFFF;}</style>
	<style>.data_numeric {text-align:right;}</style>
	<style>.data_numeric_err {text-align:right; background-color:#CCCCFF;}</style>
	<style>.i_h {background-color:#FFFFFF;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.data_bold {font-weight:bold;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.data_base_form.user_lang_s.value = '*'; data_base_form.submit();}</SCRIPT>

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Administrator/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmManagerUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmReferences.php");
require_once("DataBaseTest.php");
require_once("DataBaseUpdate.php");
require_once("DataBaseUtilities.php");
require_once("ManagerDBCreate.php");


require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$dbh = GetOnlyDB("db_manager");
if (!$dbh) ExitSession(Title(252)."|FF0000");
$Mes = array();
$_SESSION['db_server'] = GetServerDatabases("db_manager");
if (count($_POST) == 0)
{
	$_SESSION['db_sel'] = "";
	$_SESSION['del_ref'] = array();
	$_SESSION['server_table'] = false;
	TestDBList($Mes);
}
else SaveDBPost();
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	$a = explode("|", $str_key);
	$s_k = (count($a) == 1) ? $str_key : $a[0];
	switch ($s_k)
	{
        case "idle_button"      : break;
		case "user_lang_s"		: TestDBList($Mes); AfterLangChoice($dbh, "user_lang_s", "user_lang", $sw_break); break;
		case "DB_exit"			: $goto = DataBaseListExit($dbh, $Mes); if ($goto != "") header("Location: ".$goto.".php"); break;
		case "DB_save"			: RestDBValues(); if (TestDBList($Mes, (!$_SESSION['alarm']))) RewriteDB($dbh, $Mes); break;
		case "DB_new"			: RestDBValues(); $_SESSION['server_table'] = true; break;
		case "db_mark_del"		: RestDBValues(); SwitchDBDel($dbh, (integer)$a[1]); TestDBList($Mes); break;
		case "db_select"		: RestDBValues(); $_SESSION['server_table'] = true; $_SESSION['db_sel'] = $a[1]; break;
		case "server_db_select"	: SaveDBPost(); SelectDBServer((integer)$a[1]); $_SESSION['server_table'] = false; TestDBList($Mes); break;
		case "exit_server"	    : RestDBValues(); SaveDBPost(); $_SESSION['server_table'] = false; TestDBList($Mes); break;
		case "correct_id"	    : RestDBValues(); CorrectDBID($dbh, (integer)$a[1]); TestDBList($Mes); break;
		default					: $sw_break = false;
	}
	if ($sw_break) break;
}
if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
$f_exit = ($_SESSION['server_table'] == "") ? "" : " disabled";
$f_other = ($_SESSION['server_table'] == "" && $_SESSION['user_working_mode'] == 1) ? "" : " disabled";
?>
<form method="post" id="data_base_form" name="data_base_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
	<?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on");?>
	<div align="center"><font size="+2"><b><?php echo Title(255);?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td><button name="DB_exit" type="submit" title="<?php echo Title(263);?>" class="exit_button"<?php echo $f_exit;?>><?php echo Title(8);?></button></td>
			<?php
			if ($_SESSION['user_working_mode'] == 1)
            {
                echo "<td><button name='DB_save' type='submit' title='".Title(264)."' class='save_button'".$f_exit.">".Title(30)."</button></td>";
                if (count($_SESSION['db_server']) > 0) echo "<td><button name='DB_new' type='submit' class='add_button'".$f_other.">".Title(158)."</button></td>";
            }
            ?>
		</tr>
	</table>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<?php
	if (count($Mes) > 0)
	{
		for ($i = 0; $i < count($Mes); $i++) echo "<br>".$Mes[$i];
		echo "<hr align='left' size='1' noshade='noshade color='#000000' >";
	}
    if ($_SESSION['server_table']) require_once("DataBasesSelectTable.php");
	else require_once("DataBasesTable.php");
	?>
<form>
