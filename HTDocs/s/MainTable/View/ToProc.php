<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/LogProcessing/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/SelectFromCatalog.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/_MainTable/SetSession.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/_MainTable/CommonUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/_MainTable/MTRequests.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/UserList.php");

session_start();
$Mes = array();
$dbh = GetDB($_SESSION['db_info']['db_name'], $_SESSION['db_info']['S_name'], $_SESSION['db_info']['U_name'], $_SESSION['db_info']['U_pass'], $Mes, $_SESSION['db_info']['DB_coding']);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['db_name']."</b>|FF0000", 1);
if (GetUpdateFlag($dbh) == 1) ExitSession(Title(53), 1);
else InitSession($dbh, "view");
?>
