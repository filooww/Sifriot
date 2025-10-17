<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<SCRIPT language=JavaScript>
function db_list_on {document.export.db_list_s.value='*'; export.submit();}
function tables_on {document.export.tables_s.value='*'; export.submit();}
</SCRIPT>


<?php
require_once 'ExportTablesUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';

/*
USE database;
SELECT @@character_set_database;
SELECT @@collation_database;
*/

session_start();
$Mes = [0, ''];
if (count($_POST) == 0) {
    $_SESSION['db'] = '';
    $_SESSION['table'] = '';
    GetListDBs('_Credentials.txt', $Mes); // $_SESSION['db_list']
    $_SESSION['table_list'] = [''];
} else {
    $_SESSION['db'] = $_POST['db'];
    $_SESSION['table'] = $_POST['table'];
}
foreach (array_keys($_POST) as $str_key) {
    $sw_break = true;
    switch ($str_key) {
        case 'db_list_s': AfterSelectDBList($sw_break, $Mes);
            break;
        case 'tables_s': AfterSelectTableList($sw_break);
            break;
        case 'file_export': ExportToFile($Mes);
            break;
        case 'file_exit': if (count($_SESSION) > 0) {
            session_destroy();
        } exit('End of import/export');
        default: $sw_break = false;
    }
    if ($sw_break) {
        break;
    }
}
?>
<form method="post" id="export" name="export">
    <table width="100%">
        <tr>
            <td width="10%"></td>
            <td width="30%"><button name='file_export' type='submit' value='*'>Export</button></td>
            <td><button name='file_exit' type='submit' value='*'>Exit</button></td>
        </tr>
    </table>
    <?php
//    if (count($_POST) > 0)
//    {
        echo "<hr align='left' size='2' noshade='noshade' color='#000000' >";
echo '<br>Choose database : ';
SelectTag('db', $_SESSION['db_list'], $_SESSION['db'], 'db_list_s', false, '', 'db_list_on');
echo "<hr align='left' size='1' noshade='noshade' color='#000000' >";
echo '<br>Choose table (if necessary) : ';
SelectTag('table', $_SESSION['table_list'], $_SESSION['table'], 'tables_s', false, '', 'tables_on');
//    }
if ($Mes[0] > 0) {
    echo "<hr align='left' size='1' noshade='noshade' color='#000000' >";
    echo "<font color='FF0000'><b>".$Mes[1].'</b>';
}
?>
</form>

                                                              •
