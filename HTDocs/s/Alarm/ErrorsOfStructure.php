<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.exit_button {color:#FFFFFF; font-size:120%; font-weight:700; border:1px solid rgb(250,172,17); border-radius:7px; background:rgb(56,81,202) linear-gradient(rgb(56,81,202), rgb(55,180,202));}</style>
</head>

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleServices.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitlePortion.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/NoTitle.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession("No connection to system data base|FF0000");
if (isset($_POST['sys_exit'])) ExitSession();
if ($_SESSION['alarm'])
{
    $title_mes = "Errors were found in the structure of the system tables (see below). They must be corrected using database server commands. Contact system administrator.";
    $title_button = "Exit";
}
else
{
    $title_mes = Title(607)." ".Title(613).".";
    $title_button = Title(8);
}
?>
<form method="post" id="manager_no_title_form" name="manager_no_title_form">
    <table width="100%"><tr><td><button name="sys_exit" type="submit" value="*" class="exit_button"><?php echo $title_button;?></button></td></tr></table>
    <div align='center'><b><?php echo $title_mes; ?></b></div>
    <hr align="left" size="1" noshade="noshade" color="#000000" >
    <?php
    foreach (array_keys($_SESSION['structure_errors']) as $table)
    {
        echo "<br>".Title(365)." <font size='+2'><b>".$table."</b></font>";
        foreach ($_SESSION['structure_errors'][$table] as $field => $action) echo "<br>".$action;
        echo "<hr align='left' size='1' noshade='noshade' color='#000000' >";
    }
    ?>
</form>

