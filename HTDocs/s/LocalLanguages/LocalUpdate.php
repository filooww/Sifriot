<?php
function RewriteLocalCodes($dbh, &$Mes)
{
	$fl = false;
	$arr_del = array();
 	foreach ($_SESSION['trans_codes'] as $k => $v)
	{
		if ($v['new'])
		{
		    mysqli_query($dbh, "INSERT INTO translate_table VALUES (".(string)$_SESSION['sel_coding'][0].",".(string)$v['lang_code'].",'".$k."','".$v['translit_set']."')");
		    $fl = true;
		}
		elseif (isset($_POST["translit_set|".$k]) && $v['translit_set'] != $_POST["translit_set|".$k])
        {
            mysqli_query($dbh, "UPDATE translate_table SET translit_letters = '".$_POST["translit_set|".$k]."' WHERE id_coding = ".(string)$_SESSION['sel_coding'][0]." AND letter = '".$k."'"); // what a language ?
            $fl = true;
		}
	}
    $_SESSION['new_symb_param'] = array("", "");
	if ($fl) $Mes[] = "<font color='#0000FF'><b>".Title(199)."</b></font>";
	if (isset($_SESSION['change_lang_letter'])) unset($_SESSION['change_lang_letter']);
}
function TestSymbolUnique($dbh, &$arr_err)
{
    $fl = array(0, "");
	$res = mysqli_query($dbh, "SELECT id_lang FROM translate_table WHERE letter = '".$_POST['new_symbol']."' AND id_coding = ".(string)$_SESSION['sel_coding'][0]);
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
            $lang_name = (isset($_SESSION['local_langs'][$row[0]])) ? $_SESSION['local_langs'][$row[0]] : "<<>>";
		    $fl = array($row[0], $lang_name);
		}
		mysqli_free_result($res);
		if ($fl[1] != "") $arr_err[] = "<font color='#FF0000'><b>".$_POST['new_symbol']."</b> - ".Title(579)." ".Title(587)." ".FTM(Title(231))." <b>".$fl[1]."</b> (".Title(614)." <b>".$fl[0]."</b>)</font>";
	}
}
function AddNewSymbol($dbh)
{
    $arr_err = TestNewSymbol($dbh);
    if (count($arr_err) == 0)
    {
	    $arr_sym = array(ord(substr($_POST['new_symbol'], 0)), ord(substr($_POST['new_symbol'], 1)), ord(substr($_POST['new_symbol'], 2)));
	    $_SESSION['trans_codes'][$_POST['new_symbol']] = array("lang_code"=>(integer)$_SESSION['sel_local_lang'][0], "b_0"=>$arr_sym[0], "b_1"=>$arr_sym[1], "b_2"=>$arr_sym[2], "translit_set"=>"", "new"=>true, "del"=>false, "err"=>$arr_err);
	    ksort($_SESSION['trans_codes']);
	    $_SESSION['change_lang_letter'] = array($_POST['new_symbol'], false);
	    $_SESSION['new_symb_param'][1] = "<font color='#0000FF'>".FTM(Title(277))." ".Title(586)."</font>";
	}
	else $_SESSION['new_symb_param'][1] = implode("; ", $arr_err);
}
function TestNewSymbol($dbh)
{
    $arr_err = array();
    if ($_POST['new_symbol'] == "") $arr_err[] = "<font color='#FF0000'>".FTM(Title(277))." ".Title(573)."</font>";
    else
    {
        if (strpos($_POST['new_symbol'], chr(39)) !== false) $arr_err[] = "<font color='#FF0000'>".FTM(Title(277))." ".Title(572)."</font>";
        if (mb_strlen($_POST['new_symbol'], 'utf-8') > 1) $arr_err[] = "<font color='#FF0000'>".Title(578)."</font>";
        elseif (ord($_POST['new_symbol']) > 31 && ord($_POST['new_symbol']) < 127) $arr_err[] = "<font color='#FF0000'>".Title(671)."</font>";
        else TestSymbolUnique($dbh, $arr_err);
    }
    return $arr_err;
}
function TestTranslateRow($k, $v)
{
    $arr_err = array();
    $_SESSION['trans_codes'][$k]['err'] = array();
    if ($k == "") $arr_err[] = "<font color='#FF0000'>".FTM(Title(277))." ".Title(573)."</font>";
    else
    {
        if (strpos($k, chr(39)) !== false) $arr_err[] = "<font color='#FF0000'>".FTM(Title(277))." ".Title(572)."</font>";
        if (mb_strlen($k, 'utf-8') > 1) $arr_err[] = "<font color='#FF0000'>".Title(578)."</font>";
        elseif (ord($k) > 31 && ord($k) < 127) $arr_err[] = "<font color='#FF0000'>".Title(671)."</font>";
    }
    if (isset($_SESSION['local_langs'][$v['lang_code']]))
    {
        if ($v['lang_code'] > 1 && $v['translit_set'] == "") $arr_err[] = "<font color='#FF0000'>".FTM(Title(285))." ".Title(573)."</font>";
        if ($v['lang_code'] == 0 && $v['translit_set'] != "") $arr_err[] = "<font color='#FF0000'>".Title(659)."</font>";
    }
    if (!isset($_SESSION['local_langs'][$v['lang_code']])) $arr_err[] = "<font color='#FF0000'>".FTM(Title(602))." ".Title(542)." (<b>".(string)$v['lang_code']."</b>)</font>";
    if (gettype($_SESSION['sel_local_lang'][0]) == "string" && !isset($_SESSION['coding_list'][$v['id_code']])) $arr_err[] = "<font color='#FF0000'>".Title(331)." (<b>".(string)$v['id_code']."</b>)</font>";
    if (strpos($v['translit_set'], chr(39)) !== false) $arr_err[] = "<font color='#FF0000'>".FTM(Title(285))." ".Title(572)."</font>";
    if (TransliteratonSetError($v['translit_set'])) $arr_err[] = "<font color='#FF0000'>".FTM(Title(285))." ".Title(657).", ".Title(666)."</font> <b><font class='del_symb'>|</font></b>";
    return $arr_err;
}
function AfterLocalLangChoice(&$Mes, &$sw_break)
{
	if ($_POST['local_lang_s'] == "")
    {
        $sw_break = false;
        return false;
    }
    $_SESSION['new_symb_param'] = array("", "");
    if (isset($_SESSION['change_lang_letter'])) unset($_SESSION['change_lang_letter']);
    $_SESSION['local_langs_for_page'] = ChangeLocalLangs();
    return true;
}
function AfterChoiceCoding(&$Mes, &$sw_break)
{
	if ($_POST['sel_coding_s'] == "")
    {
        $sw_break = false;
        return false;
    }
    $_SESSION['new_symb_param'] = array("", "");
    if (isset($_SESSION['change_lang_letter'])) unset($_SESSION['change_lang_letter']);
    $new_coding = array_search($_POST['sel_coding'], $_SESSION['coding_list']);
    $_SESSION['sel_coding'] = array($new_coding, $_POST['sel_coding']);
    return true;
}
function TestAllLocalCodes(&$Mes, $mes_del = false)
{

    $fl = true;
    $arr_del = array();
	foreach ($_SESSION['trans_codes'] as $k => $v)
	{
        if (in_array($k, $_SESSION['del_local']) !== false) $arr_del[] = "<font class='del_symb'><b>".$k."</b></font>";
        else
        {
            $arr_err = TestTranslateRow($k, $v);
   	        if (count($arr_err) > 0)
            {
                $_SESSION['trans_codes'][$k]['err'] = $arr_err;
                $fl = false;
            }
        }
	}
	if ($mes_del && count($arr_del) > 0) $Mes[] = MesDelTitles($arr_del);
	return $fl;
}
function MesDelTitles($arr_del)
{
    $symb = (count($arr_del) == 1) ? Title(277) : Title(584);
    $delt = (count($arr_del) == 1) ? Title(138) : Title(585);
    return "<font color='#0000FF'>".$symb."</font> ".implode(" ", $arr_del)." <font color='#0000FF'>".$delt;
}
function SetInvalidRefs()
{
    $invalid_letters = array();
    foreach ($_SESSION['pre_ref']['translate_table']['p'] as $type_arr) foreach ($type_arr['v'] as $letters) foreach ($letters as $letter) $invalid_letters[] = "'".$letter."'";
    if (count($invalid_letters) == 0) return array();
    return array_unique($invalid_letters);
}
function SetLocalLanguage($dbh, &$Mes)
{
    RewriteLocalCodes($dbh, $Mes);
    if (isset($_SESSION['local_langs_for_page']['nu']) && $_POST['sel_local_lang'] == $_SESSION['local_langs_for_page']['nu'])
    {
        $_SESSION['sel_local_lang'] = array("nu", $_POST['sel_local_lang']);
	    $_SESSION['trans_codes'] = SetTransCodes($dbh, SetInvalidRefs());
    }
    else
    {
        $lang_key = array_search($_POST['sel_local_lang'], $_SESSION['local_langs']);
        if ($lang_key !== false)
        {
            $_SESSION['sel_local_lang'] = array($lang_key, $_POST['sel_local_lang']);
            $_SESSION['sel_lang_for_change'] = $lang_key;
            $_SESSION['sel_coding_for_change'] = $_SESSION['sel_coding'][0];
            $_SESSION['trans_codes'] = SetTransCodes($dbh, $lang_key);
        }
   	}
    TestAllLocalCodes($Mes);
}
function MoveSymbol($dbh, $letter, &$Mes)
{
    if (gettype($_SESSION['sel_local_lang'][0]) == "string") $old_coding_id = $_SESSION['trans_codes'][$letter]['id_code'];
    else $old_coding_id = $_SESSION['sel_coding'][0];
    if (gettype($_SESSION['sel_local_lang'][0]) == "string") $new_coding_id = array_search($_POST["change_local_coding|".$letter], $_SESSION['coding_list']);
    else $new_coding_id = $_SESSION['sel_coding'][0];
    $old_lang_id = $_SESSION['trans_codes'][$letter]['lang_code'];
    $new_lang_id = array_search($_POST["change_local_lang|".$letter], $_SESSION['local_langs']);
    if ($old_coding_id == $new_coding_id && $old_lang_id == $new_lang_id) $Mes[] = "<font color='#0000FF'>".Title(244)."</font>";
    else
    {
        mysqli_query($dbh, "UPDATE translate_table SET id_coding = ".(string)$new_coding_id.", id_lang = ".(string)$new_lang_id." WHERE letter = '".$letter."'");
        if ($_SESSION['pre_ref']['translate_table']['over'])
        {
            unset($_SESSION['pre_ref']['translate_table']);
            SetReferenceTable($dbh, "translate_table", 125, QueryReferencesLocals(), array(array("cd", 0, 2, 281, -277), array("li", 1, 2, 542, -277)), $_SESSION['pre_ref']);
            ksort($_SESSION['pre_ref']);
            SetInitReplaceIDs("translate_table", array("cd"=>"coding_list", "li"=>"local_langs"));
        }
        else
        {
            if (isset($_SESSION['pre_ref']['translate_table']['p']['cd']['v'][(string)$old_coding_id])) ChangeInvalidRefTable($dbh, "translate_table", "cd", $old_coding_id, $letter);
            if (isset($_SESSION['pre_ref']['translate_table']['p']['li']['v'][(string)$old_lang_id])) ChangeInvalidRefTable($dbh, "translate_table", "li", $old_lang_id, $letter);
        }
        $_SESSION['new_symb_param'] = array("", "");
        if (isset($_SESSION['trans_codes'][$letter]['del']) && $_SESSION['trans_codes'][$letter]['del']) $_SESSION['trans_codes'][$letter]['del'] = false;
        $Mes[] = "<font color='#0000FF'>".Title(277)."</font> <font class='emph_data'><b>".$letter."</b></font> <font color='#0000FF'>".Title(586)."</font>";
        $_SESSION['sel_coding'] = array($new_coding_id, $_SESSION['coding_list'][$new_coding_id]);
        $_SESSION['sel_local_lang'] = array($new_lang_id, $_SESSION['local_langs'][$new_lang_id]);
        $_SESSION['sel_coding_for_change'] = $new_coding_id;
        $_SESSION['sel_lang_for_change'] = $new_lang_id;
        $_SESSION['trans_codes'] = SetTransCodes($dbh, $new_lang_id);
        $_SESSION['change_lang_letter'] = array($letter, false);
        TestAllLocalCodes($Mes);
    }
}
function AfterChoiceLocalLang($dbh, &$sw_break)
{
	if (AfterLangChoice($dbh, "user_lang_s", "user_lang", $sw_break))
	{
        if (PermitInvRef("translate_table", array("cd", "li"))) $_SESSION['local_langs_for_page']['nu'] = "-- ".FTM(Title(602))." ".Title(542)." ".Title(82)."/".Title(567)." ".Title(281)." --";
        if (isset($_POST['new_symbol']) && $_POST['new_symbol'] != "")
        {
            $arr_err = array();
            TestNewSymbol($dbh, $arr_err);
            if (count($arr_err) > 0) $_SESSION['new_symb_param'][1] = implode("; ", $arr_err);
        }
        return true;
	}
	return false;
}
function DeleteMarkedLetters($dbh)
{
	$arr_del = array();
 	foreach ($_SESSION['del_local'] as $k)
    {
        if (gettype($_SESSION['sel_local_lang'][0]) == "string") $coding_id = $_SESSION['trans_codes'][$k]['id_code'];
        else $coding_id = $_SESSION['sel_coding'][0];
        $arr_del[$k] = array($coding_id, $_SESSION['trans_codes'][$k]['lang_code']);
    }
    mysqli_query($dbh, "DELETE FROM translate_table WHERE letter IN ('".implode("','", $_SESSION['del_local'])."')");
    mysqli_query($dbh, "ALTER TABLE translate_table AUTO_INCREMENT = 1");
    foreach ($arr_del as $k => $v)
    {
        if ($_SESSION['pre_ref']['translate_table']['over'])
        {
            unset($_SESSION['pre_ref']['translate_table']);
            SetReferenceTable($dbh, "translate_table", 125, QueryReferencesLocals(), array(array("cd", 0, 2, 281, -277), array("li", 1, 2, 542, -277)), $pre_ref);
            ksort($_SESSION['pre_ref']);
            SetInitReplaceIDs("translate_table", array("cd"=>"coding_list", "li"=>"local_langs"));
        }
        else
        {
            if (isset($_SESSION['pre_ref']['translate_table']['p']['cd']['v'][(string)$v[0]])) ChangeInvalidRefTable($dbh, "translate_table", "cd", $v[0], $k);
            if (isset($_SESSION['pre_ref']['translate_table']['p']['li']['v'][(string)$v[1]])) ChangeInvalidRefTable($dbh, "translate_table", "li", $v[1], $k);
        }
        unset($_SESSION['trans_codes'][$k]);
    }
}
function LocalSetQuestionString()
{
    $arr = array();
    foreach ($_SESSION['del_local'] as $k) $arr[] = $k;
    return $arr;
}

?>
