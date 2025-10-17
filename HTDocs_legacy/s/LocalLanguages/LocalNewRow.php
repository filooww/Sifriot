<?php $dis = (gettype($_SESSION['sel_local_lang'][0]) == 'integer') ? '' : ' disabled'; ?>
<table>
    <tr>
        <td></td>
		<td><b><?php echo Title(71).' '.FTM(Title(277)); ?> >> </b><input size="1" type="text" name="new_symbol"<?php echo $dis; ?> value="<?php echo $_SESSION['new_symb_param'][0]; ?>"></td>
		<td><button name="add_new_symbol" type="submit"<?php echo $dis; ?> value="*"><?php echo Title(658); ?></button></td>
		<td><?php echo $_SESSION['new_symb_param'][1]; ?></td>
    </tr>
</table>
<hr align="left" size="1" noshade="noshade" color="#000000" >

