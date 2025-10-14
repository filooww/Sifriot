<?php
function SetVisitErrors($db, $user, $work_start, $visit_count, $work_mode, $priority, $k)
{
	$err = array();
	$str_err = array("str"=>"", "err"=>true);
	if ($db == "") $err[] = "<font color='#FF0000'>".Title(514)."</font>";
	if ($user == "") $err[] = "<font color='#FF0000'>".Title(515)."</font>";
	if ($db != "" && $user != "" && (!is_numeric($priority) || is_numeric($priority) && ((integer)$priority < 11 || (integer)$priority > 99) && $db == "db_manager")) $err[] = "<font color='#FF0000'>".Title(149)."</font>";
	if ($visit_count < 1) $err[] = "<font color='#FF0000'>".Title(566)." ".Title(490)." <b>".FTM(Title(165))."</b></font>";
	if ($work_mode < -1 || $work_mode > 1) $err[] = "<font color='#FF0000'>".FTM(Title(153))." ".Title(535)."</font>";
	elseif (is_null($work_start) && $work_mode >= 0 || !is_null($work_start) && $work_mode == -1) $err[] = "<font color='#FF0000'>".Title(566)." <b>".FTM(Title(164))."</b> ".Title(567)." <b>".FTM(Title(153))."</b></font>";
	elseif (!is_null($work_start) && $work_mode >= 0) $err[] = "<font color='#0000FF'>".Title(516)."</font>";
	$str_err['str'] = implode("; ", $err);
	if (strpos($str_err['str'], "#FF0000") === false) $str_err['err'] = false;
	return $str_err;
}
function TestCondition($curr)
{
	if (isset($_SESSION['alarm']) && $curr[7] > 0 || !isset($_SESSION['alarm']) && ($curr[7] > 0 || $curr[9]['err'])) return false;
	return true;
}
function TestVisitNavigation($act)
{
	switch ($act)
	{
		case "lnup"	: if (count($_SESSION['visit_list']) >= $_SESSION['portion'] && !TestCondition(end($_SESSION['visit_list']))) return false;
		case "lndn"	: if (!TestCondition(reset($_SESSION['visit_list']))) return false;
		default		: foreach ($_SESSION['visit_list'] as $k => $v) if (!TestCondition($v)) return false;
	}
	return true;
}
function TestVisitList()
{
    $err_flags = array("del"=>false, "err"=>false);
    foreach ($_SESSION['visit_list'] as $k => $v)
    {
        if ($v[7] == 2) $err_flags['del'] = true;
        if ($v[9]['err']) $err_flags['err'] = true;
        if ($err_flags['del'] && !$err_flags['err']) return $err_flags;
    }
    return $err_flags;
}
?>
