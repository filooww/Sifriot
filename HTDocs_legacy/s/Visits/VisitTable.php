<?php
echo Title(51).': <b>'.$_SESSION['visit_total_size']."</b><font class='cell_inv'>XXX</font>".Title(527).': <b>'.(string) $_SESSION['visit_size'].'</b>';
?>
<hr noshade="noshade" color="#000000" >
<table>
	<tr valign="top">
		<?php
        if (! $_SESSION['cancel_started']) {
            echo "<td width='3%'>";
            echo '<table>';
            echo "<tr><td><font class='cell_inv'>X</font></td></tr>";
            echo "<tr><td><button name='visit_navigation|beg' type='submit' class='i_h' title='".Title(35)."'>".SysImage('LineFirst', 16, 16).'</button></td></tr>';
            echo "<tr><td><button name='visit_navigation|pgup' type='submit' class='i_h' title='".Title(36)."'>".SysImage('PageUp', 16, 16).'</button></td></tr>';
            echo "<tr><td><button name='visit_navigation|lnup' type='submit' class='i_h' title='".Title(37)."'>".SysImage('LineUp', 16, 16).'</button></td></tr>';
            echo "<tr><td><button name='visit_navigation|lndn' type='submit' class='i_h' title='".Title(38)."'>".SysImage('LineDown', 16, 16).'</button></td></tr>';
            echo "<tr><td><button name='visit_navigation|pgdn' type='submit' class='i_h' title='".Title(39)."'>".SysImage('PageDown', 16, 16).'</button></td></tr>';
            echo "<tr><td><button name='visit_navigation|end' type='submit' class='i_h' title='".Title(40)."'>".SysImage('LineEnd', 16, 16).'</button></td></tr>';
            echo '</table>';
            echo '</td>';
        }
?>
		<td>
			<table>			
				<tr>
					<?php if ($_SESSION['user_working_mode'] != 0) {
					    echo '<td></td>';
					}?>
					<td><b><?php echo Title(163); ?></b></td>
					<?php if ($_SESSION['alarm']) {
					    echo '<td></td>';
					}?>
					<td><b><?php echo Title(6); ?></b></td>
					<?php if ($_SESSION['alarm']) {
					    echo '<td></td>';
					}?>
					<td align="center"><b><?php echo Title(164); ?></b></td>
					<td align="center"><b><?php echo Title(165); ?></b></td>
					<td align="center"><b><?php echo Title(153); ?></b></td>
					<td></td>
					<td></td>
				</tr>
				<?php
                foreach ($_SESSION['visit_list'] as $k => $v) {
                    $arr = explode('|', $k);
                    $u_this = ($arr[0] == '0' && $arr[1] == $_SESSION['user_id'] && $v[6] == $_SESSION['user_working_mode']);

                    echo "<tr valign='top'>";
                    if ($_SESSION['user_working_mode'] != 0) {
                        if (! $u_this) {
                            if ($v[7] == 0) {
                                $b_image = SysImage('BlankBorder', 16, 16, $_SESSION['user_working_mode'] == 0);
                            } elseif ($v[7] == 1) {
                                $b_image = SysImage('CheckBorder', 16, 16, $_SESSION['user_working_mode'] == 0);
                            } else {
                                $b_image = SysImage('Delete', 16, 16, $_SESSION['user_working_mode'] == 0);
                            }
                            echo "<td><button name='suspend_session|".$k."' title='".Title(166)."' type='submit' class='button_class' value='*'>".$b_image.'</button></td>';
                        } else {
                            echo '<td></td>';
                        }
                    }
                    echo "<td><input size='15' disabled type='text' name='db_name|".$k."' value='".$v[0]."'></td>";
                    if ($_SESSION['alarm']) {
                        echo "<td><input size='5' disabled type='text' name='db_id|".$k."' value='".$v[1]."' class='".(($v[0] == '') ? 'data_numeric_bold_red' : 'data_numeric_bold')."'></td>";
                    }
                    echo "<td><input size='15' disabled type='text' name='user_name|".$k."' value='".$v[2]."'></td>";
                    if ($_SESSION['alarm']) {
                        echo "<td><input size='5' disabled type='text' name='user_id|".$k."' value='".$v[3]."' class='".(($v[2] == '') ? 'data_numeric_bold_red' : 'data_numeric_bold')."'></td>";
                    }
                    echo "<td><input size='20' disabled type='text' class='data_center_bold' name='work_start|".$k."' value='".ToDate($v[4], '.', true)."'></td>";
                    echo "<td><input size='18' disabled type='text' name='visit_count|".$k."' value='".$v[5]."' class='".((! is_numeric($v[5]) || is_numeric($v[5]) && (int) $v[5] < 1) ? 'data_numeric_bold_red' : 'data_numeric_bold')."'></td>";
                    echo "<td><input size='12' disabled type='text' name='working_mode|".$k."' value='".WorkingMode($v[6], Title(154), Title(155))."' class='".(($v[6] < -1 || $v[6] > 1) ? 'data_center_bold_red' : 'data_center_bold')."'></td>";
                    if ($u_this || CompareSessionParameters($v)) {
                        echo '<td></td>';
                    } else {
                        echo "<td><button name='reset_session|".$k."' title='".Title(194)."' type='submit' class='w_b' value='*'>...</button></td>";
                    }
                    echo '<td>'.$v[9]['str'].'</td>';
                    echo '</tr>';
                }
for ($i = count($_SESSION['visit_list']); $i < $_SESSION['portion']; $i++) {
    echo "<tr><td align='right' class='dis_text'>".(string) ($i + 1).'</td></tr>';
}
?>
			</table>
		</td>
	</tr>
</table>
