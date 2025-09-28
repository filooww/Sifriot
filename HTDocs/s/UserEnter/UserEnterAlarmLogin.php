<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.form_exit {background-color:#FFCC00;}</style>
	<style>.form_save {background-color:#CCFF00;}</style>
	<style>.invisible_row {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
</head>
<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");
require_once("UserEnterUtilities.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession("No connection to system data base|FF0000", "0");
$Mes = array();
if (count($_POST) == 0)
{
	$_SESSION['user_working_mode'] = 1;
	$_SESSION['admin_mes'] = array(0, "");
	$_SESSION['login'] = array("name"=>"", "password"=>"", "password_d"=>"");
	$_SESSION['try_count'] = 0;
}
else SessionFromPost();
if ($_SESSION['try_count'] > $_SESSION['try_limit']) ExitSession();
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	switch ($str_key)
	{
		case "login_OK": case "log_OK"	: $sw_break = false; $goto = UserAlarmLogin($dbh_sys, $Mes); if ($goto != "" && count($Mes) == 0) header("Location: ".$goto); break;
		case "user_exit"				: ExitSession(); break;
		default							: $sw_break = false;
	}
	if ($sw_break) break;
}
?>
<form method="post" id="login_form" name="login_form">
	<button name="log_OK" type="submit" value="*" class="hidden_button"></button>
    <?php require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/SystemAlarmErrors.php"); ?>
	<table width="100%">
		<tr>
			<td width="20%"></td>
			<td width="60%">
				<table align="center">
					<tr>
						<td>
							<table><tr><td><font size="4">Login:</font></td></tr></table>
							<table bgcolor="#CCFFFF" frame="border">
								<tr><td><b>User name</b></td><td><input autofocus name="user_name" type="text" value="<?php echo $_SESSION['login']['name'];?>"></td></tr>
								<tr><td><b>User password</b></td><td><input name="user_password" type="password" value="<?php echo $_SESSION['login']['password'];?>"></td></tr>
							</table>
							<table>
								<tr>
									<td><button name="login_OK" type="submit" class="form_save" value="*">OK</button></td>
									<td><button name="user_exit" type="submit" class="form_exit" value="*">Exit</button></td>
									<td><input name="try_count" type="hidden" value="<?php echo (string)$_SESSION['try_count'];?>" /></td>
								</tr>
							</table>
							<table><tr><td>Try <b><?php echo (string)$_SESSION['try_count'];?></b></td></tr></table>
							<?php
							if (isset($_POST['login_OK']) || isset($_POST['log_OK']))
                            {
                                echo "<table>";
                                    for ($i = 0; $i < count($Mes); $i++) echo "<tr><td><font color='#FF0000'><b>".$Mes[$i]."</b></font></td></tr>";
                                echo "</table>";
                            }
                            ?>
							<table><tr><td><font class="invisible_row">X</font></td></tr></table>
							<?php require_once("HTMLNoTitleRules.php");?>
						</td>
					</tr>
				</table>
			</td>
			<td width="20%"></td>
		</tr>
	</table>
</form>
