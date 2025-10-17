<table>
	<?php
    foreach ($_SESSION['special_interface'] as $k => $v) {
        $row_num = max(ceil(count($v['numbers']) / $_SESSION['max_table_columns']), 1);
        $col0 = 0;
        for ($row = 0; $row < $row_num; $row++) {
            if ($v['illegal']) {
                $color = " class='illegal_color'";
                $title = Title(621);
            } elseif (count($v['numbers']) == 0) {
                if ($v['missed']) {
                    $color = " class='missed_color'";
                    $title = Title(616);
                } else {
                    $color = " class='empty_color'";
                    $title = Title(620);
                }
            } else {
                $color = '';
                $title = '';
            }
            echo "<tr valign='top'".$color.'>';
            if ($v['illegal']) {
                echo "<td width='20%'><b>".$k.'</b></td>';
            } elseif ($row > 0) {
                echo "<td width='20%'></td>";
            } else {
                echo "<td width='21%'><b>".$_SESSION['spec_titles'][$k].'</b></td>';
            }
            SpecialTextRow($k, $v, isset($_SESSION['title_edit_row']) || $_SESSION['user_working_mode'] == 0, ($row == 0) ? 'add_special|'.$k : '', $col0);
            echo '<td>'.$title.'</td>';
            echo '</tr>';
            $col0 += $_SESSION['max_table_columns'];
        }
    }
	?>
</table>
<hr align="left" size="1" noshade="noshade" color="#000000" >

