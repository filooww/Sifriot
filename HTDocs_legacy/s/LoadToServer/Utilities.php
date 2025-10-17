<head>
	<style>.row_invisible {color:#FFFFFF; background-color:#FFFFFF;}</style>
	<style>.start_load {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>	
</head>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';

function UploadErrorMessages($errc)
{
    switch ($errc) {
        case 1: return 'the uploaded file exceeds the upload_max_filesize directive in php.ini';
        case 2: return 'the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case 3: return 'the uploaded file was only partially uploaded';
        case 4: return 'no file was uploaded';
        case 6: return 'missing a temporary folder';
        case 7: return 'failed to write file to disk';
        case 6: return 'a PHP extension stopped the file upload';
        default: return 'unknown error';
    }
}
function NoHTNLChar($arr_piece, &$arr_code, $last_index)
{
    for ($j = 0; $j < strlen($arr_piece); $j++) {
        if (substr($arr_piece[$j], 0, 2) == '&#' && is_numeric(substr($arr_piece[$j], 2))) {
            return $j;
        }
        $arr_code[] = ord($arr_piece[$j]);
    }
    if (! $last_index) {
        $arr_code[] = ord(';');
    }

    return -1;
}
function UploadFileNames($file_name, $file_tmp = '', $file_error = 0)
{
    $arr_str = str_split($file_name);
    $arr_code = [];
    for ($n = 0; $n < count($arr_str); $n++) {
        $ind_sc = array_search(';', array_slice($arr_str, $n));
        if ($ind_sc === false) {
            $arr_code[] = ord($arr_str[$n]);
        } elseif (implode('', array_slice($arr_str, $n, 2)) == '&#') {
            $str_code = implode('', array_slice($arr_str, $n + 2, $ind_sc - 2));
            if (is_numeric($str_code)) {
                $num_code = (int) $str_code;
                $ind_code = array_search($num_code, $_SESSION['trans_codes']['html_code']);
                if ($ind_code === false) {
                    $arr_code[] = ord($arr_str[$n]);
                } else {
                    $arr_code[] = $_SESSION['trans_codes']['letter_code'][$ind_code];
                    $n += $ind_sc;
                }
            } else {
                $arr_code[] = ord($arr_str[$n]);
            }
        } else {
            $arr_code[] = ord($arr_str[$n]);
        }
    }
    if ($file_tmp == '') {
        return $arr_code;
    } else {
        return ['tmp' => $file_tmp, 'name' => $arr_code, 'err_ind' => $file_error];
    }
}
function SingleFileUpload($s_file, $d_file, $file_name)
{
    if (ResExists($s_file)) {
        if (copy($s_file, $d_file)) {
            return ['time' => GetMessageDate(), 'text' => 'File <b>'.$file_name.'</b> uploaded to the server successfully!', 'status' => 'statement'];
        } else {
            return ['time' => GetMessageDate(), 'text' => 'Error while file <b>'.$file_name.'</b> uploaded into destination<br>(on the server this file has the name <b>'.$d_file.'</b>)<br>', 'status' => 'error'];
        }
    } else {
        return ['time' => GetMessageDate(), 'text' => 'File <b>'.$file_name.'</b> not exists<br>', 'status' => 'error'];
    }
}
function Transit($mess, $act_script)
{
    echo "<p class='row_invisible' align='center'>S</p>";
    echo "<p class='row_invisible' align='center'>S</p>";
    echo "<p class='row_invisible' align='center'>S</p>";
    if (is_array($mess)) {
        foreach ($mess as $v) {
            echo "<p align='center'><font color='#FF0000'><b>".$v.'</b></font></p>';
        }
    } else {
        echo "<p align='center'><font color='#FF0000'><b>".$mess.'</b></font></p>';
    }
    echo "<p class='row_invisible' align='center>S</p>";
    echo "<p class='row_invisible' align='center'>S</p>";
    echo "<form action='".$act_script.".php' method='post'>";
    echo "<table align='center'>";
    echo '<tr>';
    echo "<td><input type='submit' name='repeate' value='Repeate selection' class='start_load' height='200'></td>";
    echo "<td><input type='submit' name='exit' value='Return to home page' class='start_load' height='200'></td>";
    echo "<td><input type='submit' name='continue' value='Continue' class='start_load' height='200'></td>";
    echo '</tr>';
    echo '</table>';
    echo '</form>';
}

?>
