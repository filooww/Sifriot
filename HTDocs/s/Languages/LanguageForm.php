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
<SCRIPT language=JavaScript>function user_lang_on() {document.language_form.user_lang_s.value = '*'; language_form.submit();}</SCRIPT>
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
require_once("LanguageUtilities.php");
require_once("LanguageUpdate.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$Mes = array();
$dbh = GetOnlyDB("db_manager");
if (!$dbh) ExitSession(Title(252)."|FF0000");
if (count($_POST) == 0)
{
    $_SESSION['lang_del'] = false;
    $_SESSION['del_langs'] = array();
    $_SESSION['ref_add'] = false;
    $_SESSION['lang_param'] = ReadLanguages($dbh, true);
	TestLangList($dbh, true);
}
else foreach ($_SESSION['lang_param'] as $k => $v) if (isset($_POST["lang|".(string)$k]) && $_POST["lang|".(string)$k] != $v[0]) $_SESSION['lang_param'][$k][0] = $_POST["lang|".(string)$k];
foreach ($_POST as $str_key => $str_v)
{
	$k = explode("|", $str_key);
	$s_k = (count($k) == 1) ? $str_key : $k[0];
	$sw_break = true;
	switch ($s_k)
	{
        case "idle_button"  : TestLangList($dbh); break;
        case "user_lang_s"	: if (AfterLangChoice($dbh, "user_lang_s", "user_lang", $sw_break)) TestLangList($dbh); break;
		case "language_OK"	: if (isset($_SESSION['pre_lang_err'])) unset($_SESSION['pre_lang_err']); TestLangList($dbh); RewriteLanguages($dbh, $Mes); $_SESSION['lang_del'] = true; break;
		case "lang_exit"	: $goto = LanguageExit($dbh, $Mes); if ($goto != "") header("Location: ".$goto.".php"); break;
		case "lang_restore" : if (isset($_SESSION['pre_lang_err'])) unset($_SESSION['pre_lang_err']); $_SESSION['lang_param'][$k[1]][0] = $_SESSION['lang_param'][$k[1]][1]; TestLangList($dbh); RestoreUserLanguageList(); break;
		case "lang_del"	    : LangDel($dbh); $_SESSION['del_langs'] = array(); $_SESSION['lang_del'] = false; TestLangList($dbh); $Mes[] = "<b>".Title(182)."</b>"; RestoreUserLanguageList(); break;
		case "lang_no_del"	: $_SESSION['lang_del'] = false; DelLangListRestore(); TestLangList($dbh); break;
		default				: $sw_break = false;
	}
	if ($sw_break) break;
}
$fl_del = $_SESSION['lang_del'] && count($_SESSION['del_langs']) > 0;
$dis_del = ($fl_del) ? " disabled" : "";
?>
<form method="post" id="language_form" name="language_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
    <?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on", $fl_del);?>
    <div align="center"><font size="+2"><b><?php echo Title(267);?></b></font></div>
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
		    <td><button name="lang_exit" type="submit" title="<?php echo Title(268);?>" class="exit_button"<?php echo $dis_del; ?>><?php echo Title(8);?></button></td>
		    <?php if ($_SESSION['user_working_mode'] == 1) echo "<td><button".$dis_del." name='language_OK' type='submit' title='".Title(269)."' class='save_button'>".Title(30)."</button></td>"; ?>
	    </tr>
    </table>
    <?php if ($fl_del) DBMDelQuestion("modal_form", array("lang_del", "lang_no_del"), LangSetQuestionString(), 208, 625, 622); ?>
    <?php /*if ($_SESSION['lang_del'] && count($_SESSION['del_langs']) > 0) DBMDelQuestion("modal_form", array("lang_del", "lang_no_del"), LangSetQuestionString(), 208, 625, 622);*/ ?>
    <table>
        <tr>
            <td>
                <table>
                    <?php
                    foreach ($_SESSION['lang_param'] as $k => $v)
                    {
	                    echo "<tr valign='top'>";
	                        if (gettype($k) == "string") $zk = Title(71);
	                        else $zk = (string)$k;
		                    echo "<td><input size='3' type='text' class='right_align' name='id_lang|".$k."' value='".$zk."' disabled></td>";
		                    echo "<td><input name='lang|".(string)$k."' type='text' value='".$v[0]."'".LanguageForbid($k, $v[0]).(($_SESSION['user_working_mode'] == 0 || $fl_del) ? " disabled" : "")."></td>";
                            echo "<td>".$v[2]."</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
</form>

