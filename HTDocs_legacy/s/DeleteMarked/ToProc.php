<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/UserList.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/ConfigUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/TableUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/FieldRead.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/_MainTable/SetSession.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/_MainTable/MTRequests.php';

session_start();
if ($_SESSION['db_info']['err'] != '') {
    ExitSession($_SESSION['db_info']['err'].'|FF0000', 1);
}
$Mes = [];
$dbh = GetDB($_SESSION['db_info']['DB_name'], $_SESSION['db_info']['S_name'], $_SESSION['db_info']['U_name'], $_SESSION['db_info']['U_pass'], $Mes, $_SESSION['db_info']['DB_coding']);
if (! $dbh) {
    ExitSession(Title(1).' <b>'.$_SESSION['db_info']['DB_name'].'</b>|FF0000', 1);
}
$rr = UserActivityCheck($dbh, $_SESSION['db_info']['DB_name']);
if ($rr != '') {
    ExitSession($rr);
} else {
    SetUpdateFlag($dbh, 1);
    InitSession($dbh);
    $_SESSION['ext_call'] = '';
    $_SESSION['item_arr'] = GetMTPortion($dbh, $_SESSION['db_info']['t_main'], $_SESSION['p_start'], $_SESSION['conf']['portion_item'], $_SESSION['PR'], $_SESSION['conf']['match_case'], 'inactive');
    $_SESSION['p_count']['total'] = GetMTLimit($dbh, $_SESSION['db_info']['t_main'], 'total', $_SESSION['PR']);
    $_SESSION['p_count']['active'] = GetMTLimit($dbh, $_SESSION['db_info']['t_main'], 'active', $_SESSION['PR']);
    $_SESSION['p_count']['inactive'] = $_SESSION['p_count']['total'] - $_SESSION['p_count']['active'];
    $_SESSION['p_code'] = GetInitItemCode($dbh, $_SESSION['db_info']['t_main'], $_SESSION['PR']['con'], true);
    header('Location: List.php');
}
?>
