<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.mes_style {background-color:#CCFFFF;}</style>
	<style>.save_button {background-color:#CCFF00;}</style>
	<style>.exit_button {background-color:#FFCC00;}</style>
	<style>.cell_invisible {color:#FFFFFF; background-color:#FFFFFF; border:none;}</style>
	<style>.right_align {text-align:right;}</style>
	<style>.center_align {text-align:center;}</style>
	<style>.del_button {color:#FFFFFF; background-color:#FF0000;}</style>
</head>
<SCRIPT language=JavaScript>function user_lang_on() {document.config_form.user_lang_s.value = '*'; config_form.submit();}</SCRIPT>

<?php
require_once 'Utils.php';
session_start();
$dbh = GetOnlyDB('db_manager');
if (! $dbh) {
    exit('No connection to DB');
}
if (! isset($_POST['trans_exit']) && ! isset($_POST['trans_save'])) {
    $_SESSION['trans_codes'] = GetLocalCodes($dbh, 2);
} elseif (isset($_POST['trans_exit'])) {
    exit('END');
} elseif (isset($_POST['trans_save'])) {
    for ($i = 0; $i < count($_SESSION['trans_codes']['letter']); $i++) {
        if ($_POST['to_lower-'.(string) $i] != '') {
            $res = mysqli_query($dbh, "SELECT * FROM translate_table WHERE letter = '".$_SESSION['trans_codes']['letter'][$i]."'");
            if ($res) {
                if ($row = mysqli_fetch_row($res)) {
                    mysqli_query($dbh, "UPDATE translate_table SET to_lower = '".$_POST['to_lower-'.(string) $i]."' WHERE letter = '".$_SESSION['trans_codes']['letter'][$i]."'");
                }
                mysqli_free_result($res);
            }
        }
    }
    $_SESSION['trans_codes'] = GetLocalCodes($dbh, 2);
}
?>

<!-- <form action="TranslateTable.php" method="post"> -->
<form method="post">
	<div align="center"><font size="+1"><b>Translate table</b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td><button name="trans_exit" type="submit" value="*" class="exit_button">Exit</button></td>
			<td><button name="trans_save" type="submit" value="*" class="save_button">Save table</button></td>
		</tr>
	</table>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table align="center" width="60%">
		<tr>
			<td><b>Order ##</b></td>
			<td align="center"><b>Language</b></td>
			<td><b>Letter</b></td>
			<td align="center"><b>Byte 0</b></td>
			<td align="center"><b>Byte 1</b></td>
			<td align="center"><b>Byte 2</b></td>
			<td><b>Lower case</b></td>
			<td><b>Full code</b></td>
		</tr>
		<?php
        for ($i = 0; $i < count($_SESSION['trans_codes']['letter']); $i++) {
            echo '<tr>';
            echo "<td><input size='3' type='text' class='right_align' name='o_num-".(string) $i."' value='".(string) ($i + 1)."' disabled></td>";
            echo "<td><input size='3' type='text' class='center_align' name='lang_code-".(string) $i."' value='".(string) $_SESSION['trans_codes']['lang_code'][$i]."'></td>";
            echo "<td><input size='1' type='text' class='center_align' name='letter-".(string) $i."' value='".$_SESSION['trans_codes']['letter'][$i]."'></td>";
            echo "<td><input size='10' type='text' class='right_align' name='byte_0-".(string) $i."' value='".(string) $_SESSION['trans_codes']['byte_1'][$i]."'></td>";
            echo "<td><input size='10' type='text' class='right_align' name='byte_1-".(string) $i."' value='".(string) $_SESSION['trans_codes']['byte_1'][$i]."'></td>";
            echo "<td><input size='10' type='text' class='right_align' name='byte_2-".(string) $i."' value='".(string) $_SESSION['trans_codes']['byte_2'][$i]."'></td>";
            echo "<td><input size='1' type='text' class='center_align' name='to_lower-".(string) $i."' value='".$_SESSION['trans_codes']['to_lower'][$i]."'></td>";
            echo "<td><input size='11' type='text' class='right_align' name='full_code-".(string) $i."' value='".$_SESSION['trans_codes']['full_code'][$i]."'></td>";
            echo '</tr>';
        }
?>
	</table>
</form>

