<?php

function SetTitleFilterPosition($dbh)
{
    $old_filter = $_SESSION['title_filter'];
    $_SESSION['title_filter'] = TitleCommonFilter($dbh);
    if ($_SESSION['title_filter'] != $old_filter) {
        $_SESSION['title_count'] = TitleCounts($dbh);
        StartTitlePositionByFilter($dbh);

        return true;
    }

    return false;
}
function TitleFilter($dbh)
{
    if (SetTitleFilterPosition($dbh)) {
        $_SESSION['title_param'] = GetTitlePortion($dbh);
        TestTitles($dbh);
    }
}
function TitleDoubleList($dbh)
{
    $d_list = [];
    $res = mysqli_query($dbh, "SELECT GROUP_CONCAT(CONCAT_WS('_', id_title, id_language)) FROM interface_texts WHERE title_text <> '' GROUP BY title_text HAVING COUNT(*) > 1");
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $arr_text = explode(',', $row[0]);
            foreach ($arr_text as $ids) {
                $arr_ids = explode('_', $ids);
                $d_list[] = 'id_title = '.$arr_ids[0].' AND id_language = '.$arr_ids[1];
            }
        }
        mysqli_free_result($res);
    }

    return $d_list;
}
function UnusedTitles($dbh)
{
    $unused_titles = [];
    $_SESSION['scripts_title_ids'] = [];
    TitleByDirs($_SERVER['DOCUMENT_ROOT'].'/s');
    $_SESSION['ex_title_ids'] = AllSpecialTitleNumbers($dbh);
    $res = mysqli_query($dbh, 'SELECT DISTINCT id_title FROM interface_texts');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            if (! in_array($row[0], $_SESSION['scripts_title_ids']) && ! in_array($row[0], $_SESSION['ex_title_ids'])) {
                $unused_titles[] = (string) $row[0];
            }
        }
        mysqli_free_result($res);
    }

    return $unused_titles;
}
function TitleCommonFilter($dbh)
{
    $arr_filter = [];
    if ($_SESSION['selected_title_lang'][0] != 0) {
        $arr_filter[] = 'id_language = '.(string) $_SESSION['selected_title_lang'][0];
    }
    if ((int) $_SESSION['title_filter_id'] > 0) {
        $arr_filter[] = 'id_title >= '.$_SESSION['title_filter_id'];
    }
    if ($_SESSION['title_filter_text'] != '') {
        $arr_filter[] = "title_text LIKE '%".$_SESSION['title_filter_text']."%'";
    }
    switch ($_SESSION['view_title_mode'][0]) {
        case 1: $arr_filter[] = "title_text = ''";
            break;
        case 2: $d_list = TitleDoubleList($dbh);
            if (count($d_list) > 0) {
                $arr_filter[] = '('.implode(' OR ', $d_list).')';
            } break;
        case 3: $unused_titles = UnusedTitles($dbh);
            if (count($unused_titles) > 0) {
                $arr_filter[] = 'id_title IN ('.implode(',', $unused_titles).')';
            } break;
        case 4: $arr_filter[] = '((SELECT languages.id_language FROM languages WHERE languages.id_language = interface_texts.id_language) IS NULL OR interface_texts.id_language = 0 OR id_title = 0 OR id_title > 99999)';
            break;
    }
    if (count($arr_filter) == 0) {
        return '';
    } else {
        return ' WHERE '.implode(' AND ', $arr_filter);
    }
}
function TitleResetFilter($dbh, $set_title_count = true)
{
    $_SESSION['selected_title_lang'] = [0, '--'.Title(441).'--'];
    $_SESSION['title_filter_id'] = '';
    $_SESSION['title_filter_text'] = '';
    $_SESSION['view_title_mode'] = [0, Title(435)];
    $_SESSION['title_filter'] = '';
    if ($set_title_count) {
        $_SESSION['title_count'] = TitleCounts($dbh);
    }
}
function GetFilterParameters()
{
    $filter_param['language_value'] = GetFilterValue('id_language = ', 14, ' ');
    $filter_param['empty_pos'] = strpos($_SESSION['title_filter'], 'title_text = '.chr(39).chr(39));
    $filter_param['doubled_arr'] = GetFilterValue('id_title = ', 0, ')', ' OR ');
    $filter_param['unused_arr'] = GetFilterValue('id_title IN (', 13, ')', ',');
    $filter_param['errors_pos'] = strpos($_SESSION['title_filter'], '((SELECT ');
    $filter_param['id_value'] = GetFilterValue('id_title >= ', 12, ' ');
    $filter_param['text_value'] = GetFilterValue('title_text LIKE '.chr(39).'%', 16, ' ');

    return $filter_param;
}
function StartTitlePositionByFilter($dbh)
{
    $filter_param = GetFilterParameters();
    $first_key = FindFirstTitle($filter_param);
    if (count($first_key) > 0) {
        GetTitleStartPosition($dbh, $first_key[0], $first_key[1]);
    } else {
        $_SESSION['start'] = 0;
    }
}
function GetTitleStartPosition($dbh, $title_id, $title_lang_id)
{
    $w_w = ($_SESSION['title_filter'] == '') ? 'WHERE' : 'AND';
    $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM interface_texts '.$_SESSION['title_filter'].' '.$w_w.' id_title < '.(string) $title_id.' ORDER BY id_title');
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $s_p = $row[0];
        }
        mysqli_free_result($res);
    }
    if ($_SESSION['selected_title_lang'][0] == 0) {
        $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM interface_texts '.$_SESSION['title_filter'].' '.$w_w.' id_title = '.(string) $title_id.' AND id_language < '.(string) $title_lang_id.' ORDER BY id_language');
        if ($res) {
            if ($row = mysqli_fetch_row($res)) {
                $s_p += $row[0];
            }
            mysqli_free_result($res);
        }
    }
    if (isset($s_p)) {
        $_SESSION['start'] = $s_p;
    }
}
function GetFilterValue($search_value, $value_offset, $terminate_symbol, $value_separator = '')
{
    $start_pos = strpos($_SESSION['title_filter'], $search_value);
    if ($start_pos === false) {
        return false;
    }
    $start_pos += $value_offset;
    $filter_string = substr($_SESSION['title_filter'], $start_pos);
    if ($start_pos + strlen($filter_string) == strlen($_SESSION['title_filter'])) {
        $result_string = $filter_string;
    } else {
        $end_pos = strpos($filter_string, $terminate_symbol);
        if ($end_pos === false) {
            return false;
        }
        $result_string = substr($filter_string, 0, $end_pos - 1);
    }
    if ($value_separator == '') {
        return $result_string;
    } else {
        return explode($value_separator, $result_string);
    }
}
function FindFirstTitle($filter_param)
{
    foreach ($_SESSION['title_param'] as $v) {
        if (TitleRowComparison($filter_param, $v[0], $v[2], $v[3])) {
            return [$v[2], $v[3]];
        }
    }

    return [];
}
function TitleRowComparison($filter_param, $title_text, $title_id, $title_lang)
{
    foreach ($filter_param as $key_param => $v_param) {
        if ($v_param !== false) {
            switch ($key_param) {
                case 'language_value': if ((string) $title_lang != $v_param) {
                    return false;
                } break;
                case 'empty_pos': if ($title_text != '') {
                    return false;
                } break;
                case 'doubled_arr': if (! in_array('id_title = '.$title_id.' AND id_language = '.(string) $title_lang, $v_param)) {
                    return false;
                } break;
                case 'unused_arr': if (! in_array($title_id, $v_param)) {
                    return false;
                } break;
                case 'errors_pos': if (isset($_SESSION['title_langs'][$title_lang]) && (int) $v[2] > 0 && (int) $title_id <= 99999) {
                    return false;
                } break;
                case 'id_value': if ((string) $title_lang != $v_param) {
                    return false;
                } break;
                case 'text_value': if (strpos($title_text, substr($v_param, 2, strlen($v_param) - 4)) === false) {
                    return false;
                }
            }
        }
    }

    return true;
}
