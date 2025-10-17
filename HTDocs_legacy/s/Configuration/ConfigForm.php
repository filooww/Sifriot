<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.button_save {background-color:#00CC99;}</style>
	<style>.button_restore {background-color:#CCCCFF;}</style>
	<style>.cell_invisible {color:#FFFFFF; background-color:#FFFFFF; border:none;}</style>
	<style>.exit_button {background-color:#00FFCC;}</style>
	<style>.modal_form {background-color:#66FFFF; border:solid;}</style>
	<style>.data_numeric {text-align:right; font-weight:bold;}</style>
	<style>.refresh_button {background-color:#33FFFF;}</style>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.q_form {color:#FF00FF background-color:#CCCCFF; border:solid;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.config_form.user_lang_s.value = '*'; config_form.submit();}</SCRIPT>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Administrator/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Languages/LanguageUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LocalLanguages/LocalUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LocalLanguages/LocalUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmManagerUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/ManagerDBCreate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUpdate.php';
require_once 'ConfigUtilities.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';

session_start();
$Mes = [];
$arr_warn = ['changed' => [], 'illegal' => [], 'numeric' => [], 'doubled' => [], 'deleted' => []];
$dbh_sys = GetOnlyDB('db_manager');
if (! $dbh_sys) {
    ExitSession(Title(252).'|FF0000');
}
if (! $_SESSION['common_config']) {
    $dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
    if (! $dbh) {
        ExitSession(Title(1).' <b>'.$_SESSION['db_info']['name'].'</b>`'.implode('`', $Mes).'|FF0000', $_SESSION['db_info']['id']);
    }
}
if (count($_POST) == 0) {
    TestConfigs($Mes, true);
    $_SESSION['config_list_copy'] = ListCopy($_SESSION['config_list']);
}
foreach ($_POST as $str_key => $str_v) {
    $sw_break = true;
    $arr_key = explode('-', $str_key);
    $s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
    switch ($s_k) {
        case 'idle_button': TestConfigs($Mes, true);
            break;
        case 'user_lang_s': if (AfterLangChoice($dbh_sys, 'user_lang_s', 'user_lang', $sw_break)) {
            $arr_warn = BeforeSave($Mes, true);
        } break;
        case 'config_exit': $goto = ActionConfigExit($dbh_sys, $Mes);
            if ($goto != '') {
                header('Location: '.$goto.'.php');
            } break;
        case 'config_save': $arr_warn = BeforeSave($Mes, false);
            break;
        case 'config_fill': FillConfigs($dbh, $dbh_sys, ($_SESSION['common_config']) ? 'db_s_configs' : 'db_configs', $Mes);
            break;
        case 'conf_type': TestConfigs($Mes, false, $arr_key[1]);
            break;
        case 'yes_save': $_SESSION['config_list'] = RewriteConfigs(($_SESSION['common_config']) ? $dbh_sys : $dbh, ($_SESSION['common_config']) ? 'db_s_configs' : 'db_configs', $Mes);
            break;
        case 'no_save': $_SESSION['config_list'] = ListCopy($_SESSION['config_list_copy']);
            TestConfigs($Mes, true);
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
$fl_save = (count($arr_warn['changed']) + count($arr_warn['deleted']) > 0 && (count($arr_warn['illegal']) + count($arr_warn['numeric']) + count($arr_warn['doubled']) == 0));
$dis = ($_SESSION['user_working_mode'] == 0 || $fl_save) ? ' disabled' : '';
$invalid_config_types = InvalidConfigType();
?>
<form method="post" id="config_form" name="config_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
	<?php
    SelectTag('user_lang', $_SESSION['user_langs'], $_SESSION['user_lang'][1], 'user_lang_s', false, '', 'user_lang_on');
if (count($Mes) > 0) {
    foreach ($Mes as $z) {
        echo '<br>'.$z;
    }
}
?>
	<div align="center"><font size="+2"><b><?php echo TitleConfig(); ?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td><button name="config_exit"<?php echo ($fl_save) ? ' disabled' : ''; ?> type="submit" title="<?php echo Title(8); ?>" class="exit_button"><?php echo Title(8); ?></button></td>
			<?php
        if ($_SESSION['user_working_mode'] == 1) {
            echo "<td><button name='config_save'".$dis." type='submit' class='button_save'>".Title(30).'</button></td>';
            if (! $_SESSION['common_config']) {
                echo "<td><button name='config_fill'".$dis." type='submit' title='".Title(306)."' class='refresh_button'>".Title(306).'</button></td>';
            }
        }
?>
			<td><button name="ib_user" class="cell_invisible">X</button></td>
			<td><font class="cell_invisible">X</font></td>
		</tr>
	</table>
	<?php if ($fl_save) {
	    QuestionForm('q_form', Title(418), ['yes_save', 'no_save'], [Title(209), Title(210)]);
	}?>
	<table>
		<tr>
			<td width="3%"></td>
			<td width="10%"><b><?php echo Title(150); ?></b></td>
			<td width="10%"><b><?php echo Title(205); ?></b></td>
			<td width="5%" align="center"><b><?php echo Title(302); ?></b></td>
			<?php if (count($invalid_config_types) > 0) {
			    echo "<td width='3%'></td>";
			}?>
			<td><b><?php echo Title(234); ?></b></td>
		</tr>
		<?php
        foreach ($_SESSION['config_list'] as $k => $v) {
            echo '<tr>';
            if ($k == 'new') {
                $zk = Title(71);
            } else {
                $zk = $k;
            }
            echo "<td><input size='2' type='text' readonly class='data_numeric' value='".$zk."'></td>";
            echo "<td><input size='20' type='text' name='conf_name-".$k."' value='".$v[5]."'".$dis.'></td>';
            echo "<td><input size='25' type='text' name='conf_value-".$k."' value='".$v[6]."'".$dis.'></td>';
            echo "<td align='center'><button name='conf_type-".$k."' type='submit' class='button_class' value='*'".$dis.'>'.SysImage((($v[7] == 1) ? 'CheckBorder' : 'BlankBorder'), 16, 16, $_SESSION['user_working_mode'] == 0).
            '</button></td>';
            if (count($invalid_config_types) > 0) {
                if (in_array($k, $invalid_config_types)) {
                    echo "<td><input size='2' type='text' disabled class='data_numeric' value='".(string) $v[7]."'></td>";
                } else {
                    echo '<td></td>';
                }
            }
            echo "<td><input size='70' type='text' name='conf_description-".$k."' value='".$v[8]."'".$dis.'></td>';
            echo '</tr>';
        }
?>
	</table>
</form>
