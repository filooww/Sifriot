<table>
    <tr>
        <td><font size='+2'><b><?php echo Title(541); ?></b></font></td>
    </tr>
</table>
<?php
if (isset($_SESSION['old_number_warn']) && $_SESSION['number_warn'] != $_SESSION['old_number_warn']) {
    $_SESSION['pre_ref'] = InvalidReferenceTable($dbh);
    $_SESSION['old_number_warn'] = $_SESSION['number_warn'];
}
        foreach ($_SESSION['pre_ref'] as $k_table => $v_table) {
            if (IsInvalidTableReferences($k_table)) {
                echo '<table>';
                $invalid_title = ($v_table['t_comm'] < 0) ? FTM(Title(-$v_table['t_comm'])) : Title($v_table['t_comm']);
                echo "<tr class='table_color'>";
                echo '<td>'.$invalid_title.'</td>';
                echo '<td></td>';
                echo '</tr>';
                foreach ($_SESSION['pre_ref'][$k_table]['p'] as $k_type => $v_type) {
                    if (isset($_SESSION['pre_ref'][$k_table]['p'][$k_type]['v']) && count($_SESSION['pre_ref'][$k_table]['p'][$k_type]['v']) > 0) {
                        echo "<tr class='group_color'>";
                        echo '<td>'.(($v_type['ts'][0] < 0) ? FTM(Title(-$v_type['ts'][0])) : Title($v_type['ts'][0])).'</td>';
                        echo '<td>'.(($v_type['ts'][1] < 0) ? FTM(Title(-$v_type['ts'][1])) : Title($v_type['ts'][1])).'</td>';
                        echo '</tr>';
                        foreach ($v_type['v'] as $k => $v) {
                            echo "<tr valign='top'>";
                            echo '<td><b>'.$k.'</b></td>';
                            echo '<td><b>'.implode(' ', $v).'</b></td>';
                            echo '</tr>';
                        }
                    }
                }
                if ($_SESSION['pre_ref'][$k_table]['over']) {
                    echo "<tr valign='top'><td><font color='#0000FF'><i>".Title(359).' ... </i></font></td><td></td></tr>';
                }
                echo "<tr><td><font class='invis_color'>X</font></td><td></td></tr>";
                echo '</table>';
            }
        }

        ?>


