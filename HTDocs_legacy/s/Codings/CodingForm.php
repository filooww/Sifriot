<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.save_button {background-color:#CCFF00;}</style>
	<style>.right_align {text-align:right; font-weight:bold;}</style>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.main_sign_align {text-align:center; font-weight:bold;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
    <style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
    <style>.i_r {background-color:#01CCFF;}</style>
	<style>.data_numeric {text-align:right;}</style>
	<style>.data_numeric_err {text-align:right; background-color:#CCCCFF;}</style>
	<style>.invisible_text {background-color:#FFFFFF; color:#FFFFFF;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.coding_form.user_lang_s.value = '*'; coding_form.submit();}</SCRIPT>
<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Administrator/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleServices.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmManagerUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmReferences.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/ManagerDBCreate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");
require_once("CodingUtilities.php");
require_once("CodingUpdate.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$Mes = array();
$fl_OK = false;
$dbh = GetOnlyDB("db_manager");
if (!$dbh) ExitSession(Title(252)."|FF0000");
if (count($_POST) == 0)
{
    $_SESSION['coding_del'] = false;
    $_SESSION['del_coding'] = array();
    $_SESSION['ref_add'] = false;
    $_SESSION['coding_list'] = SetCodingList($dbh, true);
	TestCodingList($dbh, true);
}
else foreach ($_SESSION['coding_list'] as $k => $v) if (isset($_POST["coding|".(string)$k]) && $_POST["coding|".(string)$k] != $v[0]) $_SESSION['coding_list'][$k][0] = $_POST["coding|".(string)$k];
foreach ($_POST as $str_key => $str_v)
{
	$k = explode("|", $str_key);
	$s_k = (count($k) == 1) ? $str_key : $k[0];
	$sw_break = true;
	switch ($s_k)
	{
        case "idle_button"    : TestCodingList($dbh); break;
        case "user_lang_s"	  : if (AfterLangChoice($dbh, "user_lang_s", "user_lang", $sw_break)) TestCodingList($dbh); break;
		case "coding_OK"	  : if (isset($_SESSION['pre_coding_err'])) unset($_SESSION['pre_coding_err']); TestCodingList($dbh); RewriteCoding($dbh, $Mes); $_SESSION['coding_del'] = true; break;
		case "coding_exit"	  : $goto = CodingExit($dbh, $Mes); if ($goto != "") header("Location: ".$goto.".php"); break;
		case "coding_restore" : if (isset($_SESSION['pre_coding_err'])) unset($_SESSION['pre_coding_err']); $_SESSION['del_coding'][$k[1]][0] = $_SESSION['del_coding'][$k[1]][1]; TestCodingList($dbh); break;
		case "coding_del"	  : CodingDel($dbh); $_SESSION['del_coding'] = array(); $_SESSION['coding_del'] = false; TestCodingList($dbh); $Mes[] = "<b>".Title(694)."</b>"; break;
		case "coding_no_del"  : $_SESSION['coding_del'] = false; DelCodingListRestore(); TestCodingList($dbh); break;
		default				  : $sw_break = false;
	}
	if ($sw_break) break;
}
$fl_del = $_SESSION['coding_del'] && count($_SESSION['del_coding']) > 0;
$dis_del = ($fl_del) ? " disabled" : "";
?>
<form method="post" id="coding_form" name="coding_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
    <?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on", $fl_del);?>
    <div align="center"><font size="+2"><b><?php echo FTM(Title(281), true);?></b></font></div>
    <hr align="left" size="1" noshade="noshade" color="#000000" >
    <?php
    if (count($Mes) > 0)
    {
        foreach ($Mes as $z) echo "<br>".$z;
	    echo "<hr align='left' size='1' noshade='noshade' color='#000000' >";
    }
    ?>
    <table>
	    <tr>
		    <td><button name="coding_exit"<?php echo $dis_del;?> type="submit" class="exit_button"><?php echo Title(8);?></button></td>
		    <?php if ($_SESSION['user_working_mode'] == 1) echo "<td><button name='coding_OK'".$dis_del." type='submit' class='save_button'>".Title(30)."</button></td>"; ?>
	    </tr>
    </table>
    <?php
    if ($fl_del) DBMDelQuestion("modal_form", array("coding_del", "coding_no_del"), CodingSetQuestionString(), 208, 702, 703); ?>
    <table>
        <tr>
            <td>
                <table>
                    <?php
                    foreach ($_SESSION['coding_list'] as $k => $v)
                    {
	                    echo "<tr valign='top'>";
	                        if (gettype($k) == "string") $zk = Title(254);
	                        else $zk = (string)$k;
		                    echo "<td><input size='3' type='text' class='right_align' name='id_coding|".$k."' value='".$zk."' disabled></td>";
		                    echo "<td><input name='coding|".(string)$k."' type='text' value='".$v[0]."'".CodingForbid($k, $v[0]).(($_SESSION['user_working_mode'] == 0 || $fl_del) ? " disabled" : "")."></td>";
                            echo "<td>".$v[2]."</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
</form>

