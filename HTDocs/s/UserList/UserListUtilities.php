<?php
function ChangeUserListScreen($dbh, $offs)
{
	$mes_screen = "";
	if (gettype($offs) == "integer")
	{
		if ($offs == 1)
		{
			$_SESSION['portion']++;
			SetUserFilterPosition($dbh);
			GetUserListPortion($dbh);
		}
		else
		{
			if ($_SESSION['portion'] > count($_SESSION['user_list'])) $_SESSION['portion']--;
			elseif ($_SESSION['portion'] == 1) $mes_screen = "<font color='#FF0000'>".Title(178)."</font>";
			else
			{
				end($_SESSION['user_list']);
				if (PermitRowAction(key($_SESSION['user_list'])))
				{
					if (count($_SESSION['user_list']) == $_SESSION['portion']) array_pop($_SESSION['user_list']);
					$_SESSION['portion']--;
				}
				else $mes_screen = "<font color='#FF0000'>".Title(237)." ".Title(471)."</font>";
			}
		}
	}
	else
	{
		if (!is_numeric($offs)) $mes_screen = "<font color='#FF0000'>".Title(177)." ".Title(77)."</font>";
		elseif ((integer)$offs < 1 || strpos($offs, "-") !== false || strpos($offs, ".") != false) $mes_screen = "<font color='#FF0000'>".Title(177)." ".Title(512)." (".Title(299)." <b>".$offs."</b>)</font>";
		elseif ((integer)$offs > count($_SESSION['user_list']))
		{
			$old = $_SESSION['portion'];
			$_SESSION['portion'] = (integer)$offs;
			if (count($_SESSION['user_list']) >=  $old)
            {
                SetUserFilterPosition($dbh);
                GetUserListPortion($dbh);
            }
		}
		elseif ($_SESSION['alarm']) SetAlarmUserListPortionSize((integer)$offs);
        else $mes_screen = SetUserListPortionSize((integer)$offs);
	}
	foreach ($_SESSION['user_list'] as $k => $v) TestUser($k, $v);
	return $mes_screen;
}
function ShowUserCategoriesCounts()
{
	foreach ($_SESSION['categories'] as $k => $v)
	{
		echo "<td>";
            echo $v[0]." <b>";
            if ($k != "invalid")
            {
                echo (string)$v[3]."</b>";
                if ((integer)$v[4] > 0) echo " (<b><font color='#FF0000'>".$v[4]."</font></b>)";
            }
            else
            {
                if ($v[3] == 0) echo "0</b>";
                else echo "<font color='#FF0000'>".(string)$v[3]."</font></b>";
            }
		echo "</td>";
		echo "<td><font color='#FFFFFF'>XX</font></td>";
	}
}
function UserListExit($dbh, &$Mes)
{
	if (is_numeric($_POST['user_height']) && (integer)$_POST['user_height'] > 0) SaveUserScreenPortion($dbh, $_POST['user_height']);
	if ($_SESSION['alarm'])
	{
		UserSysTableCheck($dbh);
		if (isset($_SESSION['pre_ref']['visits']))
		{
            unset($_SESSION['pre_ref']['visits']);
            SetReferenceTable($dbh, "visits", 167, QueryReferencesVisits(), array(array("db", 0, 1, 545, 544), array("ui", 1, 0, 544, 545)), $_SESSION['pre_ref']);
            ksort($_SESSION['pre_ref']);
        }
        return "../Alarm/CommonAlarmForm";
	}
    return "../Administrator/MainForm";
}
function UserDataColor($k, $err_class, $norm_class, $flag_num)
{
    if ($_SESSION['user_flag'][$k][$flag_num]) return " class='".$err_class."'";
    if (isset($_SESSION['update_replace'][$k][$flag_num]) && $_SESSION['update_replace'][$k][$flag_num]) return " class='".$err_class."'";
    if (isset($_SESSION['update_double'][$k][$flag_num]) && $_SESSION['update_double'][$k][$flag_num] != "") return " class='".$err_class."'";
    return ($norm_class == "") ? "" : " class='".$norm_class."'";
}
function UserButtonColor($k, $unhide_img, $no_err_img, $err_img)
{
    if ($_SESSION['user_info'] == $k && $_SESSION['user_info'] != "") return $unhide_img;
    if (UserErrorsFree($k, array(7, 8, 9), array(), 4)) return $no_err_img;
    return $err_img;
}
function PermitRowAction($k)
{
	if ($_SESSION['alarm'])
	{
		if ($_SESSION['user_delete'][0] != "") return false;
		return true;
	}
	if (!UserErrorsFree($k, array(), array(1, 2))) return false;
	return true;
}
function DefUserCategories()
{
	$user_categories['all'] = array(Title(169), 0, 99, 0, 0);
	$user_categories['admins'] = array(Title(135), 11, 99, 0, 0);
	$user_categories['users'] = array(Title(136), 1, 10, 0, 0);
	$user_categories['guests'] = array(Title(137), 0, 0, 0, 0);
	$user_categories['invalid'] = array(Title(232), -1, -1, 0, 0);
	return $user_categories;
}
function StringFieldErrors($k, $flag_num, &$arr, $double = true)
{
    if ($_SESSION['user_list'][$k][2] == 0 && $flag_num == 1) $arr[] = Title(301);
	if ($_SESSION['user_flag'][$k][$flag_num]) $arr[] = Title(31).(($double && $_SESSION['user_list'][$k][2] == 0) ? " (".Title(618).")" : "");
    if (isset($_SESSION['update_replace'][$k][$flag_num]) && $_SESSION['update_replace'][$k][$flag_num]) $arr[] = Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'];
    if ($double && isset($_SESSION['update_double'][$k][$flag_num]) && $_SESSION['update_double'][$k][$flag_num] != "") $arr[] = Title(430);
}
function GetDataTitle($k, $flag_num)
{
	$arr = array();
	switch ($flag_num)
	{
		case 0: if ($_SESSION['user_flag'][$k][0]) $arr[] = Title(171); break;
		case 1: case 2: StringFieldErrors($k, $flag_num, $arr); break;
		case 3: if ($_SESSION['user_flag'][$k][$flag_num]) $arr[] = FTM(Title(232)); break;
		case 4: if ($_SESSION['user_flag'][$k][$flag_num]) $arr[] = Title(546); break;
		case 5: if ($_SESSION['user_flag'][$k][$flag_num]) $arr[] = Title(547); break;
		case 6: if ($_SESSION['user_flag'][$k][$flag_num]) $arr[] = Title(548); break;
		case 7: StringFieldErrors($k, $flag_num, $arr, false); break;
		case 8: if ($_SESSION['user_flag'][$k][$flag_num]) $arr[] = Title(600)." ".chr(34).Title(49).chr(34); break;
        case 9: if ($_SESSION['user_flag'][$k][$flag_num]) $arr[] = Title(600)." ".chr(34).Title(595).chr(34); break;
	}
	return implode("; ", $arr);
}
function HideUnhideUserInfo($k)
{
    if ($_SESSION['user_info'] == "") $_SESSION['user_info'] = $k;
    else
    {
        if ($k == $_SESSION['user_info']) $_SESSION['user_info'] = "";
        else $_SESSION['user_info'] = $k;
    }
    if ($k != $_SESSION['edit_user']) $_SESSION['edit_user'] = "";
}
function AfterUserListLangChoice($dbh, &$sw_break)
{
    $fl = false;
	if (AfterLangChoice($dbh, "user_lang_s", "user_lang", $sw_break))
	{
 	    $_SESSION['list_db']['nu'] = "--".Title(574)."--";
 	    ChangeUserListCategory();
 	    $fl = true;
 	}
	return $fl;
}
function AfterCategoryFilterChoice($dbh, &$sw_break)
{
	if ($_POST['filter_category_s'] == "") $sw_break = false;
    else UserFilter($dbh);
}
function AfterErrorsFilterChoice($dbh, &$Mes, &$sw_break)
{
	if ($_POST['list_errors_s'] == "")
    {
        $sw_break = false;
        return false;
    }
    $_SESSION['filter_error'] = !$_SESSION['filter_error'];
    $_SESSION['inv_ref'] = false;
    $_SESSION['user_info'] = "";
    return true;
}
function UserCheckBox($k, $v_check, $check_prefix, $flag_num, $t_user)
{
    echo "<input type='checkbox' name='".$check_prefix."|".$k."' title='".GetDataTitle($k, $flag_num)."' value='*'".$t_user.(($v_check == 1) ? " checked" : "").">";
    if ($_SESSION['update_replace'][$k][$flag_num] != "") echo "<font color='#FFFFFF'>:</font><font color='#FF0000'><b>".$_SESSION['update_replace'][$k][$flag_num]."</b></font>";
}
function GetUserNameArray()
{
    $name_arr = array();
    foreach ($_SESSION['user_list'] as $v) $name_arr[] = $v[0];
    return $name_arr;
}
function UserListInitial($dbh)
{
    $_SESSION['double_quote_fix'] = false;
    $_SESSION['categories'] = DefUserCategories();
	$_SESSION['start'] = 0;
	$_SESSION['filter_name'] = "";
	$_SESSION['filter_id'] = "";
	$_SESSION['filter_error'] = false;
	$_SESSION['category'] = array("all", Title(169));
	$_SESSION['user_filter'] = "";
	$_SESSION['user_size'] = UserListSize($dbh);
	GetUserListPortion($dbh);
	GetUserCounts($dbh);
	$_SESSION['edit_user'] = "";
	$_SESSION['inv_ref'] = false;
	$_SESSION['user_info'] = "";
	$_SESSION['user_delete'] = array("", "", array());
	$_SESSION['list_db'] = SetDBNameArray("--".Title(574)."--");
    SetInitReplaceIDs("user_ident", array("li"=>"user_langs", "db"=>"list_db"));
    foreach ($_SESSION['user_list'] as $k => $v) TestUser($k, $v);
}
function UserListMessages($mes_screen, $edit_mes, $Mes)
{
    echo "<table>";
        if ($mes_screen != "") echo "<tr valign='top'><td>".$mes_screen."</td></tr>";
        if (count($edit_mes) > 0) echo "<tr valign='top'><td>".ViewEditMessages($edit_mes)."</td></tr>";
        if (count($Mes) > 0) foreach ($Mes as $z) echo "<tr valign='top'><td>".$z."</td></tr>";
	echo "</table>";
}
?>
