<head>
	<style>.OK_button {background-color:#CCFF00;}</style>
	<style>.exit_button {vertical-align:middle; background-color:#FFCC00;}</style>
</head>
<?php
function CalendarSelect($arr_key, $sy, &$calend_date, &$p_var, &$calend_mes)
{
	RememberSettings($p_var);
	$sw_break = true;
	switch ($arr_key[0])
	{
		case "to_calendar":		$calend_mes = CalendSetDate("filter_text-".$arr_key[1], "text", $sy, $calend_date); break;
		case "to_calendar_to":	$calend_mes = CalendSetDate("filter_max-".$arr_key[1], "to", $sy, $calend_date); break;
		case "calendar_OK":		CalendarSetValue($calend_date, $p_var); break;
		case "calendar_exit":	$calend_date = array("id_key"=>"UploadDate", "id_filter"=>"", "value"=>""); break;
		default:				$sw_break = false;
	}
	return $sw_break;
}
function CalendarSetValue(&$calend_date, &$p_var)
{
	$id_key = $calend_date['id_key'];
	$id_filter = $calend_date['id_filter'];
	$p_var[$id_key]['filter'][$id_filter] = CalendGetDate(array($_POST['calendar_day'], $_POST['calendar_month'], $_POST['calendar_year']));
	$calend_date = array("id_key"=>"UploadDate", "id_filter"=>"", "value"=>"");
}
function CalendSelect($v_name, $value, $min_v, $max_v, $h_name, $tag_class = "", $on_func = "", $disabled_user = "", $tag_title = "")
{
    $calend_list = array();
    for ($i = $min_v; $i <= $max_v; $i++) $calend_list[] = $i;
    SelectTag($v_name, $calend_list, $value, $h_name, false, "", $on_func, $disabled_user, false, ($tag_class == "") ? "" : " class='".$tag_class."'", $tag_title);
}
function CalendView($start_year, $cury, $calend_date)
{
	$arr_date = explode($_SESSION['date_format'][0], $calend_date);
	echo CalendSelect("calendar_day", $arr_date[0], 1, CalendMaxDay($arr_date[1], $arr_date[2]));
	echo CalendSelect("calendar_month", $arr_date[1], 1, 12);
	echo CalendSelect("calendar_year", $arr_date[2], $start_year, $cury);
}
function CalendEnterDate($start_year, $cury, $k, $calend_date_f)
{
	echo "Select date (day.month.year): ";
	CalendView($start_year, $cury, $calend_date_f['value']);
	echo "<button name='calendar_OK-".$k."' type='submit' class='OK_button' value='*'>OK</button>";
	echo "<button name='calendar_exit-".$k."' type='submit' class='exit_button' value='*'>".ImgV("Close", 12, 16)."</button>";
}
function CalendGetDate($arr_date)
{
    switch (substr($_SESSION['date_format'], 1))
    {
        case "dmy" : return sprintf("%'.02d", $arr_date[0]).$_SESSION['date_format'][0].sprintf("%'.02d", $arr_date[1]).$_SESSION['date_format'][0].$arr_date[2];
        case "mdy" : return sprintf("%'.02d", $arr_date[1]).$_SESSION['date_format'][0].sprintf("%'.02d", $arr_date[0]).$_SESSION['date_format'][0].$arr_date[2];
        case "ydm" : return $arr_date[2].$_SESSION['date_format'][0].sprintf("%'.02d", $arr_date[0]).$_SESSION['date_format'][0].sprintf("%'.02d", $arr_date[1]);
        default    : return $arr_date[2].$_SESSION['date_format'][0].sprintf("%'.02d", $arr_date[1]).$_SESSION['date_format'][0].sprintf("%'.02d", $arr_date[0]);
    }
}
function CalendSetDate($post_name, $filter_name, $start_y, &$calend_date)
{
	$calend_mes = array();
	date_default_timezone_set("UTC");
	$c_date = getdate();
	$calend_date['id_filter'] = $filter_name;
	if (isset($_POST[$post_name]) && $_POST[$post_name] != "")
	{
		$empty_ym = "";
        $arr_date = explode($_SESSION['date_format'][0], $_POST[$post_name]);
		if (count($arr_date) == 2)
		{
			$calend_mes[] = array("time"=>"", "text"=>Title(21), "status"=>"warning");
			$arr_date[] = "";
		}
		elseif (count($arr_date) == 1)
		{
			$calend_mes[] = array("time"=>"", "text"=>Title(21), "status"=>"warning");
			$arr_date[] = "";
			$arr_date[] = "";
		}
		elseif (count($arr_date) > 3) $calend_mes[] = array("time"=>"", "text"=>Title(22), "status"=>"warning");
		$ad = GetPartDateNumbers();
		TestYear($arr_date[$ad[2]], $start_y, $c_date['year'], $calend_mes);
		TestMonth($arr_date[$ad[1]], $calend_mes);
		TestDay($arr_date[$ad[0]], $arr_date[$ad[1]], $arr_date[$ad[2]], $calend_mes);
		$calend_date['value'] = implode($_SESSION['date_format'][0], $arr_date);
	}
	else
	{
		$calend_mes[] = array("time"=>"", "text"=>Title(23), "status"=>"warning");
        switch (substr($_SESSION['date_format'], 1))
        {
            case "dmy" : return sprintf("%'.02d", $c_date['mday']).$_SESSION['date_format'][0].sprintf("%'.02d", $c_date['mon']).$_SESSION['date_format'][0].$c_date['year'];
            case "mdy" : return sprintf("%'.02d", $c_date['mon']).$_SESSION['date_format'][0].sprintf("%'.02d", $c_date['mday']).$_SESSION['date_format'][0].$c_date['year'];
            case "ydm" : return $c_date['year'].$_SESSION['date_format'][0].sprintf("%'.02d", $c_date['mday']).$_SESSION['date_format'][0].sprintf("%'.02d", $c_date['mon']);
            default    : return $c_date['year'].$_SESSION['date_format'][0].sprintf("%'.02d", $c_date['mon']).$_SESSION['date_format'][0].sprintf("%'.02d", $c_date['mday']);
        }
	}
	return $calend_mes;
}
function TestYear(&$sy, $starty, $cury, &$calend_mes)
{
	if ($sy == "" || !is_numeric($sy))
	{
		$sy = (string)$cury;
		$calend_mes[] = array("time"=>"", "text"=>Title(27)." ".Title(535).". ".Title(24), "status"=>"warning");
	}
	elseif ((integer)$sy < $starty || (integer)$sy > $cury)
	{
		$sy = (string)$cury;
		$calend_mes[] = array("time"=>"", "text"=>Title(27)." ".Title(535).". ".Title(24), "status"=>"warning");
	}
}
function TestMonth(&$sm, &$calend_mes)
{
	if ($sm == "" || !is_numeric($sm))
	{
		$sm = "01";
		$calend_mes[] = array("time"=>"", "text"=>Title(28)." ".Title(535).". ".Title(25), "status"=>"warning");
	}
	elseif ((integer)$sm < 1 || (integer)$sm > 12)
	{
		$sm = "01";
		$calend_mes[] = array("time"=>"", "text"=>Title(28)." ".Title(535).". ".Title(25), "status"=>"warning");
	}
}
function TestDay(&$sd, $sm, $sy, &$calend_mes)
{
	if ($sd == "" || !is_numeric($sd))
	{
		$sd = "01";
		$calend_mes[] = array("time"=>"", "text"=>Title(29)." ".Title(535).". ".Title(26), "status"=>"warning");
	}
	elseif ((integer)$sd < 1 || (integer)$sd > CalendMaxDay((integer)$sm, (integer)$sy))
	{
		$sd = "01";
		$calend_mes[] = array("time"=>"", "text"=>Title(29)." ".Title(535).". ".Title(26), "status"=>"warning");
	}
}
function CalendMaxDay($m, $y)
{
	if ($m == 2 && $y % 4 != 0) return 28;
	if ($m == 2 && $y % 4 == 0) return 29;
	if ($m == 4 || $m == 6 || $m == 9 || $m == 11) return 30;
	return 31;
}
function CalendTestDate($test_date, $start_y = 0)
{
	$Mes = array();
	date_default_timezone_set("UTC");
	$c_date = getdate();
    $arr_date = explode($_SESSION['date_format'][0], $test_date);
	if (count($arr_date) == 3)
	{
		$ad = GetPartDateNumbers();
		TestY($arr_date[$ad[2]], $start_y, $c_date['year'], $Mes);
		TestM($arr_date[$ad[1]], $Mes);
		TestD($arr_date[$ad[0]], $arr_date[1], $arr_date[2], $Mes);
	}
	elseif (count($arr_date) > 3) $Mes[] = Title(22)." (<b>".$_SESSION['date_format'][0]."</b>)";
	else $Mes[] = Title(21)." (<b>".$_SESSION['date_format'][0]."</b>)";
	return $Mes;
}
function TestY($sy, $starty, $cury, &$Mes)
{
	if ($sy == "" || !is_numeric($sy)) $Mes[] = Title(27)." ".Title(535);
	elseif (strlen($sy) != 4) $Mes[] = Title(27)." ".Title(535);
	elseif ($sy[0] == "+" || $sy[0] == "-") $Mes[] = Title(27)." ".Title(535);
	elseif ($starty > 0 && ((integer)$sy < $starty || (integer)$sy > $cury)) $Mes[] = Title(27)." ".Title(535);
}
function TestM($sm, &$Mes)
{
	if ($sm == "" || !is_numeric($sm)) $Mes[] = Title(28)." ".Title(535);
	elseif ((integer)$sm < 1 || (integer)$sm > 12) $Mes[] = Title(28)." ".Title(535);
}
function TestD($sd, $sm, $sy, &$Mes)
{
	if ($sd == "" || !is_numeric($sd)) $Mes[] = Title(29)." ".Title(535);
	elseif ((integer)$sd < 1 || (integer)$sd > CalendMaxDay((integer)$sm, (integer)$sy)) $Mes[] = Title(29)." ".Title(535);
}
function StringToDateFormat($date_str, $date_delim, &$Mes)
{
	$Mes = array();
	$arr_date = explode($_SESSION['date_format'][0], $calend_date);
	if (count($arr_date) == 3)
	{
        $ad = GetPartDateNumbers();
		TestY($arr_date[$ad[2]], 0, 0, $Mes);
		TestM($arr_date[$ad[1]], $Mes);
		TestD($arr_date[$ad[0]], $arr_date[1], $arr_date[2], $Mes);
		if (count($Mes) == 0) return GetDatePart($arr_date[$ad[2]], 4)."-".GetDatePart($arr_date[$ad[1]], 2)."-".GetDatePart($arr_date[$ad[0]], 4);
		else return null;
	}
	else return null;
}
function GetDatePart($date_part, $date_part_length)
{
	if (strlen($date_part) == $date_part_length) return $date_part;
	elseif (strlen($date_part) < $date_part_length) return str_repeat("0", $date_part_length - strlen($date_part)).$date_part;
	else return substr($date_part, -1, $date_part_length);
}
function ToDate($dt, $date_delim, $to_space = false)
{
	if ($dt == "") return "";
	if (is_null($dt) && $to_space) return "";
	$arr_date_time = explode(" ", $dt);
	$arr_date = explode("-", $arr_date_time[0]);
	$str_date = $arr_date[2].$date_delim.$arr_date[1].$date_delim.$arr_date[0];
	if (count($arr_date_time) == 1) return $str_date;
	else return $str_date." ".$arr_date_time[1];
}
function TestDateFormat($d_format)
{
    if (substr($d_format, 1) == "dmy" || substr($d_format, 1) == "mdy" || substr($d_format, 1) == "ydm" || substr($d_format, 1) == "ymd") return true;
    return false;
}
function GetPartDateNumbers()
{
    switch (substr($_SESSION['date_format'], 1))
	{
        case "dmy" : return array(0, 1, 2);
        case "mdy" : return array(1, 0, 2);
        case "ydm" : return array(1, 2, 0);
        case "ymd" : return array(2, 1, 0);
    }
    return array();
}
function TimeZoneYear()
{
    date_default_timezone_set("UTC");
	$curd = getdate();
	return $curd['year'];
}
function GetCurrentDate()
{
	date_default_timezone_set("UTC");
	$cdate = getdate();
	$rd = ((strlen((string)$cdate['mday']) == 1) ? "0" : "").(string)$cdate['mday'].".";
	$rd .= ((strlen((string)$cdate['mon']) == 1) ? "0" : "").(string)$cdate['mon'].".";
	$rd .= (string)$cdate['year'];
	return $rd;
}
function GetSelectYears($init_year, $end_year)
{
	$arr_year = array();
	for ($i = $init_year; $i <= $end_year; $i++) $arr_year[] = $i;
}
function GetDBDate($str_date)
{
	$arr = explode($_SESSION['date_format'][0], $str_date);
	if (count($arr) == 3)
    {
        switch (substr($_SESSION['date_format'], 1))
        {
            case "dmy" : return $arr[2]."-".$arr[1]."-".$arr[0];
            case "mdy" : return $arr[2]."-".$arr[0]."-".$arr[1];
            case "ydm" : return $arr[0]."-".$arr[2]."-".$arr[1];
            default    : return $arr[0]."-".$arr[1]."-".$arr[2];
        }
    }
	else return "NOW()";
}

?>
