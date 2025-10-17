<?php

function SetDoubleLangParameters($v)
{
    for ($i = 0; $i < count($v); $i++) {
        $arr_v = [];
        for ($j = 0; $j < count($v); $j++) {
            if ($j != $i) {
                $arr_v[] = $v[$j];
            }
        }
        if ($_SESSION['lang_param'][$v[$i]][2] == '') {
            $_SESSION['lang_param'][$v[$i]][2] = "<font color='#FF0000'>".Title(538).' '.FTM(Title(181)).' '.((count($arr_v) == 1) ? Title(151) : Title(152)).' <b>'.implode(', ', $arr_v).'</b></font>';
        } else {
            $_SESSION['lang_param'][$v[$i]][2] .= "; <font color='#FF0000'>".Title(538).' '.FTM(Title(181)).' '.((count($arr_v) == 1) ? Title(151) : Title(152)).' <b>'.implode(', ', $arr_v).'</b></font>';
        }
    }
}
function TestLangList($dbh, $init = false)
{
    $_SESSION['del_langs'] = [];
    foreach ($_SESSION['lang_param'] as $k => $v) {
        TestLangRow($dbh, $k, $v[0], $init);
    }
    if (! $init) {
        $dd = TestLanguageDouble();
        if (count($dd) > 0) {
            foreach ($dd as $v) {
                SetDoubleLangParameters($v);
            }
        }
    }
}
function TestLangRow($dbh, $k, $v0, $init)
{
    $arr_mark = [];
    $_SESSION['lang_param'][$k][3] = false;
    if ((string) $k == '1' && $v0 == 'English') {
        $arr_mark[] = "<font color='#0000FF'><b>".Title(270).'</b></font>';
    }
    if ($v0 == '' && gettype($k) == 'integer') {
        TestEmptyLanguage($dbh, $k, $init, $arr_mark);
    }
    if ($v0 != '' && strpos($v0, chr(39)) !== false) {
        $_SESSION['lang_param'][$k][0] = str_replace(chr(39), $_SESSION['apostrophe_replace'], $v0);
        $_SESSION['lang_param'][$k][1] = $_SESSION['lang_param'][$k][0];
        $arr_mark[] = "<font color='#0000FF'>".Title(120).' '.Title(469).' '.FTM(Title(277))."<font><font color='#990000' size='+2'> <b>".$_SESSION['apostrophe_replace'].'</b></font>';
        $_SESSION['lang_param'][$k][3] = true;
    }
    if (isset($_SESSION['pre_lang_err'][$k])) {
        $arr_mark[] = "<font color='#0000FF'>".DigitsToTitle($_SESSION['pre_lang_err'][$k]).'</font>';
    }
    if (count($arr_mark) > 0) {
        $_SESSION['lang_param'][$k][2] = implode('; ', $arr_mark);
    } else {
        $_SESSION['lang_param'][$k][2] = '';
    }
}
function TestEmptyLanguage($dbh, $k, $init, &$arr_mark)
{
    if ($init) {
        $arr_mark[] = "<font color='#FF0000'>".FTM(Title(181)).' '.Title(573).'</font>';
        $_SESSION['lang_param'][$k][3] = true;
    } else {
        $lang_refs = LanguageReferencesForDelete($dbh, $k);
        if (count($lang_refs) > 0) {
            $arr_mark[] = InvalidLangDelMessage($k, $lang_refs);
            $_SESSION['lang_param'][$k][3] = true;
        } else {
            $arr_mark[] = "<font color='#0000FF'>".Title(581).'</font>';
            $_SESSION['del_langs'][] = (string) $k;
        }
    }
}
function InvalidLangDelMessage($k, $lang_refs)
{
    $str = "<font color='#FF0000'>".Title(204).' '.FTM(Title(181)).' <b>'.$_SESSION['lang_param'][$k][0].'</b>';
    $str .= ', '.FTM(Title(305)).' '.((DelMesMult($lang_refs)) ? FTM(Title(185)) : FTM(Title(183))).' '.((count($lang_refs) == 1) ? Title(184) : Title(186)).' '.DelMesList($lang_refs).'</font>. ';
    $str .= " <font color='#0000FF'><b>".Title(187).'</b></font>';
    if ($_SESSION['lang_param'][$k][0] != $_SESSION['lang_param'][$k][1]) {
        $str .= " <button name='lang_restore|".(string) $k."' type='submit'>".FTM(Title(224)).' '.Title(490).'</button>';
    }

    return $str;
}
function RewriteLanguages($dbh, &$Mes)
{
    $fl_change = false;
    foreach ($_SESSION['lang_param'] as $k => $v) {
        if ($v[0] != '' && ! $v[2]) {
            if (gettype($k) == 'string' && $v[0] != '') {
                InsertNewLanguage($dbh, $v[0], $fl_change);
            } elseif ((int) $k > 1 && $v[0] != $v[1]) {
                UpdateLanguage($dbh, $k, $v[0], $fl_change);
            }
        }
    }
    if ($fl_change) {
        $Mes[] = '<b>'.Title(182).'</b>';
        TestLangList($dbh);
        RestoreUserLanguageList();
    }
}
function InsertNewLanguage($dbh, $v0, &$fl_change)
{
    $new_lang_id = NewTableID($_SESSION['lang_param'], 1);
    $_SESSION['lang_param'][$new_lang_id] = [$v0, $v0, '', false];
    unset($_SESSION['lang_param']['new']);
    ksort($_SESSION['lang_param']);
    $_SESSION['lang_param']['new'] = ['', '', '', false];
    mysqli_query($dbh, 'INSERT INTO languages VALUES ('.(string) $new_lang_id.",'".VValue($v0)."')");
    $fl_change = true;
    $_SESSION['ref_add'] = true;
}
function UpdateLanguage($dbh, $k, $v0, &$fl_change)
{
    mysqli_query($dbh, "UPDATE languages SET language = '".VValue($v0)."' WHERE id_language = ".(string) $k);
    if ($k == $_SESSION['user_lang'][0]) {
        $_SESSION['user_lang'][1] = $_SESSION['lang_param'][$k][0];
    }
    $fl_change = true;
}
function LangDel($dbh)
{
    if (isset($_SESSION['pre_lang_err'])) {
        unset($_SESSION['pre_lang_err']);
    }
    mysqli_query($dbh, 'DELETE FROM languages WHERE id_language IN ('.implode(',', $_SESSION['del_langs']).')');
    mysqli_query($dbh, 'ALTER TABLE languages AUTO_INCREMENT = 1');
    foreach ($_SESSION['del_langs'] as $z) {
        unset($_SESSION['lang_param'][$z]);
    }
}
function LangSetQuestionString()
{
    $arr = [];
    foreach ($_SESSION['del_langs'] as $k_del) {
        $arr[] = $_SESSION['lang_param'][$k_del][1].' ('.$k_del.')';
    }

    return $arr;
}
function LanguageReferencesForDelete($dbh, $id_lang)
{
    $lang_refs = [];
    $str_req = "SELECT 'interface_texts', GROUP_CONCAT(id_title SEPARATOR ', ') FROM interface_texts WHERE id_language = ".$id_lang.' GROUP BY id_language';
    $str_req .= ' UNION ';
    $str_req .= "SELECT 'translate_table', GROUP_CONCAT(letter SEPARATOR ', ') FROM translate_table WHERE id_lang = ".$id_lang.' GROUP BY id_lang';
    $str_req .= ' UNION ';
    $str_req .= "SELECT 'user_ident', GROUP_CONCAT(id_user SEPARATOR ', ') FROM user_ident WHERE use_lang_id = ".$id_lang.' GROUP BY use_lang_id';
    $res = mysqli_query($dbh, $str_req);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $arr = explode(', ', $row[1]);
            if (count($arr) > $_SESSION['number_warn']) {
                $lang_refs[$row[0]] = implode(', ', array_slice($arr, 0, $_SESSION['number_warn'])).', ...';
            } else {
                $lang_refs[$row[0]] = $row[1];
            }
        }
        mysqli_free_result($res);
    }

    return $lang_refs;
}
function DelMesMult($lang_refs)
{
    if (count($lang_refs) == 1) {
        $c = reset($lang_refs);
        if (strpos($c, ', ') === false) {
            return false;
        }

        return true;
    } else {
        return true;
    }
}
function DelMesList($lang_refs)
{
    $str_arr = [];
    foreach ($lang_refs as $k => $v) {
        $str_arr[] = '<b>'.(string) $k.'</b> ('.$v.')';
    }

    return implode(', ', $str_arr);
}
function IsLanguageErrors()
{
    foreach ($_SESSION['lang_param'] as $v) {
        if ($v[3]) {
            return true;
        }
    }

    return false;
}
function LanguageExit($dbh, &$Mes)
{
    $ret_addr = '';
    if ($_SESSION['user_working_mode'] == 1) {
        $fl_exit = ! IsLanguageErrors();
        if ($_SESSION['alarm']) {
            SimpleSysTableCheck($dbh, 'languages', "language = '' OR language LIKE '%''%'");
            if ($_SESSION['ref_add']) {
                unset($_SESSION['pre_ref']['interface_texts']);
                SetReferenceTable($dbh, 'interface_texts', 128, QueryReferencesTitles(), [['li', 0, 1, 542, 543]], $_SESSION['pre_ref']);
                unset($_SESSION['pre_ref']['translate_table']);
                SetReferenceTable($dbh, 'translate_table', 125, QueryReferencesLocals(), [['cd', 0, 2, 281, -277], ['li', 1, 2, 542, -277]], $_SESSION['pre_ref']);
                unset($_SESSION['pre_ref']['user_ident']);
                SetReferenceTable($dbh, 'user_ident', 12, QueryReferencesUsers(), [['li', 1, 0, 542, 544], ['db', 2, 0, 545, 544]], $_SESSION['pre_ref']);
                ksort($_SESSION['pre_ref']);
            }
            $ret_addr = '../Alarm/CommonAlarmForm';
        } elseif ($fl_exit) {
            $ret_addr = '../Administrator/MainForm';
        } else {
            $Mes[] = "<font color='#0000FF'><b>".Title(223).'</b></font>';
        }
    } else {
        $ret_addr = '../Administrator/MainForm';
    }
    if ($ret_addr != '') {
        RestoreUserLanguageList();
        unset($_SESSION['lang_param']);
        if (isset($_SESSION['del_langs'])) {
            unset($_SESSION['del_langs']);
        }
        if (isset($_SESSION['pre_lang_err'])) {
            unset($_SESSION['pre_lang_err']);
        }
    }

    return $ret_addr;
}
function RestoreUserLanguageList()
{
    $_SESSION['common_langs'] = [];
    $_SESSION['user_langs'] = [];
    foreach ($_SESSION['lang_param'] as $k => $v) {
        if (gettype($k) == 'integer') {
            $_SESSION['common_langs'][$k] = $v[0];
            if ($k != 0) {
                $_SESSION['user_langs'][$k] = $v[0];
            }
        }
    }
}
function TestLanguageDouble()
{
    $arr_dd = [];
    foreach ($_SESSION['lang_param'] as $k => $v) {
        if ($v[0] != '') {
            $arr_dd[$v[0]][] = (string) $k;
        }
    }
    foreach ($arr_dd as $k => $v) {
        if (count($v) == 1) {
            unset($arr_dd[$k]);
        } else {
            foreach ($v as $z) {
                $_SESSION['lang_param'][$z][3] = true;
            }
        }
    }

    return $arr_dd;
}
function LanguageForbid($k, $v0)
{
    if ((string) $k == '0' && $v0 == '(special)' || (string) $k == '1' && $v0 == 'English') {
        return ' disabled';
    } else {
        return '';
    }
}
function DelLangListRestore()
{
    if (isset($_SESSION['pre_lang_err'])) {
        unset($_SESSION['pre_lang_err']);
    }
    foreach ($_SESSION['del_langs'] as $k) {
        if ($_SESSION['lang_param'][$k][0] != $_SESSION['lang_param'][$k][1]) {
            $_SESSION['lang_param'][$k][0] = $_SESSION['lang_param'][$k][1];
        }
    }
    $_SESSION['del_langs'] = [];
}
