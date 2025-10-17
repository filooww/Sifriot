<table>
	<tr>
		<td><?php echo TableAdjustment('list_minus', 'list_plus', 'w_b', $t_block); ?></td>
		<td>
			<input name="list_height" size="6" type="text" title="<?php echo Title(15); ?>" class="data_num" value="<?php echo (string) $_SESSION['portion']; ?>"<?php echo $t_block; ?>>
			<input name="list_height_b" type="submit" class="w_b" title="<?php echo Title(32); ?>" value="..."<?php echo $t_block; ?>>
		</td>
		<td><?php if ($page_mes != '') {
		    echo $page_mes;
		} ?></td>
	</tr>
</table>
<hr align="left" size="1" noshade="noshade" color="#000000" >
