<head><style>.mess_exit {color:#0000FF; font-weight:bold;}</style></head>
<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

function UserActivityCheck($dbh, $db_name)
{
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM userlist WHERE user_active = 1");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res)) $cnt = $row[0];
		else $cnt = 0;
		mysqli_free_result($res);
		if ($cnt > 0) return "Now ".(string)$cnt." users are working on the site with database <b>".$db_name."</b>. Technical work cannot be carried out.";
		else return "";
	}
	return "";
}
/*function ExitFromViewMode($dbh, $u_id, $db_info)
{
	$pr = 0;
	$res = mysqli_query($dbh, "SELECT user_priority FROM userlist WHERE id_user = ".$_SESSION['user_id']." LIMIT 1");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res)) $pr = $row[0];
		mysqli_free_result($res);
	}
	if ($pr == 0) DeleteGuest($dbh, $_SESSION['user_id'], $db_info);
	else mysqli_query($dbh, "UPDATE userlist SET ".ResetUserSettings()." WHERE id_user = ".(string)$_SESSION['user_id']);
	DeleteFieldsSettings($dbh, $_SESSION['user_id']);
	exit("<div align='center' class='mess_exit'>Session completed</div>");
}*/
/*function GetUpdateFlag($dbh)
{
	$uflag = false;
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM userlist WHERE user_active = 1 AND user_priority > 10");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res)) $uflag = ($row[0] > 0);
		mysqli_free_result($res);
	}
	return $uflag;
}*/
function SetUpdateFlag($dbh, $flag)
{
	$ff = "";
	$res = mysqli_query($dbh, "SELECT user_active FROM userlist WHERE user_priority = 0 LIMIT 1");
	if (mysqli_errno($dbh) > 0) $ff = mysqli_error($dbh);
	if ($res)
	{
		$row = mysqli_fetch_row($res);
		if (isset($row[0]))
		{
			 mysqli_query($dbh, "UPDATE userlist SET user_active = ".(string)$flag." WHERE user_priority = 0");
			 if (mysqli_errno($dbh) > 0) $ff = mysqli_error($dbh);
		}
		else
		{
			mysqli_query($dbh, "INSERT INTO userlist (name,pass,user_priority,user_active) VALUES ('','',0,".(string)$flag.")");
			if (mysqli_errno($dbh) > 0) $ff = mysqli_error($dbh);
		}
		mysqli_free_result($res);
	}
	else 
	{
		mysqli_query($dbh, "INSERT INTO userlist (name,pass,user_priority,user_active) VALUES ('','',0,".(string)$flag.")");
		if (mysqli_errno($dbh) > 0) $ff = mysqli_error($dbh);
	}
	return $ff;
}
?>
