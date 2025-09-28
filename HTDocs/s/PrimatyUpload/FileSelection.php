<!-- START -->
<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>
		.start_load {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}
	</style>
	<style>
		.blink_class {animation: blinker-two 1.5s linear infinite;}
		@keyframes blinker-two {100% {opacity: 0;}}
	</style>
	<style>
		.row_invisible {color:#FFFFFF; background-color:#FFFFFF;}
	</style>
</head>

<?php
require_once("Utils.php");


session_start();
$_SESSION['main_params']['const'][1]['Title']['ref'] = "title";
$_SESSION['main_params']['var'][1]['Title']['code'] = "";
$_SESSION['main_params']['const'][1]['Title']['file'] = false;
$_SESSION['main_params']['const'][1]['Title']['type'] = "string";
$_SESSION['main_params']['const'][1]['Title']['comm'] = false;
$_SESSION['main_params']['const'][1]['CommonText']['ref'] = "";
$_SESSION['main_params']['var'][1]['CommonText']['code'] = "";
$_SESSION['main_params']['const'][1]['CommonText']['file'] = false;
$_SESSION['main_params']['var'][1]['CommonText']['value'] = "";
$_SESSION['main_params']['const'][1]['CommonText']['title'] = "Common title";
$_SESSION['main_params']['const'][1]['CommonText']['type'] = "string";
$_SESSION['main_params']['const'][1]['CommonText']['comm'] = true;
$_SESSION['main_params']['const'][1]['FileYear']['ref'] = "file_issue_year";
$_SESSION['main_params']['var'][1]['FileYear']['code'] = "";
$_SESSION['main_params']['const'][1]['FileYear']['file'] = true;
$_SESSION['main_params']['const'][1]['FileYear']['type'] = "string";
$_SESSION['main_params']['const'][1]['FileYear']['comm'] = false;
$_SESSION['main_params']['const'][1]['FileVolume']['ref'] = "file_volume";
$_SESSION['main_params']['var'][1]['FileVolume']['code'] = "";
$_SESSION['main_params']['const'][1]['FileVolume']['file'] = true;
$_SESSION['main_params']['const'][1]['FileVolume']['type'] = "string";
$_SESSION['main_params']['const'][1]['FileVolume']['comm'] = false;
$_SESSION['main_params']['const'][1]['FileNumber']['ref'] = "file_number";
$_SESSION['main_params']['var'][1]['FileNumber']['code'] = "";
$_SESSION['main_params']['const'][1]['FileNumber']['file'] = true;
$_SESSION['main_params']['const'][1]['FileNumber']['type'] = "string";
$_SESSION['main_params']['const'][1]['FileNumber']['comm'] = false;
$_SESSION['main_params']['const'][1]['FilePage']['ref'] = "file_page";
$_SESSION['main_params']['var'][1]['FilePage']['code'] = "";
$_SESSION['main_params']['const'][1]['FilePage']['file'] = true;
$_SESSION['main_params']['const'][1]['FilePage']['type'] = "string";
$_SESSION['main_params']['const'][1]['FilePage']['comm'] = false;
$_SESSION['primary_dir'] = "";
$_SESSION['arr_alg_seq'] = array();
$_SESSION['mes']['u'] = array();
$_SESSION['time_zone'] = "UTC";
$dbh = GetOnlyDB("db_manager");
if (!$dbh) echo "No connection to DB";
$_SESSION['trans_codes'] = GetLocalCodes($dbh);
$_SESSION['URL_p'] = "D:/WebProg/HTDocs/Z_TestUpload/LiterFS";
$_SESSION['conf']['w_01'] = 150;

?>
<p class="row_invisible" align="center">S</p>
<form action="UploadDirectory.php" method="post" enctype="multipart/form-data">
	<p align="center"><b>Loading the files to the server</b></p>
	<hr noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td>Select files for uploading</td>
			<td><input size="<?php echo $_SESSION['conf']['w_01'];?>" type="file" name="filename[]" multiple></td>
		</tr>
		<tr>
			<td>Source directory</td>
			<td><input size="<?php echo $_SESSION['conf']['w_01'];?>" type="text" name="source_directory" value="D:\WebProg\HTDocs\Primary\PrimatyUploadNew\LiterTest"></td>
		</tr>
		<tr>
			<td>Common title</td>
			<td><input size="<?php echo $_SESSION['conf']['w_01'];?>" type="text" name="common_title"></td>
		</tr>
	</table>
	<p class="row_invisible" align="center">S</p>
	<p class="row_invisible" align="center">S</p>
	<p align="center"><input type="submit" value="Upload files to server" class="start_load" height="100"></p>
</form>
<p class="row_invisible" align="center">S</p>
<p class="row_invisible" align="center">S</p>
<p class="blink blink_class" align="center"><font color="#FF0000" size="6"><b>Don't take any action while this text blinks to avoid interrupting the uploading!</b></font></p>		

