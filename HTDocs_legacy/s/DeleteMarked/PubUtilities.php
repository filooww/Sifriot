<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/TestScripts.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Calendar.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LogProcessing/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/MainTable/CommonUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/MainTable/MTRequests.php';

function DeletePub($dbh, $main_table, $attach, $PR, &$start_pos, $conf, &$p_code, &$p_count, &$item_arr, $URL_path)
{
    $k_f = GetKeyField($PR['con'], $main_table);
    $d_m = GetSpecialField($main_table, 'del_mark');
    if ($k_f != '' && $d_m != '') {
        $arr_keys = array_keys($item_arr);
        $i = array_search((string) $p_code, $arr_keys);
        if ($i < count($arr_keys) - 1) {
            $n_code = (int) $arr_keys[$i + 1];
        } elseif (count($arr_keys) < $_SESSION['portion']) {
            if ($i > 0) {
                $n_code = (int) $arr_keys[$i - 1];
            } else {
                $n_code = 0;
                $start_pos--;
            }
        } else {
            $n_code = (int) $arr_keys[$i - 1];
        }
        mysqli_query($dbh, 'DELETE FROM '.$attach.' WHERE '.$k_f.' = '.(string) $p_code);
        mysqli_query($dbh, 'DELETE FROM '.$main_table.' WHERE '.$d_m.' = 1');
        mysqli_query($dbh, 'ALTER TABLE '.$main_table.' AUTO_INCREMENT = 1');
        if (mysqli_errno($dbh) == 0) {
            DeleteFilesDir($URL_path, $p_code, []);
            $p_count['total']--;
            $item_arr = GetMTPortion($dbh, $main_table, $start_pos, $_SESSION['portion'], $PR, 'inactive');
            if ($n_code == 0) {
                if (count($item_arr) > 0) {
                    $item_keys = array_keys($item_arr);
                    $p_code = (int) $item_keys[0];
                } else {
                    $p_code = 0;
                }
            } else {
                $p_code = $n_code;
            }

            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function DeleteAllMarked($dbh, $main_table, $attach, $p_con, $URL_path, &$p_count, &$item_arr, &$p_code)
{
    $k_f = GetKeyField($p_con, $main_table);
    $d_m = GetSpecialField($main_table, 'del_mark');
    if ($k_f != '' && $d_m != '') {
        $res = mysqli_query($dbh, 'SELECT '.$k_f.' FROM '.$main_table.' WHERE '.$d_m.' = 1');
        if ($res) {
            $arr_del = [];
            while ($row = mysqli_fetch_row($res)) {
                $arr_del[] = (string) $row[0];
            }
            mysqli_free_result($res);
            if (count($arr_del) > 0) {
                mysqli_query($dbh, 'DELETE FROM '.$attach.' WHERE '.$k_f.' IN ('.implode(',', $arr_del).')');
                mysqli_query($dbh, 'DELETE FROM '.$main_table.' WHERE '.$d_m.' = 1');
                mysqli_query($dbh, 'ALTER TABLE '.$main_table.' AUTO_INCREMENT = 1');
                DeleteFilesList($URL_path, $arr_del);
                $p_count['total'] -= count($arr_del);
                $p_count['inactive'] = 0;
                $item_arr = [];
                $p_code = 0;
            }
        }
    }
}
function DeleteFilesList($del_dir, $item_id_arr)
{
    $dir = opendir($del_dir);
    if ($dir) {
        while (($file = readdir($dir)) !== false) {
            $arr_f = explode('-', pathinfo($file, PATHINFO_FILENAME));
            $cnt = count($arr_f);
            if ($cnt > 1 && in_array($arr_f[0], $item_id_arr)) {
                unlink($del_dir.'/'.$file);
            }
        }
        closedir($dir);
    }
}
function RestorePub($dbh, $main_table, $rest_code, &$p_code, &$start_pos, $PR, &$item_arr, &$p_count)
{
    $k_f = GetKeyField($p_con, $main_table);
    $d_m = GetSpecialField($main_table, 'del_mark');
    if ($k_f != '' && $d_m != '') {
        mysqli_query($dbh, 'UPDATE '.$main_table.' SET '.$d_m.' = 0 WHERE '.$k_f.' = '.$rest_code);
        $p_code = GetInitItemCode($dbh, $main_table, $PR['con'], true);
        $start_pos = 0;
        $item_arr = GetMTPortion($dbh, $main_table, $start_pos, $_SESSION['portion'], $PR, 'inactive');
        $p_count['active']++;
        $p_count['inactive']--;
    }
}
