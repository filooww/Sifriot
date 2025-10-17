<?php

function TitleKey($key_id, $key_lang)
{
    return sprintf("%'.06d", (string) $key_id).'|'.sprintf("%'.02d", (string) $key_lang);
}
function TitlePartKey($title_key, $part = 0)
{
    $arr = explode('|', $title_key);

    return (int) $arr[$part];
}
function TitleExit($dbh, &$Mes)
{
    $exit_addr = '';
    if (is_numeric($_POST['list_height']) && (int) $_POST['list_height'] > 0) {
        SaveUserScreenPortion($dbh, $_POST['list_height']);
    }
    if ($_SESSION['alarm']) {
        if (! $_SESSION['pre_ref']['interface_texts']['over'] && isset($_SESSION['pre_ref']['interface_texts']['p']['li']['v']) && count($_SESSION['pre_ref']['interface_texts']['p']['li']['v']) == 0) {
            unset($_SESSION['pre_ref']['interface_texts']['p']['li']['v']);
        }
        InterfaceTableCheck($dbh);
        $_SESSION['titles'] = GetTitlesByLanguage($dbh, $_SESSION['user_lang'][0]);
        ResetTitleParameters();
        $exit_addr = '../Alarm/CommonAlarmForm';
    } elseif ($_SESSION['user_working_mode'] == 1) {
        $is_title_error = IsTitleError();
        if ($is_title_error) {
            $Mes[] = "<font color='#FF0000'><b>".Title(223).'</b></font>';
            $exit_addr = '';
        } else {
            if (isset($_SESSION['special_interface'])) {
                SaveSpecialTexts($dbh);
            }
            $_SESSION['titles'] = GetTitlesByLanguage($dbh, $_SESSION['user_lang'][0]);
            ResetTitleParameters();
            $exit_addr = '../Administrator/MainForm';
        }
    } else {
        $exit_addr = '../Administrator/MainForm';
    }
    if ($exit_addr != '') {
        unset($_SESSION['special_interface']);
    }

    return $exit_addr;
}
function ResetTitleParameters()
{
    if (isset($_SESSION['title_edit_row'])) {
        unset($_SESSION['title_edit_row']);
    }
    if (isset($_SESSION['title_langs'])) {
        unset($_SESSION['title_langs']);
    }
    if (isset($_SESSION['selected_title_lang'])) {
        unset($_SESSION['selected_title_lang']);
    }
    if (isset($_SESSION['title_filter_id'])) {
        unset($_SESSION['title_filter_id']);
    }
    if (isset($_SESSION['title_filter_text'])) {
        unset($_SESSION['title_filter_text']);
    }
    if (isset($_SESSION['title_insert'])) {
        unset($_SESSION['title_insert']);
    }
    if (isset($_SESSION['title_modes_all'])) {
        unset($_SESSION['title_modes_all']);
    }
    if (isset($_SESSION['view_title_mode'])) {
        unset($_SESSION['view_title_mode']);
    }
    if (isset($_SESSION['title_param'])) {
        unset($_SESSION['title_param']);
    }
    if (isset($_SESSION['title_del'])) {
        unset($_SESSION['title_del']);
    }
    if (isset($_SESSION['spec_title'])) {
        unset($_SESSION['spec_title']);
    }
    if (isset($_SESSION['spec_titles'])) {
        unset($_SESSION['spec_titles']);
    }
    if (isset($_SESSION['mes_integrity'])) {
        unset($_SESSION['mes_integrity']);
    }
    if (isset($_SESSION['title_count'])) {
        unset($_SESSION['title_count']);
    }
    if (isset($_SESSION['title_langs_all'])) {
        unset($_SESSION['title_langs_all']);
    }
    if (isset($_SESSION['new_special_number'])) {
        unset($_SESSION['new_special_number']);
    }
    if (isset($_SESSION['replace_mes'])) {
        unset($_SESSION['replace_mes']);
    }
}
function ViewTitleRow($no_first_lang, $k, $v, $free_langs, $b_edit_image, $b_edit_block, $text_block, $del_block, $id_class)
{
    $edit_dis = ($b_edit_block) ? ' disabled' : '';
    $ins_dis = (! isset($_SESSION['title_edit_row']) && count($free_langs) > 1) ? '' : ' disabled';
    $text_dis = ($text_block) ? ' disabled' : '';
    $del_dis = (isset($_SESSION['title_edit_row'])) ? '' : ' disabled';
    if ($_SESSION['user_working_mode'] == 1) {
        if ($no_first_lang || $_SESSION['view_title_mode'][0] > 0 || count($free_langs) < 2 || $_SESSION['selected_title_lang'][0] > 0 || isset($_SESSION['title_edit_row'])) {
            echo '<td></td>';
        } else {
            echo "<td><button type='submit' class='i_h' title='".Title(304)."' name='title_lang_insert|".$k."' ".BSize(30, 20)." value='*' ".$ins_dis.'>'.ImgV('LineDown', 13, 12).'</button></td>';
        }
        echo "<td><button type='submit' name='title_identifier|".$k."' ".BSize(30, 20)." title='".Title(291)."' class='button_class' value='*'".$edit_dis.'>'.SysImage((($b_edit_image) ? 'CheckBorder' : 'BlankBorder'), 16, 16, $b_edit_block).'</button></td>';
        echo "<td><button type='submit' name='title_mark_del|".$k."' ".BSize(30, 20)." title='".Title(283).' '.Title(221)."' class='button_class' value='*'".$del_dis.'>'.SysImage((($b_edit_image && isset($_SESSION['title_del'])) ? 'CheckBorder' : 'BlankBorder'), 16, 16, $del_block).'</button></td>';
    }
    echo "<td><input size='11' class='".$id_class."' type='text' name='title_number|".$k."' value='".(string) $v[2]."'".$text_dis.'></td>';
    echo '<td>';
    SelectTag('title_lang|'.$k, $free_langs, $v[3], '', true, '', '', $text_block || count($free_langs) == 1);
    echo '</td>';
    echo "<td><input size='82' type='text' name='title_text|".$k."' title='".$v[0]."'value='".$v[0]."'".$text_dis.'></td>';
    if ($k != $_SESSION['del_mes'][0]) {
        echo '<td>'.$v[1].'</td>';
    } elseif ($_SESSION['del_mes'][1] == '') {
        echo '<td>'.$v[1].'</td>';
    } elseif ($v[1] == '') {
        echo '<td>'.$_SESSION['del_mes'][1].'</td>';
    } else {
        echo '<td>'.$_SESSION['del_mes'][1].'; '.$v[1].'</td>';
    }
}
function SwitchTitleMode($dbh, &$sw_break)
{
    if ($_POST['title_modes'] == '') {
        $sw_break = false;
    } else {
        TitleFilter($dbh);
    }
}
function AfterTitleLangChoice($dbh, &$sw_break)
{
    if (AfterLangChoice($dbh, 'user_lang_s', 'user_lang', $sw_break)) {
        $_SESSION['spec_title'] = (isset($_SESSION['special_interface'])) ? Title(466) : Title(465);
        $_SESSION['spec_titles'] = ['table_types' => Title(266), 'compare_mode' => Title(400), 'sort_mode' => Title(424), 'field_align' => Title(451), 'field_using' => Title(452), 'field_types' => Title(453), 'group_types' => Title(454), 'z_o' => Title(582)];
        $_SESSION['title_langs_all'] = SetLanguageList(0);
        $_SESSION['title_langs'] = array_slice($_SESSION['title_langs_all'], 1, null, true);
        $_SESSION['selected_title_lang'] = [$_SESSION['selected_title_lang'][0], $_SESSION['title_langs_all'][$_SESSION['selected_title_lang'][0]]];
        $_SESSION['title_modes_all'] = [Title(435), Title(437), Title(438), Title(442), FTM(Title(592))];
        $_SESSION['view_title_mode'] = [$_SESSION['view_title_mode'][0], $_SESSION['title_modes_all'][$_SESSION['view_title_mode'][0]]];
        TitleFilter($dbh);
        TestTitles($dbh);
    }
}
function AfterTitleSelLangChoice($dbh, &$sw_break)
{
    if ($_POST['title_lang_s'] == '') {
        $sw_break = false;
    } else {
        if ($_POST['selected_title_lang'] == '--'.Title(441).'--') {
            $_SESSION['selected_title_lang'] = [0, '--'.Title(441).'--'];
        } else {
            $lang_key = array_search($_POST['selected_title_lang'], $_SESSION['title_langs']);
            if ($lang_key !== false) {
                $_SESSION['selected_title_lang'] = [(int) $lang_key, $_POST['selected_title_lang']];
            }
        }
        TitleFilter($dbh);
    }
}
function DigitsToTitle($digit_arr)
{
    $title_str = '';
    for ($i = 0; $i < count($digit_arr); $i++) {
        if (substr($digit_arr[$i], 0, 1) == '*') {
            $n = (int) substr($digit_arr[$i], 1);
            if ($n < 0) {
                $title_str .= FTM(Title(-$n)).' ';
            } else {
                $title_str .= Title($n).' ';
            }
        } else {
            $title_str .= $digit_arr[$i].' ';
        }
    }
    if ($title_str != '') {
        $title_str = trim($title_str);
    }

    return $title_str;
}
