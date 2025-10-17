<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.line_gap {line-height: 1.5;}</style>
	<style>.table_fix {table-layout:fixed; width:100%;}</style>
	<style>.cell_fix {word-wrap:break-word;}</style>
	<style>.separator_invisible {color:#FFFFFF; background-color:#FFFFFF;}</style>
</head>

<?php
require_once 'Utils.php';

session_start();
$_SESSION['primary_dir'] = str_replace(chr(92), chr(92).chr(92), $_POST['source_directory']);

$_SESSION['main_params']['var'][1]['CommonText']['value'] = $_POST['common_title'];
$dbh = GetOnlyDB('db_manager');
if (! $dbh) {
    echo 'No connection to DB';
}
$correct_files = 0;
$load_files = [];
if ($_SESSION['primary_dir'] == '') {
    exit('Source directopy not specified');
}
for ($i = 0; $i < count($_FILES['filename']['name']); $i++) {
    if ($_FILES['filename']['name'][$i] != '') {
        if ($_FILES['filename']['error'][$i] == 0) {
            $load_file = ['tmp' => $_FILES['filename']['tmp_name'][$i], 'name' => $_FILES['filename']['name'][$i], 'err_ind' => $_FILES['filename']['error'][$i]];
            $_SESSION['main_params']['var'][1]['Title']['code'] = str_replace(chr(39), chr(96), pathinfo($load_file['name'], PATHINFO_FILENAME)); // replace the apostrophe
            $file_struct = UpdatePrimaryPublication($dbh, $load_file, $_SESSION['primary_dir'], $_SESSION['arr_alg_seq'], $_SESSION['mes']['u']);
            if ($file_struct['id_pub'] != '') {
                AddPublicationFile($dbh, $file_struct, pathinfo($load_file['name'], PATHINFO_BASENAME), $_SESSION['PR']['var']);
                $_SESSION['mes']['u'][] = SingleFileUpload($load_file['tmp'], $_SESSION['URL_p'].'/'.$file_struct['id_pub'].'-'.(string) $file_struct['ord_num'].'.'.pathinfo($load_file['name'], PATHINFO_EXTENSION), $load_file['name']);
                $m = end($_SESSION['mes']['u']);
                if ($m['status'] = 'statement') {
                    $correct_files++;
                }
            }
        } else {
            $_SESSION['mes']['u'] = ['time' => GetMessageDate(), 'text' => 'File <b>'.$load_file[$i]['name'].'</b>: '.UploadErrorMessages($_FILES['filename']['error'][$i]), 'status' => 'error', 'log' => true];
        }
        $load_files[] = $load_file;
    }
}
?>
<p align="center"><b>File upload information:</b></p>
<hr noshade="noshade" color="#000000" >
<table class="table_fix">
	<tr valign="top">
		<td class="cell_fix">
			<p class="line_gap">
				<?php for ($i = 0; $i < count($load_files); $i++) {
				    PutToFileList($load_files[$i], $i);
				}?>
			</p>
		</td>
	</tr>
</table>
