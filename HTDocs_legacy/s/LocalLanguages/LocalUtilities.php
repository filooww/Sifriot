<?php

function SetTransCodes($dbh, $id_lang)
{
    if (gettype($id_lang) == 'array') {
        $res = mysqli_query($dbh, 'SELECT * FROM translate_table WHERE letter IN ('.implode(',', $id_lang).') ORDER BY letter');
    } else {
        $res = mysqli_query($dbh, 'SELECT * FROM translate_table WHERE id_coding = '.(string) $_SESSION['sel_coding'][0].' AND id_lang = '.(string) $id_lang.' ORDER BY letter');
    }
    if ($res) {
        return FillTransParam($res);
    } else {
        return [];
    }
}
function FillTransParam($res)
{
    $arr = [];
    while ($row = mysqli_fetch_row($res)) {
        $arr_err = [];
        if ($row[2] == '') {
            $arr_err[] = "<font color='#FF0000'>".FTM(Title(277)).' '.Title(573).'</font>';
        }
        if (isset($_SESSION['local_langs'][$row[1]])) {
            if ($row[1] > 1 && $row[3] == '') {
                $arr_err[] = "<font color='#FF0000'>".FTM(Title(285)).' '.Title(573).'</font>';
            }
            if ($row[1] == 0 && $row[3] != '') {
                $arr_err[] = "<font color='#FF0000'>".Title(659).'</font>';
            }
        } else {
            $arr_err[] = "<font color='#FF0000'>".Title(602).' '.Title(542).' (<b>'.(string) $row[1].'</b>)</font>';
        }
        if (gettype($_SESSION['sel_local_lang'][0]) == 'string' && ! isset($_SESSION['coding_list'][$row[0]])) {
            $arr_err[] = "<font color='#FF0000'>".Title(331).' (<b>'.(string) $row[0].'</b>)</font>';
        }
        if (TransliteratonSetError($row[3])) {
            $arr_err[] = "<font color='#FF0000'>".FTM(Title(285)).' '.Title(657).', '.Title(666)."</font> <b><font class='del_symb'>|</font></b>";
        }
        $arr_sym = [ord(substr($row[2], 0)), ord(substr($row[2], 1)), ord(substr($row[2], 2))];
        $sym_code = GetSymbolCode($row[2]);
        if (gettype($_SESSION['sel_local_lang'][0]) == 'integer') {
            $arr[$row[2]] = ['lang_code' => $row[1], 'b_0' => $arr_sym[0], 'b_1' => $arr_sym[1], 'b_2' => $arr_sym[2], 'translit_set' => $row[3], 'new' => false, 'err' => $arr_err];
        } else {
            $arr[$row[2]] = ['id_code' => $row[0], 'lang_code' => $row[1], 'b_0' => $arr_sym[0], 'b_1' => $arr_sym[1], 'b_2' => $arr_sym[2], 'translit_set' => $row[3], 'new' => false, 'err' => $arr_err];
        }
    }
    mysqli_free_result($res);

    return $arr;
}
function ExitLocals($dbh, &$Mes)
{
    $fl = TestAllLocalCodes($Mes);
    RewriteLocalCodes($dbh, $Mes);
    if (! $fl && ! $_SESSION['alarm']) {
        return '';
    }
    if (isset($_SESSION['change_lang_letter'])) {
        unset($_SESSION['change_lang_letter']);
    }
    if ($_SESSION['alarm']) {
        if (! $_SESSION['pre_ref']['translate_table']['over'] && isset($_SESSION['pre_ref']['translate_table']['p']['li']['v']) && count($_SESSION['pre_ref']['translate_table']['p']['li']['v']) == 0) {
            unset($_SESSION['pre_ref']['translate_table']['p']['li']['v']);
        }
        TranslateTableCheck($dbh);

        return '../Alarm/CommonAlarmForm';
    } else {
        return '../Administrator/MainForm';
    }
}
function StringTranslit($s_str)
{
    $d_str = $s_str;
    foreach ($_SESSION['translit_param'] as $k => $v) {
        $arr_set = explode('|', $v[1]);
        foreach ($arr_set as $z) {
            if ($z != '') {
                $d_str = str_replace($z, chr((int) $k), $d_str);
            }
        }
    }

    return $d_str;
}
function ViewLocalLanguage($k, $v, $bg_class)
{
    echo "<tr valign='top' class='".$bg_class."'>";
    if ($_SESSION['user_working_mode'] == 1) {
        $fl_del = (in_array($k, $_SESSION['del_local']) !== false);
        echo "<td><button name='local_mark_delete|".$k."'".(($_SESSION['local_delete']) ? ' disabled' : '')." title='".Title(358)."' type='submit' class='button_class' value='*'>".SysImage((($fl_del) ? 'CheckBorder' : 'BlankBorder'), 19, 16, $_SESSION['local_delete']).'</button></td>';
    }
    echo "<td align='center' ><b>".$k."</b></td><td align='center'><b>".$v['b_0']."</b></td><td align='center'><b>".$v['b_1']."</b></td><td align='center'><b>".$v['b_2'].'</b></td>';
    echo '<td><input '.(($_SESSION['user_working_mode'] == 0 || $v['lang_code'] == 0 && $v['translit_set'] == '' || $_SESSION['local_delete']) ? ' disabled' : '')." size='31' type='text' name='translit_set|".$k."' value='".$v['translit_set']."'></td>";
    if (gettype($_SESSION['sel_local_lang'][0]) == 'string') {
        echo '<td>';
        SelectTag('change_local_coding|'.$k, $_SESSION['coding_list'], $_SESSION['sel_coding_for_change'], '', true, '', '', $_SESSION['local_delete'], false);
        echo '</td>';
    }
    echo '<td>';
    SelectTag('change_local_lang|'.$k, $_SESSION['local_langs'], $_SESSION['sel_lang_for_change'], '', true, '', '', $_SESSION['local_delete'], false);
    echo '</td>';
    echo "<td><button name='change_local_ids|".$k."'".(($_SESSION['user_working_mode'] == 0 || $_SESSION['local_delete']) ? ' disabled' : '')." title='".FTM(Title(669)).' '.Title(542).' '.Title(82).'/'.Title(567).' '.Title(281)."' type='submit' class='button_class' value='*'><<</button></td>";
    if (count($v['err']) == 0) {
        echo '<td></td>';
    } else {
        echo '<td>'.implode('; ', $v['err']).'</td>';
    }
}
function PostToTransliterations()
{
    if (! isset($_POST['local_save'])) {
        foreach ($_SESSION['trans_codes'] as $k => $v) {
            if (isset($_POST['translit_set|'.$k]) && $v['translit_set'] != $_POST['translit_set|'.$k]) {
                $_SESSION['trans_codes'][$k]['translit_set'] = $_POST['translit_set|'.$k];
            }
        }
    }
    if (isset($_POST['new_symbol'])) {
        $_SESSION['new_symb_param'][0] = $_POST['new_symbol'];
    }
}
function TransliteratonSetError($str)
{
    for ($i = 0; $i < mb_strlen($str, 'utf-8'); $i++) {
        $sym = mb_substr($str, $i, 1, 'utf-8');
        if (mb_strlen($sym, 'utf-8') > 1) {
            return true;
        }
    }

    return false;
}
function MarkLocalDelete($k)
{
    $i = array_search($k, $_SESSION['del_local']);
    if ($i === false) {
        $_SESSION['del_local'][] = $k;
    } else {
        unset($_SESSION['del_local'][$i]);
    }
    if (isset($_SESSION['change_lang_letter'])) {
        unset($_SESSION['change_lang_letter']);
    }
    $_SESSION['new_symb_param'] = ['', ''];
}
function ChangeLocalLangs()
{
    $arr = $_SESSION['local_langs'];
    if (PermitInvRef('translate_table', ['cd', 'li'])) {
        $arr['nu'] = '-- '.FTM(Title(602)).' '.Title(542).' '.Title(82).'/'.Title(567).' '.Title(281).' --';
    }

    return $arr;
}
function ReplaceLocalIDs($dbh, $k_type, $k)
{
    if ($k_type == 'cd') {
        $fl = ReplaceRefID($dbh, 'translate_table', 'id_coding', $k_type, $k, $_SESSION['repl_id'][$k_type][$k]);
    } else {
        $fl = ReplaceRefID($dbh, 'translate_table', 'id_lang', $k_type, $k, $_SESSION['repl_id'][$k_type][$k]);
    }
    if ($fl) {
        if (isset($_SESSION['local_langs_for_page']['nu']) && $_POST['sel_local_lang'] == $_SESSION['local_langs_for_page']['nu']) {
            $invalid_letters = SetInvalidRefs();
            if (count($invalid_letters) > 0) {
                $_SESSION['trans_codes'] = SetTransCodes($dbh, $invalid_letters);
            }
        } else {
            $_SESSION['trans_codes'] = SetTransCodes($dbh, $_SESSION['sel_local_lang'][0]);
            $_SESSION['sel_coding_for_change'] = $_SESSION['sel_coding'][0];
        }
        if ($_SESSION['pre_ref']['translate_table']['over']) {
            unset($_SESSION['pre_ref']['translate_table']);
            SetReferenceTable($dbh, 'translate_table', 125, QueryReferencesLocals(), [['cd', 0, 2, 281, -277], ['li', 1, 2, 542, -277]], $_SESSION['pre_ref']);
            ksort($_SESSION['pre_ref']);
        }
        TestAllLocalCodes($Mes);
    }
    SetInitReplaceIDs('translate_table', ['cd' => 'coding_list', 'li' => 'local_langs']);
}
