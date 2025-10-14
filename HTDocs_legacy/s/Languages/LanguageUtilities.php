<?php
function ReadLanguages($dbh, $param = true)
{
	$langs = array();
	$res = mysqli_query($dbh, "SELECT * FROM languages");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
        {
	        if ($param) $langs[$row[0]] = array($row[1], $row[1], "", false);
            else $langs[$row[0]] = $row[1];
        }
		mysqli_free_result($res);
	}
	ksort($langs);
	if ($param) $langs['new'] = array("", "", "", false);
	return $langs;
}
function SetLanguageList($list_type) // 0 - title, 1 - local, 2 - user
{
    $langs = array();
    if ($list_type == 0) $langs[0] = "--".Title(441)."--";
    foreach ($_SESSION['common_langs'] as $k => $v) if ($list_type == 0 && $k != 0 || $list_type == 2 && $k != 0 || $list_type == 1 && $k != 1) $langs[$k] = $v;
    return $langs;
}
function CorrectLanguageList($dbh_sys)
{
    $fl_change = array("special"=>false, "main"=>false);
    UpdateSystemLanguage(0, "(special)", $fl_change['special']);
    UpdateSystemLanguage(1, "English", $fl_change['main']);
    if ($fl_change['special'] || $fl_change['main'])
    {
        ksort($_SESSION['common_langs']);
	    mysqli_query($dbh_sys, "DELETE FROM languages");
	    mysqli_query($dbh_sys, "ALTER TABLE languages AUTO_INCREMENT = 1");
	    $ins_arr = array();
        foreach ($_SESSION['common_langs'] as $k => $v) $ins_arr[] = "(".(string)$k.",'".VValue($v)."')";
        mysqli_query($dbh_sys, "INSERT INTO languages VALUES ".implode(",", $ins_arr));
        $_SESSION['user_langs'] = SetLanguageList(2);
    }
}
function UpdateSystemLanguage($sys_id, $sys_name, &$fl_change)
{
    if (!isset($_SESSION['common_langs'][$sys_id]))
    {
        $k = array_search($sys_name, $_SESSION['common_langs']);
        if ($k !== false) ChangeInvalidLanguage($k, $sys_name);
        AddSystemLanguage($sys_id, $sys_name);
        $fl_change = true;
    }
    elseif ($_SESSION['common_langs'][$sys_id] != $sys_name)
    {
        AddInvalidLanguage($sys_id, $_SESSION['common_langs'][$sys_id], $sys_name);
        $k = array_search($sys_name, $_SESSION['common_langs']);
        if ($k !== false) ChangeInvalidLanguage($k, $sys_name);
        AddSystemLanguage($sys_id, $sys_name);
        $fl_change = true;
    }
}
function AddInvalidLanguage($k, $v, $sys_name)
{
    $new_id = NewTableID($_SESSION['common_langs'], 1);
    $_SESSION['common_langs'][$new_id] = $v."~";
    ksort($_SESSION['common_langs']);
    $_SESSION['pre_lang_err'][$new_id] = array("*-181", "<b>".$v."</b>", "*201", "*-147", "<b>".(string)$k."</b>, ", "*439", "*468", "*82", "*-147", "*624", "<b>".$new_id."</b>", "*82", "<b>".$v."~</b>", "*534");
}
function AddSystemLanguage($sys_id, $sys_name)
{
    $_SESSION['common_langs'][$sys_id] = $sys_name;
    $_SESSION['pre_lang_err'][$sys_id] =  array("*276");
}
function ChangeInvalidLanguage($k, $sys_name)
{
    $_SESSION['common_langs'][$k] = $sys_name."~";
    $_SESSION['pre_lang_err'][$k] = array("*-181", "<b>".$sys_name."</b>", "*469", "<b>".$_SESSION['common_langs'][$k]."</b>");
}
?>
