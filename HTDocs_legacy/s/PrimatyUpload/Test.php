<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<?php
function GetDB($dbname, $server_name, $user_name, $user_password, $coding = '')
{
    $dbh = mysqli_connect($server_name, $user_name, $user_password, $dbname);
    if ($dbh) {
        if ($coding != '') {
            mysqli_query($dbh, "SET NAMES '".$coding."'");
        }

        return $dbh;
    } else {
        return false;
    }
}
function MCV($source_value)
{
    if ($source_value == '') {
        return '';
    }
    if ($_SESSION['match_case']) {
        return $source_value;
    } else {
        $arr_str = str_split($source_value);
        $low_case = '';
        for ($n = 0; $n < count($arr_str); $n++) {
            $ind = array_search(ord($arr_str[$n]), $_SESSION['trans_codes']['b_0']); // ind - current byte index of the source string (search by translate table)
            if ($ind === false) {
                $low_case .= strtolower($arr_str[$n]);
            } // latin letter
            elseif ($n == count($arr_str) - 1) {
                $low_case .= $arr_str[$n];
            } // non letin single byte letter (it is error in the source string)
            else { // non latin letter or special character
                $i = SecondByteSearch($ind, $arr_str, $n); // i - index of second byte in the translate table
                if ($i > -1) { // if the second byte found in the translate table
                    if ($n == count($arr_str) - 2) { // if the double-byte character is at the end of the source string
                        $low_case .= ToLowerLetter($_SESSION['trans_codes']['to_lower'][$i], $arr_str[$n].$arr_str[$n + 1]); // add the double-byte character
                        $n++; // move one byte
                    } else { // three-byte character is possible
                        $j = ThirdByteSearch($i, $arr_str, $n); // search for the third byte second byte search (j - index of third byte in the translate table)
                        if ($j > -1) { // three-byte character found in the translate table
                            $low_case .= ToLowerLetter($_SESSION['trans_codes']['to_lower'][$j], $arr_str[$n].$arr_str[$n + 1].$arr_str[$n + 2]); // add the third-byte character
                            $n += 2; // move two bytes
                        } else { // it is double-byte character
                            $low_case .= ToLowerLetter($_SESSION['trans_codes']['to_lower'][$i], $arr_str[$n].$arr_str[$n + 1]); // add the double-byte character
                            $n++; // move one byte
                        }
                    }
                } else {
                    $low_case .= $arr_str[$n];
                } // if the second byte not found in the translate table (it is error)
            }
        }

        return $low_case;
    }
}
function SecondByteSearch($ind, $arr_str, $n)
{
    for ($i = $ind; $i < count($_SESSION['trans_codes']['b_0']) && $_SESSION['trans_codes']['b_0'][$i] == ord($arr_str[$n]) && $_SESSION['trans_codes']['b_1'][$i] < ord($arr_str[$n + 1]); $i++);
    if ($i < count($_SESSION['trans_codes']['b_0']) && $_SESSION['trans_codes']['b_0'][$i] == ord($arr_str[$n]) && $_SESSION['trans_codes']['b_1'][$i] == ord($arr_str[$n + 1])) {
        return $i;
    } else {
        return -1;
    }
}
function ThirdByteSearch($ind, $arr_str, $n)
{
    for ($i = $ind; $i < count($_SESSION['trans_codes']['b_0']) && $_SESSION['trans_codes']['b_0'][$i] == ord($arr_str[$n]) && $_SESSION['trans_codes']['b_1'][$i] == ord($arr_str[$n + 1]) && $_SESSION['trans_codes']['b_2'][$i] < ord($arr_str[$n + 2]); $i++);
    if ($i < count($_SESSION['trans_codes']['b_0']) && $_SESSION['trans_codes']['b_0'][$i] == ord($arr_str[$n]) && $_SESSION['trans_codes']['b_1'][$i] == ord($arr_str[$n + 1]) && $_SESSION['trans_codes']['b_2'][$i] == ord($arr_str[$n + 2])) {
        return $i;
    } else {
        return -1;
    }
}
function ToLowerLetter($lower_case_letter, $source_letter)
{
    if ($lower_case_letter == '') {
        return $source_letter;
    } else {
        return $lower_case_letter;
    }
}
function GetAutoIncrement($dbh)
{
    $res = mysqli_query($dbh, 'SELECT LAST_INSERT_ID()');
    if (mysqli_errno($dbh) > 0) {
        return '';
    }
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $r = (string) GetValueFromTable($res, 0);
        } else {
            $r = '';
        }
        mysqli_free_result($res);

        return $r;
    } else {
        return '';
    }
}
function GetLocalCodes($dbh, $id_lang = -1)
{
    $arr = ['lang_code' => [], 'letter' => [], 'b_0' => [], 'b_1' => [], 'b_2' => [], 'to_lower' => [], 'full_code' => []];
    if ($id_lang == -1) {
        $res = mysqli_query($dbh, 'SELECT * FROM translate_table ORDER BY letter');
    } else {
        $res = mysqli_query($dbh, 'SELECT * FROM translate_table WHERE id_lang = '.(string) $id_lang.' ORDER BY letter');
    }
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $arr['lang_code'][] = $row[0];
            $arr['letter'][] = $row[1];
            $arr['b_0'][] = ord(substr($row[1], 0));
            $arr['b_1'][] = ord(substr($row[1], 1));
            $arr['b_2'][] = ord(substr($row[1], 2));
            $arr['to_lower'][] = $row[2];
            $f_code = 1000000 * end($arr['b_0']) + 1000 * end($arr['b_1']) + end($arr['b_2']);
            $arr['full_code'][] = $f_code;
            if ($row[3] != $f_code) {
                mysqli_query($dbh, 'UPDATE translate_table SET letter = '.(string) $f_code." WHERE letter = '".$row[1]."'");
            }
        }
        mysqli_free_result($res);
    }

    return $arr;
}
$dbh = GetDB('my_db', 'localhost', 'root', 'msql06091949', 'utf8');
$_SESSION['trans_codes'] = GetLocalCodes($dbh);
mysqli_query($dbh, "UPDATE test SET to_lower = ''");
$res = mysqli_query($dbh, 'SELECT * FROM test');
if ($res) {
    while ($row = mysqli_fetch_row($res)) {
        $aa = MCV($row[0], false);
        mysqli_query($dbh, "UPDATE test SET to_lower = '".$aa."'");
    }
    mysqli_free_result($res);
}
?>
