<?php
$k = $_SESSION['algorithm_info'];
$v = $_SESSION['arr_alg'][$k];
$dis = (($_SESSION['edit_algorithm'] == "") ? " disabled" : "");
?>

<table>
	<tr class="radio_row">
        <td width="19%"><button name="algorithm_copy" type="submit"<?php echo (($_SESSION['algorithm_insert']) ? " disabled" : "");?>><?php echo Title(689);?></button></td>
		<td><button name="algorithm_parse" type="submit"><?php echo Title(690);?></button></td>
	</tr>
	<tr class="radio_row">
		<td width="19%"><?php echo Title(189);?></td>
		<td>
            <input size="2" type="text"<?php echo $dis;?> name="alg_offset|<?php echo $k;?>" <?php echo AlgColor($k, "data_error_id", "data_numeric", "offset")." value='".$v['offset']."'"?> />
			<font class="invisible_button">X</font>
			<?php echo Title(207);?>:
			<?php AlgorithmCheckBox($k, $v['del_from_source'], "del_from_source", $dis);?>
		</td>
	</tr>
	<tr class="radio_row">
		<td width="19%"><?php echo Title(246);?></td>
		<td>
            <input size="20" type="text"<?php echo $dis;?> name="alg_beg_delim|<?php echo $k;?>" <?php echo "title='".AlgStrTitle($k, "beg_del")."' ".AlgColor($k, "sel_text_err", "sel_text", "beg_del")." value='".$v['beg_del']."'"?>" />
            <font class="invisible_button">X</font>
            <?php echo Title(247)?>:
			<input size="2" type="text"<?php echo $dis;?> name="alg_beg_number|<?php echo $k;?>" <?php echo AlgColor($k, "data_error_id", "data_numeric", "beg_num")." value='".$v['beg_num']."'"?> />
			<font class="invisible_button">X</font>
			<?php echo Title(250)?>:
			<?php AlgorithmCheckBox($k, $v['beg_inc'], "beg_inc", $dis);?>
			<font class="invisible_button">X</font>
			<?php echo Title(207)?>:
			<?php AlgorithmCheckBox($k, $v['beg_scr'], "beg_scr", $dis);?>
		</td>
	</tr>
	<tr class="radio_row">
	    <td width="19%"><?php echo Title(260);?></td>
		<td><input size="20" type="text"<?php echo $dis;?> name="inn_del|<?php echo $k;?>" <?php echo "title='".AlgStrTitle($k, "inn_del")."' ".AlgColor($k, "sel_text_err", "sel_text", "inn_del")." value='".$v['inn_del']."'"?>" /></td>
	</tr>
	<tr class="radio_row">
	    <td width="19%"><?php echo Title(265);?></td>
		<td>
            <input size="20" type="text"<?php echo $dis;?> name="alg_end_delim|<?php echo $k;?>" <?php echo "title='".AlgStrTitle($k, "end_del")."' ".AlgColor($k, "sel_text_err", "sel_text", "end_del")." value='".$v['end_del']."'"?>" />
            <font class="invisible_button">X</font>
            <?php echo Title(247)?>:
			<input size="2" type="text"<?php echo $dis;?> name="alg_end_number|<?php echo $k;?>" <?php echo AlgColor($k, "data_error_id", "data_numeric", "end_num")." value='".$v['end_num']."'"?> />
			<font class="invisible_button">X</font>
			<?php echo Title(250)?>:
			<?php AlgorithmCheckBox($k, $v['end_inc'], "end_inc", $dis);?>
			<font class="invisible_button">X</font>
			<?php echo Title(207)?>:
			<?php AlgorithmCheckBox($k, $v['end_scr'], "end_scr", $dis);?>
		</td>
	</tr>
	<tr class="radio_row">
	    <td width="19%"><?php echo Title(248);?></td>
		<td>
            <input size="20" type="text"<?php echo $dis;?> name="del_sym|<?php echo $k;?>" <?php echo "title='".AlgStrTitle($k, "del_sym")."' ".AlgColor($k, "sel_text_err", "sel_text", "del_sym")." value='".$v['del_sym']."'"?>" />
            <font class="invisible_button">X</font>
            <input size="20" type="text"<?php echo $dis;?> name="ins_sym|<?php echo $k;?>" <?php echo "title='".AlgStrTitle($k, "ins_sym")."' ".AlgColor($k, "sel_text_err", "sel_text", "ins_sym")." value='".$v['ins_sym']."'"?>" />
            <font class="invisible_button">X</font>
			<?php echo Title(278)?>:
			<?php AlgorithmCheckBox($k, $v['field_only'], "field_only", $dis);?>
         </td>
	</tr>
	<tr class="radio_row">
		<td width="19%"><?php echo Title(279);?></td>
		<td>
            <input size="20" type="text"<?php echo $dis;?> name="reg_exp|<?php echo $k;?>" <?php echo "title='".AlgStrTitle($k, "reg_exp")."' ".AlgColor($k, "sel_text_err", "sel_text", "reg_exp")." value='".$v['reg_exp']."'"?>" />
			<font class="invisible_button">X</font>
			<?php echo Title(207);?>:
			<?php AlgorithmCheckBox($k, $v['reg_scr'], "reg_scr", $dis);?>
		</td>
	</tr>
</table>

