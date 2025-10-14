<?php
function UserGuest($dbh_sys)
{
    mysqli_query($dbh_sys, "ALTER TABLE user_ident AUTO_INCREMENT = 1");
	mysqli_query($dbh_sys, "INSERT INTO user_ident (user_priority,use_lang_id) VALUES (0,".(string)$_SESSION['user_lang'][0].")");
	$_SESSION['user_id'] = GetAutoIncrement($dbh_sys);
	mysqli_query($dbh_sys, "UPDATE user_ident SET name = '_".$_SESSION['user_id']."',pass =  '#####".$_SESSION['user_id']."' WHERE id_user = ".$_SESSION['user_id']);
	$_SESSION['priority'] = 0;
	$_SESSION['portion'] = 10;
	$_SESSION['date_format'] = ".dmy";
	$_SESSION['hide_list'] = false;
	$_SESSION['match_case'] = false;
	return SessionPreparation($dbh_sys, "");
}
function UserLogin($dbh_sys, &$Mes)
{
    $u_data = array("id" => 0, "db" => "");
	$_SESSION['try_count'] = (integer)$_POST['try_count'] + 1;
	if (!$_SESSION['registration'] && $_SESSION['try_count'] > $_SESSION['try_limit']) ExitSession(Title(9)."|FF0000");
	elseif (TestLoginFill($Mes)) $u_data = GetUserData($dbh_sys, $Mes);
	if ($u_data['id'] > 0)
	{
        $_SESSION['user_id'] = (string)$u_data['id'];
        if ($_SESSION['alarm'] && $_SESSION['priority'] < 99) ExitSession(Title(610).". ".Title(613).".");
        else return SessionPreparation($dbh_sys, $u_data['db']);
	}
	elseif ($u_data['id'] == 0) return "";
	else ExitSession();
}
function TestLoginFill(&$Mes)
{
	if ($_SESSION['registration'])
	{
		if ($_POST['user_password'] == "" && $_POST['user_password_d'] == "")
		{
			if ($_POST['user_name'] == "") $Mes[] = Title(122);
			else $Mes[] = Title(123);
		}
		elseif ($_POST['user_password'] != $_POST['user_password_d']) $Mes[] = Title(118);
	}
	else
	{
		if ($_POST['user_name'] == "" && $_POST['user_password'] == "") $Mes[] = Title(2);
		elseif ($_POST['user_name'] == "" && $_POST['user_password'] != "") $Mes[] = Title(3);
		elseif ($_POST['user_name'] != "" && $_POST['user_password'] == "") $Mes[] = Title(4);
	}
	if (count($Mes) == 0)
	{
		$fl_name = TestUserName($_POST['user_name']);
		$fl_pass = TestUserPassword($_POST['user_password']);
		if (!$fl_name && $fl_pass) $Mes[] = FTM(Title(142));
		elseif ($fl_name && !$fl_pass) $Mes[] = FTM(Title(143));
		elseif (!$fl_name && !$fl_pass) $Mes[] = Title(144);
	}
	return (count($Mes) == 0);
}
function TestUserName($user_data)
{
	if (strlen($user_data) < 3) return false;
	$arr_str = str_split($user_data);
	for ($i = 0; $i < count($arr_str); $i++)
	{
		if (ord($arr_str[$i]) > 32 && ord($arr_str[$i]) < 48) return false;
		if (ord($arr_str[$i]) > 57 && ord($arr_str[$i]) < 97) return false;
		if (ord($arr_str[$i]) > 122) return false;
	}
	return true;
}
function TestUserPassword($user_data)
{
	if (strlen($user_data) < 6 || strlen($user_data) > 12) return false;
	$arr_str = str_split($user_data);
	for ($i = 0; $i < count($arr_str); $i++)
	{
		if (ord($arr_str[$i]) < 33) return false;
		if (ord($arr_str[$i]) == 34 || ord($arr_str[$i]) == 39) return false;
		if (ord($arr_str[$i]) > 126) return false;
	}
	return true;
}
function SetRegistration($dbh_sys, &$Mes)
{
	$res = mysqli_query($dbh_sys, "SELECT * FROM user_ident WHERE name = '".$_POST['user_name']."' OR pass = '".$_POST['user_password']."'");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
			if ($row[1] == $_POST['user_name'] && $row[2] == $_POST['user_password']) {$Mes[] = Title(117); break;}
			if ($row[1] == $_POST['user_name'] && $row[2] != $_POST['user_password']) {$Mes[] = Title(113)." ".Title(587); break;}
			if ($row[1] != $_POST['user_name'] && $row[2] == $_POST['user_password']) {$Mes[] = Title(114)." ".Title(587); break;}
		}
		mysqli_free_result($res);
		return 0;
	}
	if (count($Mes) == 0)
	{
        mysqli_query($dbh_sys, "ALTER TABLE user_ident AUTO_INCREMENT = 1");
		mysqli_query($dbh_sys, "INSERT INTO user_ident (name,pass,user_priority,use_lang_id) VALUES ('".$_POST['user_name']."','".$_POST['user_password']."',1,".(string)$_SESSION['user_lang'][0].")");
		$r = GetAutoIncrement($dbh_sys);
		$_SESSION['user_id'] = $r;
		$_SESSION['priority'] = 1;
		$_SESSION['portion'] = 10;
		$_SESSION['date_format'] = ".dmy";
		return (integer)$r;
	}
}
function GetUserData($dbh_sys, &$Mes)
{
	$u_data = array("id" => 0, "db" => "");
	if ($_SESSION['registration']) $id = SetRegistration($dbh_sys, $Mes);
	else
	{
        $res = mysqli_query($dbh_sys, "SELECT * FROM user_ident WHERE name = '".$_POST['user_name']."' AND pass = '".$_POST['user_password']."'");
		if ($res)
		{
			if ($row = mysqli_fetch_row($res))
			{
                $u_data = SetSessionParameters($dbh_sys, $row);
			}
			else $Mes[] = Title(121);
			mysqli_free_result($res);
		}
        else $Mes[] = Title(121);
	}
	return $u_data;
}
function SetSessionParameters($dbh_sys, $row)
{
	$_SESSION['priority'] = $row[3];
	if ($_SESSION['user_lang'][0] != $row[4])
    {
        if (isset($_SESSION['user_langs'][$row[4]]))
        {
            $_SESSION['user_lang'] = array($row[4], $_SESSION['user_langs'][$row[4]]);
            $_SESSION['titles'] = GetTitlesByLanguage($dbh_sys, (integer)$_SESSION['user_lang'][0]);
         }
    }
	$_SESSION['portion'] = $row[5];
    if (is_null($row[6])) $db = "";
    else $db = $row[6];
	$_SESSION['date_format'] = $row[7];
	$_SESSION['hide_list'] = ($row[8] == 1);
	$_SESSION['match_case'] = ($row[9] == 1);
	return array("id" => $row[0], "db" => $db);
}
function SessionFromPost()
{
	$_SESSION['login']['name'] = $_POST['user_name'];
	$_SESSION['login']['password'] = $_POST['user_password'];
	if (!$_SESSION['alarm'])
	{
    	if (isset($_POST['user_password_d'])) $_SESSION['login']['password_d'] = $_POST['user_password_d'];
	    else $_SESSION['login']['password_d'] = "";
    }
    if (isset($_POST['user_lang']))
    {
		$lang_key = array_search($_POST['user_lang'], $_SESSION['user_langs']);
		if ($lang_key !== false) $_SESSION['user_lang'] = array($lang_key, $_POST['user_lang']);
    }
    else $_SESSION['user_lang'] = array(1, "English");
}
function SessionPreparation($dbh_sys, $db)
{
    SessionSettings($dbh_sys);
    if ($_SESSION['alarm'])
    {
        VisitParameters($dbh_sys, 0, 1, 99);
        return "../Alarm/CommonAlarmForm.php";
    }
    if ($_SESSION['priority'] == 99)
    {
        $_SESSION['user_working_mode'] = 1;
        VisitParameters($dbh_sys, 0, $_SESSION['user_working_mode'], $_SESSION['priority']);
        return "../Administrator/MainForm.php";
    }
    if ($db == "") return "../Administrator/MainForm.php";
    return GoToDB($dbh_sys, $db);
}
function SessionSettings($dbh_sys)
{
    $_SESSION['set_pad'] = false;
	$_SESSION['compare_img'] = array("AtBegin", "AnyWhere", "Exact");
	$_SESSION['mes'] = array("0"=>array(), "1"=>array(), "c"=>array(), "m"=>array(), "u"=>array());
	$_SESSION['compare_mode'] = GetSpecialTexts($dbh_sys, "compare_mode");
	$_SESSION['field_align'] = GetSpecialTexts($dbh_sys, "field_align");
	$_SESSION['field_types'] = GetSpecialTexts($dbh_sys, "field_types");
    $_SESSION['field_using'] = GetSpecialTexts($dbh_sys, "field_using");
    $_SESSION['group_types'] = GetSpecialTexts($dbh_sys, "group_types");
    $_SESSION['sort_mode'] = GetSpecialTexts($dbh_sys, "sort_mode");
    $_SESSION['table_types'] = GetSpecialTexts($dbh_sys, "table_types");
}
function AdminSession($dbh_sys)
{
	VisitParameters($dbh_sys, 0, $_SESSION['user_working_mode'], $_SESSION['priority']);
	$_SESSION['category'] = array("all", Title(169));
	$_SESSION['admin_mes'] = SetWorkingMode($dbh_sys, 0, $_SESSION['user_working_mode']);
	if ($_SESSION['alarm']) return "../Alarm/CommonAlarmForm.php";
    else return "../Administrator/MainForm.php";
}
//*********************
function UserAlarmLogin($dbh_sys, &$Mes)
{
	$id = 0;
	$_SESSION['try_count'] = (integer)$_POST['try_count'] + 1;
	if ($_SESSION['try_count'] > $_SESSION['try_limit']) ExitSession("You have exceeded maximum number of login attempts");
	elseif (TestAlarmLoginFill($Mes)) $id = GetAlarmUserData($dbh_sys, $Mes);
	if ($id > 0)
	{
        $_SESSION['user_id'] = (string)$id;
        if ($_SESSION['priority'] != 99) ExitSession("Errors were found in the system tables. Contact system administrator.");
        else return SessionAlarmPreparation($dbh_sys);
	}
	elseif ($id == 0) return "";
	else ExitSession();
}
function TestAlarmLoginFill(&$Mes)
{
	if ($_POST['user_name'] == "" && $_POST['user_password'] == "") $Mes[] = "You didn`t fill in user name and password";
    elseif ($_POST['user_name'] == "" && $_POST['user_password'] != "") $Mes[] = "You didn`t fill in user name";
	elseif ($_POST['user_name'] != "" && $_POST['user_password'] == "") $Mes[] = "You didn`t fill in password";
	if (count($Mes) == 0)
	{
		$fl_name = TestUserName($_POST['user_name']);
		$fl_pass = TestUserPassword($_POST['user_password']);
		if (!$fl_name && $fl_pass) $Mes[] = "User name rules violated";
		elseif ($fl_name && !$fl_pass) $Mes[] = "Password rules violated";
		elseif (!$fl_name && !$fl_pass) $Mes[] = "user name and password rules violated";
	}
	return (count($Mes) == 0);
}
function GetAlarmUserData($dbh_sys, &$Mes)
{
	$id = 0;
    $res = mysqli_query($dbh_sys, "SELECT * FROM user_ident WHERE name = '".$_POST['user_name']."' AND pass = '".$_POST['user_password']."'");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
 	        $_SESSION['priority'] = $row[3];
	        $_SESSION['portion'] = $row[5];
	        $id = $row[0];
		}
		else $Mes[] = "User name and(or) password You entered is incorrect";
	}
    else $Mes[] = "User name and(or) password You entered is incorrect";
    mysqli_free_result($res);
	return $id;
}
function SessionAlarmPreparation($dbh_sys)
{
    unset($_SESSION['try_limit'], $_SESSION['login'], $_SESSION['try_count']);
	VisitParameters($dbh_sys, 0, 1, 99);
    return "../Alarm/ErrorsOfStructure.php";
}

?>
