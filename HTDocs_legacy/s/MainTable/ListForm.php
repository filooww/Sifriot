<head>
	<style>.odd_row {background-color:#FFFFFF;}</style>
	<style>.even_row {background-color:#CCFFFF;}</style>
	<style>.cur_row {background-color:#CC99FF;}</style>
	<style>.mark_button {background-color:#FFFFFF; color:#FF0000}</style>
	<style>.edit_button {background-color:#33CC99;}</style>
	<style>.copy_button {background-color:#999900;}</style>
	<style>.dis_text {color:#CCCCCC;}</style>
	<style>.round_button {color:#000000; border:none; border-radius:13px; background: rgb(255,255,255) linear-gradient(rgb(255,255,255), rgb(0,0,0));}</style>
	<style>.file_bgc {background:#CCFF66;}</style>
	<style>.invisible_button {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin;}</style>
</head>

<?php
function FieldFilter($k_v, $URL_path)
{
    if (! array_key_exists($k_v, $_SESSION['main_params']['const'][1])) {
        return false;
    }
    if ($_SESSION['main_params']['const'][1][$k_v]['screen_order'] == 0) {
        return false;
    }
    if ($URL_path == '' && strpos($_SESSION['main_params']['const'][1][$k_v]['using'], 'update') !== false) {
        return true;
    }
    if ($URL_path != '' && strpos($_SESSION['main_params']['const'][1][$k_v]['using'], 'view') !== false) {
        return true;
    }

    return false;
}
function ViewMTPortion($main_table, $p_files, $item_mark_del_question, $URL_path = '', $user_priority = 0, $view_on = false)
{
    $cl = true;
    $bl = $_SESSION['block']['item_del'] || $_SESSION['block']['pad'] || $_SESSION['block']['pad_cat'];
    $n_max = MaxWidth(($URL_path = '') ? '1' : '4'); // update | view
    foreach ($_SESSION['item_arr'] as $k => $v) {
        $bgcl = ($cl) ? "class='odd_row'" : "class='even_row'";
        if ($k == (string) $_SESSION['p_code']) {
            $bgcl = "class='cur_row'";
        }
        echo "<tr valign='top' ".$bgcl.'>';
        if ($URL_path != '' && $user_priority > 0) {
            $button_title = ($view_on) ? Title(46) : Title(65);
            echo "<td width='2%' align='center'><button name='view_item-".$k."' type='submit' title='".$button_title."' class='round_button' value='*' style='text-align:center'>.</button></td>";
        }
        if ($URL_path == '') {
            echo "<td><button name='mark_item-".$k."' type='submit' title='mark this item as deleted' class='mark_button' value='*' style='text-align:center' ".SetExPar($bl, '').'>'.ImgV(ViewMarkDel($v[$_SESSION['spec_fld'][1]['del_mark']][0]), 18, 16).'</button></td>';
            echo "<td><button name='edit_item-".$k."' type='submit' title='edit this item' class='edit_button' value='*' style='text-align:center' ".SetExPar($bl, '').'>'.ImgV('Edit', 16, 16).'</button></td>';
            echo "<td><button name='copy_item-".$k."' type='submit' title='copy this item' class='copy_button' value='*' style='text-align:center' ".SetExPar($bl, '').'>'.ImgV('Copy', 16, 16).'</button></td>';
        }
        foreach ($v as $k_v => $v_v) {
            if (FieldFilter($k_v, $URL_path)) {
                $v_out = ($v_v[1]) ? "<font color='#FF0000'>Broken link</font>" : PutValue($v_v[0], $_SESSION['PR']['const'][$k_v]);
                echo "<td align='".$select_align[$_SESSION['main_params']['const'][1][$k_v]['f_align']]."'>".$v_out.'</td>';
            }
        }
        echo '</tr>';
        $cl = ! $cl;
        if ($URL_path != '' && $user_priority > 0 && $k == (string) $_SESSION['p_code'] && $view_on) {
            ViewPubFiles($p_files, $URL_path);
        }
        if ($URL_path == '' && $k == (string) $_SESSION['p_code'] && $item_mark_del_question) {
            ViewMarkQuestion($v['del_mark'][0], $n_max, count($v));
        }
    }
    for ($i = count($_SESSION['item_arr']); $i < $_SESSION['portion']; $i++) {
        echo '<tr>';
        if ($URL_path == '') {
            echo '<td></td><td></td><td></td>';
        }
        echo "<td align='right' class='dis_text'>".(string) ($i + 1).'</td>';
        echo '</tr>';
    }
}
function ViewMarkDel($mark_del_flag)
{
    if ($mark_del_flag) {
        return 'MarkDelete';
    } else {
        return 'Blank';
    }
}
function PutValue($col_value, $p_con_k)
{
    if ($p_con_k['table1'] != '' && ! $_SESSION['m_col_v']) {
        $arr_set = explode($p_con_k['separator'], $col_value);
        $col_v = (count($arr_set) == 0) ? $col_value : $arr_set[0];
    } else {
        $col_v = $col_value;
    }
    if ($p_con_k['type'] == 'integer' && $col_v == '0') {
        return '';
    } else {
        return $col_v;
    }
}
function ViewPubFiles($p_files, $conf_root)
{
    echo "<tr class='file_bgc'>";
    echo '<td></td>';
    echo '<td></td>';
    echo "<td align='left'><b>File name (".Total(51).' '.(string) count($p_files).')</b></th>';
    echo "<td align='center'><b>Year</b></th>"; // titles #67
    echo "<td align='center'><b>Volume</b></th>";
    echo "<td align='center'><b>Number</b></th>";
    echo "<td align='center'><b>Page</b></th>";
    echo "<td align='left'><b>File description</b></th>";
    echo '<td></td>';
    echo '</tr>';
    // p_con!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    foreach ($p_files as $k => $v) {
        echo "<tr valign='top'  class='file_bgc'>";
        echo '<td></td>';
        echo '<td></td>';
        // ~~url
        if ($v['url_file'] == '') {
            echo "<td width='30%'><font color='#FF0000'><< ".Title(72).' >></font></td>';
        } elseif (! ResExists($v['url_file'])) {
            echo "<td width='50%'><font color='#FF0000'><< ".Title(72).' >></font></td>';
        } else {
            echo "<td width='30%'><a name='file_name-".$k."' href='".RefToRes($v['url_file'], $conf_root.'/')."' target='_blank'>".$v['file_name'].'</a></td>';
        } // ~~url
        echo "<td width='6%' align='center'>".(($v['file_issue_year'] == 0) ? '' : (string) $v['file_issue_year']).'</td>';
        echo "<td width='3%' align='center'>".$v['file_volume'].'</td>';
        echo "<td width='3%' align='center'>".$v['file_number'].'</td>';
        echo "<td width='3%' align='center'>".$v['file_page'].'</td>';
        echo '<td>'.$v['file_description'].'</td>';
        // ~~url
        echo '<td></td>';
        echo '</tr>';
    }
}
function ViewMarkQuestion($p_mark, $n_max, $count_v)
{
    $text = 'Do You want to '.(($p_mark) ? 'cancel' : 'set').' deletion mark?';
    echo '<tr>';
    echo '<td></td>';
    echo '<td></td>';
    echo '<td></td>';
    for ($i = 0; $i < $n_max; $i++) {
        echo '<td></td>';
    }
    echo '<td>';
    echo '<b>'.$text.'</b>';
    echo "<font class='invisible_button'>X</font>";
    echo "<input size='10' name='yes_mark' type='submit' value='Yes'>";
    echo "<font class='invisible_button'>X</font>";
    echo "<input size='10' name='no_mark' type='submit' value='No'>";
    echo '</td>';
    for ($i = 0; $i < $count_v - $n_max - 1; $i++) {
        echo '<td></td>';
    }
    echo '</tr>';
}

?>
