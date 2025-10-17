<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.cell_invisible {color:#FFFFFF; background-color:#FFFFFF; border:none;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.new_row {background-color:#00CC99;}</style>
	<style>.special_texts {background-color:#CC99FF;}</style>
	<style>.integrity_check {background-color:#00CCFF;}</style>
	<style>.data_num {text-align:right;}</style>
	<style>.data_ital {font-style:italic;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
	<style>.table_color {background-color:#CCCCCC;}</style>
	<style>.i_r {background-color:#01CCFF;}</style>
    <style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
    <style>.dis_text {color:#CCCCCC;}</style>
    <style>.id_class {text-align:right;text-align:right; font-weight:bold;}</style>
    <style>.t_bord {border-spacing:1px;}</style>
	<style>.emph_data {background-color:#CCFFFF;}</style>
</head>
<SCRIPT language=JavaScript>
function user_lang_on() {document.title_form.user_lang_s.value = '*'; title_form.submit();}
function title_lang_on() {document.title_form.title_lang_s.value = '*'; title_form.submit();}
function row_mode_on() {document.title_form.title_modes.value = '*'; title_form.submit();}
function replace_on(obj) {document.title_form.replace_s.value = obj.name; title_form.submit();}
</SCRIPT>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Administrator/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmManagerUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmReferences.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Languages/LanguageUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/ManagerDBCreate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUpdate.php';
require_once 'TitleNavigation.php';
require_once 'TitlePortion.php';
require_once 'TitleSelect.php';
require_once 'TitleServices.php';
require_once 'TitleUpdate.php';
require_once 'TitleUtilities.php';
require_once 'TitleTest.php';
require_once 'TitleFilter.php';
require_once 'SpecialTextsUtils.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';

session_start();
$dbh = GetOnlyDB('db_manager');
if (! $dbh) {
    ExitSession(Title(252).'|FF0000');
}
$Mes = [];
$_SESSION['del_mes'] = ['', ''];
$page_mes = '';
$_SESSION['new_special_number'] = '';
if (count($_POST) == 0) {
    $_SESSION['spec_title'] = Title(465);
    $_SESSION['inv_ref'] = false;
    SetInitReplaceIDs('interface_texts', ['li' => 'title_langs']);
    if (PermitInvRef('interface_texts', ['li'])) {
        $_SESSION['replace_mes'] = [];
    }
    $_SESSION['spec_titles'] = ['table_types' => Title(266), 'compare_mode' => Title(400), 'sort_mode' => Title(424), 'field_align' => Title(451), 'field_using' => Title(452), 'field_types' => Title(453), 'group_types' => Title(454), 'z_o' => Title(582)];
} else {
    ListParamSave($Mes);
}
foreach ($_POST as $str_key => $str_v) {
    $k = explode('|', $str_key);
    $s_k = (count($k) == 1) ? $str_key : $k[0];
    $sw_break = true;
    switch ($s_k) {
        case 'idle_button': break;
        case 'user_lang_s': AfterTitleLangChoice($dbh, $sw_break);
            break;
        case 'title_lang_s': AfterTitleSelLangChoice($dbh, $sw_break);
            break;
        case 'title_modes': SwitchTitleMode($dbh, $sw_break);
            break;
        case 'replace_s': if (AfterReplaceSelect(['li' => $_SESSION['title_langs']], $sw_break)) {
            TitleFilter($dbh);
        } break;
        case 'title_exit': $goto = TitleExit($dbh, $Mes);
            if ($goto != '' && count($Mes) == 0) {
                header('Location: '.$goto.'.php');
            } break;
        case 'special_texts': SwitchSpecialTexts($dbh, $Mes);
            TitleFilter($dbh);
            break;
        case 'integrity_check': IntegrityCheck($dbh);
            TitleFilter($dbh);
            break;
        case 'title_identifier': TitleEdit($dbh, $k[1], $k[2]);
            TitleFilter($dbh);
            break;
        case 'title_lang_insert': TitleLangInsert($dbh, (int) $k[1], (int) $k[2]);
            TitleFilter($dbh);
            break;
        case 'list_minus': ChangeTitleScreen($dbh, -1, $page_mes);
            break;
        case 'list_plus': ChangeTitleScreen($dbh, 1, $page_mes);
            break;
        case 'list_height_b': ChangeTitleScreen($dbh, $_POST['list_height'], $page_mes);
            break;
        case 'title_navigation': TitleNavigation($dbh, $k[1]);
            break;
        case 'title_filter': TitleFilter($dbh);
            break;
        case 'add_new_title': AddNewTitle($dbh);
            break;
        case 'add_special': $_SESSION['special_interface'][$k[1]]['numbers'][] = ['', 0];
            $_SESSION['new_special_number'] = $k[1];
            TitleFilter($dbh);
            break;
        case 'title_references': GetInvRef('interface_texts', ['li' => $_SESSION['title_langs']]);
            TitleFilter($dbh);
            break;
        case 'title_mark_del': $_SESSION['del_mes'] = TitleMarkDelete($k[1], $k[2]);
            if ($_SESSION['del_mes'][1] == '') {
                TitleFilter($dbh);
            } break;
        case 'title_del': TitleDelete($dbh);
            break;
        case 'title_no_del': unset($_SESSION['title_del']);
            TitleFilter($dbh);
            break;
        case 'replace_id': $_SESSION['replace_mes'] = [];
            ReplaceTitleLangIDs($dbh, $k[1], $k[2]);
            break;
        case 'reset_replace_mes': if (isset($_SESSION['replace_mes'])) {
            unset($_SESSION['replace_mes']);
        } break;
        default: $sw_break = false;
    }
    if ($sw_break) {
        break;
    }
}
if ($_SESSION['admin_mes'][0] > 0) {
    echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1].'</b></h3></font></div>';
}
$exit_block = (isset($_SESSION['title_edit_row']) && ! $_SESSION['alarm'] || isset($_SESSION['title_del'])) ? ' disabled' : '';
$upd_block = (isset($_SESSION['title_edit_row']) || $_SESSION['title_filter'] != '') ? ' disabled' : '';
$add_block = (isset($_SESSION['title_edit_row']) || $_SESSION['title_filter'] != '' || $_SESSION['user_working_mode'] == 0) ? ' disabled' : '';
$t_block = (! isset($_SESSION['title_edit_row'])) ? '' : ' disabled';
?>

<form method="post" id="title_form" name="title_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
    <?php SelectTag('user_lang', $_SESSION['user_langs'], $_SESSION['user_lang'][1], 'user_lang_s', false, '', 'user_lang_on', isset($_SESSION['title_del'])); ?>
	<div align="center"><font size="+2"><b><?php echo Title(128); ?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
    <?php
    if ($_SESSION['inv_ref'] && $_SESSION['title_filter'] == '' && PermitInvRef('interface_texts', ['li'])) {
        InvRefTable('interface_texts', 'emph_data', ['li' => $_SESSION['title_langs']]);
    }
require_once 'TitleFormButtons.php';
require_once 'TitleFormPageAdjust.php';
require_once 'TitleFormFilter.php';
if (isset($_SESSION['mes_integrity'])) {
    foreach ($_SESSION['mes_integrity'] as $z) {
        $str = PrintIntegrityMes($z);
        if ($str != '') {
            echo '<div>'.$str.'</div>';
        }
    }
    echo "<hr align='left' size='1' noshade='noshade' color='#000000' >";
}
if (count($Mes) > 0) {
    foreach ($Mes as $z) {
        echo '<div>'.$z.'</div>';
    }
}
if (isset($_SESSION['special_interface']) && count($_SESSION['special_interface']) > 0) {
    require_once 'SpecialTextsForm.php';
}
echo (($_SESSION['title_filter'] == '') ? Title(51) : Title(52)).' '.Title(300).' <b>'.(string) $_SESSION['title_count'].'</b>';
?>
	<table frame="border" width="100%">
		<tr valign="top">
			<td width="3%"><?php require_once 'TitleFormNavigation.php'; ?></td>
			<td>
			<?php
        if (count($_SESSION['title_param']) == 0) {
            echo "<table align='center'><tr><td><font size='+2'><b>".Title(553).'</b></font></td></tr></table>';
        } else {
            if (isset($_SESSION['title_del'])) {
                DBMDelQuestion('modal_form', ['title_del', 'title_no_del'], [], 208, 362);
            }
            require_once 'TitlesTable.php';
        }
?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="replace_s" id="replace_s" value="">
</form>

