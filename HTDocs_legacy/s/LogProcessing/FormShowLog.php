<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
 
session_start();
if (isset($_POST['clear_log']))
{
	$fn = $_SESSION['conf']['upload_log'];
	if (ResExists($fn)) unlink($fn);
	$log_file = fopen($_SESSION['conf']['upload_log'], "w");
	if ($log_file !== false) fclose($log_file);
}
?>

<!-- <form action="FormShowLog.php" method="post"> -->
<form method="post">
	<div align="center"><h3><b>The message log</b></h3></div>
<?php
	if (isset($_SESSION['conf']['upload_log']))
	{
		if (file_exists($_SESSION['conf']['upload_log']))
		{
			$log_file = fopen($_SESSION['conf']['upload_log'], "r");
			if ($log_file === false) echo "<div align='center'><h3><<< Failure on file opening >>></h3></div>";
			else
			{
				$fl = false;
				if ($log_file)
				{
					while (($strMes = fgets($log_file)) != false)
					{
						echo $strMes."<br>";
						$fl = true;
					}
					fclose($log_file);
				}
				if (!$fl) echo "<div align='center'><h3><<< Log is empty >>></h3></div>";
			}
		}
		else echo "<div align='center'><h3><<< Log file not exists >>></h3></div>";
	}
	else echo "<div align='center'><h3><<< Log is not determined >>></h3></div>";

?>
	<table>
		<tr>
			<td><input name="clear_log" type="submit" value="Clear log"></td>
		</tr>
	</table>
</form>

