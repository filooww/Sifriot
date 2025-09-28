<!-- enter point -->
<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmDBUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmReferences.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmManagerUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Algorithms/AlgorithmTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Codings/CodingUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseCorrect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/ManagerDBCreate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/UserDBCreate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Fields/FieldUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/SpecialTextsUtils.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserList/UserListFilter.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/UserList/UserListPortion.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$no_session = (count(array_keys($_SESSION)) == 0);
$dbh = GetManagerDBFile("db_manager", $_SERVER['DOCUMENT_ROOT']."/s/_Credentials.txt");
if (!$dbh) ExitSession("No connection to system data base|FF0000");
if ($no_session)
{
    $_SESSION['structure_errors'] = array();
    $_SESSION['alarm'] = false;
    $_SESSION['preliminary_flags'] = array("table_errors"=>array(), "no_existed_tables"=>array());
    TestManagerTablesExist($dbh);
    if (count($_SESSION['preliminary_flags']['no_existed_tables']) > 0)
    {
        $dbh = GetOnlyDB("db_manager");
        if (!$dbh) ExitSession("No connection to system data base|FF0000");
    }
    SystemConfigs($dbh);
    $_SESSION['mandatory_language_errors'] = array();
    $_SESSION['scripts_title_ids'] = array();
    TitleByDirs($_SERVER['DOCUMENT_ROOT']."/s");
    $_SESSION['ex_title_ids'] = AllSpecialTitleNumbers($dbh);
    $_SESSION['structure_errors'] = TestManagerTableStructure($dbh, ManagerDataBaseStructureDefinition());
    if (count($_SESSION['structure_errors']) == 0)
    {
        $_SESSION['common_langs'] = ReadLanguages($dbh, false);
        $_SESSION['user_langs'] = SetLanguageList(2);
        $_SESSION['titles'] = GetTitlesByLanguage($dbh, 1);
        $Mes = TestMissingTitles();
        if ($Mes != "") ExitSession($Mes."`"."Contact system administrator.");
        if (UserListEmpty($dbh)) ExitSession("User list is empty. Contact system administrator.");
        $_SESSION['user_lang'] = array(1, "English");
        $_SESSION['arr_db'] = CreateDBArray($dbh);
        $_SESSION['coding_list'] = SetCodingList($dbh);
        CorrectCodingList($dbh);
        CorrectLanguageList($dbh);
        CorrectDBList($dbh);
        TestInitDBList();
        ManagerPreliminaryCheck($dbh);
        $_SESSION['pre_ref'] = InvalidReferenceTable($dbh);
        $_SESSION['alarm'] = (IsReferenceErrors() || count($_SESSION['preliminary_flags']['table_errors']) > 0 || count($_SESSION['preliminary_flags']['no_existed_tables']) > 0);
    }
    if (UserListEmpty($dbh))
    {
        mysqli_query($dbh, "ALTER TABLE user_ident AUTO_INCREMENT = 1");
        mysqli_query($dbh, "INSERT INTO user_ident (name,pass,user_priority,use_lang_id) VALUES ('first admin','1!9(3#8*q&j@',99,1)");
        ExitSession("The user list is empty, so a specisl user has been added. Contact system administrator.");
    }
    if (count($_SESSION['structure_errors']) == 0) header("Location: UserEnter/UserEnterLogin.php");
    else
    {
        $_SESSION['alarm'] = true;
        header("Location: UserEnter/UserEnterAlarmLogin.php");
    }
}
else ExitSession("You are already logged in to the site in this browser"."|FF0000", "0", false);
?>

