<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.start_load {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>
	<style>.b_yes {color:#000000; background-color:#FFFFFF}</style>
	<style>.b_no {color:#FFFFFF; background-color:#FFFFFF}</style>
	<style>.catalog_exit {background-color:#00FFCC;}</style>
</head>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once 'MainUtilities.php';
require_once 'SelectFromCatalog.php';
require_once 'DoubleCatalog.php';
require_once 'CopyPasteBranch.php';
require_once 'Update.php';
require_once 'Test.php';
require_once 'TestInit.php';
require_once 'Common.php';
require_once 'Screen.php';
require_once 'Filter.php';
require_once 'Search.php';
require_once 'Navigation.php';
require_once 'Forms.php';
require_once 'CatalogButtons.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LogProcessing/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/TableUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/_MainTable/SetSession.php';

session_start();
if ($_SESSION['db_info']['err'] != '') {
    ExitSession($_SESSION['db_info']['err'].'|FF0000');
}
$Mes = [];
$dbh = GetDB($_SESSION['db_info']['DB_name'], $_SESSION['db_info']['S_name'], $_SESSION['db_info']['U_name'], $_SESSION['db_info']['U_pass'], $Mes, $_SESSION['db_info']['DB_coding']);
if (! $dbh) {
    ExitSession(Title(1).' <b>'.$_SESSION['db_info']['DB_name'].'</b>|FF0000', 1);
}
SetUpdateFlag($dbh, 1);
InitSession($dbh);
header('Location: MainForm.php');
?>
