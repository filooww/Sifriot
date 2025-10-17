<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
session_start();
?>
<html>
	<head>
		<style>.start_load {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>
		<style>
			.blink_class {animation: blinker-two 1.5s linear infinite;}
			@keyframes blinker-two {100% {opacity: 0;}}
		</style>
		<style>.row_invisible {color:#FFFFFF; background-color:#FFFFFF;}</style>
	</head>
	<body>
		<p class="row_invisible" align="center">S</p>
		<form action="<?php echo $_SESSION['file_upload_return']; ?>" method="post" enctype="multipart/form-data">
			<p align="center"><b>Loading the files to the server</b></p>
			<hr noshade="noshade" color="#000000" >
			<table>
				<tr>
					<td>Select files for uploading</td>
					<td><input size="<?php echo $_SESSION['conf']['w_01']; ?>" type="file" <?php echo InputFileDet($_SESSION['mult'], 'filename'); ?> ></td>
				</tr>
			</table>
			<p class="row_invisible" align="center">S</p>
			<p class="row_invisible" align="center">S</p>
			<p align="center"><input type="submit" value="Upload files to server" class="start_load" height="100"></p>
		</form>
		<p class="row_invisible" align="center">S</p>
		<p class="row_invisible" align="center">S</p>
		<p class="blink blink_class" align="center"><font color="#FF0000" size="6"><b>Don't take any action while this text blinks to avoid interrupting the uploading!</b></font></p>		
	</body>
</html>
