<?php

function SeveralFound($output_str, $ss_sv, $ss_s_text, $s_text)
{
    $s_r = $output_str;
    $arr_pos = array_reverse(AllPosition($ss_sv, $ss_s_text));
    for ($i = 0; $i < count($arr_pos); $i++) {
        if ($i == 0) {
            $disting_len = strlen($s_text);
            $start_tail = $arr_pos[$i] + strlen($s_text);
        } elseif ($arr_pos[$i - 1] >= $arr_pos[$i] + strlen($s_text)) {
            $disting_len = strlen($s_text);
            $start_tail = $arr_pos[$i] + strlen($s_text);
        } else {
            $disting_len = $arr_pos[$i - 1] - $arr_pos[$i];
            $start_tail = $arr_pos[$i - 1];
        }
        $s_r = substr($s_r, 0, $arr_pos[$i])."<span class='found_text'>".substr($s_r, $arr_pos[$i], $disting_len).'</span>'.substr($s_r, $start_tail);
    }

    return $s_r;
}
function RowForSearch($output_str, $n, $search_type)
{
    $ss_sv = MCV($output_str, $_SESSION['match_case']);
    $ss_s_text = MCV($_SESSION['catalog_param'][$n]['search_text'], $_SESSION['match_case']);
    if ($search_type == 1) {
        if ($ss_sv == $ss_s_text) {
            return "<span class='found_text'>".$output_str.'</span>';
        } else {
            return $output_str;
        }
    }
    if ($search_type == 2) {
        $pos = strpos($ss_sv, $ss_s_text);
        if ($pos === false) {
            return $output_str;
        }
        if ($pos == 0) {
            return "<span class='found_text'>".substr($output_str, 0, strlen($_SESSION['catalog_param'][$n]['search_text'])).'</span>'.substr($output_str, strlen($_SESSION['catalog_param'][$n]['search_text']));
        }

        return $output_str;
    } else {
        return SeveralFound($output_str, $ss_sv, $ss_s_text, $_SESSION['catalog_param'][$n]['search_text']);
    }
}
function SetMarkColor($k_ref, $v_ref, $cur_value, $var_color = '')
{
    if ($v_ref[2]) {
        return " class='error_text'";
    }
    if ($v_ref[1]) {
        return " class='changed_text'";
    }
    if ($cur_value != '' && end($v_ref) == $cur_value) {
        return " class='cur_text'";
    }
    if ($v_ref[3] == '' && is_numeric($k_ref)) {
        return " class='deleted_text'";
    }

    return $var_color;
}
function ViewPortion($n, $search_type, $cur_value)
{
    $block_button = $_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'] || $_SESSION['user_working_mode'] == 0 || ($n == 0 && ! $_SESSION['to_sel_form_catalog'] || $n == 1 && ! $_SESSION['cat_mark']['b_on']);
    $block_text = $_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'] || $_SESSION['user_working_mode'] == 0;
    $togg = true;
    foreach ($_SESSION['cat_arr'][$n] as $k_ref => $v_ref) {
        $cl = SetMarkColor($k_ref, $v_ref, $cur_value, ($togg) ? "class='odd_row'" : "class='even_row'");
        echo "<tr valign='top'".(($_SESSION['catalog_param'][$n]['found_count'] > 0 && $_SESSION['catalog_param'][$n]['view_search']) ? ' '.$cl : '').'>';
        echo "<td width='".$_SESSION['w_05']."'>";
        $ttl = ($n == '1') ? Title(402) : Title(103);
        if (! is_numeric($k_ref)) {
            echo "<button style='width:".$_SESSION['w_06'].";text-align:right;background-color:#999999' name='cat_code|".$n.'|'.$k_ref."' type='submit' value='0' disabled><span class='new_txt'>".Title(71).'</span></button>';
        } // new??
        else {
            echo "<button style='width:".$_SESSION['w_06'].";text-align:right' name='cat_code|".$n.'|'.$k_ref."' type='submit' title='".$ttl."' value='".$k_ref."'".SetExPar($block_button, '').'>'.$k_ref.'</button>';
        }
        echo '</td>';
        if ($_SESSION['catalog_param'][$n]['found_count'] > 0 && $_SESSION['catalog_param'][$n]['view_search']) {
            echo '<td>'.RowForSearch(end($v_ref), $n, $search_type).'</td>';
        } else {
            echo "<td><input type='text' name='cat_text|".$n.'|'.$k_ref."' value='".end($v_ref)."' title='".end($v_ref)."' size=".$_SESSION['w_08']."'".$cl.SetExPar($block_text, '').'></td>';
        }
        echo '</tr>';
        if ($_SESSION['catalog_param']['0']['found_count'] > 0 && $_SESSION['catalog_param']['0']['view_search']) {
            $togg = ! $togg;
        }
    }
    for ($i = count($_SESSION['cat_arr'][$n]); $i < $_SESSION['portion']; $i++) {
        echo "<tr><td align='right' class='dis_text'>".(string) ($i + 1).'</td></tr>';
    }
}
function RowForOutput($source_str, $search_type)
{
    $output_str = $source_str;
    if ($_SESSION['Catalog']['0']['cat_type'] == 1 && $_SESSION['cat_tree']) {
        $output_str = TreeViewRow($output_str);
    }
    if ($_SESSION['catalog_param']['0']['found_count'] > 0 && $_SESSION['catalog_param']['0']['view_search']) {
        $output_str = RowForSearch($output_str, '0', $search_type);
    }

    return $output_str;
}
function ViewSetsPortion($search_type)
{
    $block_select = SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'] || $_SESSION['user_working_mode'] == 0 || ! $_SESSION['to_sel_form_catalog'], '');
    $block_search = SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'] || $_SESSION['catalog_param']['0']['search_on'] || $_SESSION['user_working_mode'] == 0, '');
    if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
        echo "<tr class='vis_color'>";
        echo '<td></td>';
        echo '<td></td>';
        echo '<td><b>'.Title(73).'</b></td>';
        echo '<td></td>';
        echo '<td></td>';
        if ($_SESSION['user_working_mode'] == 0 || ! $_SESSION['to_sel_form_catalog']) {
            echo "<td width='1%'></td>";
        } else {
            echo "<td width='1%'><button name='cat_paste_root' type='submit' title='".Title(42)."' value='*'".$block_search.'>'.ImgV('Paste', 16, 16).'</button></td>';
        }
        echo '</tr>';
    }
    $togg = true;
    foreach ($_SESSION['cat_arr']['0'] as $k => $v_ref) {
        $cl = SetMarkColor($k, $v_ref, $_SESSION['cur_value'], ($togg) ? "class='odd_row'" : "class='even_row'");
        echo "<tr valign='top' ".$cl.'>';
        echo "<td width='".$_SESSION['w_05']."'>";
        if (! is_numeric($k)) {
            echo "<button style='width:".$_SESSION['w_06'].";text-align:right;background-color:#999999' name='cat_code|0|".$k."' type='submit' value='0' disabled><span class='new_txt'>".Title(71).'</span></button>';
        } // new??
        else {
            echo "<button style='width:".$_SESSION['w_06'].";text-align:right' name='cat_code|0|".$k."' type='submit' title='".Title(103)."' value='".$k."'".$block_select.'>'.$k.'</button>';
        }
        echo '</td>';
        if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
            $collapse_button = ($v_ref[0]) ? '+' : '-';
            if (! is_numeric($k)) {
                echo "<td width='1%'><font class='invisible_button'>X</font></td>";
            } // new??
            else {
                echo "<td width='1%'><button name='collapse|0|".$k.'|'.$v_ref[3]."' type='submit' value='".$collapse_button."'".$block_select.'>'.$collapse_button.'</button></td>';
            }
        }
        $title_row = end($v_ref);
        echo '<td>'.RowForOutput(end($v_ref), $search_type).'</td>';
        if ($_SESSION['cat_vis']['b_on'] && $k == $_SESSION['cat_vis']['bc']) {
            echo "<td width='1%'><button name='set_vis|0|".$k."' type='submit' title='".Title(43)."' class='set_visible' value='*'>".ImgV('ShowList', 16, 16).'</button></td>';
        } else {
            echo "<td width='1%'><button name='set_vis|0|".$k."' type='submit' title='".Title(43)."' class='w_b' value='*'>".ImgV('ShowList', 16, 16).'</button></td>';
        }
        if ($_SESSION['user_working_mode'] == 1) {
            $cls_search = ($_SESSION['cat_mark']['b_on'] && $k == $_SESSION['cat_mark']['bc']) ? " class='row_insert'" : '';
            echo "<td width='1%'><button name='cat_mark|0|".$k."' class='w_b' type='submit' title='".Title(44)."' ".$cls_search." value='*'".$block_search.'>'.ImgV('LeftC', 16, 16).'</button></td>';
        }
        if ($_SESSION['Catalog']['0']['cat_type'] == 1 && is_numeric($k)) {
            echo "<td width='1%'><button name='cat_paste|0|".$k."' type='submit' title='".Title(56)."' value='*'".$block_search.'>'.ImgV('Paste', 16, 16).'</button></td>';
        } // new??
        echo '</tr>';
        if ($_SESSION['cat_vis']['b_on'] && $k == $_SESSION['cat_vis']['bc'] && end($v_ref) != '') {
            SetByRows($k, $v_ref, $block_search);
        }
        $togg = ! $togg;
    }
    for ($i = count($_SESSION['cat_arr']['0']); $i < $_SESSION['portion']; $i++) {
        echo "<tr><td align='right' class='dis_text'>".(string) ($i + 1).'</td></tr>';
    }
}
function SetByRows($k_ref, $set_row, $block_search)
{
    $arr_set_id = array_map('trim_string', explode(',', $set_row[3]));
    $arr_set_rw = array_map('trim_string', explode($_SESSION['Catalog']['0']['separator'], $set_row[4]));
    if (count($arr_set_id) > 0) {
        for ($i = 0; $i < count($arr_set_id); $i++) {
            echo "<tr class='vis_color'>";
            echo '<td></td>';
            if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
                echo '<td></td>';
            }
            echo '<td>'.$arr_set_rw[$i].'</td>';
            if ($_SESSION['catalog_param']['0']['found_count'] == 0 || ! $_SESSION['catalog_param']['0']['view_search']) {
                if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
                    if (! is_numeric($k_ref)) { // new??
                        if ($block_search == '') {
                            echo "<td width='1%'><button name='del_from_set|0|".$arr_set_id[$i]."' type='submit' title='".Title(66)."' class='cancel_button' value='*'".$block_search.'>'.ImgV('Delete', 16, 16).'</button></td>';
                        } else {
                            echo '<td></td>';
                        }
                        echo '<td></td>';
                    } else {
                        if ($block_search == '') {
                            if ($i < count($arr_set_id) - 1) {
                                echo '<td></td>';
                            } else {
                                echo "<td width='1%'><button name='del_hier_set|0|".$k_ref."' type='submit' title='".Title(68)."' class='cancel_button' value='*'".$block_search.'>'.ImgV('DeleteBranch', 16, 16).'</button></td>';
                            }
                            echo "<td width='1%'><button name='cat_copy|0|".$k_ref.'|'.(string) $i."' type='submit' title='".Title(70)."' value='*'".$block_search.'>'.ImgV('CopyFrom', 16, 16).'</button></td>';
                        } else {
                            echo '<td></td><td></td>';
                        }
                    }
                } else {
                    if ($block_search == '') {
                        if (! is_numeric($arr_set_id[$i])) {
                            echo '<td></td>';
                        } // new??
                        else {
                            echo "<td width='1%'><button name='del_from_set|0|".$arr_set_id[$i]."' type='submit' title='".Title(66)."' class='cancel_button' value='*'".$block_search.'>'.ImgV('Delete', 16, 16).'</button></td>';
                        }
                    } else {
                        echo '<td></td>';
                    }
                }
            }
            echo '<td></td>';
            if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
                echo '<td></td>';
            }
            echo '</tr>';
        }
    }
}
function FormSetVis($parse_text, $separator)
{
    if ($parse_text != '') {
        $arr_set_vis = array_map('trim_string', explode($separator, $parse_text));
        if (count($arr_set_vis) > 0) {
            echo '<tr>';
            echo "<table frame='border'>";
            foreach ($arr_set_vis as $z) {
                echo '<tr><td><b>'.$z.'</b></td></tr>';
            }
            echo '</table>';
            echo '</tr>';
        }
    }
}
function TitleCatalog($n)
{
    echo '<table>';
    echo '<tr>';
    echo '<td><b>'.$_SESSION['Catalog'][$n]['name'].':</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo "<td align='left'>";
    if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
        echo Title(51).' <b>'.(string) $_SESSION['catalog_param'][$n]['total_count'].'</b> ('.Title(67).' <b>'.(string) $_SESSION['catalog_param'][$n]['tree_total_count'].'</b>); ';
    } else {
        echo Title(51).' <b>'.(string) $_SESSION['catalog_param'][$n]['total_count'].'</b>; ';
    }
    echo Title(84).' <b>'.(string) $_SESSION['catalog_param'][$n]['filter_count'].'</b>; ';
    echo Title(52).' <b>'.(string) $_SESSION['catalog_param'][$n]['found_count'].'</b>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
}
function ViewTitleMenu($n)
{
    $block = $_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'];
    $f_ttl = ($_SESSION['catalog_param'][$n]['filter_on']) ? FTM(Title(61)) : Title(85);
    $s_ttl = ($_SESSION['catalog_param'][$n]['search_on']) ? Title(90) : Title(87);
    echo '<table>';
    echo '<tr>';
    if ($n == '1' && $_SESSION['user_working_mode'] == 1) {
        echo "<td><button name='cat_fill' type='submit' title='".Title(429)."' class='transparent' value='*'".SetExPar($block, '').'>'.ImgV('FillCatalog', 16, 16).'</button><?td>';
    }
    if (! $_SESSION['catalog_param'][$n]['filter_on']) {
        echo "<td><button name='cat_filter|".$n."' type='submit' class='transparent' title='".$f_ttl."' value='*'".SetExPar($block, '').'>'.ImgV('Filter', 16, 16).'</button></td>';
    }
    if (! $_SESSION['catalog_param'][$n]['search_on']) {
        echo "<td><button name='cat_search|".$n."' type='submit' class='transparent' title='".$s_ttl."' value='*'".SetExPar($block, '').'>'.ImgV('Find', 16, 16).'</button></td>';
    }
    if ($_SESSION['catalog_param'][$n]['search_on'] && $_SESSION['catalog_param'][$n]['found_count'] > 0) {
        $ttl = ($_SESSION['catalog_param'][$n]['view_search']) ? Title(47) : Title(91);
        $img = ($_SESSION['catalog_param'][$n]['view_search']) ? ImgV('Hide', 16, 16) : ImgV('Unhide', 16, 16);
        echo "<td><button name='cat_search_hide|".$n."' type='submit' class='transparent' title='".$ttl."' value='*'".SetExPar($block, '').'>'.$img.'</button></td>';
    }
    echo '</tr>';
    echo '</table>';
}
function ViewNavigation($n)
{
    echo "<tr><td><button name='cat_nav|beg|".$n."' type='submit' class='transparent' title='".Title(93)."' ".SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'], '').'>'.ImgV('LineFirst', 16, 16).'</button></td></tr>';
    echo "<tr><td><button name='cat_nav|pg_up|".$n."' type='submit' class='transparent' title='".Title(94)."' ".SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'], '').'>'.ImgV('PageUp', 16, 16).'</button></td></tr>';
    echo "<tr><td><button name='cat_nav|ln_up|".$n."' type='submit' class='transparent' title='".Title(95)."' ".SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'], '').'>'.ImgV('LineUp', 16, 16).'</button></td></tr>';
    echo "<tr><td><button name='cat_nav|ln_dn|".$n."' type='submit' class='transparent' title='".Title(96)."' ".SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'], '').'>'.ImgV('LineDown', 16, 16).'</button></td></tr>';
    echo "<tr><td><button name='cat_nav|pg_dn|".$n."' type='submit' class='transparent' title='".Title(97)."' ".SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'], '').'>'.ImgV('PageDown', 16, 16).'</button></td></tr>';
    echo "<tr><td><button name='cat_nav|end|".$n."' type='submit' class='transparent' title='".Title(98)."' ".SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'], '').'>'.ImgV('LineEnd', 16, 16).'</button></td></tr>';
}
function ViewSingleCatalog($n, $cur_value)
{
    TitleCatalog($n);
    ViewTitleMenu($n);
    if ($_SESSION['catalog_param'][$n]['filter_on']) {
        FormForSelection(Title(99), 'filter_text|'.$n, 'filter_start|'.$n, Title(477), 'f_cancel|'.$n, FTM(Title(61)), 'filter_mode|'.$n, $n, 'filter_text', 'filter_compare', 'FilterStart', 'FilterNo', 'filter_match_case|'.$n);
    }
    if ($_SESSION['catalog_param'][$n]['search_on']) {
        FormForSelection(Title(83), 'search_text|'.$n, 'search_start|'.$n, Title(478), 's_cancel|'.$n, Title(90), 'search_mode|'.$n, $n, 'search_text', 'search_compare', 'FindStart', 'FindNo', 'search_match_case|'.$n);
    }
    $search_type = GetRadioType($_SESSION['catalog_param'][$n]['search_where']);
    echo "<table frame='border'>";
    echo "<tr valign='top'>";
    echo '<td>';
    echo '<table>';
    ViewNavigation($n);
    echo '</table>';
    echo '</td>';
    echo '<td>';
    echo '<table>';
    ViewPortion($n, $search_type, $cur_value);
    echo '</table>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
}
function ViewSetsCatalog()
{
    TitleCatalog('0');
    ViewTitleMenu('0');
    if ($_SESSION['catalog_param']['0']['filter_on']) {
        FormForSelection(Title(99), 'filter_text|0', 'filter_start|0', Title(477), 'f_cancel|0', FTM(Title(61)), 'filter_mode|0', '0', 'filter_text', 'filter_compare', 'FilterStart', 'FilterNo', 'filter_match_case|0');
    }
    if ($_SESSION['catalog_param']['0']['search_on']) {
        FormForSelection(Title(83), 'search_text|0', 'search_start|0', Title(478), 's_cancel|0', Title(90), 'search_mode|0', '0', 'search_text', 'search_compare', 'FindStart', 'FindNo', 'search_match_case|0');
    }
    $search_type = GetRadioType($_SESSION['catalog_param']['0']['search_where']);
    if ($_SESSION['Catalog']['0']['cat_type'] == 1 && $_SESSION['copy_paste']['copy_id'] != '') {
        $mes_txt = 'Value <b>'.$_SESSION['copy_paste']['copy_text_value'].'</b> (<b>'.$_SESSION['copy_paste']['copy_id'].'</b>) selected for copying';
        if ($_SESSION['copy_paste']['parent_value'] != '') {
            $mes_txt .= ' from <b>'.$_SESSION['copy_paste']['parent_value_text'].'</b>';
        }
        echo "<table><tr><td align='left'><p class='copy_class'>".$mes_txt.'. Select row for paste.</p></td></tr></table>';
    }
    echo "<table frame='border'>";
    echo "<tr valign='top'>";
    echo '<td>';
    echo '<table>';
    ViewNavigation('0');
    echo '</table>';
    echo '</td>';
    echo '<td>';
    echo '<table>';
    ViewSetsPortion($search_type);
    echo '</table>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
}
function ButtonPanel($block)
{
    echo '<table>';
    echo '<tr>';
    echo "<td><button name='cat_exit' type='submit' class='transparent' title='".Title(100)."' value='*'".SetExPar($block, '').'>'.ImgV('Close', 16, 16).'</button></td>'; // upd
    if ($_SESSION['user_working_mode'] == 1) {
        echo "<td><button name='cat_rest' class='transparent' type='submit' title='".Title(104)."' value='*'".SetExPar($block, '').'>'.ImgV('Reset', 16, 16).'</button></td>'; // upd??
        echo "<td><button name='cat_save' class='transparent' type='submit' title='".Title(129)."' value='*'".SetExPar($block, '').'>'.ImgV('Save', 16, 16).'</button></td>'; // upd
    }
    echo "<td><button name='cat_test' type='submit' class='transparent' title='".Title(133)."' value='*'".SetExPar($block, '').'>'.ImgV('Test', 16, 16).'</button></td>';
    echo "<td><font class='invisible_button'>X</font></td>";
    echo '<td>'.TableAdjustment('cat_height|minus', 'cat_height|plus', 'w_b').'</td>';
    echo '<td>';
    echo "<input name='cat_height_input' size='3' type='text' title='".Title(15)."' class='data_num' value='".(string) $_SESSION['portion']."'>";
    echo "<button name='cat_height|button' class='w_b' type='submit' title='".Title(32)."' value='*' style='text|align:center'>...</button>";
    echo '</td>';
    if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
        echo "<td><button name='cat_tree' type='submit' class='transparent' title='".Title(102)."' value='*'".SetExPar($block, '').'>'.ImgV('Tree', 16, 16).'</button></td>';
        echo "<td><button name='cat_tree_collapse' type='submit' class='transparent' title='".Title(290)."' value='*'".SetExPar($block, '').'>'.ImgV('TreeCollapse', 16, 16).'</button></td>';
        echo "<td><button name='cat_tree_extend' type='submit' class='transparent' title='".Title(385)."' value='*'".SetExPar($block, '').'>'.ImgV('TreeExtend', 16, 16).'</button></td>';
    }
    $ttl_mes = ($_SESSION['view_err_mes']) ? Title(476) : Title(472);
    $img_mes = ($_SESSION['view_err_mes']) ? 'HideMessages' : 'ShowMessages';
    echo "<td><button name='cat_view_messages' type='submit' class='transparent' title='".$ttl_mes."' value='*'".SetExPar($block, '').'>'.ImgV($img_mes, 16, 16).'</button></td>';
    echo '</tr>';
    echo '</table>';
}
function ViewCoupleCatalog()
{
    $block = $_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'];
    echo "<table frame='border'>";
    echo '<tr>';
    echo '<td>';
    ButtonPanel($block);
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>';
    echo "<table frame='border'>";
    echo "<tr valign='top'>";
    echo '<td>';
    if ($_SESSION['Catalog']['1']['name'] != '') {
        ViewSetsCatalog();
    } else {
        ViewSingleCatalog('0', $_SESSION['cur_value']);
    }
    echo '</td>';
    echo '<td>';
    if ($_SESSION['Catalog']['1']['name'] != '') {
        ViewSingleCatalog('1', '');
    }
    echo '</td>';
    echo '<tr>';
    echo '</table>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
}
function FormForSelection($form_name, $text_name, $button_start_name, $prompt_start_text, $button_cancel_name, $prompt_cancel_text, $compare_mode_radio_name, $n, $text_value, $compare_mode, $img_start_name, $img_cancel_name, $match_case_name)
{
    $block = $_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'];
    echo "<table frame='border'>";
    echo '<tr>';
    echo '<td><b>'.$form_name.'</b></td>';
    echo "<td><input type='text' name='".$text_name."' autofocus size='".$_SESSION['w_08']."' value='".$_SESSION['catalog_param'][$n][$text_value]."'".SetExPar($block, '').'></td>';
    if ($_SESSION['catalog_param'][$n]['search_on'] && $_SESSION['catalog_param'][$n]['prev_search_out'] != '') {
        echo "<td><button name='cat_search_move|back|".$n."' type='submit' title='".Title(88)."' value='*'".SetExPar($block, '').'><</button></td>';
    }
    echo '</tr>';
    echo '<tr>';
    echo '<td>';
    echo "<table align='center'>";
    echo '<tr>';
    echo '<td>';
    echo "<button name='".$button_start_name."' type='submit' class='transparent' title='".$prompt_start_text."' value='*'".(($block) ? ' disabled' : '').'>'.ImgV($img_start_name, 16, 16).'</button>'; // image
    echo "<button name='".$button_cancel_name."' type='submit' class='transparent' title='".$prompt_cancel_text."' value='*'".(($block) ? ' disabled' : '').'>'.ImgV($img_cancel_name, 16, 16).'</button>'; // image
    echo '<td>';
    echo '</tr>';
    echo '</table>';
    echo '</td>';
    echo '<td>';
    echo '<table>';
    echo '<tr>';
    for ($i = 0; $i < 3; $i++) {
        echo '<td>';
        echo "<input type='radio' name='".$compare_mode_radio_name."' title='".$_SESSION['compare_mode'][$i]."' value=".(string) $i.(($block) ? ' disabled' : '').(($i == $_SESSION['catalog_param'][$n][$compare_mode]) ? ' checked' : '').'>';
        echo ImgV($_SESSION['compare_img'][$i], 36, 10);
        echo '</td>';
    }
    echo "<td><input type='checkbox' name='".$match_case_name."' title='".Title(162)."' value='*'".(($block) ? ' disabled' : '').(($_SESSION['match_case']) ? ' checked' : '').'></td>';
    echo '</tr>';
    echo '</table>';
    echo '</td>';
    if ($_SESSION['catalog_param'][$n]['search_on'] && $_SESSION['catalog_param'][$n]['next_search_out'] != '') {
        echo "<td><button name='cat_search_move|forward|".$n."' type='submit' title='".Title(89)."' value='*'".SetExPar($block, '').'>></button></td>';
    }
    echo '</tr>';
    echo '</table>';
}
