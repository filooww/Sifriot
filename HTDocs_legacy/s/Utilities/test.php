<head>
	<style>.form_exit {background-color:#FFCC00;}</style>
	<style>.form_save {background-color:#CCFF00;}</style>
	<style>.form_title {background-color:#FFFFFF;}</style>
</head>

<?php
require_once 'DataBases.php';
require_once 'UserList.php';

session_start();
if (isset($_POST['user_exit'])) {
    header('Location: exit.php');
}
?>
<form action="test.php" method="post">
	<table align="center">
		<tr>
			<td>
				<table><tr><td><font size="4">Register:</font></td></tr></table> <!-- //titles #5 -->
				<table bgcolor="#CCFFFF" frame="border">
					<tr><td><b>Pick a user name</b></td><td><input autofocus name="user_name" type="text" value=""></td></tr> <!-- //titles #6 -->
					<tr><td><b>Pick a password</b></td><td><input name="user_password" type="password" value=""></td></tr> <!-- //titles #7 -->
				</table>
				<table>
					<tr>
						<td><input name="user_exit" type="submit" class="form_exit" value="Exit"></td> <!-- //titles #8 -->
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
