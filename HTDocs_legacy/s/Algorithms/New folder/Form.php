<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.data_numeric {text-align:right; font-weight:bold;}</style>
	<style>.sel_text {font-weight:bold;}</style>
	<style>.invisible_button {color:#F0F0F0; background-color:#F0F0F0; border-color:#F0F0F0; border:thin}</style>
	<style>.insert_button {background-color:#F0F0F0; border-color:#F0F0F0; border:thin}</style>
	<style>.radio_row {background-color:#F0F0F0;}</style>
	<style>.show_button {color:#FF0000; background-color:#FFFFFF; font:bold;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
	<style>.button_class {border:thin; background-color:#FFFFFF;}</style>
</head>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once 'ListUtilities.php';

session_start();
$Mesarr = [];
$dbh = GetDB($_SESSION['db_info']['db_name'], $Mes, $_SESSION['db_info']['db_coding']);
if (! $dbh) {
    ExitSession(Title(1).' <b>'.$_SESSION['db_info']['db_name'].'</b>`'.implode('`', $Mes).'|FF0000', $_SESSION['db_key']);
}
$Mes = '';
if (isset($_POST['alg_save'])) {
    if (count($_SESSION['alg_copy']) == 0) {
        if ($_SESSION['alg_edit_code'] == '') {
            $Mes = InsertAlgorithm($dbh, [chr(92), chr(92).chr(92)]);
            if ($Mes == '') {
                $_SESSION['alg_count']++;
            }
        } else {
            $Mes = UpdateAlgorithm($dbh, $_SESSION['alg_edit_code'], [chr(92), chr(92).chr(92)]);
        }
    } else {
        $Mes = InsertAlgorithm($dbh, [chr(92), chr(92).chr(92)]);
        if ($Mes == '') {
            $_SESSION['alg_count']++;
        }
        $_SESSION['alg_copy'] = [];
    }
    if ($Mes == '') {
        header('Location: List.php');
    }
} elseif (isset($_POST['yes_name']) || isset($_POST['alg_form_exit'])) {
    header('Location: List.php');
} elseif (isset($_POST['path_parse'])) {
    $_SESSION['path_example'] = PathOutPut($_POST['file_example'], $_SESSION['file_example']);
}
?>
<!-- <form action="Form.php" method="post"> -->
<form method="post">
	<table>
		<tr>
			<td>File path example</td>
			<td><input size="<?php echo $_SESSION['conf']['w_01']; ?>" name="file_example" type="text" value="<?php echo $_SESSION['file_example']; ?>" /></td>
			<td><button type="submit" name="path_parse" value="*">File path parse</button></td>
		</tr>
		<?php
        for ($i = 0; $i < count($_SESSION['path_example']); $i++) {
            echo '<tr>';
            echo "<td class='data_numeric'><b>".(string) $i.'</b></td>';
            echo "<td><input size='".$_SESSION['conf']['w_01']."' name='file_el-".(string) $i."' type='text' value='".$_SESSION['path_example'][$i]."' /></td>";
            echo '<td></td>';
            echo '</tr>';
        }
?>
	</table>
	<hr noshade="noshade" />
	<table>
		<tr>
			<td><input name="alg_form_exit" type="submit" value="Exit"></td>
			<td><input name="alg_save" type="submit" value="Save algorithm"></td>
			<td><input name="alg_reset" type="reset" value="Reset data"></td>
		</tr>
	</table>
	<hr noshade="noshade" />
	<table>
		<tr>
			<td width="10%">ID</td>
			<td><input align="right" name="alg_id" readonly type="text" class="data_numeric" value="<?php echo (count($_SESSION['alg_copy']) == 0) ? $_SESSION['alg_edit_code'] : ''; ?>" /></td>
<!--			<td><font class="data_numeric"> -->
		</tr>
		<tr>
			<td width="10%">Reference</td>
			<td>
			<?php SelectTag($alg_field, $_SESSION['p_ref'], AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'field'), '', false, '', '', false, true, 'alg_field'); ?>
<!--				<select name="alg_field" class="alg_field"> <?php /* foreach ($_SESSION['p_ref'] as $fr) if (!isset($fr['comm'])) echo OptionTag(AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], "field")); */ ?></select> <!-- // SelectTag -->
			</td>
		</tr>
		<tr class="radio_row">
			<td width="10%">Offset in path</td>
			<td>
				<table>
					<tr>
						<td><input size="2" name="alg_offset" type="text" class="data_numeric" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'offset', false); ?>" /></td>
						<td><font class="invisible_button">X</font></td>
						<td>delete from source:</td>
						<td align="center"><button name="del_from_source" type="submit" class="button_class" value="*"><?php echo SysImage(($_SESSION['arr_alg'] == $_SESSION['alg_edit_code']) ? 'CheckBorder' : 'BlankBorder'), 16, 16, $_SESSION['user_working_mode']; ?></button></td>
<!---						<td>
							<input type="radio" name="del_from_source" value="del_from_source_Y" <?php /* echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], "del_from_source", "Y"); */ ?>></input>Yes
							<input type="radio" name="del_from_source" value="del_from_source_N" <?php /* echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], "del_from_source", "N"); */ ?>></input>No
						</td>
-->
					</tr>
				</table>
			</td>
		</tr>
		<tr class="radio_row">
			<td width="10%">Begin delimiter</td>
			<td>
				<table>
					<tr>
						<td><input size="<?php echo $_SESSION['conf']['w_08']; ?>" name="alg_beg_delim" type="text" class="sel_text" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'beg_del'); ?>" /></td>
						<td><font class="invisible_button">X</font></td>
						<td>number in source:</td>
						<td><input size="2" name="alg_beg_number" type="text" class="data_numeric" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'beg_num', false); ?>" /></td>
						<td><font class="invisible_button">X</font></td>
						<td>include into reference:</td>
						<td>
							<input type="radio" name="beg_inc" value="beg_inc_Y" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'beg_inc', 'Y'); ?>></input>Yes
							<input type="radio" name="beg_inc" value="beg_inc_N" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'beg_inc', 'N'); ?>></input>No
						</td>
						<td><font class="invisible_button">X</font></td>
						<td>delete from source:</td>
						<td>
							<input type="radio" name="beg_scr" value="beg_scr_Y" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'beg_scr', 'Y'); ?>></input>Yes
							<input type="radio" name="beg_scr" value="beg_scr_N" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'beg_scr', 'N'); ?>></input>No
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr class="radio_row">
			<td width="10%">Inner delimiter</td>
			<td><input size="<?php echo $_SESSION['conf']['w_08']; ?>" name="alg_inn_delim" type="text" class="sel_text" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'inn_del'); ?>" /></td>
		</tr>
		<tr class="radio_row">
			<td width="10%">End delimiter</td>
			<td>
				<table>
					<tr>
						<td><input size="<?php echo $_SESSION['conf']['w_08']; ?>" name="alg_end_delim" type="text" class="sel_text" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'end_del'); ?>" /></td>
						<td><font class="invisible_button">X</font></td>
						<td>number in source:</td>
						<td><input size="2" name="alg_end_number" type="text" class="data_numeric" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'end_num', false); ?>" /></td>
						<td><font class="invisible_button">X</font></td>
						<td>include into reference:</td>
						<td>
							<input type="radio" name="end_inc" value="end_inc_Y" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'end_inc', 'Y'); ?>></input>Yes
							<input type="radio" name="end_inc" value="end_inc_N" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'end_inc', 'N'); ?>></input>No
						</td>
						<td><font class="invisible_button">X</font></td>
						<td>delete from source:</td>
						<td>
							<input type="radio" name="end_scr" value="end_scr_Y" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'end_scr', 'Y'); ?>></input>Yes
							<input type="radio" name="end_scr" value="end_scr_N" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'end_scr', 'N'); ?>></input>No
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr class="radio_row">
			<td width="10%">Symbol replacement</td>
			<td>
				<table>
					<tr>
						<td><input size="<?php echo $_SESSION['conf']['w_08']; ?>" name="alg_del_symbols" type="text" class="sel_text" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'del_sym'); ?>" /></td>
						<td><button name="del_space" class="insert_button" value="*">>></button></td>
						<td><input size="<?php echo $_SESSION['conf']['w_08']; ?>" name="alg_ins_symbols" type="text" class="sel_text" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'ins_sym'); ?>" /></td>
						<td><font class="invisible_button">X</font></td>
						<td>for reference only:</td>
						<td>
							<input type="radio" name="field_only" value="field_only_Y" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'field_only', 'Y'); ?>></input>Yes
							<input type="radio" name="field_only" value="field_only_N" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'field_only', 'N'); ?>></input>No
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr class="radio_row">
			<td width="10%">Regular expression</td>
			<td>
				<table>
					<tr>
						<td><input size="<?php echo $_SESSION['conf']['w_08']; ?>" name="alg_reg_exp" type="text" class="sel_text" value="<?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'reg_exp'); ?>" /></td>
						<td><font class="invisible_button">X</font></td>
						<td>delete from source:</td>
						<td>
							<input type="radio" name="reg_scr" value="reg_scr_Y" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'reg_scr', 'Y'); ?>></input>Yes
							<input type="radio" name="reg_scr" value="reg_scr_N" <?php echo SetAlgRadioCheck($_SESSION['arr_alg'], $_SESSION['alg_edit_code'], 'reg_scr', 'N'); ?>></input>No
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td width="10%">Remarks</td>
			<td><textarea name="remarks" cols="<?php echo $_SESSION['conf']['w_01']; ?>" rows="10"><?php echo AlgField($_SESSION['alg_copy'], $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], 'remarks'); ?>"</textarea></td>
		</tr>
	</table>
	
<hr noshade="noshade" />
<?php if ($Mes != '' && $Mes != '-') {
    QuestionForm('modal_form', [$Mes, 'Do You want to stop editing?'], ['yes_name', 'cancel_name'], ['Yes', 'No']);
}?>
</form>
