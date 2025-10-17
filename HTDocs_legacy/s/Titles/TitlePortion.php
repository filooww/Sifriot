<?php

function GetTitlePortion($dbh, $nav = '')
{
    $title_param = [];
    $ord = ($_SESSION['view_title_mode'][0] == 2) ? 'title_text, id_title, id_language' : 'id_title, id_language, title_text';
    $res = mysqli_query($dbh, 'SELECT * FROM interface_texts'.$_SESSION['title_filter'].' ORDER BY '.$ord.' LIMIT '.(string) $_SESSION['start'].','.(string) $_SESSION['portion']);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $title_param[TitleKey($row[0], $row[1])] = [$row[2], '', (string) $row[0], $row[1]];
        }
        mysqli_free_result($res);
        if ($_SESSION['selected_title_lang'][0] == 0 && $_SESSION['view_title_mode'][0] != 2 && count($title_param) > 0 && substr($nav, 0, 2) != 'ln') {
            $w_w = ($_SESSION['title_filter'] == '') ? 'WHERE' : 'AND';
            if ($_SESSION['start'] > 0) {
                $shift_back = TitleExtentBack($dbh, $w_w, $title_param);
                $_SESSION['start'] -= $shift_back;
                if (count($title_param) > $_SESSION['portion']) {
                    $_SESSION['portion'] += $shift_back;
                }
            }
            if (count($title_param) > 0) {
                TitleExtentForward($dbh, $w_w, $title_param);
            }
        }
        if (count($title_param) > $_SESSION['portion']) {
            $_SESSION['portion'] = count($title_param);
        }
    }

    return $title_param;
}
function TitleExtentBack($dbh, $w_w, &$title_param)
{
    $lim_key = GetFirstKey($title_param);
    $sh = 0;
    $res = mysqli_query($dbh, 'SELECT * FROM interface_texts'.$_SESSION['title_filter'].' '.$w_w.' id_title = '.(string) TitlePartKey($lim_key).' AND id_language < '.(string) TitlePartKey($lim_key, 1).' ORDER BY id_language');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            if (! array_key_exists(TitleKey($row[0], $row[1]), $title_param)) {
                $title_param[TitleKey($row[0], $row[1])] = [$row[2], '', $row[0], $row[1]];
                $sh++;
            }
        }
        mysqli_free_result($res);
        if ($sh > 0) {
            ksort($title_param);
            $_SESSION['start'] -= $sh;
            if (count($title_param) > $_SESSION['portion']) {
                $cur_key = GetLastKey($title_param);
                $t_id = TitlePartKey($cur_key);
                do {
                    unset($title_param[$cur_key]);
                    $cur_key = GetLastKey($title_param);
                } while (TitlePartKey($cur_key) == $t_id);
            }
        }
    }
}
function TitleExtentForward($dbh, $w_w, &$title_param)
{
    $lim_key = GetLastKey($title_param);
    $res = mysqli_query($dbh, 'SELECT * FROM interface_texts'.$_SESSION['title_filter'].' '.$w_w.' id_title = '.(string) TitlePartKey($lim_key).' AND id_language > '.(string) TitlePartKey($lim_key, 1).' ORDER BY id_language');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            if (! array_key_exists(TitleKey($row[0], $row[1]), $title_param)) {
                $title_param[TitleKey($row[0], $row[1])] = [$row[2], '', $row[0], $row[1]];
            }
        }
        mysqli_free_result($res);
    }
}
