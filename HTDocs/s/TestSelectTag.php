<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.data_error {background-color:#FFCCFF;}</style>
</head>
<SCRIPT language=JavaScript>
function usings_on() {document.test_form.usings_s.value = '*'; test_form.submit();}
</SCRIPT>

<?php
//function SelectTag($select_name, $options, $selected_value, $h_name = "", $by_key = false, $value_key = "", $on_func = "", $disabled_user = false, $display_invalid = true, $tag_class = "", $tag_title = "")
{
	$ttl = ($tag_title == "") ? "" : " title='".$tag_title."'";
	$dis = ($disabled_user) ? " disabled" : "";
	if ($on_func == "") echo "<select ".$ttl.$tag_class." name='".$select_name."'".$dis.">";
	else
    {
        if (strpos($on_func, "(") === false) echo "<select".$ttl.$tag_class." name='".$select_name."' id='".$select_name."' onchange='".$on_func."()'".$dis.">";
        else echo "<select".$ttl.$tag_class." name='".$select_name."' id='".$select_name."' onchange='".$on_func."'".$dis.">";
    }
	if ($display_invalid)
	{
	    if ($by_key)
	    {
            if (!isset($options[$selected_value])) $invalid_value = "<<>>";
	    }
	    else
	    {
            if (!in_array($selected_value, $options)) $invalid_value = "<<".$selected_value.">>";
        }
	}
	$its_array = (gettype(reset($options)) == "array");
	foreach ($options as $k => $v)
	{
        $sel = ($by_key && (string)$k == (string)$selected_value || !$by_key && (string)$v == (string)$selected_value) ? " selected" : "";
		$ov = ($its_array) ? $v[$value_key] : $v;
		echo "<option".$sel.">".$ov."</option>";
	}
	if (isset($invalid_value)) echo "<option selected disabled>".$invalid_value."</option>";
	echo "</select>";
	if ($h_name != "") echo "<input type='hidden' name='".$h_name."' id='".$h_name."' value=''>";
}
function TestBool($b)
{
	if ($b) return "Y";
	else return "N";
}
function FTM($source_value, $to_up = false, $coding = "utf-8")
{
	$first_char = mb_substr($source_value, 0, 1, $coding);
    if ($to_up) $first_char_modif = mb_strtoupper($first_char, $coding);
    else $first_char_modif = mb_strtolower($first_char, $coding);
	$source_len = mb_strlen($source_value, $coding);
	$rest_part = mb_substr($source_value, 1, $source_len - 1, $coding);
    return $first_char_modif.$rest_part;
}
function GetManagerDBFile($dbname, $pass_file, $coding = "")
{
	$fl = false;
	if (file_exists($pass_file))
	{
		$arrAuth = file($pass_file, FILE_IGNORE_NEW_LINES);
		if (count($arrAuth) > 2)
		{
            $_SESSION['serv'] = array("host" => $arrAuth[0], "user" => $arrAuth[1], "pass" => $arrAuth[2]);
            $dbh = GetOnlyDB($dbname, $coding);
			$fl = true;
		}
	}
	return ($fl) ? $dbh : false;
}
function GetOnlyDB($dbname, $coding)
{
	error_reporting(0);
	$dbh = mysqli_connect($_SESSION['serv']['host'], $_SESSION['serv']['user'], $_SESSION['serv']['pass'], $dbname);
	error_reporting(E_ALL);
	if ($dbh && $coding != "") mysqli_query($dbh, "SET NAMES '".$coding."'");
    return $dbh;
}
function GetTitlesByLanguage($dbh_sys, $lang_id)
{
	$arr_titles = array();
	$res = mysqli_query($dbh_sys, "SELECT id_title, title_text FROM interface_texts WHERE id_language = ".(string)$lang_id);
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
        {
            $arr_titles[(string)$row[0]] = $row[1];
        }
		mysqli_free_result($res);
	}
	return $arr_titles;
}
function Title($title_id)
{
	if (isset($_SESSION['titles'][(string)$title_id])) return $_SESSION['titles'][(string)$title_id];
	else return "Missing text ".(string)$title_id;
}
function ReadLanguages($dbh)
{
	$langs = array();
	$res = mysqli_query($dbh, "SELECT * FROM languages");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
        {
            $langs[$row[0]] = AddLanguage($row);
        }
		mysqli_free_result($res);
	}
	ksort($langs);
	return $langs;
}
function AddLanguage($row)
{
    $add_lang = $row[1];
	return $add_lang;
}
function SetLanguageList($list_type) // 0 - title, 1 - local, 2 - user
{
    $langs = array();
    if ($list_type == 0) $langs[0] = "--".Title(441)."--";
    foreach ($_SESSION['common_langs'] as $k => $v)
    {
        switch ($list_type)
        {
            case 0: case 2: if ($k != 0) $langs[$k] = $v; break;
            case 1:         if ($k != 1) $langs[$k] = $v; break;
        }
    }
    return $langs;
}
function AfterLangChoice($dbh_sys, $lang_name)
{
	$lang_key = array_search($_POST[$lang_name], $_SESSION['user_langs']);
	if ($lang_key !== false)
	{
		$_SESSION['user_lang'] = array($lang_key, $_POST[$lang_name]);
		$_SESSION['titles'] = GetTitlesByLanguage($dbh_sys, (integer)$lang_key);
	}
}
function ArrayToFile($f_name, $k, $a, $o)
{
	$log_file = fopen($f_name, "a");
	if (is_array($a))
	{
		fwrite($log_file, str_repeat("--", $o).$k." =>\r\n");
		fclose($log_file);
		$os = $o + 2;
		foreach ($a as $ka => $va) ArrayToFile($f_name, $ka, $va, $os);
	}
	else
	{
		$f = false;
		switch (gettype($a))
		{
			case "boolean": $b = TestBool($a); $f = true; break;
			case "integer": $b = (string)$a; $f = true; break;
			default       : $b = $a; $f = true; break;
		}
		if ($f) fwrite($log_file, str_repeat("--", $o).$k." => ".$b."\r\n");
		fclose($log_file);
	}
}
function GetSpecialTexts($dbh_sys, $special_type)
{
	$arr = array();
	$res = mysqli_query($dbh_sys, "SELECT * FROM interface_special_texts WHERE special_type = '".$special_type."'");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			$str_arr = explode(",", $row[1]);
			foreach ($str_arr as $n)
			{
				if ((integer)$n < 0) $arr[] = FTM(Title(-(integer)$n));
				elseif ((integer)$n > 0) $arr[] = Title((integer)$n);
				else $arr[] = "";
			}
			mysqli_free_result($res);
		}
	}
	return $arr;
}

session_start();
$dbh = GetManagerDBFile("db_manager", $_SERVER['DOCUMENT_ROOT']."/s/_Credentials.txt");
if (count($_POST) == 0)
{
    $_SESSION['common_langs'] = ReadLanguages($dbh);
    $_SESSION['user_langs'] = SetLanguageList(2);
    $_SESSION['titles'] = GetTitlesByLanguage($dbh, 1);
    $_SESSION['field_using'] = GetSpecialTexts($dbh, "field_using");
}
if (isset($_POST['usings_s']))
{
//    $id = array_search($_POST['sel_local_lang'], $_SESSION['local_langs']);
//    $_SESSION['sel_local_lang'] = array($id, $_POST['sel_local_lang']);
}
$v = array("use_type"=>0, "illegals"=>"", "max_level"=>0, "separators"=>"", "catalog_id"=>"", "catalog_value"=>"", "group_type"=>0, "second_catalog"=>"", "table_title"=>"", "low_fields"=>"", "tab_err"=>array());
$dis = "";
?>
<form method="post" id="test_form" name="test_form">
    <table>
        <tr>
            <td>
                <?php
                SelectTag("using", $_SESSION['field_using'], $_SESSION['field_using'][0], false, "", "usings_on");
                ?>
            </td>
        </tr>
    </table>
    <input type="hidden" name="usings_s" value="">
</form>

<!---
/////SelectTag("user_lang", $_SESSION['user_langs'], $_SESSION['user_lang'][1], false, "", "user_lang_on");                "u_lang_s"
/////SelectTag("userlang",  $_SESSION['user_langs'], $_SESSION['user_lang'][1], false, "", "user_lang_on");                "ulangs"
/////SelectTag("user_p_use_lang|".$k, $_SESSION['user_langs'], $v[3], true, "", "", $t_user, UserDataColor($k, "data_error", "", 4), GetDataTitle($k, $v, 4));   ""
/////SelectTag("visit_category", $_SESSION['categories'], $_SESSION['category'][0], true, 0, "category_on", $dis_suspend);  "category_s"
/////SelectTag("pref_db|".$k, $_SESSION['arr_db'], (($f[12]) ? 0 : $v[5]), true, "db_name", "", $t_user, UserDataColor($k, "data_error", "", 6), GetDataTitle($k, $v, 6)); ""
/////SelectTag("titlelang_".$k, $free_langs, $v[3], true, "", "", $lang_dis);    ""
/////SelectTag("seltitlelang", $_SESSION['title_langs_all'], $_SESSION['sel_title_lang'][1], false, "", "", $id_v);        ""
/////SelectTag("seltitlelang", $_SESSION['title_langs_all'], $_SESSION['sel_title_lang'][1], false, "", "");               ""
/////SelectTag("use_type-".$k, $_SESSION['table_types'], $v['use_type'], true, "", "sel_use_type_on", $dis);               "use_type_s"
/////SelectTag("second_catalog-".$k, $_SESSION['single_catalogs'], $v['second_catalog'], false, "", "sel_second_catalog_on", $dis);     "second_catalog_s"
/////SelectTag("group_type-".$k, $_SESSION['group_types'], $v['group_type'], true, "", "", $dis);  ""
/////SelectTag("sel_local_lang", $_SESSION['local_langs'], $_SESSION['sel_local_lang'][0], true, "", "local_lang_on");  "l_lang_s"
???//SelectTag("using", $_SESSION['field_using'], $_SESSION['field_using'][0], false, "", "usings_on");  "usings_s"
//SelectTag($select_name, $options, ""); //	FormInvalidReference
-->


