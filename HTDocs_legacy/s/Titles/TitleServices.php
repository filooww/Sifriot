<?php

function TitleCodeLists($dbh)
{
    $arr_integrity = ['total' => []];
    $arr_lang = [];
    foreach (array_keys($_SESSION['user_langs']) as $k) {
        $arr_integrity[$k] = [];
        if ($k != 'total') {
            $arr_lang[] = $k;
        }
    }
    $res = mysqli_query($dbh, 'SELECT id_title, id_language FROM interface_texts WHERE id_language IN ('.implode(',', $arr_lang).') ORDER BY id_title');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            if (! in_array($row[0], $arr_integrity['total'])) {
                $arr_integrity['total'][] = $row[0];
            }
            if (isset($arr_integrity[(string) $row[1]]) && ! in_array($row[0], $arr_integrity[(string) $row[1]])) {
                $arr_integrity[(string) $row[1]][] = $row[0];
            }
        }
        mysqli_free_result($res);
    }

    return $arr_integrity;
}
function IntegrityTitle()
{
    if (isset($_SESSION['mes_integrity'])) {
        return Title(463);
    } else {
        return Title(464);
    }
}
function IntegrityCheck($dbh)
{
    if (isset($_SESSION['mes_integrity'])) {
        unset($_SESSION['mes_integrity']);
    } else {
        $arr_integrity = TitleCodeLists($dbh);
        foreach ($arr_integrity as $k => $v) {
            if ($k != 'total') {
                $arr_err = array_diff($arr_integrity['total'], $v);
                if (count($arr_err) > 0) {
                    if (count($arr_err) < $_SESSION['number_warn']) {
                        $missing_codes = implode(', ', $arr_err);
                    } else {
                        $missing_codes = implode(', ', array_slice($arr_err, 0, $_SESSION['number_warn'])).', ...';
                    }
                    if (count($arr_err) == 1) {
                        $_SESSION['mes_integrity'][] = ['#000000', 231, ' <b>'.$_SESSION['user_langs'][(int) $k].'</b> ', 243, ' <b>'.$missing_codes.'</b>'];
                    }
                    $_SESSION['mes_integrity'][] = ['#000000', 231, ' <b>'.$_SESSION['user_langs'][(int) $k].'</b> ', 271, ' <b>'.$missing_codes.'</b>'];
                } else {
                    $_SESSION['mes_integrity'][] = ['#000000', 231, ' <b>'.$_SESSION['user_langs'][(int) $k].'</b> ', 238];
                }
            }
        }
    }
}
function PrintIntegrityMes($z)
{
    $str_print = '';
    if ($z[0] != '') {
        $str_print .= "<font color='".$z[0]."'>";
    }
    for ($i = 1; $i < count($z); $i++) {
        if ($i % 2 == 0) {
            $str_print .= $z[$i];
        } else {
            if ($z[$i] != 0) {
                if ($z[$i] < 0) {
                    $str_print .= FTM(Title(-$z[$i]));
                } else {
                    $str_print .= Title($z[$i]);
                }
            }
        }
    }
    if ($z[0] != '') {
        $str_print .= '</font>';
    }

    return $str_print;
}
function GetNewTitles($dbh)
{
    SetTitleFilterPosition($dbh);
    $_SESSION['title_param'] = GetTitlePortion($dbh);
    TestTitles($dbh);
}
function ChangePlusOne()
{
    $_SESSION['portion']++;

    return true;
}
function ChangeMinusOne(&$Mes)
{
    $cf = CountFirst();
    if ($cf == 0 && $_SESSION['portion'] > 1 || $cf > 0 && $cf <= $_SESSION['portion'] - 1) {
        if ($_SESSION['selected_title_lang'][0] > 0) {
            $_SESSION['portion']--;
        } else {
            if ($cf > 0) {
                $i = CountLast();
                if ($_SESSION['portion'] > $i) {
                    $_SESSION['portion'] -= $i;
                }
            } elseif ($_SESSION['portion'] > 1) {
                $_SESSION['portion']--;
            }
        }
        $fl = true;
    } else {
        $Mes = "<font color='#FF0000'><b>".Title(178).'</b></font>';
    }

    return $fl;
}
function ChangeAnyValue($offs, &$Mes)
{
    if (! is_numeric($offs)) {
        $Mes = "<font color='#FF0000'>".Title(177).' '.Title(77).': '.Title(299).' <b>'.$offs.'</b></font>';
    } else {
        $i = CountFirst();
        if ((int) $offs < $i) {
            $Mes = "<font color='#FF0000'><b>".Title(178).'</b></font>';
            $_SESSION['portion'] = $i;
            $fl = true;
        } else {
            $_SESSION['portion'] = (int) $offs;
            $fl = true;
        }
    }

    return $fl;
}
function ChangeTitleScreen($dbh, $offs, &$Mes)
{
    $fl = false;
    if (gettype($offs) == 'integer') {
        if ($offs > 0) {
            $fl = ChangePlusOne();
        } else {
            $fl = ChangeMinusOne($Mes);
        }
    } else {
        $fl = ChangeAnyValue($offs, $Mes);
    }
    if ($fl) {
        GetNewTitles($dbh);
    }
}
function TitleCounts($dbh)
{
    $t_count = 0;
    $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM interface_texts'.$_SESSION['title_filter']);
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $t_count = $row[0];
        }
        mysqli_free_result($res);
    }

    return $t_count;
}
function FreeLanguages($title_id, $lang_id)
{
    $free_langs = [];
    if (isset($_SESSION['title_langs'][$lang_id])) {
        $free_langs[$lang_id] = $_SESSION['title_langs'][$lang_id];
    } else {
        $free_langs[$lang_id] = '<<>>';
    }
    foreach ($_SESSION['title_langs'] as $k => $v) {
        if ($k != $lang_id && ! isset($_SESSION['title_param'][TitleKey($title_id, $k)])) {
            $free_langs[$k] = $v;
        }
    }
    ksort($free_langs);

    return $free_langs;
}
function FirstInsertedLanguage($title_id, $lang_id)
{
    foreach ($_SESSION['title_langs'] as $k => $v) {
        if ($k != $lang_id && ! isset($_SESSION['title_param'][TitleKey($title_id, $k)])) {
            return $k;
        }
    }

    return false;
}
