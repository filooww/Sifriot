<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.round_button {color:#009933; border:none; border-radius:30px; background:rgb(255,255,255) linear-gradient(rgb(255,255,255), rgb(0,153,51));}</style>
	<style>.invisible_row {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
	<style>.exit_button {color:#FFFFFF; font-size:120%; font-weight:700; border:1px solid rgb(250,172,17); border-radius:7px; background:rgb(56,81,202) linear-gradient(rgb(56,81,202), rgb(55,180,202));}</style>
	<style>.act_b {color:#0033FF; font-size:150%; font-weight:700; background-color:#FFFFFF; border:none;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
</head>
<SCRIPT language=JavaScript>
function user_lang_on() {document.main_form.user_lang_s.value = '*'; main_form.submit();}
function working_mode_on() {var rc = document.querySelector('input[name="user_working_mode"]:checked').value; document.main_form.w_mode_s.value = rc; main_form.submit();}
</SCRIPT>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/ConfigUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Languages/LanguageUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LocalLanguages/LocalUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LocalLanguages/LocalUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleServices.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitlePortion.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/SpecialTextsUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/UserDBCreate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/ManagerDBCreate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmManagerUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Alarm/AlarmDBUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Fields/FieldUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Fields/FieldEditUtilities.php';
require_once 'Utilities.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';

session_start();
$Mes = [];
$dbh_sys = GetOnlyDB('db_manager');
if (! $dbh_sys) {
    ExitSession(Title(252).'|FF0000');
}
foreach ($_POST as $str_key => $str_v) {
    $sw_break = true;
    $arr_key = explode('-', $str_key);
    switch ($arr_key[0]) {
        case 'user_lang_s': AfterLangChoice($dbh_sys, 'user_lang_s', 'user_lang', $sw_break);
            break;
        case 'w_mode_s': SwitchWorkingMode($dbh_sys, 0, (int) $_POST['w_mode_s'], $sw_break);
            break;
        case 'DBM_exit': ExitSession();
            break;
        case 'coding_table': $_SESSION['language_messages'] = [];
            header('Location: ../Codings/CodingForm.php');
            break;
        case 'common_config': $_SESSION['common_config'] = true;
            $_SESSION['config_list'] = GetAllConfigs($dbh_sys, 'db_s_configs');
            header('Location: ../Configuration/ConfigForm.php');
            break;
        case 'user_list': header('Location: ../UserList/UserListForm.php');
            break;
        case 'visit_list': header('Location: ../Visits/VisitForm.php');
            break;
        case 'languages': $_SESSION['language_messages'] = [];
            header('Location: ../Languages/LanguageForm.php');
            break;
        case 'local_languages_form': header('Location: ../LocalLanguages/LocalForm.php');
            break;
        case 'data_bases_list': header('Location: ../DataBases/DataBasesForm.php');
            break;
        case 'interface_texts': GoToTitles($dbh_sys);
            header('Location: ../Titles/TitleForm.php');
            break;
        case 'algorithms': header('Location: ../Algorithms/AlgorithmForm.php');
            break;
        case 'db_list': $goto = GoToDB($dbh_sys, (int) $arr_key[1]);
            header('Location: '.$goto.'.php');
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
?>
<form method="post" id="main_form" name="main_form">
	<table width="100%">
		<tr>
			<td width="7%"><?php SelectTag('user_lang', $_SESSION['user_langs'], $_SESSION['user_lang'][1], 'user_lang_s', false, '', 'user_lang_on'); ?></td>
			<?php
            if ($_SESSION['priority'] > 10) {
                echo "<td width='20%' valign='middle'>";
                RadioTag('user_working_mode', $_SESSION['user_working_mode'], [Title(154), Title(155)], 'hidden_button', false, 'working_mode_on', 'w_mode_s');
                echo '</td>';
            } else {
                echo "<td width='20%' valign='middle'></td>";
            }
?>
			<td align="right"><button name='DBM_exit' type='submit' value='*' class='exit_button'><?php echo Title(8); ?></button></td>
		</tr>
	</table>
	<?php
    if (count($Mes) > 0) {
        echo '<table>';
        foreach ($Mes as $z) {
            echo "<tr valign='top'><td>".$z.'</td></tr>';
        }
        echo '</table>';
    }
?>
	<table width="100%"><tr><td><font class='invisible_row'>X</font></td></tr></table>
	<table width="100%">
		<tr valign="top">
			<?php
        if ($_SESSION['priority'] == 99) {
            echo "<td width='50%' align='right'>";
            require_once 'MainFormSuper.php';
            echo '</td>';
            echo "<td width='10%'><font class='invisible_row'>X</font></td>";
            echo "<td width='40%'>";
            require_once 'DBList.php';
            echo '</td>';
        } else {
            echo "<td width='100%' align='center'>";
            require_once 'DBList.php';
            echo '</td>';
        }
?>
		</tr>
	</table>
<form>
