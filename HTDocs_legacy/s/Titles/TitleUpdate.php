<?php

function TitleEdit($dbh, $title_id_key, $lang_id_key)
{
    if (isset($_SESSION['title_edit_row'])) {
        $k = $title_id_key.'|'.$lang_id_key;
        if (isset($_POST['title_lang|'.$k])) {
            $lang_id_new = array_search($_POST['title_lang|'.$k], $_SESSION['title_langs']);
            if ($lang_id_new === false) {
                $lang_id_new = (int) $lang_id_key;
            }
        } else {
            $lang_id_new = (int) $lang_id_key;
        }
        TestThisTitle($dbh, $k, $_POST['title_number|'.$k], $lang_id_new, $_POST['title_text|'.$k]);
        $old_text = $_SESSION['title_param'][$k][0];
        SaveTitlePost($k, $lang_id_new);
        if ($_SESSION['title_param'][$k][1] == '') {
            if (isset($_SESSION['pre_ref']['interface_texts']['p']['li']['v'][(string) $lang_id_key])) {
                ChangeInvalidRefTable($dbh, 'interface_texts', 'li', (string) $lang_id_key, $_POST['title_number|'.$k]);
            }
            if ($_SESSION['title_insert'] > 0) {
                TitleInsert($dbh, $k, $lang_id_new);
            } else {
                TitleUpdate($dbh, $k, $title_id_key, $lang_id_key, $old_text, $lang_id_new);
            }
            if ($_SESSION['pre_ref']['interface_texts']['over']) {
                unset($_SESSION['pre_ref']['interface_texts']);
                SetReferenceTable($dbh, 'interface_texts', 128, QueryReferencesTitles(), [['li', 0, 1, 542, 543]], $_SESSION['pre_ref']);
                ksort($_SESSION['pre_ref']);
                SetInitReplaceIDs('interface_texts', ['li' => 'title_langs']);
            }
            if ($_SESSION['title_insert'] > 0) {
                $_SESSION['title_insert'] = 0;
            }
            if ($_SESSION['inv_ref'] && PermitInvRef('interface_texts', ['li']) && isset($_SESSION['pre_ref']['interface_texts']['p']['li']['v'][$lang_id_key])) {
                SetTitleReplaceMessage($dbh, 'li', $lang_id_key);
            }
            unset($_SESSION['title_edit_row']);
            $_SESSION['title_param'] = GetTitlePortion($dbh);
            $_SESSION['title_count'] = TitleCounts($dbh);
            TestTitles($dbh);
        }
    } else {
        $_SESSION['title_edit_row'] = [(int) $title_id_key, (int) $lang_id_key];
        $_SESSION['inv_ref'] = false;
    }
}
function TitleInsert($dbh, $k, $lang_id_new)
{
    mysqli_query($dbh, 'INSERT INTO interface_texts VALUES ('.$_POST['title_number|'.$k].','.(string) $lang_id_new.",'".$_POST['title_text|'.$k]."')");
    $_SESSION['title_count']++;
    NewTitlePlace($dbh, (int) $_POST['title_number|'.$k], $lang_id_new);
}
function SaveTitlePost($k, $lang_id_new)
{
    $_SESSION['title_param'][$k][0] = $_POST['title_text|'.$k];
    $_SESSION['title_param'][$k][2] = $_POST['title_number|'.$k];
    $_SESSION['title_param'][$k][3] = $lang_id_new;
}
function TitleMarkDelete($title, $language)
{
    if (in_array((int) $title, $_SESSION['scripts_title_ids']) && isset($_SESSION['title_langs'][$language])) {
        unset($_SESSION['title_edit_row']);

        return [$title.'|'.$language, "<font color='#FF0000'>".Title(228).'</font>'];
    }
    $_SESSION['title_del'] = $title.'|'.$language;

    return ['', ''];
}
function TitleDelete($dbh)
{
    $title = (string) TitlePartKey($_SESSION['title_del']);
    $language = (string) TitlePartKey($_SESSION['title_del'], 1);
    if (isset($_SESSION['pre_ref']['interface_texts']['p']['li']['v'][$language])) {
        ChangeInvalidRefTable($dbh, 'interface_texts', 'li', $language, $title);
    }
    mysqli_query($dbh, 'DELETE FROM interface_texts WHERE id_title = '.$title.' AND id_language = '.$language);
    unset($_SESSION['title_param'][$_SESSION['title_del']]);
    if ($_SESSION['pre_ref']['interface_texts']['over']) {
        unset($_SESSION['pre_ref']['interface_texts']);
        SetReferenceTable($dbh, 'interface_texts', 128, QueryReferencesTitles(), [['li', 0, 1, 542, 543]], $_SESSION['pre_ref']);
        ksort($_SESSION['pre_ref']);
        SetInitReplaceIDs('interface_texts', ['li' => 'title_langs']);
    }
    $_SESSION['title_count']--;
    unset($_SESSION['title_del']);
    if (isset($_SESSION['title_edit_row'])) {
        unset($_SESSION['title_edit_row']);
    }
    if (count($_SESSION['title_param']) == 0) {
        TitleNavigation($dbh, 'tlup');
    }
    SetTitleFilterPosition($dbh);
    $_SESSION['title_param'] = GetTitlePortion($dbh);
    TestTitles($dbh);
}
function TitleUpdate($dbh, $k, $title_id_key, $lang_id_key, $old_text, $lang_id_new)
{
    $arr_set = [];
    $key_change = false;
    if ($_POST['title_number|'.$k] != $title_id_key) {
        $key_change = true;
        $arr_set[] = 'id_title = '.$_POST['title_number|'.$k];
    }
    if ((string) $lang_id_new != $lang_id_key) {
        $key_change = true;
        $arr_set[] = 'id_language = '.(string) $lang_id_new;
    }
    if ($_POST['title_text|'.$k] != $old_text) {
        $arr_set[] = "title_text = '".$_POST['title_text|'.$k]."'";
    }
    if (count($arr_set) > 0) {
        mysqli_query($dbh, 'UPDATE interface_texts SET '.implode(',', $arr_set).' WHERE id_title = '.$title_id_key.' AND id_language = '.$lang_id_key);
        if ($key_change) {
            NewTitlePlace($dbh, (int) $_POST['title_number|'.$k], $lang_id_new);
        }
    }
}
function NewTitlePlace($dbh, $title_id_new, $lang_id_new)
{
    $k = TitleKey($title_id_new, $lang_id_new);
    $arr_keys = array_keys($_SESSION['title_param']);
    if ($k < $arr_keys[0] || $k > $arr_keys[count($arr_keys) - 1]) {
        if ($_SESSION['title_filter'] != '') {
            $filter_param = GetFilterParameters();
            if (TitleRowComparison($filter_param, $_POST['title_text|'.$k], $title_id_new, $lang_id_new)) {
                TitleResetFilter($dbh);
            }
        }
        GetTitleStartPosition($dbh, $title_id_new, $lang_id_new);
    }
}
function TitleLangInsert($dbh, $title_id, $lang_id)
{
    $free_lang_id = FirstInsertedLanguage($title_id, $lang_id);
    if ($free_lang_id !== false) {
        if ($_SESSION['title_filter'] != '') {
            TitleResetFilter($dbh);
        }
        $_SESSION['title_insert'] = 1;
        $_SESSION['title_param'][TitleKey($title_id, $free_lang_id)] = ['', '', $title_id, $free_lang_id];
        ksort($_SESSION['title_param']);
        $_SESSION['title_edit_row'] = [$title_id, $free_lang_id];
        if (count($_SESSION['title_param']) > $_SESSION['portion']) {
            $_SESSION['portion']++;
        }
    }
}
function AddNewTitle($dbh)
{
    $_SESSION['title_insert'] = 2;
    TitleNavigation($dbh, 'end');
    TitleResetFilter($dbh);
    if (count($_SESSION['title_param']) == 0) {
        $new_title_id = 1;
    } else {
        $new_title_id = (int) reset($_SESSION['title_param'])[2] + 1;
    }
    $_SESSION['title_param'][TitleKey($new_title_id, 1)] = ['', '', (string) $new_title_id, 1];
    $_SESSION['title_edit_row'] = [$new_title_id, 1];
}
function ListParamSave(&$Mes)
{
    if (isset($_POST['list_height']) && is_numeric($_POST['list_height'])) {
        $_SESSION['portion'] = (int) $_POST['list_height'];
    }
    if (isset($_POST['selected_title_lang'])) {
        $lang_key = array_search($_POST['selected_title_lang'], $_SESSION['title_langs_all']);
        if ($lang_key !== false) {
            $_SESSION['selected_title_lang'] = [$lang_key, $_POST['selected_title_lang']];
        }
    }
    if (isset($_POST['view_title_mode'])) {
        $mode_key = array_search($_POST['view_title_mode'], $_SESSION['title_modes_all']);
        if ($mode_key !== false) {
            $_SESSION['view_title_mode'] = [$mode_key, $_POST['view_title_mode']];
        }
    }
    if (isset($_POST['title_filter_id'])) {
        if ($_POST['title_filter_id'] == '') {
            $_SESSION['title_filter_id'] = '';
        } else {
            if (! is_numeric($_POST['title_filter_id'])) {
                $Mes[] = "<font color='#0000FF'>".Title(147).' '.Title(77).'</font>';
            } elseif ((int) $_POST['title_filter_id'] <= 0 || (int) $_POST['title_filter_id'] > 99999 || (int) $_POST['title_filter_id'] > 0 && strpos($_POST['title_filter_id'], '.') !== false) {
                $Mes[] = "<font color='#0000FF'>".Title(294).'</font>';
            } else {
                $_SESSION['title_filter_id'] = $_POST['title_filter_id'];
            }
        }
    }
    if (isset($_POST['title_filter_text'])) {
        $_SESSION['title_filter_text'] = $_POST['title_filter_text'];
    }
    if (isset($_SESSION['special_interface'])) {
        foreach ($_SESSION['special_interface'] as $k => $v) {
            for ($i = 0; $i < count($v['numbers']); $i++) {
                if (isset($_POST[$k.'|'.(string) $i])) {
                    $_SESSION['special_interface'][$k]['numbers'][$i] = [$_POST[$k.'|'.(string) $i], TestSpecialNumber($_POST[$k.'|'.(string) $i])];
                }
            }
        }
    }
}
function ReplaceTitleLangIDs($dbh, $k_type, $k)
{
    if (ReplaceRefID($dbh, 'interface_texts', 'id_language', $k_type, $k, $_SESSION['repl_id'][$k_type][$k])) {
        if ($_SESSION['pre_ref']['interface_texts']['over']) {
            unset($_SESSION['pre_ref']['interface_texts']);
            SetReferenceTable($dbh, 'interface_texts', 128, QueryReferencesTitles(), [['li', 0, 1, 542, 543]], $_SESSION['pre_ref']);
            ksort($_SESSION['pre_ref']);
            SetInitReplaceIDs('interface_texts', ['li' => 'title_langs']);
        }
        SetTitleFilterPosition($dbh);
        $_SESSION['title_param'] = GetTitlePortion($dbh);
        TestTitles($dbh);
    } else {
        SetTitleReplaceMessage($dbh, $k_type, $k);
    }
    SetInitReplaceIDs('interface_texts', ['li' => 'title_langs']);
}
function SetTitleReplaceMessage($dbh, $k_type, $k)
{
    $no_changed = [];
    $res = mysqli_query($dbh, 'SELECT id_title FROM interface_texts WHERE id_language = '.$_SESSION['repl_id'][$k_type][$k].' AND id_title IN ('.implode(',', $_SESSION['pre_ref']['interface_texts']['p'][$k_type]['v'][$k]).')');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $no_changed[] = $row[0];
        }
        mysqli_free_result($res);
    }
    if (count($no_changed) > 0) {
        $replave_value = $_POST['replacing_value|'.$k_type.'|'.$k];
        $no_change_list = implode(', ', $_SESSION['pre_ref']['interface_texts']['p'][$k_type]['v'][$k]);
        $err_list = implode(', ', $no_changed);
        $_SESSION['replace_mes'] = ['#FF0000', 667, ' <b>'.$no_change_list.'</b> ', 668, ' <b>'.$err_list.'</b> ', 672, ' <b>'.$replave_value.'</b>'];
    }
}
