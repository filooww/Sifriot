<head>
	<style>.start_load {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>
	<style>.row_invisible {color:#FFFFFF; background-color:#FFFFFF;}</style>
</head>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LogProcessing/Utilities.php';
require_once 'Utilities.php';
session_start();
$_SESSION['trans_mess'] = [];
if ($_SESSION['mult']) {
    $s = array_sum($_FILES['filename']['size']);
    $m = TransSize(ini_get('upload_max_filesize'));
    $l = TransSize(ini_get('max_file_uploads'));
    if ($s > $m) {
        $_SESSION['trans_mess'][] = 'Total size of uploaded files exceeds the directive in php.ini ('.(string) $m.')';
    }
    if (CountOfNoEmptyItems($_FILES['filename']['name']) > $l) {
        $_SESSION['trans_mess'][] = 'Number of loaded files exceeds the directive in php.ini ('.(string) $l.')';
    }
}
echo "<p class='row_invisible' align='center'>S</p><p class='row_invisible' align='center'>S</p>";
if (count($_SESSION['trans_mess']) == 0) {
    $correct_files = 0;
    $incorrect_files = 0;
    if ($_SESSION['mult']) {
        $_SESSION['load_files'] = [];
        for ($i = 0; $i < count($_FILES['filename']['name']); $i++) {
            if ($_FILES['filename']['name'][$i] != '') {
                $_SESSION['load_files'][] = UploadFileNames($_FILES['filename']['name'][$i], $_FILES['filename']['tmp_name'][$i], $_FILES['filename']['error'][$i]);
                if ($_FILES['filename']['error'][$i] == 0) {
                    $correct_files++;
                } else {
                    $incorrect_files++;
                }
            }
        }
    } else {
        $_SESSION['load_files'] = UploadFileNames($_FILES['filename']['name'], $_FILES['filename']['tmp_name'], $_FILES['filename']['error']);
    }
    if ($correct_files == 0) {
        $_SESSION['mestext'] = ($incorrect_files == 0) ? "You didn't select any files to upload on the server" : 'No file uploaded correctly to server';
        header('Location: Transit.php');
    } else {
        echo "<form action='../".$_SESSION['to_loadtodb']."' method='post'>";
        echo "<p align='center'><font size='4' color='#0000FF'><b>".(string) $correct_files.' files uploaded correctly to server</b></font></p>';
        echo "<p class='row_invisible' align='center'>S</p><p class='row_invisible' align='center'>S</p>";
        echo "<p align='center'><input type='submit' value='Continue' class='start_load' height='200'></p>";
        echo '</form>';
    }
} else {
    header('Location: Transit.php');
}
?>

