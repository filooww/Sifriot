<?php

function UpdateOnAlgorithm($dbh, &$PR, $arr_alg_seq, $d_file)
{
    $l_f = fopen('D:\\Test.txt', 'a');
    fwrite($l_f, "\r\ntest UpdateOnAlgorithm 00 ***".$d_file."===\r\n");
    fclose($l_f);
    ArrayToFile('D:\\Test.txt', '', $arr_alg_seq, 0);
    $arr_group = [];
    foreach ($arr_alg_seq as $v) {
        $l_f = fopen('D:\\Test.txt', 'a');
        fwrite($l_f, "\r\ntest UpdateOnAlgorithm 01 ***\r\n");
        fclose($l_f);
        ArrayToFile('D:\\Test.txt', '', $v, 0);
        $l_f = fopen('D:\\Test.txt', 'a');
        fwrite($l_f, "\r\ntest UpdateOnAlgorithm 02 ***\r\n");
        fclose($l_f);
        ArrayToFile('D:\\Test.txt', '', $main_params, 0);
        $k_alg = FindAlgInFV($v, $PR['var']);
        $l_f = fopen('D:\\Test.txt', 'a');
        fwrite($l_f, "\r\ntest UpdateOnAlgorithm 03 ***".$k_alg."===\r\n");
        fclose($l_f);
        ArrayToFile('D:\\Test.txt', '', $arr_group, 0);
        if ($k_alg != '' && ! in_array($k_alg, $arr_group)) {
            $arr_group[$k_alg] = [];
        }
    }
    $l_f = fopen('D:\\Test.txt', 'a');
    fwrite($l_f, "\r\ntest UpdateOnAlgorithm 04 ***\r\n");
    fclose($l_f);
    ArrayToFile('D:\\Test.txt', '', $arr_group, 0);
    $file_arr = array_reverse(explode(chr(47), $d_file));
    $l_f = fopen('D:\\Test.txt', 'a');
    fwrite($l_f, "\r\ntest UpdateOnAlgorithm 05 ***\r\n");
    fclose($l_f);
    ArrayToFile('D:\\Test.txt', '', $file_arr, 0);
    $file_arr[0] = str_replace('.'.pathinfo($file_arr[0], PATHINFO_EXTENSION), '', $file_arr[0]);
    $l_f = fopen('D:\\Test.txt', 'a');
    fwrite($l_f, "\r\ntest UpdateOnAlgorithm 06 ***\r\n");
    fclose($l_f);
    ArrayToFile('D:\\Test.txt', '', $file_arr, 0);
    foreach ($arr_alg_seq as $v) {
        $p_f_arr = GetFieldToPub($v, $file_arr[abs($v['offset'])]);
        $l_f = fopen('D:\\Test.txt', 'a');
        fwrite($l_f, "\r\ntest UpdateOnAlgorithm 07 ***".$file_arr[abs($v['offset'])]."===\r\n");
        fclose($l_f);
        ArrayToFile('D:\\Test.txt', '', $p_f_arr, 0);
        foreach ($p_f_arr as $z) {
            if (! in_array($z, $arr_group[$v['field']])) {
                $arr_group[$v['field']][] = $z;
            }
        }
    }
    $l_f = fopen('D:\\Test.txt', 'a');
    fwrite($l_f, "\r\ntest UpdateOnAlgorithm 08 ***\r\n");
    fclose($l_f);
    ArrayToFile('D:\\Test.txt', '', $arr_group, 0);
    foreach ($arr_group as $k => $v) {
        $l_f = fopen('D:\\Test.txt', 'a');
        fwrite($l_f, "\r\ntest UpdateOnAlgorithm 09 ***".$k."===\r\n");
        fclose($l_f);
        ArrayToFile('D:\\Test.txt', '', $v, 0);
        $l_f = fopen('D:\\Test.txt', 'a');
        fwrite($l_f, "\r\ntest UpdateOnAlgorithm 10 ***".TestBool(isset($main_params['const'][$k]['table'])).'===');
        fclose($l_f);
        if ($main_params['const'][$k]['table'] != '') {
            $l_f = fopen('D:\\Test.txt', 'a');
            fwrite($l_f, "\r\ntest UpdateOnAlgorithm 11 ***".TestBool(isset($main_params['const'][$k]['table1'])).'===');
            fclose($l_f);
            if ($main_params['const'][$k]['table1'] != '') {
                $ref_codes = [];
                foreach ($v as $z) {
                    $ref_codes[] = GetRefCode($dbh, $_SESSION['main_params']['const'][1][$k]['table1'], $_SESSION['main_params']['const'][1][$k]['id1'], $_SESSION['main_params']['const'][1][$k]['v1'], ltrim(rtrim($z)));
                }
                $l_f = fopen('D:\\Test.txt', 'a');
                fwrite($l_f, "\r\ntest UpdateOnAlgorithm 12 ***\r\n");
                fclose($l_f);
                ArrayToFile('D:\\Test.txt', '', $ref_codes, 0);
                $main_params['var'][$k]['code'] = GetRefCode($dbh, $_SESSION['main_params']['const'][1][$k]['table'], $_SESSION['main_params']['const'][1][$k]['c_id'], $_SESSION['main_params']['const'][1][$k]['c_v'], implode(',', $ref_codes));
                $l_f = fopen('D:\\Test.txt', 'a');
                fwrite($l_f, "\r\ntest UpdateOnAlgorithm 13 ***".$main_params['var'][$k]['code'].'===');
                fclose($l_f);
                if ($main_params['var'][$k]['code'] != '') {
                    SaveMaxLevel($dbh, count($ref_codes));
                }
            } else {
                $main_params['var'][$k]['code'] = GetRefCode($dbh, $_SESSION['main_params']['const'][1][$k]['table'], $_SESSION['main_params']['const'][1][$k]['c_id'], $_SESSION['main_params']['const'][1][$k]['c_v'], ltrim(rtrim($v[0])));
                $l_f = fopen('D:\\Test.txt', 'a');
                fwrite($l_f, "\r\ntest UpdateOnAlgorithm 14 ***".$main_params['var'][$k]['code'].'===');
                fclose($l_f);
            }
        } else {
            $main_params['var'][$k]['code'] = ltrim(rtrim($v[0]));
            $l_f = fopen('D:\\Test.txt', 'a');
            fwrite($l_f, "\r\ntest UpdateOnAlgorithm 15 ***".$main_params['var'][$k]['code'].'===');
            fclose($l_f);
        }
    }
    if ($main_params['var']['Title']['code'] == '') {
        $main_params['var']['Title']['code'] = $file_arr[0];
    }
    $l_f = fopen('D:\\Test.txt', 'a');
    fwrite($l_f, "\r\ntest UpdateOnAlgorithm 16 ***".$main_params['var']['Title']['code'].'===');
    fclose($l_f);
}
function GetRefCode($dbh, $table, $id_f, $v_f, $p_f)
{
    if ($p_f == '') {
        return '';
    }
    $f_low = MCF($v_f, $_SESSION['match_case']);
    $v_low = MCV($p_f, $_SESSION['match_case']);
    mysqli_query($dbh, 'ALTER TABLE '.$table.' AUTO_INCREMENT = 1');
    $res = mysqli_query($dbh, 'SELECT '.$id_f.' FROM '.$table.' WHERE '.$f_low." = '".$v_low."' LIMIT 1");
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $cr = (string) $row[0];
        } else {
            $cr = '';
        }
        mysqli_free_result($res);
        if ($cr == '') {
            mysqli_query($dbh, 'INSERT INTO '.$table.' ('.$v_f.', '.$v_f."_low) VALUES ('".$p_f."','".$v_low."')");
            if (mysqli_errno($dbh) == 0) {
                return GetAutoIncrement($dbh);
            } else {
                return '';
            }
        } else {
            return $cr;
        }
    } else {
        mysqli_query($dbh, 'INSERT INTO '.$table.' ('.$v_f.', '.$v_f."_low) VALUES ('".$p_f."', '".$v_low."')");
        if (mysqli_errno($dbh) == 0) {
            return GetAutoIncrement($dbh);
        } else {
            return '';
        }
    }
}
function SymbRepl($del_sym, $ins_sym, &$repl_field)
{
    $del_arr = ($del_sym == '') ? [] : explode('|', $del_sym);
    $ins_arr = ($ins_sym == '') ? [] : explode('|', $ins_sym);
    for ($i = 0; $i < count($del_arr); $i++) {
        if ($i < count($ins_arr)) {
            $repl_field = str_replace($del_arr[$i], $ins_arr[$i], $repl_field);
        } else {
            $repl_field = str_replace($del_arr[$i], '', $repl_field);
        }
    }
}
function GetFieldToPub($v, &$source_field)
{
    if ($v['reg_exp'] != '') {
        $selit = RegExpProc($v['reg_exp'], $v['reg_scr'], $source_field);
    } //    /\d{4}/ year
    else {
        $selit = GetFieldPart($v, $source_field);
    }
    if ($v['inn_del'] == '') {
        $selit_arr = [$selit];
    } else {
        $selit_arr = explode($v['inn_del'], $selit);
    }
    if ($v['del_sym'] != '' && ! $v['field_only']) {
        SymbRepl($v['del_sym'], $v['ins_sym'], $source_field);
    }

    return $selit_arr;
}
function GetFieldPart($v, &$source_field)
{
    $beg_del_arr = ($v['beg_del'] == '') ? [] : explode('|', $v['beg_del']);
    $end_del_arr = ($v['end_del'] == '') ? [] : explode('|', $v['end_del']);
    $pos_beg = GetPos($beg_del_arr, $source_field, $v['beg_num']);
    $pos_end = GetPos($end_del_arr, $source_field, $v['end_num'], $pos_beg['pos']);
    if (count($beg_del_arr) > 0 && $pos_beg['pos'] < 0 || count($end_del_arr) > 0 && $pos_end['pos'] < 0) {
        $selit = '';
    } else {
        $pos1 = ($pos_beg['delim'] == '') ? 0 : $pos_beg['pos'] + (($v['beg_inc']) ? 0 : strlen($pos_beg['delim']));
        $pos2 = ($pos_end['delim'] == '') ? strlen($source_field) : $pos_end['pos'] + (($v['end_inc']) ? strlen($pos_end['delim']) : 0);
        $selit = substr($source_field, $pos1, $pos2 - $pos1);
        if ($v['del_sym'] != '' && $v['field_only']) {
            SymbRepl($v['del_sym'], $v['ins_sym'], $selit);
        }
        if ($v['end_scr'] && $pos_end['delim'] != '') {
            $source_field = substr($source_field, 0, $pos_end['pos']).substr($source_field, $pos_end['pos'] + strlen($pos_end['delim']));
        }
        if ($v['del_from_source']) {
            $source_field = str_replace($selit, '', $source_field);
        }
        if ($v['beg_scr'] && $pos_beg['delim'] != '') {
            $source_field = substr($source_field, 0, $pos_beg['pos']).substr($source_field, $pos_beg['pos'] + strlen($pos_beg['delim']));
        }
    }

    return $selit;
}
function PosSearchIndex($pos_arr, $init_pos, $delim_searched)
{
    if (count($pos_arr) == 0) {
        return ['pos' => -1, 'delim' => ''];
    }
    foreach ($pos_arr as $z) {
        if ($z > $init_pos) {
            return ['pos' => $z, 'delim' => $delim_searched];
        }
    }

    return ['pos' => -1, 'delim' => ''];
}
function GetMinPos($arr_pos_struct)
{
    $res_struct = ['pos' => -1, 'delim' => ''];
    foreach ($arr_pos_struct as $z) {
        if ($res_struct['pos'] == -1 || $res_struct['pos'] >= 0 && $z['pos'] < $res_struct['pos']) {
            $res_struct['pos'] = $z['pos'];
            $res_struct['delim'] = $z['delim'];
        }
    }

    return $res_struct;
}
function GetPos($delim_arr, $str, $delim_number = 0, $init_pos = -2)
{
    $arr_pos_struct = [];
    foreach ($delim_arr as $z) {
        if ($z != '') {
            if ($delim_number > 0) {
                $pos_arr = AllPosition($str, $z);
                if ($delim_number <= count($pos_arr)) {
                    $arr_pos_struct[] = ['pos' => $pos_arr[$delim_number - 1], 'delim' => $z];
                }
            }
        }
    }

    return GetMinPos($arr_pos_struct);
}
function FindAlgInFV($v_alg, $p_var)
{
    foreach ($p_var as $k => $v) {
        if ($k == $v_alg['field'] && $v['code'] == '') {
            return $k;
        }
    }

    return '';
}
function RegExpProc($reg, $regscr, &$source_field)
{
    $matches = [];
    $p = preg_match_all($reg, $source_field, $matches, PREG_OFFSET_CAPTURE);
    $res_reg = '';
    if ($p > 0) {
        $res_reg = $matches[0][$p - 1][0];
        if ($regscr == 1) {
            $source_field = str_replace($res_reg, '', $source_field);
        }
    }

    return $res_reg;
}
