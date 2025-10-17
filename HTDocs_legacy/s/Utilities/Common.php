<?php

function trim_string($str)
{
    return rtrim(ltrim($str));
}
function AllPosition($str, $sy)
{
    $pos = -1;
    $result = [];
    while (($pos = strpos($str, $sy, $pos + 1)) !== false) {
        $result[] = $pos;
    }

    return $result;
}
function ApostropheToValue($str, $to_space = false)
{
    if (strpos($str, chr(39)) == false) {
        $rs = $str;
    } else {
        $rs = str_replace(chr(39), '&#39', $str);
    }
    if ($to_space) {
        $rs = '';
    }

    return $rs;
}
function StringTest($illegal_str, $str, $cl)
{
    $rr = ['error' => false, 'symb' => ''];
    if ($str == '' || $illegal_str == '') {
        return $rr;
    }
    $ss = str_replace(chr(39), $_SESSION['apostrophe_replace'], $str);
    $arr_symb = [];
    $illegal_arr = explode($_SESSION['char_group'], $illegal_str);
    foreach ($illegal_arr as $v) {
        if ($v != '' && strpos($ss, $v) !== false) {
            $arr_symb[] = "<span class='".$cl."'>".$v.'</span>';
            $rr['error'] = true;
        }
    }
    if (count($arr_symb) > 0) {
        $rr['symb'] = implode($_SESSION['char_group'], $arr_symb);
    }

    return $rr;
}
function SetTestMessages($rr, $source_string, $row_id = '')
{
    $Mes = '';
    if ($rr['symb'] != '') {
        $Mes = Title(443).' <b>'.$source_string.'</b> '.(($row_id == '') ? '' : '(<b>'.$row_id.'</b>)').' '.Title(444).': '.$rr['symb'];
    }

    return $Mes;
}
function GetCorrectEditedValue($ill_arr, $str)
{
    $rr = $str;
    foreach ($ill_arr as $v) {
        if (count($v) > 1) {
            $rr = str_replace($v[0], $v[1], $rr);
        }
    }

    return $rr;
}
function CountOfNoEmptyItems($arr)
{
    $i = 0;
    foreach ($arr as $v) {
        if ($v != '') {
            $i++;
        }
    }

    return $i;
}
function TransSize($str)
{
    switch (substr($str, -1)) {
        case 'K': return (is_numeric(substr($str, 0, strlen($str) - 1))) ? intval(substr($str, 0, strlen($str) - 1)) * 1024 : 0;
            break;
        case 'M': return (is_numeric(substr($str, 0, strlen($str) - 1))) ? intval(substr($str, 0, strlen($str) - 1)) * 1024 * 1024 : 0;
            break;
        case 'G': return (is_numeric(substr($str, 0, strlen($str) - 1))) ? intval(substr($str, 0, strlen($str) - 1)) * 1024 * 1024 * 1024 : 0;
            break;
        default: return (is_numeric($str)) ? intval($str) : 0;
    }
}
function InputFileDet($m_mode, $name)
{
    return ($m_mode) ? "name='".$name."[]' multiple" : "name='".$name."'";
}
function ResExists($res_adr)
{
    if (substr($res_adr, 0, 4) == 'http') {
        $URL_res = get_headers($res_adr);
        $arr_rep = explode(' ', $URL_res[0]);
        if (count($arr_res) > 1) {
            return $arr_rep[1] == '200';
        } else {
            return false;
        }
    } else {
        return file_exists($res_adr);
    }
}
function RefToRes($res_adr, $pref)
{
    if (substr($res_adr, 0, 4) == 'http') {
        return $res_adr;
    } else {
        $ss = str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $res_adr);

        return $pref.$ss;
    }
}
function CutNumberAtBegin($expr)
{
    $n_str = '';
    $i = 0;
    while ($i < strlen($expr) && $expr[$i] >= '0' && $expr[$i] <= '9') {
        $n_str .= $expr[$i];
        $i++;
    }

    return ($n_str == '') ? 0 : (int) $n_str;
}
function MaxWidth($using_code)
{
    $max_width = 0;
    foreach ($_SESSION['main_params']['const'][1] as $k => $v) {
        if ($v['screen_order'] > 0 && in_array($using_code, explode(',', $v['using']))) {
            $n_str = CutNumberAtBegin($v['t_prc']);
            if ($n_str > $max_width) {
                $max_width = $n_str;
            }
        }
    }

    return $max_width;
}
function ExitSession($str_source = '', $id_db = 0, $ses_destr = true)
{
    $exit_str = '';
    $arr_str = explode('`', $str_source);
    foreach ($arr_str as $y) {
        $arr_row = explode('|', $y);
        $clr = (count($arr_row) == 1) ? '000000' : $arr_row[1];
        $exit_str .= "<div align='center'><h2><font color='#".$clr."'><b>".$arr_row[0].'</b></font></h2></div>';
    }
    if ($ses_destr) {
        if (isset($_SESSION['priority'])) {
            $dbh_sys = GetOnlyDB('db_manager');
            if ($dbh_sys) {
                VisitParameters($dbh_sys, $id_db, -1, $_SESSION['priority']);
            }
        }
        $tt = Title(16);
        if (substr($tt, 0, 13) == 'Missing text ') {
            $tt = 'Session completed';
        }
        $exit_str .= "<br><br><br><div align='center'><h2><b>".$tt.'</b></h2></div>';
        if (count($_SESSION) > 0) {
            session_destroy();
        }
    }
    exit($exit_str);
}
function TestSysString($str)
{
    $arr_str = str_split($str);
    if (ord($arr_str[0]) < 97 || ord($arr_str[0]) > 122) {
        return false;
    }
    for ($i = 1; $i < count($arr_str); $i++) {
        if (ord($arr_str[$i]) < 48 || ord($arr_str[$i]) > 57 && ord($arr_str[$i]) < 95 || ord($arr_str[$i]) == 96 || ord($arr_str[$i]) > 122) {
            return false;
        }
    }

    return true;
}
function GetIDArraySelect($arr_select, $value, $n = -1)
{
    if ($n == -1) {
        $k = array_search($value, $arr_select);
        if ($k === false) {
            return '';
        } else {
            return $k;
        }
    }
    foreach ($arr_select as $k => $v) {
        if ($v[$n] == $value) {
            return $k;
        }
    }

    return '';
}
function VValue($s_str)
{
    $str = $s_str;
    $str = str_replace(chr(39), chr(39).chr(39), $str);

    return str_replace(chr(92), chr(92).chr(92), $str);
}
function ChangeTimerInterval($interval_file, $interval_value)
{
    $str = file_get_contents($interval_file);
    $pos = strpos($str, ', ');
    $str = substr($str, 0, $pos + 2).(string) ((int) $interval_value * 1000).');';
    file_put_contents($interval_file, $str);
}
function SaveUserScreenPortion($dbh_sys, $post_portion)
{
    $res = mysqli_query($dbh_sys, 'SELECT user_list_portion FROM user_ident WHERE id_user = '.$_SESSION['user_id']);
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            if ($row[0] != (int) $post_portion) {
                mysqli_query($dbh_sys, 'UPDATE user_ident SET user_list_portion = '.$post_portion.' WHERE id_user = '.$_SESSION['user_id']);
            }
        }
        mysqli_free_result($res);
    }
}
function GetCorrectDBValue($ini_v, $arr_conv)
{
    $s = $ini_v;
    if (count($arr_conv) > 0) {
        if (count($arr_conv) > 1) {
            $s = str_replace($arr_conv[0], $arr_conv[1], $s);
        } elseif (is_array($arr_conv[0]) && count($arr_conv[0]) > 1) {
            foreach ($arr_conv as $v) {
                $s = str_replace($v[0], $v[1], $s);
            }
        }
    }

    return $s;
}
function FTM($source_value, $to_up = false, $coding = 'utf-8')
{
    $first_char = mb_substr($source_value, 0, 1, $coding);
    if ($to_up) {
        $first_char_modif = mb_strtoupper($first_char, $coding);
    } else {
        $first_char_modif = mb_strtolower($first_char, $coding);
    }
    $source_len = mb_strlen($source_value, $coding);
    $rest_part = mb_substr($source_value, 1, $source_len - 1, $coding);

    return $first_char_modif.$rest_part;
}
function IsReferenceErrors()
{
    foreach ($_SESSION['pre_ref'] as $table => $t_info) {
        if (isset($t_info['p'])) {
            foreach ($t_info['p'] as $ref_type => $type_params) {
                if (isset($type_params['v'])) {
                    return true;
                }
            }
        }
    }

    return false;
}
function NewTableID($arr, $max_m)
{
    if (count($arr) == 0) {
        return $max_m + 1;
    }
    $key_arr = [];
    foreach ($arr as $k => $v) {
        if (gettype($k) == 'integer' && (int) $k > $max_m) {
            $key_arr[] = $k;
        }
    }
    $prev = $max_m;
    for ($i = 0; $i < count($key_arr); $i++) {
        if ($key_arr[$i] - $prev > 1) {
            return $prev + 1;
        }
        $prev = $key_arr[$i];
    }

    return $prev + 1;
}
function GetFirstKey($arr)
{
    reset($arr);

    return key($arr);
}
function GetLastKey($arr)
{
    end($arr);

    return key($arr);
}
function GetSymbolCode($k) // ??? coding &#??
{
    $arr_ord = [ord(substr($k, 0)), ord(substr($k, 1)), ord(substr($k, 2))];

    return 1000000 * $arr_ord[0] + 1000 * $arr_ord[1] + $arr_ord[2];
}
function EmphSymb($v, $emph)
{
    $vv = [];
    foreach ($v as $z) {
        $vv[] = "<font class='".$emph."'><b>".$z.'</b></font>';
    }

    return $vv;
}
function DoubleQuoteFix($str)
{
    if (strpos($str, chr(34)) === false) {
        $_SESSION['double_quote_fix'] = true;

        return $str;
    } else {
        $_SESSION['double_quote_fix'] = true;

        return str_replace(chr(34), chr(39).chr(39), $str);
    }
}
function SpecSymbSearch($str_filter, $spec_symb, $escape_symbol)
{
    $str = str_replace($spec_symb, $escape_symbol.$spec_symb, $str_filter);

    return "LIKE '%".$str."%' ESCAPE '".$escape_symbol."'";
}
function ChangeMesAfterLangChoice($mes_arr)
{
    $mes = [];
    for ($i = 0; $i < count($mes_arr); $i++) {
        if (is_number($mes_arr[$i])) {
            if ($mes_arr[$i] > 0) {
                $mes .= Title($mes_arr[$i]);
            } else {
                $mes .= FTM(Title($mes_arr[$i]));
            }
        } else {
            $mes .= $mes_arr[$i];
        }
    }

    return $mes;
}
