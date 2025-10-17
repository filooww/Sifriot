<?php
function TestItemFilling($dbh, $p_code, $p_con, $item_row, &$Mes)
{
    $c_mes = count($Mes);
    $f_unique = false;
    foreach (array_keys($item_row) as $k) {
        if (strpos($k, '_code') === false && $k != 'del_mark') {
            $source_f = (isset($_POST[$k])) ? $_POST[$k] : $item_row[$k];
            $on_mes = count($Mes);
            $v = ($p_con[$k]['default'] != '' && $source_f == '') ? $p_con[$k]['default'] : $source_f;
            if ($v == '' && ! $p_con[$k]['blank']) {
                TestBlank($p_con[$k]['name'], $v, $p_con[$k]['type'], $Mes);
            }
            if ($on_mes == count($Mes) && $p_con[$k]['table'] == '') {
                $illegal_mes = SetTestMessages(StringTest($p_con[$k]['illegals'], $v, 'replace_text'), $v);
                if ($illegal_mes != '') {
                    $Mes[] = ['time' => '', 'text' => $illegal_mes, 'status' => 'error'];
                }
            }
            if ($on_mes == count($Mes) && $p_con[$k]['type'] != '') {
                if ($p_con[$k]['type'] == 'integer' && $v != '' && ! is_numeric($v)) {
                    $Mes[] = ['time' => '', 'text' => 'The <b>'.$p_con[$k]['name'].'</b> of item is not numeric', 'status' => 'error'];
                }
                if ($p_con[$k]['type'] == 'date') {
                    $date_err = CalendTestDate($v);
                    if (count($date_err) > 0) {
                        $Mes[] = ['time' => '', 'text' => 'The <b>'.$p_con[$k]['name'].'</b> of item: '.implode('; ', $date_err), 'status' => 'error'];
                    }
                }
            }
            if ($on_mes == count($Mes) && $p_con[$k]['unique']) {
                TestUnique($dbh, $p_con, $p_code, $item_row, $Mes, $p_con[$k]['ref'], $p_con[$k]['name'], $v);
                $f_unique = true;
            }
        }
    }
    if (! $f_unique) {
        TestUnique($dbh, $p_con, $p_code, $item_row, $Mes);
    }

    return count($Mes) == $c_mes;
}
function InsertItem($dbh, &$p_code, $p_con, $item_row, &$Mes)
{
    $c_mes = count($Mes);
    $q_txt = ItemQueryText('insert', $item_row, $p_con);
    if ($q_txt != '') {
        mysqli_query($dbh, 'ALTER TABLE '.$main_table.' AUTO_INCREMENT = 1');
        mysqli_query($dbh, 'INSERT INTO '.$main_table.' '.$q_txt);
        if (mysqli_errno($dbh) > 0) {
            $Mes[] = ['time' => '', 'text' => mysqli_error($dbh), 'status' => 'error'];
        } else {
            $item_id = GetAutoIncrement($dbh);
            $p_code = ($item_id == '') ? 0 : (int) $item_id;
        }
    }

    return count($Mes) == $c_mes;
}
function UpdateItem($dbh, $main_table, $p_code, $p_con, $item_row, &$Mes)
{
    $c_mes = count($Mes);
    $q_txt = ItemQueryText('update', $item_row, $p_con);
    if ($q_txt != '') {
        mysqli_query($dbh, 'UPDATE '.$main_table.' SET '.$q_txt.' WHERE '.$_SESSION['spec_fld'][1]['key'].' = '.(string) $p_code);
        if (mysqli_errno($dbh) > 0) {
            $Mes[] = ['time' => '', 'text' => mysqli_error($dbh), 'status' => 'error'];
        }
    }

    return count($Mes) == $c_mes;
}
function SaveItem($dbh, &$s)
{
    $c_mes = count($s['mes']['m']);
    if (TestItemFilling($dbh, $s['p_code'], $s['PR']['const'], $s['item_row']['e'], $s['mes']['m'])) {
        if ($s['p_code'] == 0) {
            $no_err = InsertItem($dbh, $s['p_code'], $s['PR']['const'], $s['item_row'], $s['mes']['m']);
        } else {
            $no_err = UpdateItem($dbh, $s['p_code'], $s['PR']['const'], $s['item_row'], $s['mes']['m']);
        }
        if ($no_err) {
            ChangeItemFiles($dbh, $s['p_code'], $s['p_files'], $s['URL_p'], $s['mes']['m']);
            CopyItemRowFiles($s['item_row'], $s['p_files']);
            ChangeItemArr($dbh, $s['p_code'], $s['item_row'], $s['item_arr']);
        }
    }

    return count($s['mes']['m']) == $c_mes;
}
function ChangeItemArr($dbh, $c_code, $item_row, &$item_arr)
{
    $item_keys = array_keys($item_arr);
    if (! in_array((string) $c_code, $item_keys)) {
        if (count($item_arr) == $_SESSION['portion']) {
            $del_key = GetFirstKey($item_arr);
            unset($item_arr[$del_key]);
        }
    }
    $item_arr[(string) $c_code] = $item_row['e'];
}
function TestValue($k, $v, $item_row)
{
    if ($v['table'] != '') {
        return $item_row[$k.'_code'];
    } elseif ($v['type'] != '' && $$v['type'] == 'integer') {
        return $item_row[$k];
    }

    return "'".$item_row[$k]."'";
}
function TestUnique($dbh, $main_table, $p_con, $p_code, $item_row, &$Mes, $unique_field = '', $unique_name = '', $unique_value = '')
{
    if ($unique_field == '') {
        $arr_compar = [];
        foreach ($p_con as $k => $v) {
            if (! $v['key'] && $v['field_check']) {
                $arr_compar[] = $v['ref'].' = '.TestValue($k, $v, $item_row);
            }
        }
        if (count($arr_compar) > 0) {
            $ww = implode(' AND ', $arr_compar).(($p_code == 0) ? '' : ' AND '.$_SESSION['spec_fld'][1]['key'].' <> '.(string) $p_code);
            $res = mysqli_query($dbh, 'SELECT '.$_SESSION['spec_fld'][1]['key'].' FROM '.$main_table.' WHERE '.$ww);
        }
    } else {
        $res = mysqli_query($dbh, 'SELECT '.$_SESSION['spec_fld'][1]['key'].' FROM '.$main_table.' WHERE '.$unique_field." = '".$unique_value."' AND ".$_SESSION['spec_fld'][1]['key'].' <> '.(string) $p_code);
    }
    if ($res) {
        $arr_id = [];
        while ($row = mysqli_fetch_row($res)) {
            $arr_id[] = (string) $row[0];
        }
        mysqli_free_result($res);
        if (count($arr_id) > 0) {
            $s = (count($arr_id) == 1) ? '' : 's';
            if ($unique_field == '') {
                $Mes[] = ['time' => '', 'text' => 'The same data have follow item ID'.$s.': <b>'.implode(', ', $arr_id).'</b>', 'status' => 'error'];
            } else {
                $Mes[] = ['time' => '', 'text' => 'The same <b>'.$unique_name.'</b> have follow item ID'.$s.': <b>'.implode(', ', $arr_id).'</b>', 'status' => 'error'];
            }
        }
    }
}
function TestBlank($test_name, $test_value, $ref_type, &$Mes)
{
    if ($test_value == '') {
        $Mes[] = ['time' => '', 'text' => 'The <b>'.$test_name.'</b> is empty', 'status' => 'error'];
    } else {
        if ($ref_type == 'integer' && $test_value == '0') {
            $Mes[] = ['time' => '', 'text' => 'The <b>'.$test_name.'</b> is empty', 'status' => 'error'];
        } elseif ($ref_type == 'date' && is_null($test_value)) {
            $Mes[] = ['time' => '', 'text' => 'The <b>'.$test_name.'</b> is empty', 'status' => 'error'];
        }
    }
}
function CopyItemRowFiles(&$item_row, &$p_files)
{
    foreach ($item_row['e'] as $k => $v) {
        $item_row['i'][$k] = $v;
    }
    foreach ($p_files['e'] as $k => $v) {
        foreach ($v as $k0) {
            $p_files['i'][$k][$k0] = $p_files['e'][$k][$k0];
        }
    }
    /*
        {
            $p_files['i'][$k]['file_name'] = $p_files['e'][$k]['file_name'];
            $p_files['i'][$k]['file_description'] = $p_files['e'][$k]['file_description'];
            $p_files['i'][$k]['file_issue_year'] = $p_files['e'][$k]['file_issue_year'];
            $p_files['i'][$k]['file_volume'] = $p_files['e'][$k]['file_volume'];
            $p_files['i'][$k]['file_number'] = $p_files['e'][$k]['file_number'];
            $p_files['i'][$k]['file_page'] = $p_files['e'][$k]['file_page'];
            $p_files['i'][$k]['url_file'] = $p_files['e'][$k]['url_file']; //~~url
        }
    */
}
function ItemSavePost($p_con, &$item_row, &$p_files)
{
    foreach ($p_con as $k => $v) {
        if (isset($_POST[$k]) && $v['table'] == '') {
            $item_row[$k] = $_POST[$k];
        }
    }
    foreach ($p_files as $k => $v) {
        if (isset($_POST['file_issue_year-'.$k])) {
            $p_files[$k]['file_issue_year'] = (($_POST['file_issue_year-'.$k] == '') ? 0 : $_POST['file_issue_year-'.$k]);
        }
        if (isset($_POST['file_volume-'.$k])) {
            $p_files[$k]['file_volume'] = $_POST['file_volume-'.$k];
        }
        if (isset($_POST['file_number-'.$k])) {
            $p_files[$k]['file_number'] = $_POST['file_number-'.$k];
        }
        if (isset($_POST['file_page-'.$k])) {
            $p_files[$k]['file_page'] = $_POST['file_page-'.$k];
        }
        if (isset($_POST['file_description-'.$k])) {
            $p_files[$k]['file_description'] = $_POST['file_description-'.$k];
        }
    }
}
function ItemFormCompare($item_row, $p_files)
{
    foreach ($item_row['e'] as $k => $v) {
        if ($item_row['i'][$k] != $v) {
            return false;
        }
    }
    if (count($p_files['e']) != count($p_files['i'])) {
        return false;
    }
    foreach ($p_files['e'] as $k => $v) {
        foreach ($v as $k0 => $v0) {
            if ($p_files['i'][$k][$k0] != $v0) {
                return false;
            }
        }
    }

    return true;
}
function MaxSize($p_con)
{
    $max_size = 0;
    foreach ($p_con as $k => $v) {
        if (array_key_exists($k, $v) && $v['screen_order'] > 0 && strpos($v['using'], 'update') !== false && $v['table'] != '' && $v['size'] > $max_size) {
            $max_size = $v['size'];
        }
    }

    return $max_size;
}
function ItemHTMLForm($p_con, $main_table, $item_row, $block, $init_v, $end_v) // !!!!!!! $init_v, $end_v
{
    $arr = [''];
    for ($i = $init_v; $i <= $end_v; $i++) {
        $arr[] = $i;
    }
    $max_size = max(MaxSize($p_con), 40);
    foreach ($p_con as $k => $v) {
        if (array_key_exists($k, $v) && $v['screen_order'] > 0 && strpos($v['using'], 'update') !== false) {
            echo "<trvalign='top'>";
            echo '<td>'.$v['name'].'</td>';
            if ($v['table'] == '') {
                echo "<td width='3%'></td>";
                echo "<td width='3%'></td>";
                if ($v['type'] == 'select') {
                    echo '<td>';
                    SelectTag($k, $arr, $item_row[$k], '', false, '', '', $block);
                    //							echo "<select name='".$k."' ".FormExPar($block).">"; // SelectTag
                    //								echo OptionTag($item_row[$k], "");
                    //								for ($y = $init_v; $y <= $end_v; $y++) echo OptionTag($item_row[$k], (string)$y);
                    //								unset($y);
                    //							echo "</select>";
                    echo '</td>';
                } else {
                    if ($k == $_SESSION['spec_fld'][1]['key']) {
                        if ($_SESSION['spec_fld'][1]['key'] == '') {
                            echo '<td></td>';
                        }
                        echo "<td><input size='".$v['size']."' name='".$k."' type='text' style='style='text-align:".$sel_align[$v['f_align']]."' value='".$item_row[$k]."' ".FormExPar($block).'></td>';
                    } else {
                        echo "<td><input size='".$v['size']."' name='".$k."' readonly type='text' style='style='text-align:".$sel_align[$v['f_align']]."' value='".$item_row[$k]."' ".FormExPar($block).'></td>';
                    }
                }
            } else {
                echo "<td width='3%'><button type='submit' name='select_".$k."' title='select ".strtolower($v['name'])."' value='*' ".FormExPar($block).'>...</button></td>';
                echo "<td width='3%'><button type='submit' name='cancel_".$k."' title='cancel ".strtolower($v['name'])."' value='*' ".PM($block, 'cancel_button').'>'.ImgV('Delete', 13, 16).'</button></td>';
                echo "<td><input size='".(string) $max_size."' name='".$k."' readonly type='text' value='".$item_row[$k]."' ".FormExPar($block).'/></td>';
            }
            echo '</tr>';
        }
    }
}

?>

