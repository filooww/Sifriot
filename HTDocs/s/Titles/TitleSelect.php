<?php
function GetTitlesByLanguage($dbh, $lang_id)
{
	$arr_titles = array();
	$res = mysqli_query($dbh, "SELECT id_title, title_text FROM interface_texts WHERE id_language = ".(string)$lang_id);
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
        {
            $arr_titles[$row[0]] = $row[1];
        }
		mysqli_free_result($res);
	}
	return $arr_titles;
}
function Title($title_id)
{
	if (isset($_SESSION['titles'][$title_id])) return $_SESSION['titles'][$title_id];
	else return "Missing text ".(string)$title_id;
}
function SplitTitle($title_id)
{
	$title_split = explode("|", Title($title_id));
	echo "<table>";
		foreach ($title_split as $t_p) echo "<tr><td>".$t_p."</td></tr>";
	echo "</table>";
}
function TitleByDirs($dir_name)
{
	if (is_dir($dir_name))
	{
		$dir = opendir($dir_name);
		if ($dir)
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file != "." && $file != "..")
				{
					if (is_dir($dir_name."/".$file)) TitleByDirs($dir_name."/".$file);
					elseif (pathinfo($dir_name."/".$file, PATHINFO_EXTENSION) == "php") AddTitleIds(file_get_contents($dir_name."/".$file));
				}
			}
			closedir($dir);
		}
	}
}
function AddTitleIds($str_file)
{
	$p = 0;
	$i = 0;
	while ($i !== false)
	{
		$i = strpos($str_file, "Title(", $p);
		if ($i !== false)
		{
			$j = strpos($str_file, ")", $i + 6);
			if ($j !== false)
			{
				$title_id = substr($str_file, $i + 6, $j - $i - 6);
				if (is_numeric($title_id) && !in_array((integer)$title_id, $_SESSION['scripts_title_ids'])) $_SESSION['scripts_title_ids'][] = (integer)$title_id;
			}
			$p = $j + 1;
		}
	}
}

?>
