<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.form_exit {background-color:#FFCC00;}</style>
	<style>.form_save {background-color:#CCFF00;}</style>
	<style>.invisible_row {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.reg_trial_button {color:#FFFFFF; font-size: 150%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(56,81,202) linear-gradient(rgb(56,81,202), rgb(55,180,202));}</style>
	<style>.hidden_button {visibility:hidden;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.login_form.user_lang_s.value = '*'; login_form.submit();}</SCRIPT>
<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Administrator/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmDBUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmManagerUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmReferences.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/ManagerDBCreate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/UserDBCreate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DatabaseTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DatabaseUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Fields/FieldEditUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Fields/FieldUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/SpecialTextsUtils.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Calendar.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once("UserEnterUtilities.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000", "0");
$Mes = array();
if (count($_POST) == 0)
{
	$_SESSION['user_working_mode'] = ($_SESSION['alarm']) ? 1 : 0;
	$_SESSION['registration'] = false;
	$_SESSION['admin_mes'] = array(0, "");
    $_SESSION['login'] = array("name"=>"", "password"=>"", "password_d"=>"");
	$_SESSION['try_count'] = 0;
}
else SessionFromPost();
if (!$_SESSION['registration'] && $_SESSION['try_count'] > $_SESSION['try_limit']) ExitSession();
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	switch ($str_key)
	{
		case "user_lang_s"				: AfterLangChoice($dbh_sys, "user_lang_s", "user_lang", $sw_break); break;
		case "register_OK"				: $sw_break = false; $_SESSION['registration'] = true; $_SESSION['try_count'] = 0; break;
		case "login_OK": case "log_OK"	: $sw_break = false; $goto = UserLogin($dbh_sys, $Mes); if ($goto != "") header("Location: ".$goto); break;
		case "trial_OK"					: $goto = UserGuest($dbh_sys); if ($goto != "") header("Location: ".$goto); break;
		case "user_exit"				: ExitSession(); break;
		default							: $sw_break = false;
	}
	if ($sw_break) break;
}
?>
<form method="post" id="login_form" name="login_form">
	<button name="log_OK" type="submit" value="*" class="hidden_button"></button>
	<table width="100%"><tr><?php echo "<td width='20%'>".SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on")."</td>"; ?></tr></table>
    <?php
    if (count($_SESSION['structure_errors']) > 0 || $_SESSION['alarm']) require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/SystemTitleErrors.php");
    else require_once("LoginButtons.php");
    ?>
	<table width="100%">
		<tr>
			<td width="20%"></td>
			<td width="60%">
				<table align="center">
					<tr>
						<td>
							<table><tr><td><font size="4"><?php echo Title(10);?>:</font></td></tr></table>
							<table bgcolor="#CCFFFF" frame="border">
								<tr><td><b><?php echo Title(6);?></b></td><td><input autofocus name="user_name" type="text" value="<?php echo $_SESSION['login']['name'];?>"></td></tr>
								<tr><td><b><?php echo Title(7);?></b></td><td><input name="user_password" type="password" value="<?php echo $_SESSION['login']['password'];?>"></td></tr>
								<?php if ($_SESSION['registration']) echo "<tr><td><b>".Title(112)."</b></td><td><input name='user_password_d' type='password' value='".$_SESSION['login']['password_d']."'></td></tr>";?>
							</table>
							<table>
								<tr>
									<td><button name="login_OK" type="submit" class="form_save" value="*">OK</button></td>
									<td><button name="user_exit" type="submit" class="form_exit" value="*"><?php echo Title(8);?></button></td>
									<td><input name="try_count" type="hidden" value="<?php echo (string)$_SESSION['try_count'];?>" /></td>
								</tr>
							</table>
							<table><tr><td><?php echo Title(11)." <b>".(string)$_SESSION['try_count']."</b>";?></td></tr></table>
							<?php
                            if (isset($_POST['login_OK']) || isset($_POST['log_OK']))
                            {
                                echo "<table>";
                                    for ($i = 0; $i < count($Mes); $i++) echo "<tr><td><font color='#FF0000'><b>".$Mes[$i]."</b></font></td></tr>";
                                echo "</table>";
                            }
                            ?>
							<table><tr><td><font class="invisible_row">X</font></td></tr></table>
							<?php require_once("HTMLRules.php");?>
						</td>
					</tr>
				</table>
			</td>
			<td width="20%"></td>
		</tr>
	</table>
</form>
