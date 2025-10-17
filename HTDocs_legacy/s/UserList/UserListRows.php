<table>
	<tr>
		<?php if ($_SESSION['user_working_mode'] == 1) {
		    echo "<td align='center'>".ImgV('Edit', 18, 16).'</td>';
		}?>
		<td align="center"><b><?php echo Title(147); ?></b></td>
		<td><b><?php echo Title(6); ?></b></td>
		<td><b><?php echo Title(7); ?></b></td>
		<td align="center"><b><?php echo Title(148); ?></b></td>
		<td></td>
	</tr>
	<?php
    foreach ($_SESSION['user_list'] as $k => $v) {
        echo "<tr valign='top'>";
        $_SESSION['user_flag'][$k][10] = UserDisabled($k);
        $t_user = ($_SESSION['user_flag'][$k][10]) ? ' disabled' : '';
        $button_image = ($_SESSION['user_info'] != $k);
        $button_title = ($button_image) ? Title(597).' '.Title(598) : FTM(Title(431)).' '.Title(598);
        if ($_SESSION['user_working_mode'] == 0) {
            $f_button_edit = false;
            $t_edit = '';
        } else {
            if ($k == $_SESSION['user_id']) {
                $f_button_edit = true;
                $t_edit = ' disabled';
                echo '<td></td>';
            } else {
                $f_button_edit = ($_SESSION['edit_user'] != '' && $k != $_SESSION['edit_user'] || $_SESSION['user_delete'][0] != '');
                $t_edit = (($f_button_edit) ? ' disabled' : '');
                echo "<td><button name='user_edit|".$k."' type='submit' title='".Title(241)."' class='button_class' value='*'".$t_edit.'>'.SysImage((($k == $_SESSION['edit_user'] && $_SESSION['edit_user'] != '') ? 'CheckBorder' : 'BlankBorder'), 16, 16, $f_button_edit).'</button></td>';
            }
        }
        echo "<td><input size='12' disabled title='".GetDataTitle($k, 0)."' ".UserDataColor($k, 'data_error_id', 'data_id', 0)." type='text' name='user_numb|".$k."' value='".$k."'></td>";
        echo "<td><input size='16' type='text' title='".GetDataTitle($k, 1)."' ".UserDataColor($k, 'data_error', '', 1)." name='user_login|".$k."' value='".$v[0]."'".$t_user.'></td>';
        echo "<td><input size='20' type='text' title='".GetDataTitle($k, 2)."' ".UserDataColor($k, 'data_error', '', 2)." name='user_password|".$k."' value='".$v[1]."'".$t_user.'></td>';
        echo "<td><input size='10' type='text' title='".GetDataTitle($k, 3)."' ".UserDataColor($k, 'data_error_num', 'data_num', 3)." name='user_priority|".$k."' value='".$v[2]."'".$t_user.'></td>';
        echo "<td><button name='user_info|".$k."' type='submit' title='".$button_title."' class='button_class' value='*'>".SysImage(UserButtonColor($k, 'CheckBorder', 'BlankBorder', 'RoseBorder'), 16, 16).'</button></td>';
        echo '</tr>';
    }
		for ($i = count($_SESSION['user_list']); $i < $_SESSION['portion']; $i++) {
		    echo (($_SESSION['user_working_mode'] == 0) ? '' : '<tr><td></td><td>')."</td><td align='right' class='dis_text'>".(string) ($i + 1).'</td></tr>';
		}
		?>
</table>
