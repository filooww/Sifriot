<?php

function TestSpecialType($special_type, $save)
{
    $r_type = in_array($special_type, array_keys($_SESSION['spec_titles']));
    for ($i = 0; $i < count($_SESSION['special_interface'][$special_type]['numbers']); $i++) {
        if ($r_type) {
            $_SESSION['special_interface'][$special_type]['numbers'][$i][1] = TestSpecialNumber($_SESSION['special_interface'][$special_type]['numbers'][$i][0]);
            if (! $save && $_SESSION['special_interface'][$special_type]['numbers'][$i][1] > 0 || $save && $_SESSION['special_interface'][$special_type]['numbers'][$i][1] > 1) {
                return false;
            }
        }
    }

    return true;
}
function TestSpecialTexts($save)
{
    foreach (array_keys($_SESSION['special_interface']) as $k) {
        if (! TestSpecialType($k, $save)) {
            return false;
        }
    }

    return true;
}
function GetSpecialNumbers($dbh)
{
    $spec_numbers = [];
    $res = mysqli_query($dbh, 'SELECT * FROM interface_special_texts');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $spec_numbers[$row[0]]['illegal'] = ! isset($_SESSION['spec_titles'][$row[0]]);
            $spec_numbers[$row[0]]['missed'] = false;
            if (strlen($row[1]) == 0) {
                $spec_numbers[$row[0]]['numbers'] = [];
            } else {
                $str_arr = explode(',', $row[1]);
                for ($i = 0; $i < count($str_arr); $i++) {
                    $spec_numbers[$row[0]]['numbers'][] = [$str_arr[$i], TestSpecialNumber($str_arr[$i])];
                }
            }
        }
        mysqli_free_result($res);
    }
    foreach (array_keys($_SESSION['spec_titles']) as $k_type) {
        if (! isset($spec_numbers[$k_type]['illegal'])) {
            $spec_numbers[$k_type]['illegal'] = false;
            $spec_numbers[$k_type]['missed'] = true;
            $spec_numbers[$k_type]['numbers'] = [];
        }
    }

    return $spec_numbers;
}
function SwitchSpecialTexts($dbh, &$Mes)
{
    if (isset($_SESSION['special_interface']) && count($_SESSION['special_interface']) > 0) {
        if (TestSpecialTexts(true)) {
            if ($_SESSION['user_working_mode'] == 1) {
                SaveSpecialTexts($dbh);
            }
            $_SESSION['special_interface'] = [];
            $_SESSION['special_interface_copy'] = [];
            $_SESSION['spec_title'] = Title(465);
        } else {
            $Mes[] = "<font color='#FF0000'><b>".Title(471).'</b></font>';
        }
    } else {
        $_SESSION['special_interface'] = GetSpecialNumbers($dbh);
        $_SESSION['special_interface_copy'] = $_SESSION['special_interface'];
        if ($_SESSION['user_working_mode'] == 0) {
            $_SESSION['spec_title'] = Title(640);
        } else {
            $_SESSION['spec_title'] = Title(466);
        }
    }
}
function FindSpecialNumber($sn)
{
    foreach ($_SESSION['special_interface_copy'] as $k_type => $v) {
        foreach ($v['numbers'] as $z) {
            if (abs($z[0]) == $sn && $z[1] == 3) {
                return false;
            }
        }
    }

    return true;
}
function GetSpecialTexts($dbh, $special_type)
{
    $arr = [];
    $res = mysqli_query($dbh, "SELECT * FROM interface_special_texts WHERE special_type = '".$special_type."'");
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $str_arr = explode(',', $row[1]);
            foreach ($str_arr as $n) {
                if ((int) $n < 0) {
                    $arr[] = FTM(Title(-(int) $n));
                } elseif ((int) $n > 0) {
                    $arr[] = Title((int) $n);
                } else {
                    $arr[] = '';
                }
            }
            mysqli_free_result($res);
        }
    }

    return $arr;
}
function SaveSpecialTexts($dbh)
{
    $arr_ins = [];
    foreach ($_SESSION['special_interface'] as $k => $v) {
        if (! $v['illegal']) {
            $arr = [];
            foreach ($v['numbers'] as $z) {
                if ($z[1] == 0) {
                    $arr[] = $z[0];
                }
            }
            if (count($arr) == 0) {
                $arr_ins[] = "('".$k."','')";
            } else {
                $arr_ins[] = "('".$k."','".implode(',', $arr)."')";
            }
        }
    }
    if (count($arr_ins) > 0) {
        mysqli_query($dbh, 'DELETE FROM interface_special_texts');
        mysqli_query($dbh, 'INSERT INTO interface_special_texts VALUES '.implode(',', $arr_ins));
    }
}
function TitleTitles($spec_num)
{
    $n_t = abs((int) $spec_num);
    if (isset($_SESSION['titles'][$n_t])) {
        if ((int) $spec_num < 0) {
            return FTM(Title($n_t));
        }

        return Title($n_t);
    } else {
        return '';
    }
}
function SpecialTextRow($spec_type, $v, $block_flag, $add_button, $col0)
{
    if (! $block_flag) {
        $dis = ($_SESSION['user_working_mode'] == 0) ? ' disabled' : '';
        if ($add_button == '' || $v['illegal']) {
            echo "<td width='1%'></td>";
        } else {
            echo "<td width='1%'><button name='".$add_button."' title='".Title(456)."' type='submit' value='*'".$dis.'>>></button></td>';
        }
    }
    $last_number = min(count($v['numbers']), $col0 + $_SESSION['max_table_columns']);
    $flag_same = ($_SESSION['new_special_number'] == $spec_type);
    for ($i = $col0; $i < $last_number; $i++) {
        $cl = ($v['numbers'][$i][1] > 0) ? 'special_texts' : 'data_numeric';
        $auto = ($flag_same && $i == count($v['numbers']) - 1) ? ' autofocus' : '';
        if ($v['numbers'][$i][1] == 1) {
            $title = Title(455).' ('.Title(581).')';
        } elseif ($v['numbers'][$i][1] == 2) {
            $title = Title(458);
        } elseif ($v['numbers'][$i][1] == 3) {
            $title = Title(459);
        } else {
            $title = TitleTitles($v['numbers'][$i][0]);
        }
        echo "<td width='3%'><input type='text' size='4' title='".$title."'".$auto." name='".$spec_type.'|'.(string) $i."' class='".$cl."' value='".$v['numbers'][$i][0]."'".(($block_flag || $v['illegal']) ? ' disabled' : '').'></td>';
    }
    while ($i < $_SESSION['max_table_columns']) {
        echo '<td></td>';
        $i++;
    }
    echo '<td></td>';
}
function TestSpecialNumber($tested_value)
{
    if ($tested_value == '') {
        return 1;
    } elseif (! is_numeric($tested_value)) {
        return 2;
    } elseif ((int) $tested_value != 0) {
        $n_t = abs((int) $tested_value);
        if (! isset($_SESSION['titles'][$n_t]) || isset($_SESSION['titles'][$n_t]) && $_SESSION['titles'][$n_t] == 'Missing text '.(string) $n_t) {
            return 3;
        } else {
            return 0;
        }
    }

    return 0;
}
function AllSpecialTitleNumbers($dbh)
{
    $arr_res = [];
    $res = mysqli_query($dbh, 'SELECT special_numbers FROM interface_special_texts');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            if ($row[0] != '') {
                $arr = explode(',', $row[0]);
                if (count($arr) > 0) {
                    foreach ($arr as $z) {
                        if (is_numeric($z) && $z != '0' && ! in_array(abs((int) $z), $arr_res)) {
                            $arr_res[] = abs((int) $z);
                        }
                    }
                }
            }
        }
        mysqli_free_result($res);
    }

    return $arr_res;
}
function SpecialNumbersChunk()
{
    $spec_num_arr = [];
    $spec_keys = ['table_types', 'compare_mode', 'sort_mode', 'field_align', 'field_using', 'field_types', 'group_types', 'z_o'];
    foreach ($spec_keys as $special_type) {
        $spec_num_arr[$special_type] = array_chunk($_SESSION['special_interface'][$special_type]['numbers'], $_SESSION['max_table_columns']);
    }

    return $spec_num_arr;
}
