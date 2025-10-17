<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.exit_button {background-color:#00FFCC;}</style>
	<style>.odd_row {background-color:#CCFFFF;}</style>
	<style>.new_field {background-color:#33CC99;}</style>
	<style>.edit_field {background-color:#33CC99;}</style>
	<style>.emp {background-color:#FFFFFF; color:#FFFFFF;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
	<style>.field_auto {background-color:#FFCCFF;}</style>
	<style>.field_no {background-color:#99FF99;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.field_form.user_lang_s.value = '*'; field_form.submit();}</SCRIPT>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/SpecialTextsUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Languages/LanguageUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Administrator/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/DataBases/DataBaseUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/ConfigUtilities.php';

require_once 'FieldUtilities.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';

session_start();
$Mes = [];
$dbh_sys = GetOnlyDB('db_manager');
if (! $dbh_sys) {
    ExitSession(Title(252).'|FF0000');
}
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (! $dbh) {
    ExitSession(Title(1).' <b>'.$_SESSION['db_info']['name'].'</b>`'.implode('`', $Mes).'|FF0000', $_SESSION['db_info']['id']);
}
foreach ($_POST as $str_key => $str_v) {
    $k = explode('|', $str_key);
    $s_k = (count($k) == 1) ? $str_key : $k[0];
    $sw_break = true;
    switch ($s_k) {
        case 'idle_button': break;
        case 'user_lang_s': if (AfterLangChoice($dbh_sys, 'user_lang_s', 'user_lang', $sw_break)) {
            $_SESSION['field_using'] = GetSpecialTexts($dbh_sys, 'field_using');
        } break;
        case 'field_exit': if ($_SESSION['field_change']) {
            $_SESSION['all_field_list'] = GetAllFieldList($dbh, $Mes, -1);
        } header('Location: ../Administrator/DataBaseActions.php');
            break;
        case 'sort_field': $_SESSION['field_sort'] = SortFieldDefinitions('field');
            break;
        case 'sort_id': $_SESSION['field_sort'] = SortFieldDefinitions('table_name');
            break;
        case 'sort_table': $_SESSION['field_sort'] = SortFieldDefinitions('in_table');
            break;
        case 'sort_screen': $_SESSION['field_sort'] = SortFieldDefinitions('in_screen');
            break;
        case 'sort_all': $_SESSION['field_sort'] = SortFieldDefinitions('table_field');
            break;
        case 'field_edit': $_SESSION['f_k'] = [(int) $k[1], $k[2]];
            header('Location: FieldEditForm.php');
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
$dis = ($_SESSION['user_working_mode'] == 0) ? ' disabled' : '';
?>
<form method="post" id="field_form" name="field_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
	<?php SelectTag('user_lang', $_SESSION['user_langs'], $_SESSION['user_lang'][1], 'user_lang_s', false, '', 'user_lang_on'); ?>
	<div align="center"><font size="+2"><b><?php echo Title(334); ?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td><button name="field_exit" type="submit" title="<?php echo Title(8); ?>" class="exit_button"><?php echo Title(8); ?></button></td>
			<td class="emp">X</td>
			<td class="emp">X</td>
			<td><?php echo Title(380); ?></td>
			<td><button name="sort_field" type="submit"><?php echo '<b>'.Title(403).'</b>'; ?></button></td>
			<td><button name="sort_id" type="submit"><?php echo '<b>'.Title(347).'</b>'; ?></button></td>
			<td><button name="sort_table" type="submit"><?php echo '<b>'.Title(332).'</b>'; ?></button></td>
			<td><button name="sort_screen" type="submit"><?php echo '<b>'.Title(333).'</b>'; ?></button></td>
			<td><button name="sort_all" type="submit"><?php echo '<b>'.Title(365).'-'.Title(403).'</b>'; ?></button></td>
		</tr>
	</table>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr valign="top">
			<td width='1%' class='emp'>X</td>
			<td width="1%" class="emp">X</td>
			<td width="10%"><b><?php echo Title(365); ?></b></td>
			<td width="10%"><b><?php echo Title(403); ?></b></td>
			<td width="10%"><b><?php echo Title(347); ?></b></td>
			<td width="12%" align="center"><b><?php echo Title(332); ?></b></td>
			<td width="12%" align="center"><b><?php echo Title(333); ?></b></td>
			<td width="20%"><b><?php echo Title(378); ?></b></td>
			<td></td>
		</tr>
		<?php
        $cl = true;
foreach (array_keys($_SESSION['field_sort']) as $z) {
    $arr_k = explode('|', $z);
    $v = $_SESSION['field_definitions'][$arr_k[0]][$arr_k[1]];
    $bgcl = ($cl) ? " class='odd_row'" : '';
    $str_auto = GetAutoClass($v['auto']);
    echo "<tr valign='top'>";
    echo "<td><button name='field_edit|".$z."' type='submit' title='".Title(336)."' class='edit_field' value='*'>".ImgV((($_SESSION['user_working_mode'] == 0) ? 'Test' : 'Edit'), 16, 16).'</button></td>';
    echo "<td class='emp'>X</td>";
    echo '<td'.$bgcl.'>'.$_SESSION['mandatory_db_tables'][$arr_k[0]].'</td>';
    echo '<td'.$str_auto[0]." title='".$str_auto[1]."'>".$arr_k[1].'</td>';
    echo '<td'.$bgcl.'>'.(($v['f_key']) ? '<b>'.$v['f_name'].'</b>' : $v['f_name']).'</td>'; // color
    echo "<td align='center'".$bgcl.'>'.(string) $v['ind_in_t'].'</td>';
    echo "<td align='center'".$bgcl.'>'.(($v['screen_order'] == 0) ? '' : (string) $v['screen_order']).'</td>'; // color
    echo '<td'.$bgcl.'>'.ViewFieldUsing($v['f_using']).'</td>';
    echo '<td>'.implode('; ', $v['errors']).'</td>';
    echo '</tr>';
    $cl = ! $cl;
}
?>
	</table>
</form>
	

