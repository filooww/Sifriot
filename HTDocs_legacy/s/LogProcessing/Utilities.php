<?php
function OutputAllToLog($LogFile)
{
	$log_file = fopen($LogFile, "a");
	if ($log_file)
	{
		if (isset($_SESSION['mes']['0'])) foreach ($_SESSION['mes']['0'] as $valMes) if ($valMes['text'] != "" && $valMes['log']) fwrite($log_file, $valMes['time']." ".strip_tags($valMes['text'])."\r\n");
		if (isset($_SESSION['mes']['1'])) foreach ($_SESSION['mes']['1'] as $valMes) if ($valMes['text'] != "" && $valMes['log']) fwrite($log_file, $valMes['time']." ".strip_tags($valMes['text'])."\r\n");
		if (isset($_SESSION['mes']['c'])) foreach ($_SESSION['mes']['c'] as $valMes) if ($valMes['text'] != "" && $valMes['log']) fwrite($log_file, $valMes['time']." ".strip_tags($valMes['text'])."\r\n");
		if (isset($_SESSION['mes']['m'])) foreach ($_SESSION['mes']['m'] as $valMes) if ($valMes['text'] != "" && $valMes['log']) fwrite($log_file, $valMes['time']." ".strip_tags($valMes['text'])."\r\n");
		if (isset($_SESSION['mes']['u'])) foreach ($_SESSION['mes']['u'] as $valMes) if ($valMes['text'] != "" && $valMes['log']) fwrite($log_file, $valMes['time']." ".strip_tags($valMes['text'])."\r\n");
		fclose($log_file);
	}
}
function OutputOneToLog($Mes, $LogFile)
{
	$log_file = fopen($LogFile, "a");
	if ($log_file)
	{
		if ($Mes['text'] != "" && $Mes['log']) fwrite($log_file, $Mes['time']." ".strip_tags($Mes['text'])."\r\n");
		fclose($log_file);
		return true;
	}
	return false;
}
function GetMessageDate()
{
	date_default_timezone_set("UTC");
	$cdate = getdate();
	return sprintf("%d-%'.02d-%'.02d %'.02d:%'.02d:%'.02d ", $cdate['year'], $cdate['mon'], $cdate['mday'], $cdate['hours'], $cdate['minutes'], $cdate['seconds']);
}
function AllMesCount()
{
	$amc = 0;
	foreach (array_keys($_SESSION['mes']) as $k) if (count($_SESSION['mes'][$k]) > 0) $amc += count($_SESSION['mes'][$k]);
	return $amc;
}
function ViewMesPortion($indent, $MesPortion, $C_name, $n, &$i_cur, $last_mes, &$block, $status_arr)
{
	if (count($MesPortion) > 0)
	{
		if ($C_name == "") echo "<tr><td></td></tr>";
		else
		{
			echo "<tr><td></td></tr>";
			echo "<tr><td>".Title(399)." <b>".$C_name."</b></td></tr>";
		}
		for ($i = 0; $i < count($MesPortion) && $i_cur + $i < $last_mes; $i++)
		{
			if ($MesPortion[$i]['text'] != "" && !isset($MesPortion[$i]['now']))
			{
				echo "<tr>";
					echo "<td>";
						if ($MesPortion[$i]['status'] == "nothing") echo $indent.$MesPortion[$i]['text'];
						else echo "<font color='".$status_arr[$MesPortion[$i]['status']]."'>".$indent.$MesPortion[$i]['text']."</font>";
						if (isset($MesPortion[$i]['goto']))
						{
							echo "<button name='topos-".(string)($MesPortion[$i]['goto'])."-".$n."' type='submit' style='text-align:center' value='*'>go to this value</button>"; 
							$block['cat_goto'] = true;
						}
					echo "</td>";
				echo "</tr>";
			}
		}
        $i_cur += $i;
	}
}
function ViewMessages($to_log, $log_file, $lim_values, $C, $del_row, &$block, $del_flag)
{
	$status_arr = array("error"=>"#FF0000", "warning"=>"#009900", "statement"=>"#0000FF", "nothing"=>"");
	$single_log = false;
	if ($to_log) OutputAllToLog($log_file);
	else foreach (array_keys($_SESSION['mes']) as $k) if (isset($_SESSION['mes'][$k])) foreach ($_SESSION['mes'][$k] as $v) if ($v['text'] != "" && !$to_log && isset($v['log'])) $single_log = OutputOneToLog($v, $log_file);
	echo "<table>";
		$last_mes = AllMesCount();
		$i_cur = 0;
		if (count($C) > 0)
		{
			if (isset($_SESSION['mes']['1']) && isset($C['1']['name'])) ViewMesPortion(" -- ", $_SESSION['mes']['1'], $C['1']['name'], "1", $i_cur, $lim_values, $block, $status_arr);
			if (isset($_SESSION['mes']['0']) && $i_cur < $lim_values) ViewMesPortion(" -- ", $_SESSION['mes']['0'], $C['0']['name'], "0", $i_cur, $lim_values, $block, $status_arr);
		}
		if (isset($_SESSION['mes']['c']) && $i_cur < $lim_values) ViewMesPortion("", $_SESSION['mes']['c'], "", "", $i_cur, $lim_values, $block, $status_arr);
		if (isset($_SESSION['mes']['m']) && $i_cur < $lim_values) ViewMesPortion("", $_SESSION['mes']['m'], "", "", $i_cur, $lim_values, $block, $status_arr);
		if (isset($_SESSION['mes']['u']) && $i_cur < $lim_values) ViewMesPortion("", $_SESSION['mes']['u'], "", "", $i_cur, $lim_values, $block, $status_arr);
		if ($i_cur < $last_mes) echo "<tr><td>".str_repeat(" ", 20)." - ... ".Title(628)."</td></tr>";
		if ($to_log || $single_log) echo "<tr><td><b>".Title(629)."</b></td></tr>";
	echo "</table>";
	if ($del_flag && count($del_row) > 0 && count($del_row['0']) + count($del_row['1']) > 0) 
	{
		echo "<table frame='border'>";
			echo "<tr>";
				echo "<td>Do You sure You want delete the references listed above?</td>";
				echo "<td>";
					echo "<button name='answer_yes' type='submit' style='text-align:center' value='*'>".Title(408)."</button>";
					echo "<button name='answer_no' type='submit' style='text-align:center' value='*'>".Title(409)."</button>";
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		$block['cat_del'] = true;
	}
}
function ViewNowMessages()
{
	$status_arr = array("error"=>"#FF0000", "warning"=>"#009900", "statement"=>"#0000FF", "nothing"=>"");
	foreach (array_keys($_SESSION['mes']) as $k)
	{
		if (isset($_SESSION['mes'][$k]))
		{
			foreach ($_SESSION['mes'][$k] as $v)
			{
				if ($v['text'] != "" && isset($v['now']))
				{
					if ($v['status'] == "nothing") echo "<br>".$v['text'];
					else echo "<br><font color='".$status_arr[$v['status']]."'>".$v['text']."</font>";
				}
			}
		}
	}
}

?>
