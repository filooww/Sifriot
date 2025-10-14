<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");

function GetFileNum($p_files, $f_n)
{
	if ($f_n == "")
	{
		$max_num = 0;
		foreach (array_keys($p_files) as $k) if ((integer)$k > $max_num) $max_num = (integer)$k;
		return (string)($max_num + 1);
	}
	else return $f_n;
}
function GetItemFile($dbh, $item_field_key, $item_id, $f_num, $attach, $file_field_key)
{
	$ff = false;
	$res = mysqli_query($dbh, "SELECT * FROM ".$attach." WHERE ".$item_field_key." = ".(string)$item_id." AND ".$file_field_key." = ".$f_num." LIMIT 1");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
        {
            $ff = true;
        }
		mysqli_free_result($res);
	}
	return $ff;
}
function UpdateOneItemFile($dbh, $p_con, $item_field_key, $item_id, $k, $v, $vi, $attach, $file_field_key, &$Mes)
{
	$arr_query = array();
	foreach ($v as $k0 => $v0)
	{
		if ($v0 != $vi[$k0] && $k0 != $_SESSION['spec_fld'][2]['URL_link']) //~~url

		{
			if ($v0['f_type'] == "date") $arr_query[] = ($v0 == "") ? $k0." = 0" : $k0." = ".(string)$v0;
			else $arr_query[] = $k0." = '".$v0."'";
			if ($v0['f_lower']) $arr_query[] = $k0."_low = '".MCV($v0, false)."'";
		}
	}
	if (count($arr_query) > 0)
	{
		mysqli_query($dbh, "UPDATE ".$attach." SET ".implode(", ", $arr_query)."  WHERE ".$item_field_key." = ".(string)$item_id." AND ".$file_field_key." = ".$k);
		if (mysqli_errno($dbh) > 0) $Mes[] = array("time"=>"", "text"=>mysqli_error($dbh), "status"=>"error");
	}
}
function InsertOneItemFile($dbh, $p_con, $item_id, $k, $v, &$Mes)
{
	$arr_fields = array();
	$arr_values = array();
		$_SESSION['p_files']['e'][$_SESSION['load_file_number']][$_SESSION['spec_fld'][2]['URL_file']] = $_FILES['db_filename']['name'];
		$_SESSION['p_files']['e'][$_SESSION['load_file_number']][$_SESSION['spec_fld'][2]['URL_link']] = $d_file;
		foreach ($p_con as $k0 => $v0)
		{
			if ($v0['own_table'] == 0 && $v0['f_type'] != $_SESSION['spec_fld'][2]['URL_link'])
			{
				if ($v0['f_type'] == "date") $arr_fields[] = ($v0 == "") ? $k0." = 0" : $k0." = ".(string)$v0;
				else $arr_fields[] = $v['f_ref'];
				if ($v['f_key']) $arr_values[] = $k;
				if ($v['f_typr'] == "integer") $_SESSION['p_files']['e'][$_SESSION['load_file_number']][$k] = 0;
				else $_SESSION['p_files']['e'][$_SESSION['load_file_number']][$k] = "";
			}
		}
/*
	$arr_fields[] = "`ord_num`";
	$arr_values[] = $k;
	$arr_fields[] = "`id_publication`";
	$arr_values[] = $pub_id;
	foreach ($v as $k0 => $v0) 
	{
		if ($k0 != "url_file") //~~url
		{
			$arr_fields[] = $k0;
			if ($k0 == "file_issue_year") $arr_values[] = ($v0 == "") ? "0" : $v0;
			else $arr_values[] = "'".$v0."'";
		}
	}
	$arr_fields[] = "`file_name_low`";
	$arr_values[] = "'".MCV($v['file_name'], false, $trans_codes)."'";
*/
	mysqli_query($dbh, "INSERT INTO files (".implode(", ", $arr_fields).") VALUES (".implode(",", $arr_values).")"); // NEW
	if (mysqli_errno($dbh) > 0) $Mes[] = array("time"=>"", "text"=>mysqli_error($dbh), "status"=>"error");
}
function DeleteOneItemFile($dbh, $item_key_key, $item_id, $attach, $attach_key, $ord_num, $URL_path, &$Mes)
{
	if (ResExists($URL_path)) unlink($URL_path);
	mysqli_query($dbh, "DELETE FROM ".$attach." WHERE ".$item_key_id." = ".(string)$item_id." AND ".$attach_key." = ".$ord_num);
	if (mysqli_errno($dbh) > 0) $Mes[] = array("time"=>"", "text"=>mysqli_error($dbh), "status"=>"error");
}
function ChangeItemFiles($dbh, $item_id, &$p_files, $URL_path, &$Mes)
{
	SetFileRows($p_files['e']);
	foreach ($p_files['e'] as $k => $v)
	{
		if (array_key_exists($k, $p_files['i'])) UpdateOneItemFile($dbh, $item_id, $k, $v, $p_files['i'][$k], $Mes);
		elseif (!GetItemFile($dbh, $item_id, $k)) InsertOneItemFile($dbh, $item_id, $k, $v, $Mes);
	}
	foreach ($p_files['i'] as $k => $v) if (!array_key_exists($k, $p_files['e'])) DeleteOneItemFile($dbh, $item_id, $k, $v, $v['URL_p'], $Mes);
	DeleteDeleted($dbh, $URL_path, $item_id, $p_files['e'], $Mes);
}
function SetFileRows(&$p_files)
{
	foreach ($_POST as $str_key => $str_v)
	{
		$arr_key = explode("-", $str_key);
		if (count($arr_key) > 1 && substr($arr_key[0], 0, 5) == "file_" && $p_files[$arr_key[1]][$arr_key[0]] != $str_v) $p_files[$arr_key[1]][$arr_key[0]] = $str_v;
	}
}
function DeleteDeleted($dbh, $dirname, $p_code, $p_files, &$Mes)
{
	if (is_dir($dirname))
	{
		$dir = opendir($dirname);
		if ($dir)
		{
			while (($file = readdir($dir)) !== false) if ($file != "." && $file != ".." )
			{
				$ff = pathinfo($file, PATHINFO_FILENAME);
				$arr_file = explode("-", $ff);
				if (count($arr_file) == 2 && $arr_file[0] == (string)$p_code && !array_key_exists($arr_file[1], $p_files)) unlink($dirname."/".$file);
			}
			closedir($dir);
		}
	}
	$str_keys = implode(",", array_keys($p_files));
	if ($str_keys != "")
	{
		mysqli_query($dbh, "DELETE FROM files WHERE id_publication = ".(string)$p_code." AND ord_num NOT IN (".$str_keys.")");
		if (mysqli_errno($dbh) > 0) $Mes[] = array("time"=>"", "text"=>mysqli_error($dbh), "status"=>"error");
	}
}
function FindFileInArray($p_files, $file_name)
{
	foreach ($p_files as $f_num => $f_inf) if ($f_inf['file_name'] == $file_name) return $f_num;
	return "";
}
function FileUploading(&$s, $file_number, &$Mes)
{
	if (empty($s['URL_p'])) $Mes[] = array("time"=>"", "text"=>"Destination directory is not specified", "status"=>"error");
	else
	{
		$s['file_update'] = ($file_number != ""); 
		if ($file_number == "") $s['load_file_number'] = GetFileNum($s['p_files']['e'], $file_number); 
		else $s['load_file_number'] = $file_number;
		if ($s['p_code'] > 0) return true;
		return false;
	}
	return false;
}

?>
