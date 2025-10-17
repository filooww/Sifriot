<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.data_num {text-align:right;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.save_button {background-color:#CCFF00;}</style>
	<style>.refresh_button {background-color:#33FFFF;}</style>
	<style>.dis_text {color:#CCCCCC;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
	<style>.data_error {background-color:#FFCCFF;}</style>
	<style>.data_error_num {text-align:right; background-color:#FFCCFF;}</style>
	<style>.data_error_id {text-align:right; font-weight:bold; background-color:#FFCCFF;}</style>
	<style>.data_id {text-align:right; font-weight:bold;}</style>
	<style>.data_name {font-weight:bold;}</style>
	<style>.data_error_date {text-align:center; background-color:#FFCCFF;}</style>
	<style>.data_date {text-align:center;}</style>
	<style>.table_color {background-color:#CCCCCC;}</style>
	<style>.i_r {background-color:#01CCFF;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
	<style>.un_do_button {color:#FFFFFF; border:1px solid rgb(250,172,17); border-radius:7px; background:rgb(56,81,202) linear-gradient(rgb(56,81,202), rgb(55,180,202));}</style>
    <style>.emph_data {background-color:#CCFFFF;}</style>
</head>
<SCRIPT language=JavaScript>
function user_lang_on() {document.user_list_form.user_lang_s.value = '*'; user_list_form.submit();}
function replace_on(obj) {document.user_list_form.replace_s.value = obj.name; user_list_form.submit();}
function filter_category_on() {document.user_list_form.filter_category_s.value = '*'; user_list_form.submit();}
function list_errors_on() {document.user_list_form.list_errors_s.value = '*'; user_list_form.submit();}
</SCRIPT>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/UserEnter/UserEnterUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Languages/LanguageUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LocalLanguages/LocalUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LocalLanguages/LocalUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Administrator/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmManagerUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmReferences.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/ManagerDBCreate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitFormUtilities.php';
require_once 'UserListUtilities.php';
require_once 'UserListTest.php';
require_once 'UserListPortion.php';
require_once 'UserListUpdate.php';
require_once 'UserListFilter.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';

session_start();
$dbh = GetOnlyDB('db_manager');
if (! $dbh) {
    ExitSession(Title(252).'|FF0000');
}
$Mes = [];
if (count($_POST) == 0) {
    UserListInitial($dbh);
} else {
    UserSavePost();
}
$mes_screen = '';
$edit_mes = [];
foreach ($_POST as $str_key => $str_v) {
    $sw_break = true;
    $arr_key = explode('|', $str_key);
    $s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
    switch ($s_k) {
        case 'idle_button': break;
        case 'user_lang_s': if (AfterUserListLangChoice($dbh, $sw_break)) {
            UserFilter($dbh);
        } break;
        case 'replace_s': if (AfterReplaceSelect(['li' => $_SESSION['user_langs'], 'db' => $_SESSION['list_db']], $sw_break)) {
            UserFilter($dbh);
        } break;
        case 'filter_category_s': AfterCategoryFilterChoice($dbh, $sw_break);
            break;
        case 'list_errors_s': if (AfterErrorsFilterChoice($dbh, $Mes, $sw_break)) {
            UserFilter($dbh);
        } break;
        case 'user_exit': $_SESSION['user_info'] = '';
            $goto = UserListExit($dbh, $Mes);
            if ($goto != '') {
                header('Location: '.$goto.'.php');
            } break;
        case 'user_height_minus': $_SESSION['user_info'] = '';
            $mes_screen = ChangeUserListScreen($dbh, -1);
            break;
        case 'user_height_plus': $_SESSION['user_info'] = '';
            $mes_screen = ChangeUserListScreen($dbh, 1);
            break;
        case 'user_height_button': $_SESSION['user_info'] = '';
            $mes_screen = ChangeUserListScreen($dbh, $_POST['user_height']);
            break;
        case 'user_navigation': $sw_break = false;
            $_SESSION['user_info'] = '';
            UserListNavigation($dbh, $arr_key[1], $Mes);
            break;
        case 'yes_del': $sw_break = false;
            DeleteUser($dbh);
            $_SESSION['user_delete'] = ['', '', []];
            $_SESSION['user_info'] = '';
            break;
        case 'no_del': $sw_break = false;
            $_SESSION['user_info'] = '';
            $_SESSION['user_list'][$_SESSION['edit_user']][0] = $_SESSION['user_delete'][1];
            $_SESSION['user_delete'] = ['', '', []];
            UserFilter($dbh);
            break;
        case 'user_filter': $sw_break = false;
            $_SESSION['inv_ref'] = false;
            $_SESSION['user_info'] = '';
            UserFilter($dbh);
            break;
        case 'user_edit': $sw_break = false;
            $edit_mes = EditUser($dbh, $arr_key[1], $_SESSION['user_list'][$arr_key[1]], $_SESSION['user_flag'][$arr_key[1]], $Mes);
            break;
        case 'user_info': $sw_break = false;
            HideUnhideUserInfo($arr_key[1]);
            break;
        case 'user_references': $_SESSION['user_info'] = '';
            GetInvRef('user_ident', ['li' => $_SESSION['user_langs'], 'db' => $_SESSION['list_db']]);
            UserFilter($dbh);
            break;
        case 'replace_id': ReplaceUserIDs($dbh, $arr_key[1], $arr_key[2]);
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
$f_block = $_SESSION['user_delete'][0] != '' || $_SESSION['edit_user'] != '';
?>
<form method="post" id="user_list_form" name="user_list_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
	<?php SelectTag('user_lang', $_SESSION['user_langs'], $_SESSION['user_lang'][1], 'user_lang_s', false, '', 'user_lang_on', $_SESSION['user_delete'][0] != ''); ?>
	<div align="center"><font size="+2"><b><?php echo Title(12); ?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<?php if ($_SESSION['inv_ref'] && PermitInvRef('user_ident', ['li', 'db'])) {
	    InvRefTable('user_ident', 'emph_data', ['li' => $_SESSION['user_langs'], 'db' => $_SESSION['list_db']]);
	} ?>
	<table>
		<tr>
			<td><button name="user_exit" <?php echo ($f_block) ? ' disabled' : ''; ?> type="submit" title="<?php echo Title(20); ?>" class="exit_button"><?php echo Title(8); ?></button></td>
            <td><font color="#FFFFFF">X</font></td>
			<td><?php echo TableAdjustment('user_height_minus', 'user_height_plus', 'w_b', (($f_block) ? ' disabled' : '')); ?></td>
			<td>
				<input name="user_height"<?php echo ($f_block) ? ' disabled' : ''; ?> size="6" type="text" title="<?php echo Title(15); ?>" class="data_num" value="<?php echo (string) $_SESSION['portion']; ?>">
				<button name="user_height_button"<?php echo ($f_block) ? ' disabled' : ''; ?> class="w_b" type="submit" title="<?php echo Title(32); ?>" value="*">...</button>
			</td>
			<td><font color='#FFFFFF'>X</font></td>
			<?php
            if (PermitInvRef('user_ident', ['li', 'db'])) {
                echo "<td><button name='user_references' type='submit' value='*' class='i_r'".(($f_block || $_SESSION['user_filter'] != '') ? ' disabled' : '').'>'.Title(541).'</button></td>';
            }
if ($_SESSION['user_filter'] != '') {
    echo "<td><font color='#FFFFFF'>X</font></td>";
    echo "<td><font color='#0000FF'>".FTM(Title(527), true).' '.Title(300).': <b>'.(string) $_SESSION['user_size'].'</b></font></td>';
}
?>
		</tr>
	</table>
	<table>
		<tr>
			<td><b><?php echo Title(423); ?>:</b></td>
			<td>
				<table frame="border">
					<tr>
						<td><?php echo FTM(Title(147)); ?> <input name="user_filter_id"<?php echo ($f_block) ? ' disabled' : ''; ?> size="12" class="data_id" type="text" value="<?php echo $_SESSION['filter_id']; ?>"></td>
						<td><font color="#FFFFFF">X</font></td>
						<td><?php echo FTM(Title(54)); ?> <input name="user_filter_name"<?php echo ($f_block) ? ' disabled' : ''; ?> size="20" class="data_name" type="text" value="<?php echo DoubleQuoteFix($_SESSION['filter_name']); ?>"></td>
                        <td><font color="#FFFFFF">X</font></td>
						<td><button name="user_filter" title="<?php echo Title(593); ?>" type="submit"<?php echo ($f_block) ? ' disabled' : ''; ?>><?php echo "<font color='#0000FF'>...</font>"; ?></button></td>
					</tr>
				</table>
			</td>
            <td><font color="#FFFFFF">X</font></td>
			<td><?php echo FTM(Title(134)).' ';
SelectTag('user_filter_category', $_SESSION['categories'], $_SESSION['category'][0], 'filter_category_s', true, 0, 'filter_category_on', $f_block); ?></td>
            <td><font color="#FFFFFF">X</font></td>
			<td>
                <input type="checkbox" name="user_errors"<?php echo (($_SESSION['filter_error']) ? ' checked' : '').(($f_block) ? ' disabled' : '')." onclick='list_errors_on()'>".Title(518); ?>
                <input type="hidden" name="list_errors_s" value="">
            </td>
		</tr>
	</table>
	<table><tr><?php ShowUserCategoriesCounts(); ?></tr></table>
	<hr noshade="noshade" color="#000000" >
	<?php
    if ($_SESSION['user_delete'][0] != '') {
        DBMDelQuestion('modal_form', ['yes_del', 'no_del'], [$_SESSION['user_delete'][1].' ('.$_SESSION['user_delete'][0].')'], 208, 211, 212);
    }
if (count($_SESSION['user_list']) == 0) {
    echo '<div><center><b><font size=+1>'.Title(12).' '.Title(226).'</b></font></center></div>';
} else {
    if ($mes_screen != '' || count($edit_mes) + count($Mes) > 0 || $_SESSION['user_delete'][0] != '') {
        UserListMessages($mes_screen, $edit_mes, $Mes);
    }
    require_once 'UserListTable.php';
}
?>
	<input type="hidden" name="replace_s" id="replace_s" value="">
</form>

