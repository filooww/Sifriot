<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.odd_row {background-color:#CCFFFF;}</style>
	<style>.emp {background-color:#FFFFFF; color:#FFFFFF;}</style>
</head>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Languages/LanguageUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableTest.php';
require_once 'FieldUtilities.php';

session_start();
?>
<form method="post" name="field_form_warn">
	<table>
		<tr>
			<td width="10%"><b><?php echo Title(365); ?></b></td>
			<td width="10%"><b><?php echo Title(403); ?></b></td>
			<td width="10%"><b><?php echo Title(147); ?></b></td>
			<td width="10%" align="center"><b><?php echo Title(332); ?></b></td>
			<td width="10%" align="center"><b><?php echo Title(333); ?></b></td>
			<td><b><?php echo Title(378); ?></b></td>
		</tr>
		<?php
        $cl = true;
foreach (array_keys($_SESSION['field_definitions']) as $k0) {
    foreach ($_SESSION['field_definitions'][$k0] as $k => $v) {
        $bgcl = ($cl) ? " class='odd_row'" : '';
        echo '<tr>';
        echo '<td'.$bgcl.'>'.$_SESSION['mandatory_db_tables'][$k0].'</td>';
        echo '<td'.$bgcl.'>'.$k.'</td>';
        echo '<td'.$bgcl.'>'.(($v[1] == 0) ? $v[0] : '<b>'.$v[0].'</b>').'</td>';
        echo "<td align='center'".$bgcl.'>'.(string) $v[2].'</td>';
        echo "<td align='center'".$bgcl.'>'.(($v[3] == 0) ? '' : (string) $v[3]).'</td>';
        echo '<td'.$bgcl.'>'.$v[4].'</td>';
        echo '</tr>';
        $cl = ! $cl;
    }
}
?>
	</table>
</form>
	

