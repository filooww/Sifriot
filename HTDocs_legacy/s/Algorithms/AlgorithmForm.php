<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.data_num {text-align:right;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.save_button {background-color:#CCFF00;}</style>
	<style>.dis_text {color:#CCCCCC;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
	<style>.data_error {background-color:#FFCCFF;}</style>
	<style>.data_error_id {text-align:right; font-weight:bold; background-color:#FFCCFF;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
    <style>.data_numeric {text-align:right; font-weight:bold;}</style>
    <style>.invisible_button {color:#F0F0F0; background-color:#F0F0F0; border-color:#F0F0F0; border:thin}</style>
    <style>.sel_text {font-weight:bold;}</style>
    <style>.sel_text_err {font-weight:bold; background-color:#FFCCFF;}</style>
    <style>.radio_row {background-color:#F0F0F0;}</style>
    <style>.edit_line {background-color:#3399FF;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.algorithm_form.user_lang_s.value = '*'; algorithm_form.submit();}</SCRIPT>

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
require_once 'AlgorithmUtilities.php';
require_once 'AlgorithmPortion.php';
require_once 'AlgorithmTest.php';
require_once 'AlgorithmUpdate.php';
// require_once("UserListUtilities.php");
// require_once("UserListTest.php");
// require_once("UserListPortion.php");
// require_once("UserListUpdate.php");
// require_once("UserListFilter.php");

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';

session_start();
$dbh = GetOnlyDB('db_manager');
if (! $dbh) {
    ExitSession(Title(252).'|FF0000');
}
$Mes = [];
if (count($_POST) == 0) {
    AlgorithmInitial($dbh);
}
$mes_screen = '';
$mark_del_mes = '';
foreach ($_POST as $str_key => $str_v) {
    $sw_break = true;
    $arr_key = explode('|', $str_key);
    $s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
    switch ($s_k) {
        case 'idle_button': break;
        case 'user_lang_s': AfterLangChoice($dbh, 'user_lang_s', 'user_lang', $sw_break);
            break;
        case 'algorithm_exit': $_SESSION['algorithm_info'] = '';
            $goto = AlgorithmExit($dbh, $Mes);
            if ($goto != '') {
                header('Location: '.$goto.'.php');
            } break;
        case 'algorithm_height_minus': $_SESSION['algorithm_info'] = '';
            $mes_screen = ChangeAlgorithmScreen($dbh, -1);
            break;
        case 'algorithm_height_plus': $_SESSION['algorithm_info'] = '';
            $mes_screen = ChangeAlgorithmScreen($dbh, 1);
            break;
        case 'algorithm_height_button': $_SESSION['algorithm_info'] = '';
            $mes_screen = ChangeAlgorithmScreen($dbh, $_POST['algorithm_height']);
            break;
        case 'algorithm_navigation': $sw_break = false;
            $_SESSION['algorithm_info'] = '';
            AlgorithmNavigation($dbh, $arr_key[1], $Mes);
            break;
        case 'yes_del': $sw_break = false;
            DeleteMarkedAlgorithms($dbh);
            $_SESSION['algorithm_delete'] = false;
            break;
        case 'no_del': $sw_break = false;
            $_SESSION['alg_del'] = [];
            $_SESSION['algorithm_delete'] = false;
            break;
        case 'algorithm_mark_del': $sw_break = false;
            $_SESSION['algorithm_info'] = '';
            AlgorithmMarkDelete($arr_key[1]);
            break;
        case 'algorithm_edit': $sw_break = false;
            if (! in_array($arr_key[1], $_SESSION['alg_del'])) {
                EditAlgorithm($dbh, $arr_key[1], $_SESSION['arr_alg'][$arr_key[1]], $Mes);
            } break;
        case 'algorithm_info': $sw_break = false;
            HideUnhideAlgorithmInfo($dbh, $arr_key[1]);
            break;
        case 'add_new_algorithm': AddNewAlgorithm($dbh, '', $Mes);
            break;
        case 'algorithm_copy': AddNewAlgorithm($dbh, $_SESSION['algorithm_info'], $Mes);
            break;
        case 'algorithm_delete': $_SESSION['algorithm_delete'] = true;
            break;
        case 'algorithm_parse': $_SESSION['path_parse'] = ! $_SESSION['path_parse'];
            break;
        case 'path_parse': $_SESSION['perform_parse'] = true;
            $_SESSION['path_example'] = PathOutPut($_POST['file_example'], $_SESSION['file_example']);
            break;
        default: $sw_break = false;
    }
    if ($sw_break) {
        break;
    }
}
if ($_SESSION['admin_mes'][0] > 0) {
    echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1].'</b></h3></font></div>';
}
$f_block = $_SESSION['algorithm_delete'] || $_SESSION['edit_algorithm'] != '';
?>
<form method="post" id="algorithm_form" name="algorithm_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
	<?php SelectTag('user_lang', $_SESSION['user_langs'], $_SESSION['user_lang'][1], 'user_lang_s', false, '', 'user_lang_on', $_SESSION['algorithm_delete']); ?>
	<div align="center"><font size="+2"><b><?php echo Title(615); ?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td><button name="algorithm_exit" <?php echo ($f_block) ? ' disabled' : ''; ?> type="submit" class="exit_button"><?php echo Title(8); ?></button></td>
			<?php
            if ($_SESSION['user_working_mode'] == 1) {
                echo "<td><font color='#FFFFFF'>X</font></td>";
                echo "<td><button name='add_new_algorithm'".(($f_block) ? ' disabled' : '')." type='submit' class='save_button'>".Title(691).'</button></td>';
                echo "<td><font color='#FFFFFF'>X</font></td>";
                echo "<td><button name='algorithm_delete'".(! $_SESSION['algorithm_delete'] && (count($_SESSION['alg_del']) > 0 && $_SESSION['edit_algorithm'] == '') ? '' : ' disabled')." type='submit' class='save_button'>".Title(242).'</button></td>';
            }
?>
            <td><font color="#FFFFFF">X</font></td>
			<td><?php echo TableAdjustment('algorithm_height_minus', 'algorithm_height_plus', 'w_b', (($f_block) ? ' disabled' : '')); ?></td>
			<td>
				<input name="algorithm_height"<?php echo ($f_block) ? ' disabled' : ''; ?> size="6" type="text" title="<?php echo Title(15); ?>" class="data_num" value="<?php echo (string) $_SESSION['portion']; ?>">
				<button name="algorithm_height_button"<?php echo ($f_block) ? ' disabled' : ''; ?> class="w_b" type="submit" title="<?php echo Title(32); ?>" value="*">...</button>
			</td>
			<td><font color='#FFFFFF'>X</font></td>
		</tr>
	</table>
	<?php
    if (count($Mes) > 0) {
        foreach ($Mes as $z) {
            echo '<div>'.$z.'</div>';
        }
    }
if ($_SESSION['algorithm_delete'] && count($_SESSION['alg_del']) > 0) {
    DBMDelQuestion('modal_form', ['yes_del', 'no_del'], AlgorithmQuestionString(), 208, 692, 693);
}
if (! isset($_SESSION['arr_alg']) || isset($_SESSION['arr_alg']) && count($_SESSION['arr_alg']) == 0) {
    echo '<div><center><b><font size=+1>'.Title(76).'</b></font></center></div>';
} else {
    require_once 'AlgorithmTable.php';
}
?>
</form>

