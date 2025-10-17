<?php

function InsMarked($dbh, $to_inf, $from_code, $from_text)
{
    if (! $to_inf['b_on']) {
        $_SESSION['mes']['0'][] = ['time' => '', 'text' => Title(495), 'status' => 'warning', 'now' => true];
    } elseif (! array_key_exists($to_inf['bc'], $_SESSION['cat_arr'])) {
        $_SESSION['mes']['0'][] = ['time' => '', 'text' => Title(496), 'status' => 'warning', 'now' => true];
    } elseif ($_SESSION['cat_arr']['1'][$from_code][2]) {
        $_SESSION['mes']['1'][] = ['time' => '', 'text' => Title(497), 'status' => 'error', 'now' => true];
    } elseif ($_SESSION['cat_arr']['1'][$from_code][1]) {
        $_SESSION['mes']['1'][] = ['time' => '', 'text' => Title(498), 'status' => 'error', 'now' => true];
    } else {
        $ed_codes = ($_SESSION['cat_arr']['0'][$to_inf['bc']][3] == '') ? [] : explode(',', $_SESSION['cat_arr']['0'][$to_inf['bc']][3]);
        if (! in_array($from_code, $ed_codes)) {
            $ed_values = (count($ed_codes) == 0) ? [] : explode($_SESSION['Catalog']['0']['separator'], $_SESSION['cat_arr']['0'][$to_inf['bc']][4]);
            $ed_codes[] = $from_code;
            $ed_values[] = $from_text;
            if ($_SESSION['Catalog']['0']['cat_type'] == 2) {
                array_multisort($ed_values, $ed_codes, SORT_STRING);
            }
            $_SESSION['cat_arr']['0'][$to_inf['bc']][1] = true;
            $_SESSION['cat_arr']['0'][$to_inf['bc']][3] = implode(',', $ed_codes);
            $_SESSION['cat_arr']['0'][$to_inf['bc']][4] = implode($_SESSION['Catalog']['0']['separator'], $ed_values);
            TestCatalogs($dbh);
        }
    }
}
function DelFromSetList($dbh, &$to_inf, $from_code)
{
    $ed_codes = explode(',', $_SESSION['cat_arr']['0'][$to_inf['bc']][3]);
    $nn = array_search($from_code, $ed_codes);
    if ($nn !== false) {
        $ed_values = explode($_SESSION['Catalog']['0']['separator'], $_SESSION['cat_arr']['0'][$to_inf['bc']][4]);
        if (count($ed_codes) == 1) {
            $to_inf['b_on'] = false;
            if (is_numeric($to_inf['bc'])) {
                $_SESSION['del_row']['0'][$to_inf['bc']] = ['old_id' => $cat_arr['0'][$to_inf['bc']][3], 'old_value' => $_SESSION['cat_arr']['0'][$to_inf['bc']][4]];
            }
        }
        unset($ed_codes[$nn], $ed_values[$nn]);
        $_SESSION['cat_arr']['0'][$to_inf['bc']][1] = (count($ed_codes) > 0 || is_numeric($to_inf['bc']));
        $_SESSION['cat_arr']['0'][$to_inf['bc']][3] = implode(',', $ed_codes);
        $_SESSION['cat_arr']['0'][$to_inf['bc']][4] = implode($_SESSION['Catalog']['0']['separator'], $ed_values);
        TestCatalogs($dbh);
    }
}
function DelHierarchic($dbh, $del_code)
{
    if ($_SESSION['cat_arr']['0'][$del_code][4] != '') {
        $_SESSION['del_row']['0'][$del_code] = ['old_id' => $_SESSION['cat_arr']['0'][$del_code][3], 'old_value' => $_SESSION['cat_arr']['0'][$del_code][4]];
        $sons_inner = GetSonsArray($_SESSION['cat_arr']['0'], $_SESSION['cat_arr']['0'][$del_code][3]);
        $_SESSION['cat_arr']['0'][$del_code] = [false, true, false, '', ''];
        if (count($sons_inner) > 0) {
            foreach ($sons_inner as $k => $v) {
                $_SESSION['del_row']['0'][$k] = ['old_id' => $v[0], 'old_value' => $v[1]];
                $_SESSION['cat_arr']['0'][$k] = [false, true, false, '', ''];
            }
        }
        $sons_outer = GetNodeSons($dbh, $sons_inner);
        foreach ($sons_inner as $k => $v) {
            $_SESSION['del_row']['0'][$k] = ['old_id' => $v[0], 'old_value' => $v[1]];
        }
    }
    TestCatalogs($dbh);
}
function SetOnOffButton(&$button_p, $a_code)
{
    if ($a_code == $button_p['bc']) {
        $button_p['b_on'] = ! $button_p['b_on'];
        if (! $button_p['b_on']) {
            $button_p['bc'] = '';
        }
    } else {
        $button_p['b_on'] = true;
        $button_p['bc'] = $a_code;
    }
}
function CatFill($dbh)
{
    if (! IsChangesAndErrors('1', Title(482).' ', '')) { // [] = array("time"=>"", "text"=>"To fill coupled catalog You must save the changes on these pages", "status"=>"warning", "log"=>false);
        $cnt = 0;
        $res = mysqli_query($dbh, 'SELECT * FROM '.$_SESSION['Catalog']['1']['table']);
        if ($res) {
            while ($row = mysqli_fetch_row($res)) {
                mysqli_query($dbh, 'ALTER TABLE '.$_SESSION['Catalog']['0']['table'].' AUTO_INCREMENT = 1');
                $resSet = mysqli_query($dbh, 'SELECT COUNT(*) FROM '.$_SESSION['Catalog']['0']['table'].' WHERE '.$_SESSION['Catalog']['0']['value']." = '".(string) $row[0]."'");
                if ($resSet) {
                    if ($rowSet = mysqli_fetch_row($resSet)) {
                        if ($rowSet[0] == 0) {
                            mysqli_query($dbh, 'INSERT INTO '.$_SESSION['Catalog']['0']['table'].' ('.$_SESSION['Catalog']['0']['value'].") VALUES ('".(string) $row[0]."')");
                            if (mysqli_errno($dbh) == 0) {
                                $cnt++;
                            }
                        }
                    } else {
                        mysqli_query($dbh, 'INSERT INTO '.$_SESSION['Catalog']['0']['table'].' ('.$_SESSION['Catalog']['0']['value'].") VALUES ('".(string) $row[0]."')");
                        if (mysqli_errno($dbh) == 0) {
                            $cnt++;
                        }
                    }
                    mysqli_free_result($resSet);
                } else {
                    mysqli_query($dbh, 'INSERT INTO '.$_SESSION['Catalog']['0']['table'].' ('.$_SESSION['Catalog']['0']['value'].") VALUES ('".(string) $row[0]."')");
                    if (mysqli_errno($dbh) == 0) {
                        $cnt++;
                    }
                }
            }
            mysqli_free_result($res);
        }
        InitCatalogParameters('0');
        if ($cnt > 0) {
            SaveMaxLevel($dbh, 1);
        }
        $_SESSION['cat_arr']['0'] = GetCatalogPortion($dbh, '0', $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion']);
        $_SESSION['catalog_param']['0']['total_count'] = GetTableLimit($dbh, 'total', '0');
        if ($_SESSION['Catalog']['0']['cat_type'] == 1) {
            $_SESSION['catalog_param']['0']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['0']['total_count']);
        }
        $_SESSION['mes']['c'][] = ['time' => '', 'text' => '<b>'.(string) $cnt.'</b> '.Title(499).' <b>'.$_SESSION['Catalog']['1']['name'].'</b> '.Title(500).' <b>'.$_SESSION['Catalog']['0']['name'].'</b>', 'status' => 'statement', 'now' => true];
    }
}
