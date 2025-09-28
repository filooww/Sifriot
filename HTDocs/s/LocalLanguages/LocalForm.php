<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.save_button {background-color:#CCFF00;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.cell_invisible {color:#FFFFFF; background-color:#FFFFFF; border:none;}</style>
	<style>.table_header {background-color:#CCFFFF;}</style>
	<style>.table_color {background-color:#CCCCCC;}</style>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.even_row {background-color:#C0FFC0;}</style>
	<style>.i_r {background-color:#01CCFF;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
	<style>.del_symb {color:#0000FF; background-color:#CCFFFF;}</style>
	<style>.ref_err {background-color:#FFCCFF;}</style>
	<style>.cur_line {background-color:#FFFFCC;}</style>
	<style>.i_h {background-color:#FFFFFF;}</style>
	<style>.data_num {text-align:right;}</style>
    <style>.emph_data {background-color:#CCFFFF;}</style>
    <style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
</head>
<SCRIPT language=JavaScript>
function coding_on() {document.local_form.sel_coding_s.value = '*'; local_form.submit();}
function user_lang_on() {document.local_form.user_lang_s.value = '*'; local_form.submit();}
function local_lang_on() {document.local_form.local_lang_s.value = '*'; local_form.submit();}
function replace_on(obj) {document.local_form.replace_s.value = obj.name; local_form.submit();}
</SCRIPT>

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Administrator/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleTest.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmManagerUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Alarm/AlarmReferences.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/ManagerDBCreate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Codings/CodingUtilities.php");
require_once("LocalUtilities.php");
require_once("LocalUpdate.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$dbh = GetOnlyDB("db_manager");
if (!$dbh) ExitSession(Title(252)."|FF0000");
$Mes = array();
if (count($_POST) == 0)
{
    $_SESSION['del_local'] = array();
    $_SESSION['coding_list'] = SetCodingList($dbh);
    $_SESSION['sel_coding'] = array(GetFirstKey($_SESSION['coding_list']), $_SESSION['coding_list'][key($_SESSION['coding_list'])]);
    $_SESSION['sel_coding_for_change'] = $_SESSION['sel_coding'][0];
    $_SESSION['local_langs'] = SetLanguageList(1);
    $_SESSION['sel_local_lang'] = array(0, $_SESSION['local_langs'][0]);
    $_SESSION['sel_lang_for_change'] = 0;
    $_SESSION['local_langs_for_page'] = ChangeLocalLangs();
	$_SESSION['trans_codes'] = SetTransCodes($dbh, 0);
	$_SESSION['local_delete'] = false;
    TestAllLocalCodes($Mes);
    $_SESSION['inv_ref'] = false;
    $_SESSION['new_symb_param'] = array("", "");
    SetInitReplaceIDs("translate_table", array("cd"=>"coding_list", "li"=>"local_langs"));
    if (PermitInvRef("translate_table", array("cd", "li"))) $_SESSION['replace_mes'] = array();
}
else
{
    PostToTransliterations();
    $_SESSION['local_langs_for_page'] = ChangeLocalLangs();
}
foreach ($_POST as $str_key => $str_v)
{
	$k = explode("|", $str_key);
	$s_k = (count($k) == 1) ? $str_key : $k[0];
	$sw_break = true;
	switch ($s_k)
	{
        case "idle_button"      : break;
		case "sel_coding_s"	    : if (AfterChoiceCoding($dbh, $sw_break)) SetLocalLanguage($dbh, $Mes); break;
		case "user_lang_s"	    : if (AfterChoiceLocalLang($dbh, $sw_break)) TestAllLocalCodes($Mes); break;
		case "local_lang_s"		: if (AfterLocalLangChoice($Mes, $sw_break)) SetLocalLanguage($dbh, $Mes); break;
        case "replace_s"        : if (AfterReplaceSelect(array("cd"=>$_SESSION['coding_list'], "li"=>$_SESSION['local_langs']), $sw_break)) TestAllLocalCodes($Mes); break;
        case "change_local_ids" : MoveSymbol($dbh, $k[1], $Mes); $_SESSION['inv_ref'] = false; break;
 		case "local_exit"     	: $goto = ExitLocals($dbh, $Mes); if ($goto != "") header("Location: ".$goto.".php"); break;
		case "local_save"	    : if (count($_SESSION['del_local']) > 0) $_SESSION['local_delete'] = true; PostToTransliterations(); TestAllLocalCodes($Mes); SetLocalLanguage($dbh, $Mes); break;
		case "local_mark_delete": MarkLocalDelete($k[1]); TestAllLocalCodes($Mes, true); break;
		case "symbol_del"       : DeleteMarkedLetters($dbh); $_SESSION['del_local'] = array(); $_SESSION['local_delete'] = false; TestAllLocalCodes($Mes); break;
        case "symbol_no_del"    : $_SESSION['del_local'] = array(); $_SESSION['local_delete'] = false; break;
        case "add_new_symbol"   : AddNewSymbol($dbh); break;
		case "local_references" : $_SESSION['new_symb_param'] = array("", ""); GetInvRef("translate_table", array("cd"=>$_SESSION['coding_list'], "li"=>$_SESSION['local_langs_for_page'])); TestAllLocalCodes($Mes); break;
        case "replace_id"       : ReplaceLocalIDs($dbh, $k[1], $k[2]); break;
        case "reset_replace_mes": if (isset($_SESSION['replace_mes'])) unset($_SESSION['replace_mes']); break;
		default				    : $sw_break = false;
	}
	if ($sw_break) break;
}
if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
$dis_delete = ($_SESSION['local_delete'] ? " disabled" : "");
?>
<form method="post" id="local_form" name="local_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
    <div align="center">
        <?php
        if (gettype($_SESSION['sel_local_lang'][0]) == "integer")
        {
            echo "<font size='+2'><b>".Title(330)."</b></font><font class='cell_invisible'>X</font>";
            SelectTag("sel_coding", $_SESSION['coding_list'], $_SESSION['sel_coding'][1], "sel_coding_s", false, "", "coding_on", $_SESSION['local_delete']);
        }
        ?>
    </div>
    <?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on", $_SESSION['local_delete']);?>
	<div align="center"><font size="+2"><b><?php echo Title(125);?></b></font></div>
    <hr align="left" size="1" noshade="noshade" color="#000000" >
	<?php if ($_SESSION['inv_ref'] && PermitInvRef("translate_table", array("cd", "li"))) InvRefTable("translate_table", "emph_data", array("cd"=>$_SESSION['coding_list'], "li"=>$_SESSION['local_langs'])); ?>
	<table>
		<tr>
			<td><button name="local_exit" type="submit" title="<?php echo Title(273);?>" class="exit_button"<?php echo $dis_delete;?>><?php echo Title(8);?></button></td>
			<?php
			if ($_SESSION['user_working_mode'] == 1)
			{
                echo "<td><button name='local_save' type='submit'".$dis_delete." title='".Title(274)."' class='save_button'>".Title(30)."</button></td>";
                if (PermitInvRef("translate_table", array("cd", "li"))) echo "<td><button name='local_references' type='submit'".$dis_delete." value='*' class='i_r'>".Title(541)."</button></td>";
            }
			echo "<td><font class='cell_invisible'>X</font></td>";
			echo "<td>".Title(275)."<font class='cell_invisible'>X</font>"; SelectTag("sel_local_lang", $_SESSION['local_langs_for_page'], $_SESSION['sel_local_lang'][0], "local_lang_s", true, "", "local_lang_on", $_SESSION['local_delete']); echo "</td>";
			echo "<td><font color='#990099'> (<b>".Title(229)."</b>)</font></td>";
            ?>
		</tr>
	</table>
    <?php
    if ($_SESSION['local_delete']) DBMDelQuestion("modal_form", array("symbol_del", "symbol_no_del"), LocalSetQuestionString(), 208, 277, 584, "(<font color='#FF0000'><b>".Title(132)."</b></font>)");
    if (count($Mes) > 0) foreach($Mes as $z) echo "<br>".$z;
	echo "<hr align='left' size='1' noshade='noshade' color='#000000' >";
    if ($_SESSION['user_working_mode'] == 1 && !$_SESSION['local_delete']) require_once("LocalNewRow.php");
    if (count($_SESSION['trans_codes']) == 0) echo "<div><center><b><font size=+1>".Title(76)."</b></font></center></div>";
    else require_once("LocalTable.php");
    ?>
    <input type="hidden" name="replace_s" id="replace_s" value="">
</form>

