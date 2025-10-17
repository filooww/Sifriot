<head>
	<style>.pad_colors1 {background:#DDDDDD;}</style>
	<style>.pad_colors2 {background:#FFFFFF;}</style>
	<style>.order_button {background-color:#3366FF; color:#FFFFFF; font:bold;}</style>
	<style>.delete_button {background-color:#FF0000; color:#FFFFFF}</style>
	<style>.to_on {background-color:rgb(0,0,255); color:rgb(255,255,255)}</style>
	<style>.to_off {background-color:rgb(192,192,192); color:rgb(192,192,192);}</style>
	<style>.create_item {background-color:#33CC99;}</style>
	<style>.calend_bg {background-color:#CCCCFF;}</style>
	<style>.cur_text {background-color:#FFCC00;}</style>
</head>
<?php
function RememberSettings(&$p_var)
{
    foreach (array_keys($p_var) as $k) {
        SetModeSorting($k, $p_var);
        SetMainFilter($k, $p_var);
    }
}
function SetModeSorting($key_sort, &$p_var)
{
    if ($p_var[$key_sort]['sort']['sort_order'] < 0) {
        $n = -$p_var[$key_sort]['sort']['sort_order'];
        $p_var[$key_sort]['sort']['sort_order'] = 0;
        $p_var[$key_sort]['sort']['sm'] = 0;
        foreach (array_keys($p_var) as $k) {
            if ($p_var[$k]['sort']['sort_order'] > $n) {
                $p_var[$k]['sort']['sort_order']--;
            }
        }
    } elseif ($p_var[$key_sort]['sort']['sort_order'] == 0) {
        if (isset($_POST['sort-'.$key_sort])) {
            $p_var[$key_sort]['sort']['sort_order'] = MaxSortOrderNumber($p_var);
            $p_var[$key_sort]['sort']['sm'] = ($_POST['sort-'.$key_sort] == 'sort_asc-'.$key_sort) ? 1 : -1;
        }
    } elseif (isset($_POST['sort-'.$key_sort])) {
        $p_var[$key_sort]['sort']['sm'] = (($_POST['sort-'.$key_sort] == 'sort_asc-'.$key_sort) ? 1 : -1);
    }
}
function SetMainFilter($k, &$p_var)
{
    if (isset($_POST['filter_text-'.$k])) {
        $p_var[$k]['filter']['text'] = $_POST['filter_text-'.$k];
    }
    if (isset($_POST['filter_max-'.$k]) && $p_var[$k]['filter']['iv']) {
        $p_var[$k]['filter']['to'] = $_POST['filter_max-'.$k];
    }
    if (isset($_POST['filter_radio-'.$k]) && $p_var[$k]['filter']['md'] > -1) {
        $v_radio = str_replace('filter_radio-'.$k.'_', '', $_POST['filter_radio-'.$k]);
        if (is_numeric($v_radio)) {
            $p_var[$k]['filter']['md'] = (int) $v_radio;
        } else {
            $p_var[$k]['filter']['md'] = 1;
        }
    }
}
function MaxSortOrderNumber($p_var)
{
    $n_max = 0;
    foreach ($p_var as $v) {
        if ($v['sort']['sort_order'] > $n_max) {
            $n_max = $v['sort']['sort_order'];
        }
    }

    return $n_max + 1;
}
function OrderSortingButton($key_sort, &$p_var)
{
    if ($p_var[$key_sort]['sort']['sort_order'] > 0) {
        $p_var[$key_sort]['sort']['sort_order'] = -$p_var[$key_sort]['sort']['sort_order'];
        $p_var[$key_sort]['sort']['sm'] = 0;
    } else {
        $p_var[$key_sort]['sort']['sort_order'] = MaxSortOrderNumber($p_var);
        if ($p_var[$key_sort]['sort']['sm'] == 0) {
            $p_var[$key_sort]['sort']['sm'] = 1;
        }
    }
}
function TestedValue($k, $v, $p_var)
{
    $fl_test = ($v['interval'] && $p_var[$k]['filter']['iv']) ? ($p_var[$k]['filter']['text'] != '' || $p_var[$k]['filter']['to'] != '') : ($p_var[$k]['filter']['text'] != '');

    return $fl_test;
}
function ValuesForCompare($v, $test_value, $filter_value, $filter_to_value, &$Mes)
{
    if ($v['type'] == 'integer') {
        $compare = ['test' => (int) $test_value, 'filter' => (int) $filter_value, 'filter_to' => (int) $filter_to_value];
    } elseif ($v['type'] == 'date') {
        $compare = ['test' => StringToDateFormat($test_value, $Mes), 'filter' => StringToDateFormat($filter_value, $Mes), 'filter_to' => StringToDateFormat($filter_to_value, $Mes)];
    } else {
        $compare = ['test' => $test_value, 'filter' => $filter_value, 'filter_to' => $filter_to_value];
    }

    return $compare;
}
function CaseCompare($filter_value, $filter_to_value)
{
    if ($filter_value == '' && $filter_to_value != '') {
        return 1;
    } elseif ($filter_value != '' && $filter_to_value == '') {
        return 2;
    } elseif ($filter_value != '' && $filter_to_value != '') {
        return 3;
    } else {
        return 0;
    }
}
function TestPubForFilter($PR, $p_code, $item_row, &$Mes)
{
    foreach ($main_params['const'] as $k => $v) {
        if (TestedValue($k, $v, $PR['var'])) {
            $m_case = $_SESSION['match_case'] || ! $v['low'];
            $test_value = ($v['key']) ? $p_code : MCV($item_row[$k], $m_case);
            $filter_value = MCV($main_params['var'][$k]['filter']['text'], $m_case);
            $filter_to_value = MCV($main_params['var'][$k]['filter']['to'], $m_case);
            $compare = ValuesForCompare($v, $test_value, $filter_value, $filter_to_value, $date_mes);
            if ($v['interval'] && $main_params['var'][$k]['filter']['iv']) {
                switch (CaseCompare($filter_value, $filter_to_value)) {
                    case 1:	if ($compare['test'] > $compare['filter_to']) {
                        return false;
                    } break;
                    case 2:	if ($compare['test'] < $compare['filter']) {
                        return false;
                    } break;
                    case 3: if ($compare['test'] < $compare['filter'] || $compare['test'] > $compare['filter_to']) {
                        return false;
                    } break;
                }
            } elseif ($compare['test'] != $compare['filter']) {
                return false;
            }
        }
    }
    foreach ($date_mes as $v) {
        $Mes[] = ['time' => '', 'text' => $v, 'status' => 'warning'];
    }

    return true;
}
function ApplySettings($dbh, $main_table, &$p_code, &$item_arr, &$start_pos, &$p_count_filter, &$Mes, $user_id = '')
{
    $p_count_filter = GetMTLimit($dbh, $main_table, 'filter');
    if (isset($item_arr[(string) $p_code])) {
        if (TestPubForFilter($_SESSION['main_params'], (string) $p_code, $item_arr[(string) $p_code], $Mes)) {
            $pos_p_code = GetItemOffset($dbh, $main_table, $_SESSION['main_params'], $p_code, $item_arr[(string) $p_code]);
            $i_pos = array_search((string) $p_code, array_keys($item_arr));
            $start_pos = ($pos_p_code == -1) ? 0 : max($pos_p_code - $i_pos, 0);
            $item_arr = GetMTPortion($dbh, $main_table, $start_pos, $_SESSION['portion'], $_SESSION['main_params'], ($user_id == '') ? 'all' : 'active');
        } else {
            $start_pos = 0;
            $item_arr = GetMTPortion($dbh, $main_table, $start_pos, $_SESSION['portion'], $_SESSION['main_params'], ($user_id == '') ? 'all' : 'active');
            if (count($item_arr) > 0) {
                $p_code = (int) GetFirstKey($item_arr);
            }
        }
    } else {
        if ($p_count_filter == 0) {
            $p_code = GetInitItemCode($dbh, $main_table, $_SESSION['main_params']['const']);
            $start_pos = 0;
        }
        $item_arr = GetMTPortion($dbh, $main_table, $start_pos, $_SESSION['portion'], $_SESSION['main_params'], ($user_id == '') ? 'all' : 'active');
    }
}
function ResetSettings($dbh, $modes, &$p_var)
{
    foreach ($p_var as $k => $v) {
        if ($modes[0]) {
            $p_var[$k]['sort']['sort_order'] = 0;
            $p_var[$k]['sort']['sm'] = 0;
        }
        if ($modes[1]) {
            $p_var[$k]['filter']['text'] = '';
            $p_var[$k]['filter']['to'] = '';
            $p_var[$k]['filter']['md'] = 1;
            $p_var[$k]['filter']['iv'] = false;
        }
    }
}
function InputCase($k, $v, $v_con_k, $block, $sel_align)
{
    $str = "<input type='text' name='filter_text-".$k."' size='".(string) $v_con_k['size']."' style='".$sel_align[$v_con_k['f_align']]."' value='".$v['filter']['text']."'".$block.'>';
    if ($v_con_k['type'] == 'date') {
        $str .= "<button name='to_calendar-".$k."' type='submit' title='".Title(57)."' value='*'".$block.'>'.ImgV('Calendar', 13, 10).'</button>';
    }
    if ($v_con_k['s_mode']) {
        if ($v_con_k['table'] == '') {
            $str .= "<button name='dummy-".$k."' type='submit' value='*'".$block.' disabled>...</button>';
        } else {
            $str .= "<button name='to_catalog-".$k."' type='submit' title='".Title(58)."' value='*'".$block.'>...</button>';
        }
    }
    if ($v_con_k['interval']) {
        $to_class = ($v['filter']['iv']) ? 'to_on' : 'to_off';
        $str .= "<button name='filter_to-".$k."' type='submit' class='".$to_class."' title='".Title(59)."' value='*'".$block.'>></button>';
        if ($v['filter']['iv']) {
            $str .= "<input type='text' autofocus name='filter_max-".$k."' size='".(string) $v_con_k['size']."' style='".$sel_align[$v_con_k['f_align']]."' value='".$v['filter']['to']."'".$block.'>';
            if ($v_con_k['type'] == 'date') {
                $str .= "<button name='to_calendar_to-".$k."' type='submit' title='".Title(57)."' value='*'".$block.'>'.ImgV('Calendar', 13, 10).'</button>';
            }
        }
    }

    return $str;
}
function MenuPart($block)
{
    echo "<table frame='border' cellpadding='0'>";
    echo '<tr>';
    echo "<td><button name='apply_settings' type='submit' title='".FTM(Title(45))."' class='create_item' value='*' style='text-align:center'".$block.'>'.Title(45).'</button></td>';
    echo "<td><button name='cancel_sort' type='submit' title='".FTM(Title(60))."' class='delete_button' value='*' style='text-align:center'".$block.'>'.Title(60).'</button></td>';
    echo "<td><button name='cancel_filter' type='submit' title='".FTM(Title(61))."' class='delete_button' value='*' style='text-align:center'".$block.'>'.Title(61).'Reset selection</button></td>';
    echo '</tr>';
    echo '</table>';
}
function SortPart($block, $k, $v_sort, $sort_mode)
{
    echo '<table>';
    echo '<tr>';
    echo "<td><button name='sort_order-".$k."' type='submit' class='order_button' title='".Title(62)."' value='*'".$block.'>'.(string) $v_sort['sort_order'].'</button></td>';
    echo "<td><input type='radio' name='sort-".$k."' title='".FTM(Title(63))."' value='sort_asc-".$k."'".(($v_sort['sm'] == 1) ? ' checked' : '').$block.'>'.$sort_mode[1].'</td>'; // RadioTag
    echo "<td><input type='radio' name='sort-".$k."' title='".FTM(Title(64))."' value='sort_desc-".$k."'".(($v_sort['sm'] == -1) ? ' checked' : '').$block.'>'.$sort_mode[2].'</td>'; // RadioTag
    echo '</tr>';
    echo '</table>';
}
function FilterPart($block, $k, $v, $v_con_k, $mode_texts, $sel_align)
{
    echo '<table>';
    echo "<tr valign='middle'>";
    echo '<td>'.InputCase($k, $v, $v_con_k, $block, $sel_align).'</td>';
    if ($v_con_k['s_mode']) {
        for ($i = 0; $i < count($mode_texts); $i++) {
            echo "<td><input type='radio' ".RadioNameValue($k, $i)."'".(($v['filter']['md'] == $i) ? ' checked' : '').$block.'>'.$mode_texts[$i].'</td>';
        }
    } // RadioTag
    echo '</tr>';
    echo '</table>';
}
function CalendPart($start_year, $cury, $k, $calend_date_f, &$calend_mes)
{
    echo '<tr>';
    echo '<td></td>';
    echo '<td></td>';
    echo "<td class='calend_bg'>";
    CalendEnterDate($start_year, $cury, $k, $calend_date_f);
    echo '</td>';
    echo '</tr>';
    foreach ($calend_mes as $v) {
        echo '<tr>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td>'.$v.'</td>';
        echo '</tr>';
        $calend_mes = [];
    }
}
function ListSettingsPad($user_id = '')
{
    $block = ($_SESSION['block']['item_del'] || $_SESSION['block']['pad_cat']) ? ' disabled' : '';
    echo '<table>';
    echo "<tr valign='top'>";
    echo "<td widht='".$_SESSION['w_09']."'>";
    MenuPart($block);
    echo "<table frame='border' cellpadding='0'>";
    $row_cl = 'pad_colors1';
    foreach ($_SESSION['main_params']['var'][1] as $k => $v) {
        if ($_SESSION['main_params']['const'][1][$k]['screen_order'] > 0 && ($user_id == '' || $user_id != '' && in_array('4', explode(',', $_SESSION['main_params']['const'][1][$k]['using'])))) { // view
            echo '<tr>';
            echo "<td class='".$row_cl."'>".$_SESSION['main_params']['const'][1][$k]['name'].'</td>';
            echo "<td class='".$row_cl."'>";
            SortPart($block, $k, $v['sort'], $_SESSION['sort_mode']);
            echo '</td>';
            echo "<td class='".$row_cl."'>";
            FilterPart($block, $k, $v, $_SESSION['PR']['const'][$k], $_SESSION['compare_mode'], $_SESSION['field_align']);
            echo '</td>';
            $row_cl = ($row_cl == 'pad_colors1') ? 'pad_colors2' : 'pad_colors1';
            echo '</tr>';
            if ($_SESSION['main_params']['const'][1][$k]['type'] == 'date' && $_SESSION['calend_date'][$k]['id_filter'] != '') {
                CalendPart($_SESSION['conf']['start_year'], $_SESSION['cury'], $k, $_SESSION['calend_date'][$k], $_SESSION['calend_mes']);
            }
        }
    }
    echo '</table>';
    echo '</td>';
    echo '<td>';
    if ($_SESSION['cat_flag']) {
        ViewCatalogForSelect($_SESSION['cat_vis'], $_SESSION['conf'], $_SESSION['block'], $_SESSION['cat_tree'], $_SESSION['radio_modes'], $_SESSION['compare_mode'], $_SESSION['cur_value'], 'cur_text');
    }
    echo '</td>';
    echo '</tr>';
    echo '</table>';
}
function RadioNameValue($k, $i)
{
    $name = 'filter_radio-'.$k;

    return "name='".$name."' value='".$name.'_'.(string) $i;
}
function GetInitItemCode($dbh, $main_table, $p_con, $inact_mode = false)
{
    if ($_SESSION['spec_fld'][1]['key'] != '' && $_SESSION['spec_fld'][1]['del_mark'] != '') {
        if ($inact_mode) {
            $res = mysqli_query($dbh, 'SELECT '.$_SESSION['spec_fld'][1]['key'].' FROM '.$main_table.' WHERE '.$_SESSION['spec_fld'][1]['del_mark'].' = 1 LIMIT 1');
        }
        $res = mysqli_query($dbh, 'SELECT '.$_SESSION['spec_fld'][1]['key'].' FROM '.$main_table.' LIMIT 1');
        if ($res) {
            if ($row = mysqli_fetch_row($res)) {
                $row[0];
            } else {
                $init_code = 0;
            }
            mysqli_free_result($res);

            return $init_code;
        }

        return 0;
    } else {
        return 0;
    }
}
function SortFind()
{
    foreach ($_SESSION['calend_date'] as $k => $v) {
        $v['id_filter'] = '';
        $v['value'] = '';
    }
    $_SESSION['set_pad'] = ! $_SESSION['set_pad'];
    $_SESSION['block']['pad'] = ! $_SESSION['block']['pad'];
    $_SESSION['cur_cat'] = '';
    if (! $_SESSION['set_pad']) {
        RememberSettings($s['PR']['var']);
    }
}

?>
