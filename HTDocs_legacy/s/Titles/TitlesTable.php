<table class="t_bord">
	<tr>
		<?php
        if ($_SESSION['user_working_mode'] == 1) {
            echo "<td width='1%' class='cell_invisible'>X</td>";
            echo "<td width='1%' align='center'>".ImgV('Edit', 18, 16).'</td>';
            echo "<td width='1%' align='center'>".ImgV('Delete', 18, 16).'</td>';
        }
		?>
		<td width="10%"><b><?php echo Title(147); ?></b></td>
		<td width="8%"><b><?php echo Title(181); ?></b></td>
		<td width="50%"><b><?php echo Title(293); ?></b></td>
		<td></td>
	</tr>
	<?php
    $id_title_prev = '';
		foreach ($_SESSION['title_param'] as $k => $v) {
		    $k_arr = explode('|', $k);
		    $free_langs = FreeLanguages((int) $k_arr[0], (int) $k_arr[1]);
		    $title_key = (isset($_SESSION['title_edit_row'])) ? TitleKey($_SESSION['title_edit_row'][0], $_SESSION['title_edit_row'][1]) : '';
		    $del_block = ! isset($_SESSION['title_edit_row']) || isset($_SESSION['title_edit_row']) && $k != $title_key;
		    $b_edit_block = (isset($_SESSION['title_edit_row']) && $k != $title_key);
		    $b_edit_image = (isset($_SESSION['title_edit_row']) && $k == $title_key);
		    $text_block = ($_SESSION['user_working_mode'] == 0 || $k != $title_key);
		    echo "<tr valign='top'>";
		    ViewTitleRow($k_arr[0] == $id_title_prev, $k, $v, $free_langs, $b_edit_image, $b_edit_block, $text_block, $del_block, 'id_class');
		    echo '</tr>';
		    $id_title_prev = $k_arr[0];
		}
		for ($i = count($_SESSION['title_param']); $i < $_SESSION['portion']; $i++) {
		    echo '<tr>';
		    if ($_SESSION['user_working_mode'] == 1) {
		        echo '<td></td><td</td>';
		    }
		    echo "<td align='right' class='dis_text'>".(string) ($i + 1).'</td><td></td><td</td>';
		    echo '</tr>';
		}
		?>
</table>
