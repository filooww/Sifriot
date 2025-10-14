<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equi="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.exit_button {background-color:#00FFCC;}</style>
	<style>.button_save {background-color:#00CC99;}</style>
	<style>.color_odd {background-color:#DDDDDD; border:none;}</style>
	<style>.color_even {background-color:#FFFFFF; border:none;}</style>
	<style>.align_text {text-align:center;}</style>
	<style>.view_symbols {background-color:#FFFF99; font:bold;}</style>
	<style>.space_symbols {background-color:#FFFF99; color:#FFFF99;}</style>
	<style>.emp {background-color:#FFFFFF; color:#FFFFFF;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
</head>
<SCRIPT language=JavaScript>
function user_lang_on() {document.field_edit_form.user_lang_s.value = '*'; field_edit_form.submit();}
function usings_on() {document.field_edit_form.usings_s.value = '*'; field_edit_form.submit();}
function table_on() {document.field_edit_form.table_s.value = '*'; field_edit_form.submit();}
</SCRIPT>

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/SpecialTextsUtils.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableTest.php");
require_once("FieldUtilities.php");
require_once("FieldEditUtilities.php");

session_start();
$Mes = array();
$fl_edit = false;
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000");
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
if (count($_POST) == 0) $_SESSION['field_param'] = ReadFieldParameters();
elseif ($_SESSION['user_working_mode'] == 1) PostFieldToParam();
foreach ($_POST as $str_key => $str_v)
{
	$k = explode("|", $str_key);
	$s_k = (count($k) == 1) ? $str_key : $k[0];
	$sw_break = true;
	switch ($s_k)
	{
        case "idle_button"      : break;
		case "user_lang_s"		: SetUserLangField($dbh_sys, $sw_break); break;
		case "usings_s"			: UsingProc($dbh_sys, $sw_break); break;
		case "table_s"			: AfterTableChoice($sw_break); break;
		case "group_symbol"		: $_SESSION['field_param']['illegals'] .= $_SESSION['char_group']; break;
		case "field_save"		: $fl_edit = SaveFieldDefinition($dbh, $Mes); break;
		case "b_check"			: $_SESSION['field_param'][$k[1]] = !$_SESSION['field_param'][$k[1]]; if ($k[1] == "s_mode") AfterFilterModeCheck(); break;
		case "exit_field_edit"	: if (FieldDefinitionExit($Mes)) header("Location: FieldForm.php"); break;
		default					: $sw_break = false;
	}
	if ($sw_break) break;
}
if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
if (count($_POST) > 0 && count($Mes) == 0 && !$fl_edit) TestFieldParameters($Mes);
?>
<form method="post" id="field_edit_form" name="field_edit_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
	<?php SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on");?>
	<div align="center"><font size="+2"><b><?php echo Title(339);?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td><button name="exit_field_edit" type="submit" value="*" class="exit_button"><?php echo Title(8);?></button></td>
			<?php
			if ($_SESSION['user_working_mode'] == 1)
			{
				$str = "<td><button name='field_save|".$_SESSION['f_k'][1]."' type='submit' value='*' class='button_save'>".Title(30)."</button></td>";
				if (isset($Mes['0'])) $str .= $Mes[0];
				echo $str;
			}
			?>
		</tr>
	</table>
	<?php
	echo "<br>".Title(381)." <b>".$_SESSION['mandatory_db_tables'][$_SESSION['f_k'][0]]."</b>, ".Title(382)." <b>".$_SESSION['f_k'][1]."</b>";
	echo "<br>".Title(378)." <b>".ViewFieldUsing($_SESSION['field_param']['using'])."</b><font class='emp'>X</font>";
	if ($_SESSION['user_working_mode'] == 1) SelectTag("using", $_SESSION['field_using'], $_SESSION['field_using'][0], "usings_s", true, "", "usings_on"");
	?>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<?php
		$even = false;
		CaseField("b_check|key", "key", false, "check", $even, "center", array(), Title(342), $Mes);
		CaseField("screen_order", "screen_order", false, "text", $even, "", array(), Title(344), $Mes, false, "align_text");
		CaseField("b_check|unique", "unique", false, "check", $even, "center", array(), Title(345), $Mes);
		CaseField("name", "name", false, "text", $even, "", array(), FTM(Title(347))." ".Title(346), $Mes);
		CaseField("type", "type", false, "select", $even, "", $_SESSION['field_types'], Title(349), $Mes);
		CaseField("f_align", "f_align", false, "select", $even, "", $_SESSION['field_align'], Title(352), $Mes);
		CaseField("b_check|interval", "interval", false, "check", $even, "center", array(), Title(353), $Mes);
		CaseField("size", "size", false, "text", $even, "", array(), Title(354), $Mes, false, "align_text");
		CaseField("t_prc", "t_prc", false, "text", $even, "", array(), Title(355), $Mes, false, "align_text");
		CaseField("table", "table", true, "select", $even, "", $_SESSION['reference_catalogs'], Title(357), $Mes);
		CaseField("b_check|blank", "blank", false, "check", $even, "center", array(), Title(364), $Mes);
		CaseField("b_check|field_check", "field_check", false, "check", $even, "center", array(), Title(368), $Mes);
		CaseField("illegals", "illegals", false, "text",  $even, "", array(), Title(367), $Mes, false, "", "group_symbol", "i_h", "GoToP");
		CaseField("default", "default", false, "text", $even, "", array(), Title(369), $Mes);
		CaseField("b_check|s_mode", "s_mode", true, "check", $even, "center", array(), Title(366), $Mes);
		CaseField("filter_md", "filter_md", false, "select", $even, "", (($_SESSION['field_param']['s_mode']) ? $_SESSION['compare_mode'] : array()), Title(372), $Mes);
		CaseField("sort_sm", "sort_sm", false, "select", $even, "", (($_SESSION['field_param']['s_mode']) ? $_SESSION['sort_mode'] : array()), Title(373), $Mes);
		CaseField("b_check|comm", "comm", false, "check", $even, "center", array(), Title(371), $Mes);
		?>
	</table>
	<input type="hidden" name="table_s" value="">
</form>

