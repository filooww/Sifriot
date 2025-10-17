<table>
	<tr>
		<td><button name="title_exit"<?php echo $exit_block; ?> type="submit" title="<?php echo Title(288); ?>" class="exit_button"><?php echo Title(8); ?></button></td>
		<td><button name="ib_title" class="cell_invisible">X</button></td>
		<?php
        if ($_SESSION['user_working_mode'] == 1) {
            echo "<td><button name='add_new_title' type='submit' value='*' class='new_row'".$upd_block.'>'.Title(292).'</button></td>';
            echo "<td><button name='ib_title' class='cell_invisible'>X</button></td>";
        }
		?>
		<td><button name="integrity_check" type="submit" value="*" title="<?php echo IntegrityTitle(); ?>" class="integrity_check"<?php echo $t_block; ?>><?php echo Title(289); ?></button></td>
		<td><button name="ib_title" class="cell_invisible">X</button></td>
		<td><button name="special_texts" type="submit" value="*" class="special_texts"<?php echo $t_block; ?>><?php echo $_SESSION['spec_title']; ?></button></td>
        <td><button name="ib_title" class="cell_invisible">X</button></td>
        <?php if (PermitInvRef('interface_texts', ['li'])) {
            echo "<td><button name='title_references' type='submit' value='*' class='i_r'".$upd_block.'>'.Title(541).'</button></td>';
        }?>
	</tr>
</table>
<hr align="left" size="1" noshade="noshade" color="#000000" >

