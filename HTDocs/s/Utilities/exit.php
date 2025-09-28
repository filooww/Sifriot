<head>
	<style>.mess_red {color:#FF0000; font-weight:bold}</style>
	<style>.mess_green {color:#00FF00; font-weight:bold}</style>
	<style>.mess_blue {color:#0000FF; font-weight:bold}</style>
	<style>.mess_black{color:#000000; font-weight:bold}</style>
</head>

<?php
function GetColor($get_par)
{
	$cl = "";
	$color_par = substr($get_par, 0, 1);
	switch ($color_par)
	{
		case "1": $cl = " class='mess_red'"; break;
		case "2": $cl = " class='mess_green'"; break;
		case "3": $cl = " class='mess_blue'"; break;
		default : $cl = " class='mess_black'";
	}
	return $cl;
}

session_start();
$exit_str = "";
foreach ($_GET as $v) $exit_str .= "<div align='center'".GetColor($v)."><h".substr($v, 1, 1).">".substr($v, 2)."</h".substr($v, 1, 1)."></div>";
session_destroy();
if ($exit_str == "") $exit_str = "<div align='center' class='mess_blue'>Session completed</div>"; //titles #16
exit($exit_str);
?>
