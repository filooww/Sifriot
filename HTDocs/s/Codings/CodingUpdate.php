<?php
function SetDoubleCodingParameters($v)
{
	for ($i = 0; $i < count($v); $i++)
	{
		$arr_v = array();
		for ($j = 0; $j < count($v); $j++) if ($j != $i) $arr_v[] = $v[$j];
		if ($_SESSION['coding_list'][$v[$i]][2] == "") $_SESSION['coding_list'][$v[$i]][2] = "<font color='#FF0000'>".Title(700)." ".FTM(Title(330))." ".((count($arr_v) == 1) ? Title(297) : Title(298))." <b>".implode(", ", $arr_v)."</b></font>";
		else $_SESSION['coding_list'][$v[$i]][2] .= "; <font color='#FF0000'>".Title(700)." ".FTM(Title(330))." ".((count($arr_v) == 1) ? Title(297) : Title(298))." <b>".implode(", ", $arr_v)."</b></font>";
	}
}
function TestCodingList($dbh, $init = false)
{
    $_SESSION['del_coding'] = array();
	foreach ($_SESSION['coding_list'] as $k => $v) TestCodingRow($dbh, $k, $v[0], $init);
	if (!$init)
	{
	    $dd = TestCodingDouble();
	    if (count($dd) > 0) foreach ($dd as $v) SetDoubleCodingParameters($v);
	}
}
function TestCodingRow($dbh, $k, $v0, $init)
{
    $arr_mark = array();
    $_SESSION['coding_list'][$k][3] = false;
	if ($v0 == "" && gettype($k) == "integer") TestEmptyCoding($dbh, $k, $init, $arr_mark);
    if ($v0 != "" && strpos($v0, chr(39)) !== false)
    {
        $_SESSION['coding_list'][$k][0] = str_replace(chr(39), $_SESSION['apostrophe_replace'], $v0);
        $_SESSION['coding_list'][$k][1] = $_SESSION['coding_list'][$k][0];
        $arr_mark[] = "<font color='#0000FF'>".Title(120)." ".Title(623)." ".FTM(Title(277))."<font><font color='#990000' size='+2'> <b>".$_SESSION['apostrophe_replace']."</b></font>";
        $_SESSION['coding_list'][$k][3] = true;
    }
    if (isset($_SESSION['pre_coding_err'][$k])) $arr_mark[] = "<font color='#0000FF'>".DigitsToTitle($_SESSION['pre_coding_err'][$k])."</font>";
	if (count($arr_mark) > 0) $_SESSION['coding_list'][$k][2] = implode("; ", $arr_mark);
	else $_SESSION['coding_list'][$k][2] = "";
}
function TestEmptyCoding($dbh, $k, $init, &$arr_mark)
{
    if ($init)
    {
        $arr_mark[] = "<font color='#FF0000'>".FTM(Title(330))." ".Title(699)."</font>";
        $_SESSION['coding_list'][$k][3] = true;
    }
    else
    {
        $coding_refs = CodingReferencesForDelete($dbh, $k);
        if (count($coding_refs) > 0)
        {
            $arr_mark[] = InvalidCodinglMessage($k, $coding_refs);
            $_SESSION['coding_list'][$k][3] = true;
        }
        else
        {
            $arr_mark[] = "<font color='#0000FF'>".Title(701)."</font>";
            $_SESSION['del_coding'][] = (string)$k;
        }
    }
}
function InvalidCodinglMessage($k, $coding_refs)
{
	$str = "<font color='#FF0000'>".Title(204)." ".FTM(Title(330))." <b>".$_SESSION['coding_list'][$k][0]."</b>";
	$str .= ", ".FTM(Title(305))." ".((DelMesMult($coding_refs)) ? FTM(Title(696)) : FTM(Title(695)))." ".((count($coding_refs) == 1) ? Title(184) : Title(186))." ".DelMesList($coding_refs)."</font>. ";
	$str .= " <font color='#0000FF'><b>".Title(187)."</b></font>";
	if ($_SESSION['coding_list'][$k][0] != $_SESSION['coding_list'][$k][1]) $str .= " <button name='coding_restore|".(string)$k."' type='submit'>".FTM(Title(224))." ".Title(490)."</button>";
	return $str;
}
function RewriteCoding($dbh, &$Mes)
{
    $fl_change = false;
    foreach ($_SESSION['coding_list'] as $k => $v)
    {
        if ($v[0] != "" && !$v[2])
        {
            if (gettype($k) == "string" && $v[0] != "") InsertNewCoding($dbh, $v[0], $fl_change);
            elseif ((integer)$k > 1 && $v[0] != $v[1])
            {
                mysqli_query($dbh, "UPDATE coding_table SET coding_name = '".VValue($v[0])."' WHERE id_coding = ".(string)$k);
                $fl_change = true;
            }
        }
	}
    if ($fl_change)
    {
        $Mes[] = "<b>".Title(694)."</b>";
        TestCodingList($dbh);
    }
}
function InsertNewCoding($dbh, $v0, &$fl_change)
{
    $new_coding_id = NewTableID($_SESSION['coding_list'], 1);
    $_SESSION['coding_list'][$new_coding_id] = array($v0, $v0, "", false);
    unset($_SESSION['coding_list']['new']);
    ksort($_SESSION['coding_list']);
    $_SESSION['coding_list']['new'] = array("", "", "", false);
    mysqli_query($dbh, "INSERT INTO coding_table VALUES (".(string)$new_coding_id.",'".VValue($v0)."')");
    $fl_change = true;
    $_SESSION['ref_add'] = true;
}
function CodingDel($dbh)
{
    if (isset($_SESSION['pre_coding_err'])) unset($_SESSION['pre_coding_err']);
	mysqli_query($dbh, "DELETE FROM coding_table WHERE id_coding IN (".implode(",", $_SESSION['del_coding']).")");
	mysqli_query($dbh, "ALTER TABLE coding_table AUTO_INCREMENT = 1");
    foreach ($_SESSION['del_coding'] as $z) unset($_SESSION['coding_list'][$z]);
}
function CodingSetQuestionString()
{
    $arr = array();
    foreach ($_SESSION['del_coding'] as $k_del) $arr[] = $_SESSION['coding_list'][$k_del][1]." (".$k_del.")";
    return $arr;
}
function CodingReferencesForDelete($dbh, $id_coding)
{
	$coding_refs = array();
	$str_req = "SELECT 'translate_table', GROUP_CONCAT(letter SEPARATOR ', ') FROM translate_table WHERE id_coding = ".$id_coding." GROUP BY id_coding";
	$res = mysqli_query($dbh, $str_req);
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
            $arr = explode(", ", $row[1]);
            if (count($arr) > $_SESSION['number_warn']) $coding_refs[$row[0]] = implode(", ", array_slice($arr, 0, $_SESSION['number_warn'])).", ...";
            else $coding_refs[$row[0]] = $row[1];
		}
		mysqli_free_result($res);
	}
	return $coding_refs;
}
function DelMesMult($coding_refs)
{
	if (count($coding_refs) == 1)
	{
		$c = reset($coding_refs);
		if (strpos($c, ", ") === false) return false;
		return true;
	}
	else return true;
}
function DelMesList($coding_refs)
{
	$str_arr = array();
	foreach ($coding_refs as $k => $v) $str_arr[] = "<b>".(string)$k."</b> (".$v.")";
	return implode(", ", $str_arr);
}
function IsCodingErrors()
{
	foreach ($_SESSION['coding_list'] as $v) if ($v[3]) return true;
	return false;
}
function CodingExit($dbh, &$Mes)
{
    $ret_addr = "";
	if ($_SESSION['user_working_mode'] == 1)
	{
        $fl_exit = !IsCodingErrors();
	    if ($_SESSION['alarm'])
	    {
            SimpleSysTableCheck($dbh, "coding_table", "coding_name = '' OR id_coding < 1");
            if ($_SESSION['ref_add'])
            {
                unset($_SESSION['pre_ref']['translate_table']);
                SetReferenceTable($dbh, "translate_table", 125, QueryReferencesLocals(), array(array("cd", 0, 2, 281, -277), array("li", 1, 2, 542, -277)), $_SESSION['pre_ref']);
                ksort($_SESSION['pre_ref']);
            }
			$ret_addr = "../Alarm/CommonAlarmForm" ;
        }
        elseif ($fl_exit) $ret_addr = "../Administrator/MainForm";
		else $Mes[] = "<font color='#0000FF'><b>".Title(223)."</b></font>";
    }
	else $ret_addr = "../Administrator/MainForm";
    if ($ret_addr != "")
    {
        unset($_SESSION['coding_list']);
        if (isset($_SESSION['del_coding'])) unset($_SESSION['del_coding']);
        if (isset($_SESSION['pre_coding_err'])) unset($_SESSION['pre_coding_err']);
    }
 	return $ret_addr;
}
function TestCodingDouble()
{
	$arr_dd = array();
	foreach ($_SESSION['coding_list'] as $k => $v) if ($v[0] != "") $arr_dd[$v[0]][] = (string)$k;
	foreach ($arr_dd as $k => $v)
    {
        if (count($v) == 1) unset($arr_dd[$k]);
        else foreach ($v as $z) $_SESSION['coding_list'][$z][3] = true;
    }
	return $arr_dd;
}
function CodingForbid($k, $v0)
{
    if ((string)$k == "1" && $v0 == "ASCII") return " disabled";
    else return "";
}
function DelCodingListRestore()
{
    if (isset($_SESSION['pre_coding_err'])) unset($_SESSION['pre_coding_err']);
    foreach ($_SESSION['del_coding'] as $k) if ($_SESSION['coding_list'][$k][0] != $_SESSION['coding_list'][$k][1]) $_SESSION['coding_list'][$k][0] = $_SESSION['coding_list'][$k][1];
    $_SESSION['del_coding'] = array();
}

?>
