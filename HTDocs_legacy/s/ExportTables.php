<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<SCRIPT language=JavaScript>function db_list_on() {document.export_form.db_list_s.value = '*'; export_form.submit();}</SCRIPT>
<?php
require_once("ExportTablesUtils.php");

session_start();
$Mes = "";
if (count($_POST) == 0)
{
	$_SESSION['db'] = "";
	$_SESSION['table'] = "";
    GetListDBs("_Credentials.txt", $Mes);
    $_SESSION['table_list'] = array("");
}
else
{
	$_SESSION['db'] = $_POST['db'];
	$_SESSION['table'] = $_POST['table'];
}
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	switch ($str_key)
	{
		case "db_list_s"  : AfterSelectDBList($sw_break, $Mes); break;
		case "file_export": $Mes = ExportToFile(); break;
		case "file_exit"  : if (count($_SESSION) > 0) session_destroy(); exit("End of import/export");
		default			  : $sw_break = false;
	}
	if ($sw_break) break;
}
?>
<form method="post" id="export_form" name="export_form">
	<table width="100%">
        <tr>
            <td width="20%">Choose database : </td>
            <td><?php echo SelectTag("db", $_SESSION['db_list'], $_SESSION['db'], "db_list_s", false, "", "db_list_on"); ?></td>
        </tr>
        <tr>
            <td width="20%">Choose table (if necessary) : </td>
            <td><?php echo SelectTag("table", $_SESSION['table_list'], $_SESSION['table']); ?></td>
        </tr>
    </table>
    <button name='file_export' type='submit' value='*'>Export</button>
    <button name='file_exit' type='submit' value='*'>Exit</button>
    <?php if ($Mes != "") echo "<br><br><font color='#FF0000'>".$Mes."</font>"; ?>
</form>

