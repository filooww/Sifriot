<?php

function IsTitleError()
{
    $fl_spec = (isset($_SESSION['special_interface'])) ? ! TestSpecialTexts(false) : false;
    $c = reset($_SESSION['title_param']);
    while ($c !== false && $c[1] == '') {
        $c = next($_SESSION['title_param']);
    }

    return $fl_spec || $c !== false;
}
function TestThisTitle($dbh, $k, $tested_id = '', $tested_lang = '', $tested_text = '')
{
    $arr_err = [];
    $arr_k = ($tested_lang == '') ? [(string) TitlePartKey($k), (string) TitlePartKey($k, 1)] : [$tested_id, $tested_lang];
    if (! is_numeric($arr_k[0])) {
        $arr_err[] = "<font color='#FF0000'>".FTM(Title(147)).' '.Title(77).'</font>';
    } elseif ((int) $arr_k[0] <= 0 || (int) $arr_k[0] > 99999 || strpos($arr_k[0], '.') !== false) {
        $arr_err[] = "<font color='#FF0000'>".FTM(Title(294)).'</font>';
    }
    if (! isset($_SESSION['user_langs'][$arr_k[1]])) {
        $arr_err[] = "<font color='#FF0000'>".Title(546).': <b>'.$arr_k[1].'</b></font>';
    }
    if ($tested_id != '' && ($arr_k[0] != TitlePartKey($k) || $arr_k[1] != TitlePartKey($k, 1))) {
        TestTitleDouble($dbh, (int) $arr_k[0], (int) $arr_k[1], $arr_err);
    }
    if (TitlePartKey($k) != (int) $arr_k[0] && $_SESSION['title_insert'] == 1) {
        $arr_err[] = "<font color='#FF0000'>".FTM(Title(179)).'</font>';
    }
    if ($tested_text != '' && strpos($tested_text, chr(39)) !== false) {
        $arr_err[] = "<font color='#FF0000'>".FTM(Title(293)).' '.Title(318).' ('.Title(665)."</font> <font color=''#990000' size'+2'><b>".$_SESSION['apostrophe_replace'].'</b>)</font>';
    }
    $_SESSION['title_param'][$k][1] = (count($arr_err) == 0) ? '' : implode(', ', $arr_err);

}
function TestTitleDouble($dbh, $new_title_id, $new_lang_id, &$arr_err)
{
    $t = '';
    $res = mysqli_query($dbh, 'SELECT title_text FROM interface_texts WHERE id_title = '.(string) $new_title_id.' AND id_language = '.(string) $new_lang_id);
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            if ($new_title_id != $row[0] || $new_lang_id != $row) {
                $tt = ($row[0] == '') ? chr(39).chr(39) : '<b>'.$row[0].'</b>';
                if (isset($_SESSION['user_langs'][$new_lang_id])) {
                    $t = Title(180).' ('.FTM(Title(147)).' <b>'.(string) $new_title_id.'</b>, '.FTM(Title(181)).' <b>'.$_SESSION['user_langs'][$new_lang_id].'</b>) '.Title(587).' - '.Title(588).' '.$tt;
                } else {
                    $t = Title(180).' ('.FTM(Title(147)).' <b>'.(string) $new_title_id.'</b>, '.Title(542).' <b>'.(string) $new_lang_id.'</b>) '.Title(587).' - '.Title(588).' '.$tt;
                }
            }
        }
        mysqli_free_result($res);
    }
    if ($t != '') {
        $arr_err[] = "<font color='#FF0000'>".$t.'</font>';
    }
}
function TestTitles($dbh)
{
    foreach (array_keys($_SESSION['title_param']) as $k) {
        if (isset($_SESSION['title_edit_row']) && $k == TitleKey($_SESSION['title_edit_row'][0], $_SESSION['title_edit_row'][1])) {
            if (isset($_POST['title_lang|'.$k])) {
                $lang_id = array_search($_POST['title_lang|'.$k], $_SESSION['title_langs']);
            } else {
                $lang_id = TitlePartKey($k, 1);
            }
            if ($lang_id === false) {
                $lang_id = TitlePartKey($k, 1);
            }
            error_reporting(0);
            TestThisTitle($dbh, $k, $_POST['title_number|'.$k], (string) $lang_id, $_POST['title_text|'.$k]);
            error_reporting(E_ALL);
        } else {
            TestThisTitle($dbh, $k);
        }
    }
}
function TestMissingTitles()
{
    $Mes = '';
    if (count($_SESSION['titles']) == 0) {
        $Mes = 'The system does not all interface texts';
    } else {
        $no_title = [];
        $title_keys = array_keys($_SESSION['titles']);
        foreach ($_SESSION['scripts_title_ids'] as $t_id) {
            if (! in_array($t_id, $title_keys)) {
                $no_title[] = $t_id;
            }
        }
        foreach ($_SESSION['ex_title_ids'] as $t_id) {
            if (! in_array($t_id, $title_keys)) {
                $no_title[] = $t_id;
            }
        }
        sort($no_title);
        if (count($no_title) > $_SESSION['number_warn']) {
            $arr = array_merge(array_slice($no_title, 0, $_SESSION['number_warn']), ['...']);
        } else {
            $arr = $no_title;
        }
        if (count($no_title) > 0) {
            $Mes = 'The system does not have interface text numbers '.implode(',', $no_title);
        }
    }

    return $Mes;
}
