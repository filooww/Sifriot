<?php

function GetAlgPortion($dbh)
{
    $alg_arr = [];
    $res = mysqli_query($dbh, 'SELECT id_algorithm, alg_remarks FROM algorithms ORDER BY id_algorithm LIMIT '.(string) $_SESSION['start'].','.(string) $_SESSION['portion']);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $alg_arr[$row[0]] = $row[1];
        }
        mysqli_free_result($res);
    }

    return $alg_arr;
}
function AddAlg($row, &$alg_arr)
{
    $alg_arr[$row[0]]['offset'] = $row[1];
    $alg_arr[$row[0]]['del_from_source'] = $row[2];
    $alg_arr[$row[0]]['beg_del'] = $row[3];
    $alg_arr[$row[0]]['beg_num'] = $row[4];
    $alg_arr[$row[0]]['beg_inc'] = $row[5];
    $alg_arr[$row[0]]['beg_scr'] = $row[6];
    $alg_arr[$row[0]]['inn_del'] = $row[7];
    $alg_arr[$row[0]]['end_del'] = $row[8];
    $alg_arr[$row[0]]['end_num'] = $row[9];
    $alg_arr[$row[0]]['end_inc'] = ($row[10] == 1);
    $alg_arr[$row[0]]['end_scr'] = ($row[11] == 1);
    $alg_arr[$row[0]]['del_sym'] = $row[12];
    $alg_arr[$row[0]]['ins_sym'] = $row[13];
    $alg_arr[$row[0]]['field_only'] = ($row[14] == 1);
    $alg_arr[$row[0]]['reg_exp'] = $row[15];
    $alg_arr[$row[0]]['reg_scr'] = ($row[16] == 1);
    $alg_arr[$row[0]]['remarks'] = $row[17];
}
function ViewAlgPortion($alg_arr)
{
    $cl = true;
    foreach ($alg_arr as $k_ref => $v_ref) {
        $bgcl = ($cl) ? "class='odd_row'" : "class='ever_row'";
        echo "<tr valign='top' ".$bgcl.'>';
        echo "<td width='4%'><button name='delete_alg-".$k_ref."' type='submit' title='delete this algorithm' class='delete_button' value='*' style='text-align:center'>Delete</button></td>";
        echo "<td width='3%'><button name='edit_alg-".$k_ref."' type='submit' title='edit this algorithm' class='edit_button' value='*' style='text-align:center'>Edit</button></td>";
        echo "<td width='3%'><button name='copy_alg-".$k_ref."' type='submit' title='copy this algorithm' class='copy_button' value='*' style='text-align:center'>Copy</button></td>";
        echo "<td width='4%'><button name='select_alg-".$k_ref."' type='submit' title='select this algorithm' class='select_button' value='*' style='text-align:center'>Select</button></td>";
        echo "<td width='4%'><button name='cancel_alg-".$k_ref."' type='submit' title='cancel this algorithm selection' class='cancel_button' value='*' style='text-align:center'>Cancel</button></td>";
        echo "<td width='4%' align='center'>".$k_ref.' </td>';
        echo "<td width='8%' align='left'>".$v_ref['field'].'</td>';
        echo "<td width='8%' align='center'>".(string) $v_ref['offset'].'</td>';
        echo "<td width='9%' align='left'>".$v_ref['beg_del'].'</td>';
        echo "<td width='8%' align='left'>".$v_ref['inn_del'].'</td>';
        echo "<td width='8%' align='left'>".$v_ref['end_del'].'</td>';
        echo "<td width='9%' align='left'>".$v_ref['del_sym'].'</td>';
        echo "<td width='10%' align='left'>".$v_ref['reg_exp'].'</td>';
        echo "<td width='30%' align='left'>".$v_ref['remarks'].'</td>';
        echo '</tr>';
        $cl = ! $cl;
    }
    for ($i = count($alg_arr); $i < $_SESSION['portion']; $i++) {
        echo '<tr>';
        echo "<td width='4%' class='cell_invisible'>X</td>";
        echo "<td width='3%' class='cell_invisible'>X</td>";
        echo "<td width='3%' class='cell_invisible'>X</td>";
        echo "<td width='4%' class='cell_invisible'>X</td>";
        echo "<td width='4%' class='cell_invisible'>X</td>";
        echo "<td width='4%' align='center' class='dis_text'>".(string) ($i + 1).'</td>';
        echo "<td width='8%' class='cell_invisible'>X</td>";
        echo "<td width='8%' class='cell_invisible'>X</td>";
        echo "<td width='9%' class='cell_invisible'>X</td>";
        echo "<td width='8%' class='cell_invisible'>X</td>";
        echo "<td width='8%' class='cell_invisible'>X</td>";
        echo "<td width='9%' class='cell_invisible'>X</td>";
        echo "<td width='9%' class='cell_invisible'>X</td>";
        echo "<td width='10%' class='cell_invisible'>X</td>";
        echo "<td width='30%' class='cell_invisible'>X</td>";
        echo '</tr>';
    }
}
function ParseAlgButtons($dbh)
{
    foreach ($_POST as $str_key => $str_v) {
        $sw_break = true;
        $arr_key = explode('-', $str_key);
        $s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
        switch ($s_k) {
            case 'alg_exit': return ['act' => 'alg_exit'];
            case 'alg_create': return ['act' => 'alg_create'];
            case 'alg_minus': return ['act' => 'scr_h', 'scr_corr' => -1];
            case 'alg_plus': return ['act' => 'scr_h', 'scr_corr' => 1];
            case 'alg_beg': return ['act' => 'nav', 'nv' => 'beg'];
            case 'alg_pg_up': return ['act' => 'nav', 'nv' => 'pgup'];
            case 'alg_ln_up': return ['act' => 'nav', 'nv' => 'lnup'];
            case 'alg_ln_dn': return ['act' => 'nav', 'nv' => 'lndn'];
            case 'alg_pg_dn': return ['act' => 'nav', 'nv' => 'pgdn'];
            case 'alg_end': return ['act' => 'nav', 'nv' => 'end'];
            case 'delete_alg': return ['act' => 'alg_del', 'code' => $arr_key[1]];
            case 'edit_alg': return ['act' => 'alg_ed', 'code' => $arr_key[1]];
            case 'copy_alg': return ['act' => 'alg_copy', 'code' => $arr_key[1]];
            case 'select_alg': return ['act' => 'alg_sel', 'code' => $arr_key[1]];
            case 'cancel_alg': return ['act' => 'alg_can', 'code' => $arr_key[1]];
            case 'yes_name': return ['act' => 'del_act', 'ans' => true];
            case 'cancel_name': return ['act' => 'del_act', 'ans' => false];
            case 'selected_alg': return ['act' => 'selected_alg'];
            default: $sw_break = false;
        }
        if ($sw_break) {
            break;
        }
    }

    return [];
}
function ChangeAlgScreenHeight($dbh, $offs, &$alg_arr)
{
    if ($_SESSION['portion'] == 1 && $offs == -1) {
        return;
    }
    $_SESSION['portion'] += $offs;
    $alg_arr = GetAlgPortion($dbh);
}
function NumberOfAlgorithms($dbh)
{
    $ctbl = 0;
    $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM algorithms');
    if ($row = mysqli_fetch_row($res)) {
        $ctbl = $row[0];
    }
    mysqli_free_result($res);

    return $ctbl;
}
function DeleteAlg($dbh, &$alg_code, &$alg_arr, &$alg_count, &$arr_alg_seq)
{
    if (isset($arr_alg_seq[$alg_code])) {
        unset($arr_alg_seq[$alg_code]);
    }
    mysqli_query($dbh, 'DELETE FROM algorithms WHERE id_algorithm = '.$alg_code);
    mysqli_query($dbh, 'ALTER TABLE algorithms AUTO_INCREMENT = 1');
    if (mysqli_errno($dbh) == 0) {
        $alg_arr = GetAlgPortion($dbh);
        $alg_count--;
        $alg_code = '';
    }
}
function GetAlgList($arr_alg_seq)
{
    $str_alg = '';
    $last_k = GetLastKey($arr_alg_seq);
    foreach ($arr_alg_seq as $k => $v) {
        $str_alg .= '# '.$k.' - '.$v['field'].' - '.$v['remarks'];
        if ($k != $last_k) {
            $str_alg .= ', ';
        }
    }

    return $str_alg;
}
function CopyAlg($arr_alg_row, &$copy_par)
{
    $copy_par['field'] = $arr_alg_row['field'];
    $copy_par['offset'] = $arr_alg_row['offset'];
    $copy_par['del_from_source'] = $arr_alg_row['del_from_source'];
    $copy_par['beg_del'] = $arr_alg_row['beg_del'];
    $copy_par['beg_num'] = $arr_alg_row['beg_num'];
    $copy_par['beg_inc'] = $arr_alg_row['beg_inc'];
    $copy_par['beg_scr'] = $arr_alg_row['beg_scr'];
    $copy_par['inn_del'] = $arr_alg_row['inn_del'];
    $copy_par['end_del'] = $arr_alg_row['end_del'];
    $copy_par['end_num'] = $arr_alg_row['end_num'];
    $copy_par['end_inc'] = $arr_alg_row['end_inc'];
    $copy_par['end_scr'] = $arr_alg_row['end_scr'];
    $copy_par['del_sym'] = $arr_alg_row['del_sym'];
    $copy_par['ins_sym'] = $arr_alg_row['ins_sym'];
    $copy_par['field_only'] = $arr_alg_row['field_only'];
    $copy_par['reg_exp'] = $arr_alg_row['reg_exp'];
    $copy_par['reg_scr'] = $arr_alg_row['reg_scr'];
    $copy_par['remarks'] = $arr_alg_row['remarks'];
}
function AlgValueByPost($radio_name, $v_post)
{
    if (isset($radio_name) == $v_post) {
        return '1';
    } else {
        return '0';
    }
}
function InsertAlgorithm($dbh, $bsl)
{
    if (! is_numeric($_POST['alg_offset'])) {
        return 'Offset in path is not numeric';
    }
    $intext = "'".$_POST['alg_field']."',";
    $intext .= $_POST['alg_offset'].',';
    $intext .= "'".AlgValueByPost($_POST['del_from_source'], 'del_from_source_Y')."',";
    $intext .= "'".$_POST['alg_beg_delim']."',";
    $intext .= $_POST['alg_beg_number'].',';
    $intext .= "'".AlgValueByPost($_POST['beg_inc'], 'beg_inc_Y')."',";
    $intext .= "'".AlgValueByPost($_POST['beg_scr'], 'beg_scr_Y')."',";
    $intext .= "'".$_POST['alg_inn_delim']."',";
    $intext .= "'".$_POST['alg_end_delim']."',";
    $intext .= $_POST['alg_end_number'].',';
    $intext .= "'".AlgValueByPost($_POST['end_inc'], 'end_inc_Y')."',";
    $intext .= "'".AlgValueByPost($_POST['end_scr'], 'end_scr_Y')."',";
    $intext .= "'".$_POST['alg_del_symbols']."',";
    $intext .= "'".$_POST['alg_ins_symbols']."',";
    $intext .= "'".AlgValueByPost($_POST['field_only'], 'field_only_Y')."',";
    $intext .= "'".GetCorrectDBValue($_POST['alg_reg_exp'], $bsl)."',";
    $intext .= "'".AlgValueByPost($_POST['reg_scr'], 'reg_scr_Y')."',";
    $intext .= "'".str_replace(chr(34), '', $_POST['remarks'])."'";
    mysqli_query($dbh, 'ALTER TABLE algorithms AUTO_INCREMENT = 1');
    mysqli_query($dbh, 'INSERT INTO algorithms (alg_field,alg_offset,del_from_source,beg_limit_set,beg_number,beg_inc,beg_scr,inner_limit_set,end_limit_set,end_number,end_inc,end_scr,del_symbols,ins_symbols,field_only,reg_expression,reg_scr,alg_remarks) VALUES ('.$intext.')');
    if (mysqli_errno($dbh) > 0) {
        return mysqli_error($dbh);
    } else {
        return '';
    }
}
function UpdateAlgorithm($dbh, $alg_code, $bsl)
{
    $uptext = "alg_field = '".$_POST['alg_field']."',";
    $uptext .= 'alg_offset = '.$_POST['alg_offset'].',';
    $uptext .= "del_from_source = '".AlgValueByPost($_POST['del_from_source'], 'del_from_source_Y')."',";
    $uptext .= "beg_limit_set = '".$_POST['alg_beg_delim']."',";
    $uptext .= 'beg_number = '.$_POST['alg_beg_number'].',';
    $uptext .= "beg_inc = '".AlgValueByPost($_POST['beg_inc'], 'beg_inc_Y')."',";
    $uptext .= "beg_scr = '".AlgValueByPost($_POST['beg_scr'], 'beg_scr_Y')."',";
    $uptext .= "inner_limit_set = '".$_POST['alg_inn_delim']."',";
    $uptext .= "end_limit_set = '".$_POST['alg_end_delim']."',";
    $uptext .= 'end_number = '.$_POST['alg_end_number'].',';
    $uptext .= "end_inc = '".AlgValueByPost($_POST['end_inc'], 'end_inc_Y')."',";
    $uptext .= "end_scr = '".AlgValueByPost($_POST['end_scr'], 'end_scr_Y')."',";
    $uptext .= "del_symbols = '".$_POST['alg_del_symbols']."',";
    $uptext .= "ins_symbols = '".$_POST['alg_ins_symbols']."',";
    $uptext .= "field_only = '".AlgValueByPost($_POST['field_only'], 'field_only_Y')."',";
    $uptext .= "reg_expression = '".GetCorrectDBValue($_POST['alg_reg_exp'], $bsl)."',";
    $uptext .= "reg_scr = '".AlgValueByPost($_POST['reg_scr'], 'reg_scr_Y')."',";
    $uptext .= "alg_remarks = '".str_replace(chr(34), '', $_POST['remarks'])."'";
    mysqli_query($dbh, 'UPDATE algorithms SET '.$uptext.' WHERE id_algorithm = '.$alg_code);
    if (mysqli_errno($dbh) > 0) {
        return mysqli_error($dbh);
    } else {
        return '';
    }
}
function AlgField($alg_copy, $alg_code, &$arr_v, $v_name, $str = true)
{
    if (count($alg_copy) == 0) {
        if ($alg_code == '') {
            return ($str) ? '' : 0;
        } elseif (isset($arr_v[$alg_code][$v_name])) {
            if ($v_name == 'remarks') {
                return FillAlgFields($arr_v[$alg_code][$v_name]);
            } else {
                return $arr_v[$alg_code][$v_name];
            }
        } else {
            return '';
        }
    } else {
        return $alg_copy[$v_name];
    }
}
function FillAlgFields($str)
{
    if (strpos($str, 'Publishing;') !== false) {
        return $str;
    }
    if (strpos($str, 'Series;') !== false) {
        return $str;
    }
    if (strpos($str, 'IssueYear;') !== false) {
        return $str;
    }
    if (strpos($str, 'IssueType;') !== false) {
        return $str;
    }
    if (strpos($str, 'Magazine;') !== false) {
        return $str;
    }
    if (strpos($str, 'Authors;') !== false) {
        return $str;
    }
    if (strpos($str, 'Themes;') !== false) {
        return $str;
    }

    return 'Publishing; Series; IssueYear; IssueType; Magazine; Authors; Themes;'.chr(13).chr(10).$str;
}
function SetAlgRadioCheck($arr_alg, $alg_code, $param, $check_mode)
{
    if ($alg_code == '') {
        return ($check_mode == 'N') ? ' checked' : '';
    }
    if ($arr_alg[$alg_code][$param] == $check_mode) {
        return ' checked';
    } else {
        return '';
    }
}
function AlgorithmCodes($dbh, $db_file)
{
    $alg_keys = '';
    $primary_file = str_replace(chr(92), chr(47), $db_file);
    $res = mysqli_query($dbh, "SELECT alg_numbers FROM primary_files WHERE path_and_file_low = '".MCV($primary_file, false)."' LIMIT 1");
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $alg_keys = $row[0];
        }
        mysqli_free_result($res);
    }

    return $alg_keys;
}
function PathOutPut($post_example, &$file_example)
{
    if ($post_example == '') {
        return [];
    }
    $file_example = str_replace(chr(39), chr(96), $post_example);
    $path_example = explode(chr(92), $file_example);

    return array_reverse($path_example);
}
function AddDelSymb($del_sy, $alg_del_symb, $arr_codes)
{
    $arr_code = explode(chr(9), $sel_symb);
    if ($alg_del_symb == '') {
        return $arr_code[1].' '.(string) $arr_code[0];
    } else {
        $in_arr = explode(' ', $alg_del_symb);
        $r_num = $in_arr[0].'|'.$arr_code[1];
        $r_let = $in_arr[1].'|'.(string) $arr_code[0];

        return $r_let.' '.$r_num;
    }
}
function AlgorithmOutput($dbh, $alg_id)
{
    $out_file = fopen($_SERVER['DOCUMENT_ROOT'].'/Algorithms/AlgorithmDetails.txt', 'a');
    if ($out_file) {
        $res = mysqli_query($dbh, 'SELECT * FROM algorithms WHERE id_algorithm = '.$alg_id);
        if ($res) {
            if ($row = mysqli_fetch_row($res)) {
                fwrite($out_file, 'ID'.chr(9).chr(9).chr(9).$alg_id."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, 'Reference'.chr(9).chr(9).$row[1]."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, 'Offset in path'.chr(9).chr(9).(string) $row[2].chr(9).'delete from source: '.GetCheck($row[3])."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, 'Begin delimiter'.chr(9).chr(9).chr(39).$row[4].chr(39).chr(9).'number in source: '.(string) $row[5].chr(9).'include into reference: '.GetCheck($row[6]).chr(9).'delete from source: '.GetCheck($row[7])."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, 'Inner delimiter'.chr(9).chr(9).chr(39).$row[8].chr(39)."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, 'End delimiter'.chr(9).chr(9).chr(39).$row[9].chr(39).chr(9).'number in source: '.(string) $row[10].chr(9).'include into reference: '.GetCheck($row[11]).chr(9).'delete from source: '.GetCheck($row[12])."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, 'Symbol replacement'.chr(9).chr(39).$row[13].chr(39).' >> '.chr(39).$row[14].chr(39).chr(9).'for reference only: '.GetCheck($row[15])."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, 'Regular expression'.chr(9).chr(39).$row[16].chr(39).chr(9).'delete from source: '.GetCheck($row[17])."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, 'Remarks'.chr(9).chr(9).chr(9).chr(39).$row[18].chr(39)."\r\n");
                fwrite($out_file, str_repeat('-', 32)."\r\n");
                fwrite($out_file, ' '."\r\n");
            }
            mysqli_free_result($res);
        }
        fclose($out_file);
    }
}
function AlgorithmErrors()
{
    $w_str = 'id_algorithm <= 0 OR ';
    $w_str .= 'del_from_source < 0 OR del_from_source > 1 OR ';
    $w_str .= 'beg_number < 0 OR ';
    $w_str .= 'beg_inc < 0 OR beg_inc > 1 OR ';
    $w_str .= 'beg_scr < 0 OR beg_scr > 1 OR ';
    $w_str .= 'end_number < beg_number OR ';
    $w_str .= 'end_inc < 0 OR end_inc > 1 OR ';
    $w_str .= 'end_scr < 0 OR end_scr > 1 OR ';
    $w_str .= 'field_only < 0 OR field_only > 1 OR ';
    $w_str .= 'reg_scr < 0 OR reg_scr > 1';

    return $w_str;
}
function GetCheck($d)
{
    return ($d == 'Y') ? 'Y' : 'N';
}
