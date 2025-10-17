<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.cell_inv {color:#FFFFFF; background-color:#FFFFFF; border:none;}</style>
	<style>.data_num {text-align:right;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
	<style>.cancel_button {background-color:#FF0000; color:#FFFFFF;}</style>
	<style>.class_day {background-color:#CCFFCC; text-align:right;}</style>
	<style>.class_month {background-color:#66FF66; text-align:right;}</style>
	<style>.class_year {background-color:#00FFFF; text-align:right;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
	<style>.d_color {background-color:#CCFFFF; text-align:center;}</style>
	<style>.data_bold {font-weight:bold;}</style>
	<style>.data_bold_red {font-weight:bold; color:#FF0000;}</style>
	<style>.dis_text {color:#CCCCCC;}</style>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.data_numeric_bold {text-align:right; font-weight:bold;}</style>
	<style>.data_numeric_bold_red {text-align:right; font-weight:bold; color:#FF0000;}</style>
	<style>.data_center_bold {text-align:center; font-weight:bold;}</style>
	<style>.data_center_bold_red {text-align:center; font-weight:bold; color:#FF0000;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.i_r {background-color:#01CCFF;}</style>
	<style>.table_color {background-color:#CCCCCC;}</style>
    <style>.emph_data {background-color:#CCFFFF;}</style>
</head>
<SCRIPT language=JavaScript>
function user_lang_on() {document.visit_form.user_lang_s.value = '*'; visit_form.submit();}
function category_on() {document.visit_form.category_s.value = '*'; visit_form.submit();}
function visit_db_on() {document.visit_form.visit_db_s.value = '*'; visit_form.submit();}
function session_type_radio_on() {var rc = document.querySelector('input[name="session_type_radio"]:checked').value; document.visit_form.session_type_s.value = rc; visit_form.submit();}
function replace_on(obj) {document.visit_form.replace_s.value = obj.name; visit_form.submit();}
function exp_d_on() {document.visit_form.exp_d_s.value = '*'; visit_form.submit();}
function exp_m_on() {document.visit_form.exp_m_s.value = '*'; visit_form.submit();}
function exp_y_on() {document.visit_form.exp_y_s.value = '*'; visit_form.submit();}
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/SetTimerInterval.txt'; ?>
function curr_timer() {var d = new Date(); document.getElementById("t_curr").value = d.getTime(); if (document.getElementById("t_start").value != '') visit_form.submit();}
</SCRIPT>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Calendar.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/ConfigUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Administrator/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LocalLanguages/LocalUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LocalLanguages/LocalUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Languages/LanguageUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/UserList/UserListUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/UserList/UserListTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/MainTable/ItemRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmManagerUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmReferences.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/ManagerDBCreate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUpdate.php';
require_once 'VisitFormUtilities.php';
require_once 'VisitUtilities.php';
require_once 'VisitTest.php';
require_once 'VisitPortion.php';
require_once 'VisitFilter.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';

session_start();
$Mes = [];
$dbh = GetOnlyDB('db_manager');
if (! $dbh) {
    ExitSession(Title(252).'|FF0000');
}
if (count($_POST) == 0) {
    VisitInitial($dbh);
} else {
    $_SESSION['t_start'] = $_POST['t_start'];
    $_SESSION['t_curr'] = $_POST['t_curr'];
    if ($_SESSION['cancel_started'] && TimeCancelSessions($dbh)) {
        $_SESSION['visit_list'] = GetVisitPortion($dbh, []);
    }
    VisitSavePost();
}
foreach ($_POST as $str_key => $str_v) {
    $sw_break = true;
    $arr_key = explode('|', $str_key);
    $s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
    switch ($s_k) {
        case 'idle_button': break;
        case 'user_lang_s': if (AfterVisitLangChoice($dbh, $sw_break)) {
            VisitFilter($dbh, false);
        } break;
        case 'replace_s': if (AfterReplaceSelect(['db' => $_SESSION['visit_db_list'], 'ui' => []], $sw_break)) {
            VisitFilter($dbh, false);
        } break;
        case 'category_s': if (AfterFilterSelect('category_s', $sw_break)) {
            VisitFilter($dbh);
        } break;
        case 'visit_db_s': AfterDBFilterSelect($sw_break);
            break;
        case 'session_type_s': if (AfterFilterSelect('session_type_s', $sw_break)) {
            VisitFilter($dbh);
        } break;
        case 'exp_d_s': if (AfterFilterSelect('exp_d_s', $sw_break)) {
            VisitFilter($dbh);
        } break;
        case 'exp_m_s': if (AfterFilterSelect('exp_m_s', $sw_break)) {
            VisitFilter($dbh);
        } break;
        case 'exp_y_s': if (AfterFilterSelect('exp_y_s', $sw_break)) {
            VisitFilter($dbh);
        } break;
        case 'visit_exit': $goto = VisitExit($dbh, $Mes);
            if ($goto != '' && count($Mes) == 0) {
                header('Location: '.$goto.'.php');
            } break;
        case 'visit_height_minus': ChangeVisitScreen($dbh, -1, $Mes);
            break;
        case 'visit_height_plus': ChangeVisitScreen($dbh, 1, $Mes);
            break;
        case 'visit_height_button': if (isset($_POST['visit_height'])) {
            ChangeVisitScreen($dbh, $_POST['visit_height'], $Mes);
        } break;
        case 'visit_navigation': VisitListNavigation($dbh, $arr_key[1], $Mes);
            break;
        case 'visit_filter': VisitFilter($dbh);
            break;
        case 'reset_session': ResetSessionParameters($dbh, $arr_key[1], $arr_key[2]);
            break;
        case 'suspend_session': MarkVisit($dbh, $arr_key[1], $arr_key[2]);
            break;
        case 'visit_cancel': StartCancelSessions($dbh, $Mes);
            break;
        case 'visit_delete': if (DeleteSessions($dbh, $Mes)) {
            $_SESSION['visit_list'] = GetVisitPortion($dbh, $_SESSION['visit_list']);
        } break;
        case 'visit_references': GetInvRef('visits', ['db' => $_SESSION['visit_db_list'], 'ui' => []]);
            VisitFilter($dbh, false);
            break;
        case 'replace_id': $_SESSION['replace_mes'] = [];
            ReplaceVisitIDs($dbh, $arr_key[1], $arr_key[2]);
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
$is_suspend = IsMarkedSessions(1);
if ($_SESSION['cancel_started'] && $is_suspend) {
    echo "<font color='#FF0000'>".Title(416).' <b>'.Title(415).'</b>. '.Title(417).' (<b>'.$_SESSION['t_rest'].'</b> '.Title(414).')</font>';
}
if (! $is_suspend) {
    $_SESSION['cancel_started'] = false;
}
$dis_suspend = ($_SESSION['cancel_started']) ? ' disabled' : '';
$dis_filter_suspend = ($_SESSION['cancel_started'] || $_SESSION['visit_filter'] != '' || IsMarkedSessions(2)) ? ' disabled' : '';
?>
<form method="post" id="visit_form" name="visit_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
    <?php SelectTag('user_lang', $_SESSION['user_langs'], $_SESSION['user_lang'][1], 'user_lang_s', false, '', 'user_lang_on', $_SESSION['cancel_started']); ?>
	<div align="center"><font size="+2"><b><?php echo Title(167); ?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<?php
    if (count($Mes) > 0) {
        foreach ($Mes as $z) {
            echo '<br>'.$z;
        }
        echo "<hr align='left' size='1' noshade='noshade' color='#000000' >";
    }
if ($_SESSION['inv_ref'] && PermitInvRef('visits', ['db', 'ui']) && ! IsMarkedSessions(2) && $_SESSION['visit_filter'] == '') {
    InvRefTable('visits', 'emph_data', ['db' => $_SESSION['visit_db_list'], 'ui' => []]);
}
?>
	<table>
		<tr>
			<td><button name="visit_exit" <?php echo $dis_suspend; ?> type="submit" title="<?php echo Title(245); ?>" class="exit_button"><?php echo Title(8); ?></button></td>
            <td><font class="cell_inv">X</font></td>
            <?php
        if ($is_suspend) {
            echo "<td><button name='visit_cancel' ".$dis_suspend." type='submit' class='cancel_button' value='*'>".Title(415).'</button></td>';
            echo "<td><font class='cell_inv'>X</font></td>";
        }
if (IsMarkedSessions(2)) {
    echo "<td><button name='visit_delete' ".$dis_suspend." type='submit' class='cancel_button' value='*'>".Title(242).'</button></td>';
    echo "<td><font class='cell_inv'>X</font></td>";
}
?>
			<td><?php echo TableAdjustment('visit_height_minus', 'visit_height_plus', 'w_b'); ?></td>
			<td>
				<input name="visit_height" size="6" type="text" title="<?php echo Title(15); ?>" class="data_num" value="<?php echo (string) $_SESSION['portion']; ?>">
				<button name="visit_height_button" class="w_b" type="submit" title="<?php echo Title(32); ?>" value="*" style="text-align:center">...</button>
			</td>
			<td><font class="cell_inv">X</font></td>
			<?php if (PermitInvRef('visits', ['db', 'ui'])) {
			    echo "<td><button name='visit_references' type='submit' value='*' class='i_r'".$dis_filter_suspend.'>'.Title(541).'</button></td>';
			}?>
		</tr>
	</table>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
            <td><?php SelectTag('visit_category', $_SESSION['categories'], $_SESSION['category'][0], 'category_s', true, 0, 'category_on', $dis_suspend); ?></td>
            <td><font class="cell_inv">XX</font></td>
			<td>
                <?php
                echo Title(425).': ';
RadioTag('session_type_radio', $_SESSION['ses_type'], [Title(524), Title(518), Title(526)], 'hidden_button', false, 'session_type_radio_on', 'session_type_s', $_SESSION['cancel_started']);
echo ExpiredView(($_SESSION['cancel_started'] || $_SESSION['ses_type'] != 2) ? ' disabled' : '');
?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
			<td><?php echo FTM(Title(163)); ?> <?php SelectTag('visit_db', array_merge(['' => ''], $_SESSION['visit_db_list']), $_SESSION['filter_db'][0], 'visit_db_s', true, '', 'visit_db_on', $_SESSION['cancel_started']); ?></td>
			<td><input name="visit_db_id" size="1" type="text" class='data_num' title="<?php echo Title(545).' '.Title(568); ?>" value="<?php echo $_SESSION['filter_db'][0]; ?>"<?php echo $dis_suspend; ?>></td>
			<td><font class="cell_inv">|</font></td>
			<td><?php echo FTM(Title(54)); ?> <input name="visit_user" size="15" type="text" title="<?php echo FTM(Title(6)).' '.Title(568); ?>" value="<?php echo $_SESSION['filter_user']; ?>"<?php echo $dis_suspend; ?>></td>
			<td><input name="visit_user_id" size="1" type="text" class='data_num' title="<?php echo Title(544).' '.Title(568); ?>" value="<?php echo $_SESSION['filter_user_id']; ?>"<?php echo $dis_suspend; ?>></td>
			<td><font class="cell_inv">|</font></td>
			<td><button name="visit_filter" class="w_b" type="submit" title="<?php echo FTM(Title(522)); ?>"<?php echo $dis_suspend; ?>>...</button></td>
		</tr>
	</table>
    <hr align="left" size="1" noshade="noshade" color="#000000" >
	<?php
        if (count($_SESSION['visit_list']) == 0) {
            echo "<table align='center'><tr><td><font size='+2'><b>".Title(227).' '.Title(226).'</b></font></td></tr></table>';
        } else {
            require_once 'VisitTable.php';
        }
?>
	<input type="hidden" id="t_curr" name="t_curr" value="<?php echo $_SESSION['t_curr']; ?>">
	<input type="hidden" id="t_start" name="t_start" value="<?php echo $_SESSION['t_start']; ?>">
	<input type="hidden" name="replace_s" id="replace_s" value="">
</form>

