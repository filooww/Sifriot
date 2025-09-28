<?php
function EditUser($dbh, $k_c, $v_c, $f_c, &$Mes)
{
    $edit_mes = array();
	if ($_SESSION['edit_user'] == "")
    {
        $_SESSION['edit_user'] = $k_c;
        $_SESSION['user_info'] = $k_c;
        $_SESSION['inv_ref'] = false;
        $_SESSION['user_delete'] = array("", $v_c[0], array());
    }
	else
	{
	    if ($_POST["user_login|".$k_c] == "") DeletePrepare($dbh, $k_c, $Mes);
	    else
	    {
            $old_err = !UserErrorsFree($k_c, array(1, 2, 7, 8, 9), array(1, 2));
		    ChangeUserParameters($k_c, $v_c);
 	        for ($i = 0; $i < 10; $i++) $_SESSION['user_flag'][$k_c][$i] = false;
		    TestUser($k_c, $_SESSION['user_list'][$k_c]);
            date_default_timezone_set("UTC");
            $_SESSION['update_double'][$k_c] = array(1=>"", 2=>"");
            $_SESSION['update_double'][$k_c][1] = TestDoubledUser($dbh, $k_c, 0, "name", $_POST["user_login|".$k_c]);
            $_SESSION['update_double'][$k_c][2] = TestDoubledUser($dbh, $k_c, 1, "pass", $_POST["user_password|".$k_c]);
		    $new_err = !UserErrorsFree($k_c, array(), array(1, 2));
		    $update_arr = UpdateUser($dbh, $k_c);
		    ChangeCategory($k_c, $v_c[2], $_SESSION['user_list'][$k_c][2], $old_err, $new_err);
            $edit_mes = UserEditMessage($k_c, $update_arr, $v_c[2]);
            ResetReplaceFlags($k_c, $update_arr['set_update']);
        }
        foreach ($_SESSION['user_list'] as $k => $v) if ($k != $k_c) TestUser($k, $v, true);
		$_SESSION['edit_user'] = "";
	}
	return $edit_mes;
}
function DeletePrepare($dbh, $k_c, &$Mes)
{
    $del_res = PendingSessions($dbh, $k_c);
    if (!$del_res['fl'])
    {
        $Mes[] = "<font color='#FF0000'>".Title(54)." <b>".$_SESSION['user_list'][$k_c][0]." (".$k_c.")</b> ".Title(426)."</font>. <font color='#0000FF'><b>".Title(187)."</b>.</font>";
        $_SESSION['user_info'] = "";
        $_SESSION['user_list'][$_SESSION['edit_user']][0] = $_SESSION['user_delete'][1];
        $_SESSION['user_delete'] = array("", "", array());
        $_SESSION['inv_ref'] = false;
    }
    else
    {
        $_SESSION['user_delete'][0] = $k_c;
        $_SESSION['user_delete'][2] = $del_res['arr'];
    }
}
function UpdateUser($dbh, $k)
{
	$update_arr = array("set_update"=>array(), "param_refs"=>array(), "param_titles"=>array());
	$res = mysqli_query($dbh, "SELECT * FROM user_ident WHERE id_user = ".$k);
	if ($res)
	{
		$order_change = false;
		if ($row = mysqli_fetch_row($res))
		{
			if (!$_SESSION['user_flag'][$k][1] && $_SESSION['update_double'][$k][1] == "" && $_SESSION['user_list'][$k][0] != $row[1])
            {
                SetUpdating($k, "name", "string", 0, 6, 1, $update_arr);
                $order_change = true;
            }
			if (!$_SESSION['user_flag'][$k][2] && $_SESSION['update_double'][$k][2] == "" && $_SESSION['user_list'][$k][1] != $row[2]) SetUpdating($k, "pass", "string", 1, 7, 2, $update_arr);
			if (!$_SESSION['user_flag'][$k][3] && $_SESSION['user_list'][$k][2] != $row[3]) SetUpdating($k, "user_priority", "integer", 2, 148, 3, $update_arr);
			if (!$_SESSION['user_flag'][$k][4] && $_SESSION['user_list'][$k][3] != $row[4]) SetUpdating($k, "use_lang_id", "integer", 3, 181, 4, $update_arr);
			if (!$_SESSION['user_flag'][$k][5] && $_SESSION['user_list'][$k][4] != $row[5]) SetUpdating($k, "user_list_portion", "integer", 4, 530, 5, $update_arr);
            if (!$_SESSION['user_flag'][$k][6] && NullFilter($row[6], $_SESSION['user_list'][$k][5])) SetUpdating($k, "preffered_db", "db", 5, 163, 6, $update_arr);
			if (!$_SESSION['user_flag'][$k][7] && $_SESSION['user_list'][$k][6] != $row[7]) SetUpdating($k, "date_format", "string", 6, 531, 7, $update_arr);
			if (!$_SESSION['user_flag'][$k][8] && $_SESSION['user_list'][$k][7] != $row[8]) SetUpdating($k, "hide_list", "check", 7, 49, 8, $update_arr);
			if (!$_SESSION['user_flag'][$k][9] && $_SESSION['user_list'][$k][8] != $row[9]) SetUpdating($k, "match_case", "check", 8, 595, 9, $update_arr);
		}
		mysqli_free_result($res);
		if (count($update_arr['set_update']) > 0)
		{
            mysqli_query($dbh, "UPDATE user_ident SET ".implode(",", $update_arr['set_update'])." WHERE id_user = ".$k);
            if ($order_change && $_SESSION['filter_id'] != "" && $_SESSION['filter_name'] == "") NewUserPlace($dbh, $k, $_SESSION['user_list'][$k]);
	        if ($_SESSION['pre_ref']['user_ident']['over'])
	        {
                unset($_SESSION['pre_ref']['user_ident']);
                SetReferenceTable($dbh, "user_ident", 12, QueryReferencesUsers(), array(array("li", 1, 0, 542, 544), array("db", 2, 0, 545, 544)), $_SESSION['pre_ref']);
                ksort($_SESSION['pre_ref']);
                SetInitReplaceIDs("user_ident", array("li"=>"user_langs", "db"=>"list_db"));
            }
            else
            {
			    if (isset($_SESSION['pre_ref']['user_ident']['p']['li']['v'][(string)$row[4]]) && in_array("use_lang_id", $update_arr['param_refs'])) ChangeInvalidRefTable($dbh, "user_ident", "li", $row[4], $k);
			    if (isset($_SESSION['pre_ref']['user_ident']['p']['db']['v'][(string)$row[6]]) && in_array("preffered_db", $update_arr['param_refs'])) ChangeInvalidRefTable($dbh, "user_ident", "db", $row[6], $k);
            }
        }
	}
	return $update_arr;
}
function NullFilter($r, $v)
{
    if (is_null($r) && gettype($v) != "string") return true;
    if (!is_null($r) && gettype($v) == "string") return true;
    if (!is_null($r) && gettype($v) != "string" && $r != $v) return true;
    return false;
}
function SetUpdating($k, $field_name, $field_type, $field_num, $title_num, $flag_num, &$param_list)
{
    switch ($field_type)
    {
        case "db"    : $param_list['set_update'][] = SetDBUpdate($_SESSION['user_list'][$k][$field_num], $field_name); break;
        case "string": $param_list['set_update'][] = $field_name." = '".$_SESSION['user_list'][$k][$field_num]."'"; break;
        case "check" : if ($_SESSION['user_list'][$k][$field_num] == 0 || $_SESSION['user_list'][$k][$field_num] == 1) $param_list['set_update'][] = $field_name." = ".$_SESSION['user_list'][$k][$field_num]; break;
        default      : $param_list['set_update'][] = $field_name." = ".$_SESSION['user_list'][$k][$field_num];
    }
    $param_list['param_titles'][] = array($title_num, $flag_num, $field_num);
    if ($flag_num == 4 || $flag_num == 6) $param_list['param_refs'][] = $field_name;
}
function SetDBUpdate($db_ref_value, $field_name)
{
    if (gettype($db_ref_value) == "string") return $field_name." = NULL";
    else return $field_name." = ".$db_ref_value;
}
function ResetReplaceFlags($k, $set_update)
{
    foreach ($set_update as $z)
    {
        if (strpos($z, "name = ") !== false) $_SESSION['update_replace'][$k][1] = false;
        if (strpos($z, "pass = ") !== false) $_SESSION['update_replace'][$k][2] = false;
        if (strpos($z, "date_format = ") !== false) $_SESSION['update_replace'][$k][7] = false;
        if (strpos($z, "hide_list = ") !== false) $_SESSION['update_replace'][$k][8] = "";
        if (strpos($z, "match_case = ") !== false) $_SESSION['update_replace'][$k][9] = "";
    }
}
function UserOutPage($k_double)
{
    if (isset($_SESSION['user_list'][$k_double])) return "";
    else return " (".Title(203).")";
}
function ReplaceText($k, $i, $field_num = 0)
{
    if (($i == 1 || $i == 2 || $i == 7) && $_SESSION['update_replace'][$k][$i]) return " (<font color='#0000FF'>".Title(572)." - ".Title(469)."</font> <font color='#990000' size'+2'><b>".$_SESSION['apostrophe_replace']."</b></font>)";
    if (($i == 8 || $i == 9) && $_SESSION['update_replace'][$k][$i] != "") return " (<font color='#0000FF'>".Title(490)." <b>".$_SESSION['update_replace'][$k][$i]."</b> ".Title(230)." <b>".$_SESSION['user_list'][$k][$field_num]."</b></font>)";
    else return "";
}
function UserDeleteMessage($k, $update_arr, $old_priority)
{
    $edit_mes = array("head" => Title(601)." <b>".$_SESSION['user_list'][$k][0]."</b> (<b>".$k."</b>)", "delete" => array());
    $edit_mes['saved'][] = Title(605).":";
    if (count($update_arr['param_titles']) > 0) for ($i = 0; $i < count($update_arr['param_titles']); $i++) $edit_mes['saved'][] = "<font color='#0000FF'>".FTM(Title($update_arr['param_titles'][$i][0]))."</font>".ReplaceText($k, $update_arr['param_titles'][$i][1], $update_arr['param_titles'][$i][2]);
    $edit_mes['errors'][] = Title(606).":";
    if ($_SESSION['user_flag'][$k][0]) $edit_mes['errors'][] = "<font color='#FF0000'>".Title(171)."</font>";
    IdentMessage($k, 0, 1, 142, 54, $old_priority, $edit_mes['errors']);
    IdentMessage($k, 1, 2, 143, 7, $old_priority, $edit_mes['errors']);
    if ($_SESSION['user_flag'][$k][3]) $edit_mes['errors'][] = "<font color='#FF0000'>".FTM(Title(232))."</font>";
    if ($_SESSION['user_flag'][$k][4]) $edit_mes['errors'][] = "<font color='#FF0000'>".Title(546)."</font>";
    if ($_SESSION['user_flag'][$k][5]) $edit_mes['errors'][] = "<font color='#FF0000'>".FTM(Title(602))." ".FTM(Title(530))."</font>";
    if ($_SESSION['user_flag'][$k][6]) $edit_mes['errors'][] = "<font color='#FF0000'>".Title(548)."</font>";
    if ($_SESSION['user_flag'][$k][7]) DateMessage($k, $edit_mes['errors']);
    if (count($edit_mes['saved']) == 1 && count($edit_mes['errors']) == 1) return array();
    return $edit_mes;
}

function UserEditMessage($k, $update_arr, $old_priority)
{
    $edit_mes = array("head" => Title(601)." <b>".$_SESSION['user_list'][$k][0]."</b> (<b>".$k."</b>)", "saved" => array(), "errors" => array());
    $edit_mes['saved'][] = Title(605).":";
    if (count($update_arr['param_titles']) > 0) for ($i = 0; $i < count($update_arr['param_titles']); $i++) $edit_mes['saved'][] = "<font color='#0000FF'>".FTM(Title($update_arr['param_titles'][$i][0]))."</font>".ReplaceText($k, $update_arr['param_titles'][$i][1], $update_arr['param_titles'][$i][2]);
    $edit_mes['errors'][] = Title(606).":";
    if ($_SESSION['user_flag'][$k][0]) $edit_mes['errors'][] = "<font color='#FF0000'>".Title(171)."</font>";
    IdentMessage($k, 0, 1, 142, 54, $old_priority, $edit_mes['errors']);
    IdentMessage($k, 1, 2, 143, 7, $old_priority, $edit_mes['errors']);
    if ($_SESSION['user_flag'][$k][3]) $edit_mes['errors'][] = "<font color='#FF0000'>".FTM(Title(232))."</font>";
    if ($_SESSION['user_flag'][$k][4]) $edit_mes['errors'][] = "<font color='#FF0000'>".Title(546)."</font>";
    if ($_SESSION['user_flag'][$k][5]) $edit_mes['errors'][] = "<font color='#FF0000'>".FTM(Title(602))." ".FTM(Title(530))."</font>";
    if ($_SESSION['user_flag'][$k][6]) $edit_mes['errors'][] = "<font color='#FF0000'>".Title(548)."</font>";
    if ($_SESSION['user_flag'][$k][7]) DateMessage($k, $edit_mes['errors']);
    if (count($edit_mes['saved']) == 1 && count($edit_mes['errors']) == 1) return array();
    return $edit_mes;
}
function IdentMessage($k, $field_num, $flag_num, $t1, $t2, $old_priority, &$edit_mes_err)
{
    $str_mes = "";
    if ($_SESSION['user_flag'][$k][$flag_num]) $str_mes = "<font color='#FF0000'>".FTM(Title($t1)).(($old_priority == 0) ? " (".Title(618).")" : "")."</font>";
    if ($_SESSION['update_replace'][$k][$flag_num] && $_SESSION['user_flag'][$k][$flag_num]) $str_mes .= ReplaceText($k, $flag_num);
    if ($_SESSION['update_double'][$k][$flag_num] != "") $str_mes .= "<font color='#FF0000'>".FTM(Title($t2))." <b>".$_SESSION['user_list'][$k][$field_num]."</b> ".Title(151)." <b>".$_SESSION['update_double'][$k][1]."</b> ".UserOutPage($_SESSION['update_double'][$k][1])."</font>";
    if ($str_mes != "") $edit_mes_err[] = $str_mes;
}
function DateMessage($k, &$edit_mes_err)
{
    $str_mes = "<font color='#FF0000'>".FTM(Title(602))." ".FTM(Title(531))."</font>";
    if ($_SESSION['update_replace'][$k][7]) $str_mes .= ReplaceText($k, 7);
    $edit_mes_err[] = $str_mes;
}
function ViewEditMessages($edit_mes)
{
    echo "<font size='+1'>".$edit_mes['head']."</font>";
    echo "<table frame='border'>";
        echo "<tr valign='top'>";
            $common_count = max(count($edit_mes['saved']), count($edit_mes['errors']));
            echo "<td>";
                MessageRows($edit_mes['saved'], $common_count);
            echo "</td>";
            echo "<td>";
                MessageRows($edit_mes['errors'], $common_count);
            echo "</td>";
        echo "</tr>";
    echo "</table>";
}
function MessageRows($mes_arr, $common_count)
{
    echo "<table frame='border'>";
    for ($i = 0; $i < count($mes_arr); $i++) echo "<tr><td>".$mes_arr[$i]."</td></tr>";
    for ($i = count($mes_arr); $i < $common_count; $i++) echo "<tr><td><font color='#FFFFFF'>X</font></td></tr>";
    echo "</table>";
}
function ChangeFromPost($post_prefix, $k, $v_ind, $f_type = "")
{
    if (gettype($f_type) == "array")
	{
		$arr_key = array_search($_POST[$post_prefix."|".$k], $f_type);
		if ($arr_key !== false) $_SESSION['user_list'][$k][$v_ind] = $arr_key;
	}
	elseif ($f_type == "check")
	{
	    if (isset($_POST[$post_prefix."|".$k]) && $_SESSION['user_list'][$k][$v_ind] != 1) $_SESSION['user_list'][$k][$v_ind] = 1;
	    elseif (!isset($_POST[$post_prefix."|".$k]) && $_SESSION['user_list'][$k][$v_ind] != 0) $_SESSION['user_list'][$k][$v_ind] = 0;
	}
	else $_SESSION['user_list'][$k][$v_ind] = $_POST[$post_prefix."|".$k];
}
function ChangeUserParameters($k, $v)
{
	if (isset($_POST["user_login|".$k]) && $_POST["user_login|".$k] != $v[0]) ChangeFromPost("user_login", $k, 0);
	if (isset($_POST["user_password|".$k]) && $_POST["user_password|".$k] != $v[1]) ChangeFromPost("user_password", $k, 1);
	if (isset($_POST["user_priority|".$k]) && $_POST["user_priority|".$k] != $v[2]) ChangeFromPost("user_priority", $k, 2);
	if (isset($_POST["pref|li|3|4|".$k]) && (!isset($_SESSION['user_langs'][$v[3]]) || isset($_SESSION['user_langs'][$v[3]]) && $_POST["pref|li|3|4|".$k] != $_SESSION['user_langs'][$v[3]])) ChangeFromPost("pref|li|3|4", $k, 3, $_SESSION['user_langs']);
	if (isset($_POST["user_portion|".$k]) && $_POST["user_portion|".$k] != $v[4]) ChangeFromPost("user_portion", $k, 4);
	if (isset($_POST["pref|db|5|6|".$k]) && (!isset($_SESSION['user_list'][$v[5]]) || isset($_SESSION['user_list'][$v[5]]) && $_POST["pref|db|5|6|".$k] != $_SESSION['user_list'][$v[5]])) ChangeFromPost("pref|db|5|6", $k, 5, $_SESSION['list_db']);
	if (isset($_POST["pref_date|".$k]) && $_POST["pref_date|".$k] != $v[6]) ChangeFromPost("pref_date", $k, 6);
    ChangeFromPost("hide_list", $k, 7, "check");
    ChangeFromPost("match_case", $k, 8, "check");
}
function ChangeDelUser($dbh, $type, $k, $ref)
{
    if (isset($_SESSION['pre_ref']['user_ident']['p'][$type]['v'][(string)$ref])) ChangeInvalidRefTable($dbh, "user_ident", $type, (string)$ref, $k);
}
function DeleteUser($dbh)
{
	mysqli_query($dbh, "DELETE FROM user_ident WHERE id_user = ".$_SESSION['user_delete'][0]);
	mysqli_query($dbh, "ALTER TABLE user_ident AUTO_INCREMENT = 1");
    if ($_SESSION['pre_ref']['user_ident']['over'])
    {
        unset($_SESSION['pre_ref']['user_ident']);
        SetReferenceTable($dbh, "user_ident", 12, QueryReferencesUsers(), array(array("li", 1, 0, 542, 544), array("db", 2, 0, 545, 544, 0, 2)), $_SESSION['pre_ref']);
        ksort($_SESSION['pre_ref']);
        SetInitReplaceIDs("user_ident", array("li"=>"user_langs", "db"=>"list_db"));
    }
    else
    {
        ChangeDelUser($dbh, "li", $_SESSION['user_delete'][0], $_SESSION['user_list'][$_SESSION['user_delete'][0]][3]);
        ChangeDelUser($dbh, "db", $_SESSION['user_delete'][0], $_SESSION['user_list'][$_SESSION['user_delete'][0]][5]);
    }
    $_SESSION['user_size'] = UserListSize($dbh);
    SetUserFilterPosition($dbh);
	GetUserListPortion($dbh);
	GetUserCounts($dbh);
	foreach ($_SESSION['user_list'] as $k => $v) TestUser($k, $v);
	DeleteSessionFromUserList($dbh, $_SESSION['user_delete'][2], $_SESSION['user_delete'][0]);
}
function UserSavePost()
{
	if (isset($_POST['user_filter_id']))
    {
        if (!is_numeric($_POST['user_filter_id'])) $_SESSION['filter_id'] = "";
        else $_SESSION['filter_id'] = $_POST['user_filter_id'];
	}
	if (isset($_POST['user_filter_name'])) $_SESSION['filter_name'] = $_POST['user_filter_name'];
	if (isset($_POST['user_filter_category'])) $_SESSION['category'] = SavePostSelect($_SESSION['categories'], $_POST['user_filter_category'], array(169, 135, 136, 137, 232));
}
function UserDisabled($k)
{
    if ($_SESSION['user_working_mode'] == 0) return true;
    if ($k == $_SESSION['user_id']) return true;
    if ($k != $_SESSION['edit_user']) return true;
    if ($_SESSION['edit_user'] == "") return true;
    return false;
}
function ReplaceUserIDs($dbh, $k_type, $k_r)
{
    if ($k_type == "li") $fl = ReplaceRefID($dbh, "user_ident", "use_lang_id", $k_type, $k_r, $_SESSION['repl_id'][$k_type][$k_r]);
    else
    {
        $new_value = (is_numeric($_SESSION['repl_id'][$k_type][$k_r])) ? $_SESSION['repl_id'][$k_type][$k_r] : "NULL";
        $fl = ReplaceRefID($dbh, "user_ident", "preffered_db", $k_type, $k_r, $new_value);
    }
    if ($fl)
    {
        if ($_SESSION['pre_ref']['user_ident']['over'])
        {
            unset($_SESSION['pre_ref']['user_ident']);
            SetReferenceTable($dbh, "user_ident", 12, QueryReferencesUsers(), array(array("li", 1, 0, 542, 544), array("db", 2, 0, 545, 544)), $_SESSION['pre_ref']);
            ksort($_SESSION['pre_ref']);
            SetInitReplaceIDs("user_ident", array("li"=>"user_langs", "db"=>"list_db"));
        }
        SetUserFilterPosition($dbh);
        GetUserListPortion($dbh);
        GetUserCounts($dbh);
        foreach ($_SESSION['user_list'] as $k => $v) TestUser($k, $v);
    }
}
function NewUserPlace($dbh, $k, $v)
{
    $name_arr = GetUserNameArray();
    if ($v[0] < $name_arr[0] || $v[0] > $name_arr[count($name_arr) - 1])
    {
        if ($_SESSION['user_filter'] != "" && UserRowComparison($k, $v)) UserResetFilter();
        GetUserStartPosition($dbh, $k);
    }
}

?>
