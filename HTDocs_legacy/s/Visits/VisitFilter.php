<?php
function AfterDBFilterSelect(&$sw_break)
{
	if ($_POST['visit_db_s'] == "") $sw_break = false;
	else
    {
        if ($_POST['visit_db'] == "") $_SESSION['filter_db'] = array("", "");
        else
        {
            $k = array_search($_POST['visit_db'], $_SESSION['visit_db_list']);
            if ($k !== false) $_SESSION['filter_db'] = array($k, $_SESSION['visit_db_list']);
        }
    }
}
function AfterFilterSelect($hide_name, &$sw_break)
{
	$fl = false;
	if ($_POST[$hide_name] == "") $sw_break = false;
	else $fl = true;
	return $fl;
}
function VisitFilter($dbh, $inv_ref_cancel = true)
{
    if ($inv_ref_cancel) $_SESSION['inv_ref'] = false;
    $_SESSION['start'] = 0;
    $_SESSION['visit_filter'] = GetVisitFilter();
    $_SESSION['visit_list'] = GetVisitPortion($dbh, array());
    $_SESSION['visit_size'] = VisitSize($dbh);
}
function GetVisitFilter()
{
	$arr = array();
	if ($_SESSION['filter_db'][1] != "") $arr[] = "T.db_name = '".$_SESSION['filter_db'][1]."'";
	if ($_SESSION['filter_db'][0] != "") $arr[] = "T.id_db = ".$_SESSION['filter_db'][0];
	if ($_SESSION['filter_user'] != "") $arr[] = "T.user_name LIKE '%".$_SESSION['filter_user']."%'";
	if ($_SESSION['filter_user_id'] != "") $arr[] = "T.id_user = ".$_SESSION['filter_user_id'];
    if ($_SESSION['category'][0] != "all" && $_SESSION['category'][0] != "invalid") $arr[] = "T.user_p <> '' AND T.user_p >= ".$_SESSION['categories'][$_SESSION['category'][0]][1]." AND T.user_p <=".$_SESSION['categories'][$_SESSION['category'][0]][2];
    elseif ($_SESSION['category'][0] == "invalid") $arr[] = "(T.user_p < 0 OR T.user_p > 99 OR T.user_p = '')";
	if ($_SESSION['ses_type'] == 1) $arr[] = "(".VisitFilterErrors().")";
	elseif ($_SESSION['ses_type'] == 2) $arr[] = "T.work_start >= '".GetDBDate(CalendGetDate(array($_SESSION['expired_date'][0], $_SESSION['expired_date'][1], $_SESSION['expired_date'][2])))."'";
	if (count($arr) > 0) return " WHERE ".implode(" AND ", $arr);
	else return "";
}
function VisitFilterErrors()
{
	$ww = "T.db_name = '' OR ";
	$ww .= "T.user_name = '' OR ";
	$ww .= "T.working_mode < -1 OR T.working_mode > 1 OR ";
	$ww .= "T.work_start IS NOT NULL AND T.working_mode = -1 OR ";
	$ww .= "T.work_start IS NULL AND T.working_mode >= 0 OR ";
	$ww .= "T.user_p <> '' AND (T.user_p > -1 AND T.user_p < 11 AND T.id_db = 0)";
	return $ww;
}
function FindFirstVisit()
{
    foreach ($_SESSION['visit_list'] as $k => $v) if (VisitRowComparison($k, $v)) return $k;
    return "";
}
function VisitRowComparison($k, $v)
{
    $ak = explode("|", $k);
    if ($_SESSION['filter_db'][0] != "" && (string)$ak[0] != $_SESSION['filter_db'][0]) return false;
    if ($_SESSION['filter_user_id'] != "" && (string)$ak[1] != $_SESSION['filter_user_id']) return false;
    if ($_SESSION['category'][0] != "all" && $_SESSION['category'][0] != "invalid" && $v[7] < $_SESSION['categories'][$_SESSION['category'][0]][1] && $v[7] > $_SESSION['categories'][$_SESSION['category'][0]][2]) return false;
    if ($_SESSION['category'][0] == "invalid" && $v[7] >= 0 && $v[7] <= 99) return false;
	if ($_SESSION['ses_type'] == 1) return VisitComparisonErrors($v);
	elseif ($_SESSION['ses_type'] == 2) return ($v[4] >= GetDBDate(CalendGetDate(array($_SESSION['expired_date'][0],$_SESSION['expired_date'][1],$_SESSION['expired_date'][2]))));
    return true;
}
function VisitComparisonErrors($v)
{
	if ($v[1] == "") return true;
	if ($v[3] == "") return true;
	if ($v[6] < -1 || $v[6] > 1) return true;
	if (!is_null($v[4]) && $v[6] == -1) return true;
	if (is_null($v[4]) && $v[6] >= 0) return true;
    return false;
}
function GetVisitStartPosition($dbh, $k)
{
    $w_w = ($_SESSION['visit_filter'] == "") ? "WHERE" : "AND";
    $w_str = CountCondition(array("T.db_name","T.user_name"), array($_SESSION['visit_list'][$k][1], $_SESSION['visit_list'][$k][3]));
    $res = mysqli_query($dbh, "SELECT COUNT(*) FROM (".VisitRequestText().") AS T".$_SESSION['visit_filter']." ".$w_w." ".$w_str." ORDER BY T.db_name, T.user_name");
    if ($res)
    {
	    if ($row = mysqli_fetch_row($res))
        {
            $s_p = $row[0];
        }
		mysqli_free_result($res);
	}
	if (isset($s_p)) $_SESSION['start'] = $s_p;
}
?>
