<?php
function RewriteDB($dbh, &$Mes)
{
	$arr_del = array();
    $arr_ins = array();
	foreach ($_SESSION['arr_db'] as $k => $v)
	{
        if ($k != 0)
        {
	        if ($v['del']) $arr_del[] = $k;
	        else
	        {
                $_SESSION['arr_db'][$k] = array("db_name"=>$v['db_name'], "db_coding"=>$_POST["db_coding|".$k], "db_comment"=>$_POST["db_comment|".(string)$k], "del"=>false, "db_err"=>TestDatabase($v['db_name'], $_POST["db_coding|".$k], (string)$k));
			    $arr_ins[] = "(".(string)$k.",'".$v['db_name']."','".$_POST["db_coding|".(string)$k]."','".$_POST["db_comment|".(string)$k]."')";
                if (isset($_SESSION['pre_ref']['user_ident']['p']['db']['v'][$k])) ChangeByInsertInvalidRefTable("user_ident", "db", $k);
                if (isset($_SESSION['pre_ref']['visits']['p']['db']['v'][$k])) ChangeByInsertInvalidRefTable("visits", "db", $k);
            }
		}
	}
    if (isset($_SESSION['pre_ref']['visits']['p']['db']['v'][0])) ChangeByInsertInvalidRefTable("visits", "db", 0);
	foreach ($arr_del as $z) unset($_SESSION['arr_db'][$z]);
	mysqli_query($dbh, "DELETE FROM db_list WHERE db_id > 0");
	mysqli_query($dbh, "ALTER TABLE db_list AUTO_INCREMENT = 1");
    mysqli_query($dbh, "INSERT INTO db_list VALUES ".implode(",", $arr_ins));
    $Mes[] = "<font color='#0000FF'>".Title(127)." ".Title(131)."</font>";
}
function DataBaseListExit($dbh, &$Mes)
{
	if ($_SESSION['user_working_mode'] == 1)
	{
        $fl_exit = TestDBList($Mes);
	    if ($_SESSION['alarm'])
	    {
            DBLExit();
			DataBaseTableCheck($dbh);
	        unset($_SESSION['pre_ref']['user_ident']);
	        SetReferenceTable($dbh, "user_ident", 12, QueryReferencesUsers(), array(array("li", 1, 0, 542, 544), array("db", 2, 0, 545, 544)), $_SESSION['pre_ref']);
            unset($_SESSION['pre_ref']['visits']);
            SetReferenceTable($dbh, "visits", 167, QueryReferencesVisits(), array(array("db", 0, 1, 545, 544), array("ui", 1, 0, 544, 545)), $_SESSION['pre_ref']);
            ksort($_SESSION['pre_ref']);
 			return "../Alarm/CommonAlarmForm" ;
        }
        if ($fl_exit)
        {
            DBLExit();
            return "../Administrator/MainForm";
		}
		else
        {
            $Mes[] = "<font color='#FF0000'>".Title(235)."</font>";
            return "";
        }
    }
	return "../Administrator/MainForm";
}
function DBLExit()
{
    if (isset($_SESSION['pre_db_err'])) unset($_SESSION['pre_db_err']);
	unset($_SESSION['db_sel']);
	unset($_SESSION['del_ref']);
    foreach (array_keys($_SESSION['arr_db']) as $k)
    {
        $_SESSION['arr_db'][$k]['del'] = false;
        $_SESSION['arr_db'][$k]['db_err'] = array();
    }
}
function GetServerDatabases($db_else)
{
	$arr = array();
	$conn = mysqli_connect($_SESSION['serv']['host'], $_SESSION['serv']['user'], $_SESSION['serv']['pass']);
	if ($conn)
	{
		$res = mysqli_query($conn, "SHOW DATABASES");
		if ($res)
		{
			while ($row = mysqli_fetch_row($res))
            {
                if ($row[0] != $db_else && !FindDBname($row[0])) $arr[] = $row[0];
            }
			mysqli_free_result($res);
		}
	}
	return $arr;
}
function FindDBname($db_name)
{
    foreach ($_SESSION['arr_db'] as $v) if ($v['db_name'] == $db_name) return true;
    return false;
}
function IsDataBase($db_name)
{
	$fl = false;
	$conn = mysqli_connect($_SESSION['serv']['host'], $_SESSION['serv']['user'], $_SESSION['serv']['pass']);
	if ($conn)
	{
		$res = mysqli_query($conn, "SHOW DATABASES");
		if ($res)
		{
			while ($row = mysqli_fetch_row($res))
            {
                if ($row[0] == $db_name)
                {
                    $fl = true;
                }
            }
			mysqli_free_result($res);
		}
	}
	return $fl;
}
function GetDBDelRef($dbh, $db_id)
{
    $arr_ref = array("user_ident"=>array(), "visits"=>array(), "over"=>false);
    $str_req = "SELECT preffered_db AS id_db, id_user, name AS user_name, 'user_ident' AS table_name FROM user_ident WHERE preffered_db = ".(string)$db_id." LIMIT ".(string)($_SESSION['number_warn'] + 1);
    $str_req .= " UNION ";
	$str_req .= "SELECT * ";
	$str_req .= "FROM ";
	$str_req .= "(SELECT id_db, id_user, ";
	$str_req .= "CASE ";
	$str_req .= "WHEN (SELECT user_ident.name FROM user_ident WHERE user_ident.id_user = visits.id_user LIMIT 1) IS NULL THEN '".Title(515)."' ";
    $str_req .= "ELSE (SELECT user_ident.name FROM user_ident WHERE user_ident.id_user = visits.id_user LIMIT 1) ";
    $str_req .= "END AS user_name, ";
    $str_req .= "'visits' AS table_name ";
    $str_req .= "FROM visits) AS T WHERE T.id_db = ".(string)$db_id." LIMIT ".(string)($_SESSION['number_warn'] + 1);
    $res = mysqli_query($dbh, $str_req);
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
            if (count($arr_ref['user_ident']) + count($arr_ref['visits']) == $_SESSION['number_warn']) $arr_ref['over'] = true;
            $arr_ref[$row[3]][] = "<b>".$row[1]."(".(string)$row[2].")</b>";
		}
		mysqli_free_result($res);
	}
    return $arr_ref;
}
function SwitchDBDel($dbh, $db_id)
{
	if ($_SESSION['arr_db'][$db_id]['del'])
    {
        if ($_SESSION['alarm']) unset($_SESSION['del_ref'][$db_id]);
        $_SESSION['arr_db'][$db_id]['del'] = false;
    }
	else
	{
        $arr_ref = GetDBDelRef($dbh, $db_id);
        if ($_SESSION['alarm'])
        {
            $_SESSION['arr_db'][$db_id]['del'] = true;
            if (count($arr_ref['user_ident']) + count($arr_ref['visits']) > 0) $_SESSION['del_ref'][$db_id] = SetDBDelMessage($arr_ref);
        }
        else
        {
            unset($_SESSION['del_ref']);
            if (count($arr_ref['user_ident']) + count($arr_ref['visits']) == 0) $_SESSION['arr_db'][$db_id]['del'] = true;
		    else $_SESSION['del_ref'][$db_id] = SetDBDelMessage($arr_ref);
		}
	}
}
function SetDBDelMessage($arr_ref)
{
    $db_mes = array();
    if (count($arr_ref['user_ident']) > 0) $db_mes[] = "<font color='#CC0000'><b>".Title(12)."</b>: ".FTM(Title(259)).": ".implode(", ", $arr_ref['user_ident'])."</font>";
    if (count($arr_ref['visits']) > 0) $db_mes[] = "<font color='#CC00CC'><b>".Title(167)."</b>: ".FTM(Title(259)).": ".implode(", ", $arr_ref['visits'])."</font>";
    if ($arr_ref['over']) $db_mes[] = "...";
    if (count($db_mes) == 0) return "";
    else return implode(", ", $db_mes);
}
function SelectDBServer($i)
{
    if ($_SESSION['db_sel'] == "") $_SESSION['db_sel'] = (string)NewTableID($_SESSION['arr_db'], 0);
	$_SESSION['arr_db'][(integer)$_SESSION['db_sel']] = array("db_name"=>$_SESSION['db_server'][$i], "db_coding"=>"", "db_comment"=>$_SESSION['db_server'][$i], "del"=>false, "db_err"=>array());
	$_SESSION['db_server'] = GetServerDatabases("db_manager");
}
function SaveDBPost()
{
    foreach ($_SESSION['arr_db'] as $k => $v)
    {
        if (isset($_POST["db_coding|".(string)$k])) $_SESSION['arr_db'][$k]['db_coding'] = $_POST["db_coding|".(string)$k];
        if (isset($_POST["db_comment|".(string)$k])) $_SESSION['arr_db'][$k]['db_comment'] = $_POST["db_comment|".(string)$k];
    }
}
function SortDBList()
{
    $arr_by_name = array();
    foreach ($_SESSION['arr_db'] as $k => $v) $arr_by_name[$v['db_name']] = array("key"=>$k, "db_coding"=>$v['db_coding'], "db_comment"=>$v['db_comment'], "del"=>$v['del'], "db_err"=>$v['db_err']);
    ksort($arr_by_name);
    $sort_db = array();
    foreach ($arr_by_name as $k => $v) $sort_db[$v['key']] = array("db_name"=>$k, "db_coding"=>$v['db_coding'], "db_comment"=>$v['db_comment'], "del"=>$v['del'], "db_err"=>$v['db_err']);
    return $sort_db;
}

?>
