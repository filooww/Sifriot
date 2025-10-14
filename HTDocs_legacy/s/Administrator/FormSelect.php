<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.invisible_button {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin}</style>
	<style>.save_button {background-color:#CCFF00;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.mess_exit {color:#0000FF; font-weight:bold;}</style>
	<style>.round_button {color:#000000; border:none; border-radius:13px; background: rgb(255,255,255) linear-gradient(rgb(255,255,255), rgb(0,0,0));}</style>
</head>
<?php
require_once("Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$_SESSION['db_info'] = array();
if (isset($_POST['DB_exit'])) exit("<div align='center' class='mess_exit'>Session completed</div>");
else
{
	foreach (array_keys($_POST) as $str_key)
	{
		$k = explode("-", $str_key);
		if ($k[0] == "sel" && isset($_POST[$str_key]))
		{
			$_SESSION['db_info'] = DBInfoSelect($_SESSION['arr_db'][(integer)$k[1]]);
			header("Location: ".$_SESSION['return_point']);
			break;
		}
	}
}
?>
<form method="post">
	<div align="center"><h3><b>Data base list</b></h3></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table><tr><td><button name="DB_exit" type="submit" title="save information of the data bases" class="exit_button">Exit</button></td></tr></table>
	<table>
		<?php
		foreach (array_keys($_SESSION['arr_db']) as $k)s
		{
			echo "<tr>";
				echo "<td width='2%' align='center'><button name='sel-".(string)$k."' type='submit' title='".Title(119)."' class='round_button' value='*' style='text-align:center'>.</button></td>";
				echo "<td>".$v['db_comment']."</td>";
			echo "</tr>";
		}
		?>
	</table>
<form>
