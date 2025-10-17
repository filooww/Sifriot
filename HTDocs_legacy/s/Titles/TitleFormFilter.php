<table>
	<tr>
        <td><b><?php echo Title(423); ?>: </b></td>
        <td><?php SelectTag('selected_title_lang', $_SESSION['title_langs_all'], $_SESSION['selected_title_lang'][1], 'title_lang_s', false, '', 'title_lang_on', isset($_SESSION['title_edit_row'])); ?></td>
        <td><?php SelectTag('view_title_mode', $_SESSION['title_modes_all'], $_SESSION['view_title_mode'][1], 'title_modes', false, '', 'row_mode_on', isset($_SESSION['title_edit_row'])); ?></td>
        <td>
            <table frame="border">
                <tr>
		            <td><?php echo FTM(Title(147)); ?></td>
                    <td><input name="title_filter_id"<?php echo $t_block; ?> class="data_num" size="5" type="text" title="<?php echo Title(172); ?>" value="<?php echo $_SESSION['title_filter_id']; ?>"></td>
                    <td><?php echo FTM(Title(293)); ?></td>
                    <td><input name="title_filter_text"<?php echo $t_block; ?> size="20" type="text" title="<?php echo Title(176); ?>" value="<?php echo $_SESSION['title_filter_text']; ?>"></td>
		            <td><button name="title_filter" type="submit" class="w_b" title="<?php echo FTM(Title(119)); ?>" value="*"<?php echo $t_block; ?>>...</button></td>
		        </tr>
		    </table>
		</td>
	</tr>
</table>
<hr align="left" size="1" noshade="noshade" color="#000000" >
