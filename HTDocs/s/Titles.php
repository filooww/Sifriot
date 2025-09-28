<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.data_num {text-align:right;}</style>
</head>

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

session_start();
$dbh = GetManagerDBFile("db_manager", $_SERVER['DOCUMENT_ROOT']."/s/_Credentials.txt");
if (!$dbh) ExitSession(Title(252)."|FF0000", $_SESSION['login']['id']);
if (count($_POST) == 0)
{
	$_SESSION['lang_param'] = array("0"=>"", "1"=>"English", "2"=>"Русский", "3"=>"עברית");
	$_SESSION['title_lang'] = array("0", "");
	$_SESSION['title_id'] = "";
	$_SESSION['title_text'] = "";
	$_SESSION['update_text'] = "";
	$_SESSION['insert_text'] = "";
}
else
{
	$_SESSION['title_id'] = $_POST['title_id'];
	$ll = array_search($_POST['title_lang'], $_SESSION['lang_param']);
	$_SESSION['title_lang'] = array((string)$ll, $_POST['title_lang']);
	$_SESSION['title_text'] = $_POST['title_text'];
	$_SESSION['update_text'] = $_POST['update_text'];
	$_SESSION['insert_text'] = $_POST['insert_text'];
}
if (isset($_POST['update_title']) && $_SESSION['title_id'] != "" && $_SESSION['title_lang'][0] != "0" && $_SESSION['update_text'] != "")
{
	$wu = "id_title = ".$_SESSION['title_id'].", id_language = ".$_SESSION['title_lang'][0];  
	mysqli_query($dbh, "UPDATE interface_texts SET title_text = '".$_SESSION['update_text']."' WHERE id_title = ".$_SESSION['title_id']." AND id_language = ".$_SESSION['title_lang'][0]);
}
if (isset($_POST['insert_title']) && $_SESSION['title_id'] != "" && $_SESSION['title_lang'][0] != "0" && $_SESSION['insert_text'] != "")
{
	mysqli_query($dbh, "INSERT INTO interface_texts VALUES (".$_SESSION['title_id'].",".$_SESSION['title_lang'][0].",'".$_SESSION['insert_text']."')");
}
?>
<form method="post">
	<table>
		<tr><td>title ID</td><td><input name="title_id" size="10" type="text" value="<?php echo $_SESSION['title_id'];?>"></td></tr>
		<tr>
			<td>title language</td>
			<td>
			<?php
				echo "<select name='title_lang'>";
					foreach ($_SESSION['lang_param'] as $v) echo "<option".(($v == $_SESSION['title_lang'][1]) ? " selected": "").">".$v."</option>";
				echo "</select>";
			?>
			</td>
		</tr>
		<tr><td>filter text</td><td><input name="title_text" size="150" type="text" value="<?php echo $_SESSION['title_text'];?>"><button name="title_display" type="submit" value="*">...</button></td></tr>
		<tr><td></td><td></td></tr>
		<tr><td>update text</td><td><input name="update_text" size="150" type="text" value="<?php echo $_SESSION['update_text'];?>"><button name="update_title" type="submit" value="*">...</button></td></tr>
		<tr><td></td><td></td></tr>
		<tr><td>insert text</td><td><input name="insert_text" size="150" type="text" value="<?php echo $_SESSION['insert_text'];?>"><button name="insert_title" type="submit" value="*">...</button></td></tr>
	</table>
</form>

<?php
if ($_SESSION['title_id'] == "" && $_SESSION['title_lang'][0] == "0" && $_SESSION['title_text'] == "") $w = "";
elseif ($_SESSION['title_id'] == "" && $_SESSION['title_lang'][0] == "0" && $_SESSION['title_text'] != "") $w = "title_text LIKE '%".$_SESSION['title_text']."%'";
elseif ($_SESSION['title_id'] == "" && $_SESSION['title_lang'][0] != "0" && $_SESSION['title_text'] == "") $w = "id_language = ".$_SESSION['title_lang'][0];
elseif ($_SESSION['title_id'] == "" && $_SESSION['title_lang'][0] != "0" && $_SESSION['title_text'] != "") $w = "id_language = ".$_SESSION['title_lang'][0]." AND title_text LIKE '%".$_SESSION['title_text']."%'";
elseif ($_SESSION['title_id'] != "" && $_SESSION['title_lang'][0] == "0" && $_SESSION['title_text'] == "") $w = "id_title = ".$_SESSION['title_id'];
elseif ($_SESSION['title_id'] != "" && $_SESSION['title_lang'][0] == "0" && $_SESSION['title_text'] != "") $w = "id_title = ".$_SESSION['title_id']." AND title_text LIKE '%".$_SESSION['title_text']."%'";
elseif ($_SESSION['title_id'] != "" && $_SESSION['title_lang'][0] != "0" && $_SESSION['title_text'] == "") $w = "id_title = ".$_SESSION['title_id']." AND id_language = ".$_SESSION['title_lang'][0];
else $w = "id_title = ".$_SESSION['title_id']." AND id_language = ".$_SESSION['title_lang'][0]." AND title_text LIKE '%".$_SESSION['title_text']."%'";
$res = mysqli_query($dbh, "SELECT * FROM interface_texts ".(($w == "") ? "": " WHERE ".$w)." ORDER BY id_title, id_language");
if ($res)
{
	echo "<table>";
	while ($row = mysqli_fetch_row($res))
	{
		echo "<tr valign='top'>";
			echo "<td class='data_num'>".$row[0]."</td>";
			echo "<td>".$row[1]."</td>";
			echo "<td><b>".$row[2]."</b></td>";
		echo "</tr>";
	}
	echo "</table>";
	mysqli_free_result($res);
}
?>
