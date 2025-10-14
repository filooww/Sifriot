<?php
function WorkingMode($w_mode, $v, $u)
{
	if ($w_mode < -1 || $w_mode > 1) return (string)$w_mode;
	if ($w_mode == 0) return $v;
	if ($w_mode == 1) return $u;
	return "";
}
function RestoreSession($k)
{
    $_SESSION['visit_list'][$k][4] = null;
    $_SESSION['visit_list'][$k][6] = -1;
    $_SESSION['visit_list'][$k][7] = 0;
    $_SESSION['visit_list'][$k][9] = SetVisitErrors($_SESSION['visit_list'][$k][0], $_SESSION['visit_list'][$k][2], $_SESSION['visit_list'][$k][4], $_SESSION['visit_list'][$k][5], $_SESSION['visit_list'][$k][6], $_SESSION['visit_list'][$k][8], $k);
}
function SetWorkingMode($dbh, $db_id, $work_mode_setted, $update_working_mode = false)
{
	$struct_mes = array(0, "");
	$struct_mes[0] = AdminWarnings($dbh, $db_id, $work_mode_setted);
	TextAdminMessage($struct_mes);
	if ($update_working_mode)
	{
		mysqli_query($dbh, "UPDATE visits SET working_mode = ".(string)$work_mode_setted." WHERE id_db = ".(string)$db_id." AND id_user = ".$_SESSION['user_id']);
		$_SESSION['user_working_mode'] = $work_mode_setted;
	}
	return $struct_mes;
}
function SetWarningCode($res, &$nn, $warning_code)
{
    if ($row = mysqli_fetch_row($res))
    {
        if ($row[0] > 0) $nn = $warning_code;
    }
    mysqli_free_result($res);
}
function AdminWarnings($dbh, $id_db, $work_mode_setted)
{
    $nn = 0;
    if ($work_mode_setted == 0 && $id_db == 0)
    {
        $res = mysqli_query($dbh, "SELECT COUNT(*) FROM visits WHERE id_user <> ".$_SESSION['user_id']." AND id_db = 0 AND working_mode = 1");
	    if ($res) SetWarningCode($res, $nn, 1);
	}
    elseif ($work_mode_setted == 0 && $id_db > 0)
    {
        $str_res = "SELECT '0', COUNT(*) FROM visits WHERE id_user <> ".$_SESSION['user_id']." AND id_db = 0 AND working_mode = 1";
        $str_res .= " UNION ";
        $str_res .= "SELECT '1', COUNT(*) FROM visits WHERE id_user <> ".$_SESSION['user_id']." AND id_db = ".(string)$id_db." AND working_mode = 1";
        $res = mysqli_query($dbh, $str_res);
        if ($res)
        {
            $arr_n = array(false, false);
            while ($row = mysqli_fetch_row($res))
            {
                if ($row[0] == "0") $arr_n[0] = true;
                if ($row[0] == "1") $arr_n[1] = true;
            }
            mysqli_free_result($res);
            if ($arr_n[0] && $arr_n[1]) $nn = 2;
            elseif ($arr_n[0] && !$arr_n[1]) $nn = 3;
            elseif (!$arr_n[0] && $arr_n[1]) $nn = 4;
        }
	}
    if ($work_mode_setted == 1 && $id_db == 0)
    {
        $res = mysqli_query($dbh, "SELECT COUNT(*) FROM visits WHERE id_user <> ".$_SESSION['user_id']." AND working_mode >= 0");
	    if ($res) SetWarningCode($res, $nn, 5);
	}
    if ($work_mode_setted == 1 && $id_db > 0)
    {
        $res = mysqli_query($dbh, "SELECT COUNT(*) FROM visits WHERE id_user <> ".$_SESSION['user_id']." AND id_db = ".(string)$id_db." AND working_mode >= 0");
	    if ($res) SetWarningCode($res, $nn, 6);
	}
	return $nn;
}
function IsVisits($dbh)
{
	$fl = false;
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM visits WHERE working_mode >= 0 AND work_start IS NOT NULL AND id_user <> ".$_SESSION['user_id']);
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if ($row[0] > 0) $fl = true;
		}
		mysqli_free_result($res);
	}
	return $fl;
}
function UpdateVisitSet($working_mode, $v_count = 0)
{
    $str_update = array();
    $str_update[] = "work_start=".(($working_mode == -1) ? "NULL" : "NOW()");
    if ($working_mode >= 0) $str_update[] = "visit_count=".(string)($v_count + 1);
    $str_update[] = "working_mode=".(string)$working_mode;
    $str_update[] = "visit_session='".(($working_mode == -1) ? "" : session_id())."'";
    $str_update[] = "rest_time=0";
	return implode(",", $str_update);
}
function VisitParameters($dbh, $id_db, $working_mode, $priority)
{
	if ($working_mode == -1)
	{
		if ($priority > 0) mysqli_query($dbh, "UPDATE visits SET ".UpdateVisitSet($working_mode)." WHERE id_db = ".(string)$id_db." AND id_user = ".$_SESSION['user_id']);
        else CancelGuest($dbh, $id_db, $working_mode);
	}
	else
	{
		$v_c = VisitAnalysis($dbh, $id_db);
        if ($v_c == 0) mysqli_query($dbh, "INSERT INTO visits VALUES (".(string)$id_db.",".$_SESSION['user_id'].",NOW(),1,".(string)$working_mode.",'".session_id()."',0)");
        else mysqli_query($dbh, "UPDATE visits SET ".UpdateVisitSet($working_mode, $v_c)." WHERE id_db = ".(string)$id_db." AND id_user = ".$_SESSION['user_id']);
	}
}
function CancelGuest($dbh, $id_db, $working_mode)
{
    mysqli_query($dbh, "DELETE FROM visits WHERE id_db = ".(string)$id_db." AND id_user = ".$_SESSION['user_id']." AND ".(string)$working_mode);
    $res = mysqli_query($dbh, "SELECT COUNT(*) FROM visits WHERE id_user = ".$_SESSION['user_id']);
    if ($res)
    {
        if ($row = mysqli_fetch_row($res))
        {
            if ($row[0] == 0)
            {
	            mysqli_query($dbh, "DELETE FROM user_ident WHERE id_user = ".$_SESSION['user_id']);
	            mysqli_query($dbh, "ALTER TABLE user_ident AUTO_INCREMENT = 1");
            }
        }
        mysqli_free_result($res);
    }
}
function VisitAnalysis($dbh, $id_db)
{
    $v_count = 0;
    $res = mysqli_query($dbh, "SELECT visit_count FROM visits WHERE id_db = ".(string)$id_db." AND id_user = ".$_SESSION['user_id']);
    if ($res)
    {
        if ($row = mysqli_fetch_row($res))
        {
            $v_count = $row[0];
        }
        mysqli_free_result($res);
    }
    return $v_count;
}
function PendingSessions($dbh, $id_user)
{
	$del_arr = array();
	$fl_del = true;
	$res = mysqli_query($dbh, "SELECT * FROM visits WHERE id_user = ".$id_user);
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
            $fl_row_del = (is_null($row[2]) && $row[4] == -1 && $row[5] == "" && $row[6] == 0);
            if ($fl_row_del) $del_arr[] = $row[0];
            else $fl_del = false;
		}
		mysqli_free_result($res);
	}
	return array("fl" => $fl_del, "arr" => $del_arr);
}
function TestSessionSuspend($dbh, $id_db, $id_user)
{
	$suspend = false;
	$res = mysqli_query($dbh, "SELECT rest_time FROM visits WHERE id_db = ".(string)$id_db." AND id_user = ".$id_user);
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if ($row[0] > 0) $suspend = true;
		}
		mysqli_free_result($res);
	}
	return $suspend;
}
function ContinueSession($dbh)
{
    $session_count = 0;
    $res = mysqli_query($dbh, "SELECT COUNT(*) FROM visits WHERE visit_session = '".session_id()."'");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			$session_count = $row[0];
		}
		mysqli_free_result($res);
    }
    return $session_count;
}
function TestSessionString($str)
{
    $rr = array(true, -1);
    $arr = explode(",", $str);
    for ($i = 0; $i < count($arr); $i++)
    {
        if (!is_numeric($arr[$i])) $rr[0] = false;
        if ($i == 0 && $arr[$i] != "0") $rr[0] = false;
        if ($arr[$i] <= $arr[$i - 1]) $rr[0] = false;
    }
    if (count($arr) > 0) $rr[1] = $arr[count($arr) - 1];
    return $rr;
}
function NewSessionID(&$str)
{
    $rr = TestSessionString($str);
    if (!$rr[0])
    {
        $n_ses = "0";
        $str = "0";
    }
    else
    {
        $n_ses = (string)($rr[1] + 1);
        $str = $str.",".$n_ses;
    }
    return $n_ses;
}
function ReplaceVisitIDs($dbh, $k_type, $k_r)
{
    if ($k_type == "db")
    {
        $fl = ReplaceRefID($dbh, "visits", "id_db", $k_type, $k_r, $_SESSION['repl_id'][$k_type][$k_r]);
        if (!$fl) SetVisitReplaceMessage($dbh, $k_type, $k_r, "id_db", "id_user");
    }
    else
    {
        $fl = TestVisitUserReplacing($dbh, $k_r);
        if ($fl)
        {
            $fl = ReplaceRefID($dbh, "visits", "id_user", $k_type, $k_r, $_POST["replacing_id|ui|".$k_r]);
            if (!$fl) SetVisitReplaceMessage($dbh, $k_type, $k_r, "id_user", "id_db");
        }
        else $_SESSION['replace_mes'] = array("#FF0000", -602, " ", 544, " <b>".$_POST["replacing_id|ui|".$k_r],"</b>");
    }
    if ($fl)
    {
        if ($_SESSION['pre_ref']['visits']['over'])
        {
            unset($_SESSION['pre_ref']['visits']);
            SetReferenceTable($dbh, "visits", 167, QueryReferencesVisits(), array(array("db", 0, 1, 545, 544), array("ui", 1, 0, 544, 545)), $_SESSION['pre_ref']);
            ksort($_SESSION['pre_ref']);
            SetInitReplaceIDs("visits", array("db"=>"visit_db_list", "ui"=>""));
        }
        ResetFilter();
        $_SESSION['visit_list'] = GetVisitPortion($dbh, array());
        $_SESSION['visit_size'] = VisitSize($dbh);
    }
    SetInitReplaceIDs("visits", array("db"=>"visit_db_list", "ui"=>""));
}
function TestVisitUserReplacing($dbh, $k)
{
    $fl = false;
    if (is_numeric($_POST["replacing_id|ui|".$k]) && (integer)($_POST["replacing_id|ui|".$k]) > 0)
    {
        $res = mysqli_query($dbh, "SELECT COUNT(*) FROM user_ident WHERE id_user = ".$_POST["replacing_id|ui|".$k]);
        if ($res)
        {
	        if ($row = mysqli_fetch_row($res))
	        {
                $fl = ($row[0] == 1);
	        }
	        mysqli_free_result($res);
	    }
	}
	return $fl;
}
function SetVisitReplaceMessage($dbh, $k_type, $k, $field1, $field2)
{
    $no_changed = array();
    $res = mysqli_query($dbh, "SELECT id_user FROM visits WHERE ".$field1." = ".$_POST["replacing_id|".$k_type."|".$k]." AND ".$field2." IN (".implode(",", $_SESSION['pre_ref']['visits']['p'][$k_type]['v'][$k]).")");
    if ($res)
    {
	    while ($row = mysqli_fetch_row($res))
	    {
            $no_changed[] = $row[0];
	    }
	    mysqli_free_result($res);
	}
    if (count($no_changed) > 0)
    {
        $no_change_list = implode(", ", $_SESSION['pre_ref']['visits']['p'][$k_type]['v'][$k]);
        $err_list = implode(", ", $no_changed);
        $_SESSION['replace_mes'] = array("#FF0000", 569, " <b>".$no_change_list."</b> ", 673, " <b>".$err_list."</b> ", 674, " <b>".$_POST["replacing_id|".$k_type."|".$k]."</b>");
    }
}
function VisitInitial($dbh)
{
	$_SESSION['categories'] = DefUserCategories();
	$_SESSION['cancel_started'] = false;
	$_SESSION['t_rest'] = (string)$_SESSION['close_delay'];
	$_SESSION['t_start'] = "";
	$_SESSION['t_curr'] = "";
	$_SESSION['end_time'] = "";
	$_SESSION['current_year'] = TimeZoneYear();
	ResetFilter();
	$_SESSION['visit_list'] = GetVisitPortion($dbh, array());
	$_SESSION['visit_total_size'] = VisitSize($dbh);
	$_SESSION['visit_size'] = $_SESSION['visit_total_size'];
    $_SESSION['inv_ref'] = false;
    $_SESSION['visit_db_list'] = SetDBNameArray();
    SetInitReplaceIDs("visits", array("db"=>"visit_db_list", "ui"=>""));
    if (PermitInvRef("visits", array("db", "ui"))) $_SESSION['replace_mes'] = array();
}
function ResetFilter()
{
    $_SESSION['start'] = 0;
	$_SESSION['filter_db'] = array("", "");
	$_SESSION['filter_user'] = "";
	$_SESSION['filter_user_id'] = "";
	$_SESSION['category'] = array("all", Title(169));
	$_SESSION['expired_date'] = array(1, 1, (integer)$_SESSION['start_year']);
   	$_SESSION['ses_type'] = 0;
   	$_SESSION['visit_filter'] = "";
}
?>
