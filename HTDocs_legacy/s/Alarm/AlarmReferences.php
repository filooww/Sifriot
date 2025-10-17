<?php

function QueryReferencesLocals()
{
    $str_req = 'SELECT id_coding, id_lang, letter FROM translate_table';
    $str_req .= ' WHERE ';
    $str_req .= '(SELECT coding_table.id_coding FROM coding_table WHERE coding_table.id_coding = translate_table.id_coding) IS NULL OR ';
    $str_req .= '(SELECT languages.id_language FROM languages WHERE languages.id_language = translate_table.id_lang) IS NULL OR ';
    $str_req .= 'translate_table.id_lang = 1';

    return $str_req.' ORDER BY id_lang,letter LIMIT '.(string) (count($_SESSION['coding_list']) * $_SESSION['number_warn'] + 1);
}
function QueryReferencesTitles()
{
    $str_req = 'SELECT id_language, id_title FROM interface_texts';
    $str_req .= ' WHERE (SELECT languages.id_language FROM languages WHERE languages.id_language = interface_texts.id_language) IS NULL OR interface_texts.id_language = 0';

    return $str_req.' ORDER BY id_language,id_title LIMIT '.(string) ($_SESSION['number_warn'] + 1);
}
function QueryReferencesUsers()
{
    $str_req = 'SELECT id_user, use_lang_id, preffered_db, user_priority FROM user_ident WHERE '.WhereReferenceUser();

    return $str_req.' ORDER BY preffered_db,id_user LIMIT '.(string) ($_SESSION['number_warn'] + 1);
}
function WhereReferenceUser()
{
    $w_req = '(SELECT languages.id_language FROM languages WHERE languages.id_language = user_ident.use_lang_id) IS NULL OR user_ident.use_lang_id = 0 OR ';
    $w_req .= 'preffered_db IS NOT NULL AND ';
    $w_req .= '((SELECT db_list.db_id FROM db_list WHERE db_list.db_id = user_ident.preffered_db LIMIT 1) IS NULL OR user_priority >= 0 AND user_priority < 11 AND preffered_db = 0)';

    return $w_req;
}
function QueryReferencesVisits()
{
    $str_req = 'SELECT * FROM ('.VisitQueryText().') AS T WHERE '.VisitWhereFilter();

    return $str_req.' ORDER BY T.id_db,T.id_user LIMIT '.(string) ($_SESSION['number_warn'] + 1);
}
function VisitQueryText()
{
    $q_text = 'SELECT id_db, id_user, ';
    $q_text .= 'CASE ';
    $q_text .= "WHEN (SELECT user_ident.user_priority FROM user_ident WHERE user_ident.id_user = visits.id_user LIMIT 1) IS NULL THEN '' ";
    $q_text .= 'ELSE (SELECT user_ident.user_priority FROM user_ident WHERE user_ident.id_user = visits.id_user LIMIT 1) ';
    $q_text .= 'END AS user_p ';
    $q_text .= 'FROM visits';

    return $q_text;
}
function VisitWhereFilter()
{
    $w_req = '(SELECT db_list.db_id FROM db_list WHERE db_list.db_id = T.id_db LIMIT 1) IS NULL OR ';
    $w_req .= "T.user_p <> '' AND (T.user_p > -1 AND T.user_p < 11 AND T.id_db = 0) OR ";
    $w_req .= "T.user_p = ''";

    return $w_req;
}
function SetReferenceTable($dbh, $table, $t_comm, $req_str, $arr_type, &$pre_ref)
{
    $pre_ref[$table]['t_comm'] = $t_comm;
    $res = mysqli_query($dbh, $req_str);
    SetInvalidRows($res, $table, $arr_type, $pre_ref);
    mysqli_free_result($res);
}
function SetInvalidRows($res, $table, $types, &$pre_ref)
{
    $pre_ref[$table]['over'] = false;
    $c = 0;
    while ($row = mysqli_fetch_row($res)) {
        foreach ($types as $type) {
            if (! isset($pre_ref[$table]['p'][$type[0]]['ts'])) {
                $pre_ref[$table]['p'][$type[0]]['ts'] = [$type[3], $type[4]];
            }
            $pre_key = (string) $row[$type[1]];
            $pre_value = (string) $row[$type[2]];
            if (IncludeInReferenceList($table, $type[0], $row) && RefUniq($pre_ref[$table]['p'][$type[0]], $pre_key, $pre_value)) {
                $c++;
                if ($c > $_SESSION['number_warn']) {
                    $pre_ref[$table]['over'] = true;

                    return;
                }
                $pre_ref[$table]['p'][$type[0]]['v'][$pre_key][] = $pre_value;
            }
        }
    }
}
function RefUniq($pre_arr, $pre_key, $pre_value)
{
    if (! isset($pre_arr['v'][$pre_key])) {
        return true;
    }
    if (! in_array($pre_value, $pre_arr['v'][$pre_key])) {
        return true;
    }

    return false;
}
function IncludeInReferenceList($table, $t_type, $row)
{
    if ($table == 'translate_table') {
        if ($t_type == 'cd') {
            return ! isset($_SESSION['coding_list'][$row[0]]);
        } else {
            return $row[1] == 1 || ! isset($_SESSION['user_langs'][$row[1]]);
        }
    }
    if ($table == 'user_ident') {
        if ($t_type == 'li') {
            return ! isset($_SESSION['user_langs'][$row[1]]);
        } else {
            return DBFilter($row[2], $row[3]);
        }
    }
    if ($table == 'visits') {
        if ($t_type == 'db') {
            return DBFilter($row[0], $row[2]);
        } else {
            return ! is_numeric($row[2]);
        }
    }

    return true;
}
function DBFilter($db_id, $priority)
{
    if (is_null($db_id)) {
        return false;
    }
    if (! array_key_exists($db_id, $_SESSION['arr_db'])) {
        return true;
    }
    if (! is_numeric($priority)) {
        return false;
    }
    if ((int) $priority > -1 && (int) $priority < 11) {
        return $db_id == 0;
    }

    return false;
}
function InvalidReferenceSum($pre_ref_table)
{
    $s = 0;
    foreach ($pre_ref_table['p'] as $k_type => $v_type) {
        foreach ($v_type['v'] as $k => $v) {
            $s += count($v);
        }
    }

    return $s;
}
function InvalidReferenceTable($dbh)
{
    $pre_ref = [];
    SetReferenceTable($dbh, 'translate_table', 125, QueryReferencesLocals(), [['cd', 0, 2, 281, -277], ['li', 1, 2, 542, -277]], $pre_ref);
    SetReferenceTable($dbh, 'interface_texts', 128, QueryReferencesTitles(), [['li', 0, 1, 542, 543]], $pre_ref);
    SetReferenceTable($dbh, 'user_ident', 12, QueryReferencesUsers(), [['li', 1, 0, 542, 544], ['db', 2, 0, 545, 544]], $pre_ref);
    SetReferenceTable($dbh, 'visits', 167, QueryReferencesVisits(), [['db', 0, 1, 545, 544], ['ui', 1, 0, 544, 545]], $pre_ref);
    ksort($pre_ref);

    return $pre_ref;
}
function IsInvalidTableReferences($k_table)
{
    if (isset($_SESSION['pre_ref'][$k_table]['p'])) {
        foreach ($_SESSION['pre_ref'][$k_table]['p'] as $v_type) {
            if (isset($v_type['v']) && count($v_type['v']) > 0) {
                return true;
            }
        }
    }

    return false;
}
function ChangeInvalidRefTable($dbh, $table, $ref_type, $ref_id, $code_id)
{
    $i = array_search((string) $code_id, $_SESSION['pre_ref'][$table]['p'][$ref_type]['v'][$ref_id]);
    if ($i !== false) {
        if (count($_SESSION['pre_ref'][$table]['p'][$ref_type]['v'][$ref_id]) == 1) {
            unset($_SESSION['pre_ref'][$table]['p'][$ref_type]['v'][$ref_id]);
        } else {
            unset($_SESSION['pre_ref'][$table]['p'][$ref_type]['v'][$ref_id][$i]);
        }
    }
}
function ChangeByInsertInvalidRefTable($table, $ref_type, $ref_id)
{
    unset($_SESSION['pre_ref'][$table]['p'][$ref_type]['v'][(string) $ref_id]);
    if (count($_SESSION['pre_ref']['user_ident']['p']['db']['v']) == 0) {
        unset($_SESSION['pre_ref']['user_ident']['p']['db']['v']);
        $_SESSION['pre_ref']['user_ident']['over'] = false;
    }
    if (isset($_SESSION['pre_ref'][$table]['p'])) {
        if (count($_SESSION['pre_ref'][$table]['p'][$ref_type]['v']) == 0) {
            unset($_SESSION['pre_ref'][$table]['p'][$ref_type]);
        }
        if (count($_SESSION['pre_ref'][$table]['p']) == 0) {
            unset($_SESSION['pre_ref'][$table]);
        }
    }
}
function IsInvalidReferences()
{
    if (! isset($_SESSION['pre_ref']) || count($_SESSION['pre_ref']) == 0) {
        return false;
    }
    foreach (array_keys($_SESSION['pre_ref']) as $k_table) {
        if (IsInvalidTableReferences($k_table)) {
            return true;
        }
    }

    return false;
}
function InvRefTable($table, $emph, $cor_ref_arr)
{
    echo "<table frame='border'>";
    foreach ($_SESSION['pre_ref'][$table]['p'] as $k_type => $v_type) {
        if (isset($v_type['v']) && count($v_type['v']) > 0) {
            $cap1 = ($v_type['ts'][1] < 0) ? FTM(Title(-$v_type['ts'][1])) : Title($v_type['ts'][1]);
            $cap2 = ($v_type['ts'][0] < 0) ? FTM(Title(-$v_type['ts'][0])) : Title($v_type['ts'][0]);
            echo "<tr class='table_color'>";
            echo '<td>'.$cap1.'</td>';
            echo '<td>'.$cap2.'</td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '</tr>';
            $ro = ($k_type == 'ui') ? '' : ' readonly';
            foreach ($v_type['v'] as $k => $v) {
                $vv = EmphSymb($v, $emph);
                echo "<tr valign='top'>";
                echo '<td><b>'.implode(' ', $vv).'</b></td>';
                echo '<td><b>'.$k.'</b></td>';
                echo "<td><button name='replace_id|".$k_type.'|'.$k."' type='submit' value='*'>".FTM(Title(669)).'</button></td>';
                echo '<td><b>'.$cap2.'</b> '.Title(670).' '.
                "<input name='replacing_id|".$k_type.'|'.$k."' size='2'".$ro." type='text' class='data_num' value='".
                $_SESSION['repl_id'][$k_type][$k]."'></td>";
                if (count($cor_ref_arr[$k_type]) == 0) {
                    echo '<td></td>';
                } else {
                    echo '<td>';
                    SelectTag('replacing_value|'.$k_type.'|'.$k, $cor_ref_arr[$k_type], $_SESSION['repl_id'][$k_type][$k], '', true, '', 'replace_on(this)', false, false);
                    echo '</td>';
                }
                echo '</tr>';
            }
        }
    }
    if ($_SESSION['pre_ref'][$table]['over']) {
        echo "<tr valign='top'><td><font color='#0000FF'><i>".Title(359).' ... </i></font></td><td></td><td></td><td></td><td></td></tr>';
    }
    echo '</table>';
    if (isset($_SESSION['replace_mes']) && count($_SESSION['replace_mes']) > 0) {
        PrintReplaceMes();
    }
    echo "<hr align='left' size='1' noshade='noshade' color='#000000' >";
}
function ReplaceRefID($dbh, $table, $ref_field, $k_type, $old_ref_id, $new_ref_id)
{
    if ($old_ref_id == $new_ref_id) {
        $_SESSION['replace_mes'] = ['#FF0000', 244];
    } else {
        mysqli_query($dbh, 'UPDATE '.$table.' SET '.$ref_field.' = '.$new_ref_id.' WHERE '.$ref_field.' = '.$old_ref_id);
        if (mysqli_errno($dbh) == 1062) {
            return false;
        }
        unset($_SESSION['pre_ref'][$table]['p'][$k_type]['v'][(int) $old_ref_id]);
        $_SESSION['replace_mes'] = ['#0000FF', 602, ' ', $_SESSION['pre_ref'][$table]['p'][$k_type]['ts'][0], ' <b>'.$old_ref_id.'</b> ', 469, ' <b>'.$new_ref_id.'</b>'];
    }

    return true;
}
function GetInvRef($table, $cor_ref_arr)
{
    $_SESSION['inv_ref'] = ! $_SESSION['inv_ref'];
    if ($_SESSION['inv_ref'] && isset($_SESSION['pre_ref'][$table]['p'])) {
        SetReplacedID($table, $cor_ref_arr);
    } else {
        $_SESSION['replacing_id'] = [];
        unset($_SESSION['replace_mes']);
    }
}
function SetReplacedID($table, $cor_ref_arr)
{
    foreach ($_SESSION['pre_ref'][$table]['p'] as $k_type => $v_type) {
        if (isset($v_type['v'])) {
            foreach (array_keys($v_type['v']) as $k) {
                if (isset($cor_ref_arr[$k_type])) {
                    reset($cor_ref_arr[$k_type]);
                    $_SESSION['replacing_id'][$k_type][$k] = (string) key($cor_ref_arr[$k_type]);
                }
            }
        }
    }
}
function AfterReplaceSelect($select_arr, &$sw_break)
{
    if ($_POST['replace_s'] == '') {
        $sw_break = false;

        return false;
    }
    $k = explode('|', $_POST['replace_s']);
    $r_id = array_search($_POST['replacing_value|'.$k[1].'|'.$k[2]], $select_arr[$k[1]]);
    if ($r_id === false) {
        return false;
    }
    $_SESSION['repl_id'][$k[1]][$k[2]] = (string) $r_id;

    return true;
}
function PermitInvRef($table, $type_arr)
{
    if (! isset($_SESSION['pre_ref'][$table]['p'])) {
        return false;
    }
    foreach ($_SESSION['pre_ref'][$table]['p'] as $k_type => $v_type) {
        if (isset($v_type['v']) && count($v_type['v']) > 0) {
            return true;
        }
    }
    foreach ($type_arr as $type) {
        if (isset($_SESSION['pre_ref'][$table]['p'][$type]['v']) && count($_SESSION['pre_ref'][$table]['p'][$type]['v']) > 0) {
            return true;
        }
    }

    return false;
}
function SetInitReplaceIDs($table, $id_types)
{
    $_SESSION['repl_id'] = [];
    foreach ($id_types as $type_k => $arr_name) {
        if (isset($_SESSION['pre_ref'][$table]['p'][$type_k]['v'])) {
            $first_k = ($arr_name == '') ? '' : GetFirstKey($_SESSION[$arr_name]);
            foreach (array_keys($_SESSION['pre_ref'][$table]['p'][$type_k]['v']) as $k) {
                $_SESSION['repl_id'][$type_k][$k] = (string) $first_k;
            }
        }
    }
}
function PrintReplaceMes()
{
    $str_print = '';
    if ($_SESSION['replace_mes'][0] != '') {
        $str_print .= "<font color='".$_SESSION['replace_mes'][0]."'>";
    }
    for ($i = 1; $i < count($_SESSION['replace_mes']); $i++) {
        if ($i % 2 == 0) {
            $str_print .= $_SESSION['replace_mes'][$i];
        } else {
            if ($_SESSION['replace_mes'][$i] != 0) {
                if ($_SESSION['replace_mes'][$i] < 0) {
                    $str_print .= FTM(Title(-$_SESSION['replace_mes'][$i]));
                } else {
                    $str_print .= Title($_SESSION['replace_mes'][$i]);
                }
            }
        }
    }
    if ($_SESSION['replace_mes'][0] != '') {
        $str_print .= '</font>';
    }
    if ($str_print != '') {
        echo "<table frame='border' bgcolor='#CCFFFF'>";
        echo "<tr valign='top'>";
        echo '<td>'.$str_print.'</td>';
        echo "<td><button name='reset_replace_mes' type='submit' title='".Title(170)."' value='*'>...</button></td>";
        echo '</tr>';
        echo '</table>';
    }
}
