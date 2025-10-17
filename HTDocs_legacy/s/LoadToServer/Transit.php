<head>
	<style>.row_invisible {color:#FFFFFF; background-color:#FFFFFF;}</style>
	<style>.start_load {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>
</head>
<?php
session_start();
if (isset($_POST['repeate'])) {
    header('Location: FileSelection.php');
} elseif (isset($_POST['exit'])) {
    header('Location: ../'.$_SESSION['main_return_point']);
}
?>
<p class="row_invisible" align="center">S</p>
<p class="row_invisible" align="center">S</p>
<p class="row_invisible" align="center">S</p>
<!-- <form action="Transit.php" method="post"> -->
<form method="post">
	<?php
    if (count($_SESSION['trans_mess']) == 0) {
        echo "<p align='center'><font size='4' color='#FF0000'><b>".$_SESSION['mestext'].'</b></font></p>';
    } else {
        foreach ($_SESSION['trans_mess'] as $v) {
            echo "<p align='center'><font color='#FF0000'><b>".$v.'</b></font></p>';
        }
    }
?>
	<p class="row_invisible" align="center">S</p>
	<p class="row_invisible" align="center">S</p>
	<p align="center">
		<input type="submit" name="repeate" value="Repeate selection" class="start_load" height="200">
		<input type="submit" name="exit" value="Return to home page" class="start_load" height="200">
	</p>
</form>

