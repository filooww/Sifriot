<?php
function ReadFieldParameters()
{
	$field_param = array();
	foreach ($_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]] as $v)
	{
        $field_param['key'] = $v[0];
        $field_param['name'] = (($v[4] == "" && $_SESSION['f_k'][1] != "") ? $_SESSION['f_k'][1] : $v[4]);
        $field_param['type'] = $_SESSION['field_types'][$v[5]];
        $field_param['size'] = $v[8];
        $field_param['interval'] = $v[9];
        $field_param['blank'] = $v[10];
        $field_param['unique'] = $v[6];
        $field_param['s_mode'] = $v[11];
        $field_param['table'] = $v[12];
        $field_param['illegals'] = $v[13];
        $field_param['default'] = $v[7];
        $field_param['field_check'] = $v[14];
        $field_param['comm'] = $v[15];
        $field_param['filter_md'] = $_SESSION['compare_mode'][$v[16]];
        $field_param['sort_sm'] = $_SESSION['sort_mode'][$v[17]];
        $field_param['using'] = $v[3];
        $field_param['f_align'] = $_SESSION['field_align'][$v[18]];
        $field_param['t_prc'] = $v[19];
        $field_param['screen_order'] = $v[2];
	}
	return $field_param;
}
function CaseField($post_name, $par_name,  $js_on, $f_type, &$even, $td_align, $field_list, $comment, $Mes, $read_only = false, $align_text = "", $add_button = "", $add_class = "", $add_image = "")
{
	$dis = ($_SESSION['user_working_mode'] == 0) ? " disabled" : "";
	$cls = ($even) ? "color_even" : "color_odd";
	$t_cls = ($align_text == "") ? "" : " class='".$align_text."'";
	echo "<tr valign='top'>";
		echo "<td class='".$cls."'".(($td_align == "") ? "" : " align='".$td_align."'").">";
			if ($f_type == "text") echo "<input style='font-weight:bold' size='16' name='".$post_name."' type='text' ".$t_cls."value='".$_SESSION['field_param'][$par_name]."'".(($read_only) ? " readonly" : "").$dis."/>";
			elseif ($f_type == "check") echo "<button type='submit' name='".$post_name."' class='".$cls."' value='*'".$dis.JSF($js_on, $par_name).">".CheckImg($_SESSION['field_param'][$par_name], 16, 15, $_SESSION['user_working_mode'] == 0)."</button>";
			elseif ($f_type == "select")
			{
			    SelectTag($post_name, $field_list, $_SESSION['field_param'][$par_name], $h_name = "", false, "", $field_name."_on", $_SESSION['user_working_mode'] == 0);
//				echo "<select name='".$post_name."'".JSF($js_on, $par_name).$dis.">"; // SelectTag
//					foreach ($field_list as $v) echo OptionTag($_SESSION['field_param'][$par_name], $v);
//				echo "</select>";
			}
		echo "</td>";
		if ($add_button != "") echo "<td><button name='".$add_button."' type='submit' title='".Title(303)."' class='".$add_class."' value='*'".$dis.">".(($add_image == "") ? "" : ImgV($add_image, 10, 16))."</button></td>";
		else echo "<td class='".$cls."'></td>";
		echo "<td class='".$cls."'>".$comment."</td>";
		if (isset($Mes[$post_name])) echo "<td><font color='FF0000'>".CrMes($Mes[$post_name])."</font></td>";
		else echo "<td></td>";
	echo "</tr>";
	$even = !$even;
}
function JSF($js_on, $field_name)
{
	if ($js_on) return " onchange='".$field_name."_on();'";
	else return "";
}
function CrMes($mess_err)
{
	if ($mess_err[0] == "*") return substr($mess_err, 1)." <a name='view_err' title='".Title(405)."' href='FieldFormWarn.php' target='_blank'><img src='".$_SESSION['image_dir']."/GoToK.bmp' width='16' height='16'></a>";
	else return $mess_err;
}
function UsingProc($dbh, &$sw_break)
{
	if ($_POST['usings_s'] == "") $sw_break = false;
	else
	{
		$i_com = array_search($_POST['using'], $_SESSION['field_using']);
		if ($i_com !== false)
		{
			if ($_SESSION['field_param']['using'] == "") $_SESSION['field_param']['using'] = (string)$i_com;
			else
			{
				$arr = explode(",", $_SESSION['field_param']['using']);
				$i = array_search((string)$i_com, $arr);
				if ($i === false) $arr[] = (string)$i_com;
				else unset($arr[$i]);
				$_SESSION['field_param']['using'] = implode(",", $arr);
			}
		}
	}
}
function SaveFieldDefinition($dbh, &$Mes)
{
	if (TestFieldParameters($Mes))
	{
        SetFieldList();
        if (isset($_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]]))
        {
      		mysqli_query($dbh, "UPDATE field_config SET ".UpdatefieldSet()." WHERE own_table = ".(string)$_SESSION['f_k'][0]." AND f_ID = '".$_SESSION['f_k'][1]."'");
		    $Mes['0'] = "<font color='0000FF'><b>".Title(404)."</b></font>";
		    $_SESSION['field_change'] = true;
        }
        else
        {
		    mysqli_query($dbh, "IMSERT INTO field_config VALUES ".InsertValues());
		    $Mes['0'] = "<font color='0000FF'><b>".Title(404)."</b></font>";
		    $_SESSION['field_change'] = true;
        }
		return true;
	}
	else return false;
}
function UpdateFieldSet()
{
	$arr[] = "f_key = ".(($_SESSION['field_param']['key']) ? "1" : "0");
	$arr[] = "f_name = '".$_SESSION['field_param']['name']."'";
	$arr[] = "f_type = ".(string)array_search($_SESSION['field_param']['type'], $_SESSION['field_types']);
	$arr[] = "f_size = ".(string)$_SESSION['field_param']['size'];
	$arr[] = "f_interval = ".(($_SESSION['field_param']['interval']) ? "1" : "0");
	$arr[] = "f_blank = ".(($_SESSION['field_param']['blank']) ? "1" : "0");
	$arr[] = "f_unique = ".(($_SESSION['field_param']['unique']) ? "1" : "0");
	$arr[] = "f_s_mode = ".(($_SESSION['field_param']['s_mode']) ? "1" : "0");
	$arr[] = "f_table = '".$_SESSION['field_param']['table']."'";
	$arr[] = "f_illegals = '".$_SESSION['field_param']['illegals']."'";
	$arr[] = "f_default = '".$_SESSION['field_param']['default']."'";
	$arr[] = "f_check = ".(($_SESSION['field_param']['field_check']) ? "1" : "0");
	$arr[] = "comm = ".(($_SESSION['field_param']['comm']) ? "1" : "0");
	$arr[] = "f_filter_md = ".(string)array_search($_SESSION['field_param']['filter_md'], $_SESSION['compare_mode']);
	$arr[] = "f_sort_sm = ".(string)array_search($_SESSION['field_param']['sort_sm'], $_SESSION['sort_mode']);
	$arr[] = "f_using = '".$_SESSION['field_param']['using']."'";
	$arr[] = "f_align = ".(string)array_search($_SESSION['field_param']['f_align'], $_SESSION['field_align']);
	$arr[] = "table_percent = '".$_SESSION['field_param']['t_prc']."'";
	$arr[] = "screen_order = ".(string)$_SESSION['field_param']['screen_order'];
    return implode(",", $arr);
}
function InsertValues()
{
	$arr[] = (string)$_SESSION['f_k'][0];
	$arr[] = "'".$_SESSION['f_k'][1]."'";
	$arr[] = (($_SESSION['field_param']['key']) ? "1" : "0");
	$arr[] = "'".$_SESSION['field_param']['name']."'";
	$arr[] = (string)array_search($_SESSION['field_param']['type'], $_SESSION['field_types']);
	$arr[] = (string)$_SESSION['field_param']['size'];
	$arr[] = (($_SESSION['field_param']['interval']) ? "1" : "0");
	$arr[] = (($_SESSION['field_param']['blank']) ? "1" : "0");
	$arr[] = (($_SESSION['field_param']['unique']) ? "1" : "0");
	$arr[] = (($_SESSION['field_param']['s_mode']) ? "1" : "0");
	$arr[] = "'".$_SESSION['field_param']['table']."'";
	$arr[] = "'".$_SESSION['field_param']['illegals']."'";
	$arr[] = "'".$_SESSION['field_param']['default']."'";
	$arr[] = (($_SESSION['field_param']['field_check']) ? "1" : "0");
	$arr[] = (($_SESSION['field_param']['comm']) ? "1" : "0");
	$arr[] = (string)array_search($_SESSION['field_param']['filter_md'], $_SESSION['compare_mode']);
	$arr[] = (string)array_search($_SESSION['field_param']['sort_sm'], $_SESSION['sort_mode']);
	$arr[] = "'".$_SESSION['field_param']['using']."'";
	$arr[] = (string)array_search($_SESSION['field_param']['f_align'], $_SESSION['field_align']);
	$arr[] = "'".$_SESSION['field_param']['t_prc']."'";
	$arr[] = (string)$_SESSION['field_param']['screen_order'];
    return "(".implode(",", $arr).")";
}
function FindFieldDefinition($own_table, $f_par, $f_ind)
{
	$nrow = 1;
	foreach (array_keys($_SESSION['field_definitions']) as $ow)
	{
		if ($ow == $own_table)
		{
			foreach ($_SESSION['field_definitions'][$ow] as $v)
			{
				if ($v[$f_ind] == $f_par) return $nrow; //?????
				$nrow++;
			}
		}
		else $nrow++;
	}
	return 0;
}
function TestNumberValue($tested, &$Mes)
{
	if (!is_numeric($tested)) $Mes[$tested] = Title(77);
	elseif ((integer)$tested < 0 || (integer)$tested > 0 && $tested != (string)(floor((integer)$tested))) $Mes[$tested] = Title(350);
}
function TestChangedPercent($tested, $end_arr, &$Mes)
{
    $m_perc = array();
    for ($i = 0; $i < strlen($tested) && is_numeric(substr($tested, $i, 1)); $i++);
    if ($i == 0) $m_perc[] = Title(713);
    elseif (!in_array(substr($perc, $i), $end_arr)) $m_perc[] = Title(714);
    if (count($m_perc) > 0) $Mes = implode(", ", $m_perc);
}
function TestFieldParameters(&$Mes)
{
	$Mes = array();
	if ($_POST['name'] == "") $Mes['name'] = Title(341);
    if (!in_array($_POST['type'], $_SESSION['reference_catalogs'])) $Mes['type'] = Title(596)." <b>".$_POST['type']."/b>";
    if ($_POST['size'] != "") TestNumberValue($_POST['size'], $Mes);
    if (!in_array($_POST['table'], $_SESSION['reference_catalogs'])) $Mes['table'] = Title(596)." <b>".$_POST['table']."/b>";
    if (!in_array($_POST['filter_md'], $_SESSION['compare_mode'])) $Mes['filter_md'] = Title(596)." <b>".$_POST['filter_md']."/b>";
    if (!in_array($_POST['sort_sm'], $_SESSION['sort_mode'])) $Mes['sort_sm'] = Title(596)." <b>".$_POST['sort_sm']."/b>";
    if ($_POST['using'] != "")
    {
        $arr = explode(",", $_POST['using']);
        $arr_err = array();
        foreach ($arr as $z) if (!isset($_SESSION['field_using'][$z])) $arr_err[] = $z;
        if (count($arr_err) > 0) $Mes['using'] = Title(596)." (<b>".implode("; ", $arr_err)."</b>)";
    }
    if (!in_array($_POST['f_align'], $_SESSION['field_align'])) $Mes['f_align'] = Title(596)." <b>".$_POST['f_align']."/b>";
    if ($_POST['t_prc'] != "") TestChangedPercent($_POST['t_prc'], $Mes);
    if ($_POST['screen_order'] != "") TestNumberValue($_POST['screen_order'], $Mes);
    $test_repeat = TestRepeatValuesForField();
    if (count($arr_rep['name']) > 0)
    {
        if (isset($Mes['name'])) $Mes['name'] .= ", ".Tilte(490)." <b>".Title(347)." (".$_POST['name'].")</b> ".Title(587)." ".((count($test_repeat['name']) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $test_repeat['name']);
        else $Mes['name'] = Tilte(490)." <b>".Title(347)." (".$_POST['name'].")</b> ".Title(587)." ".((count($test_repeat['name']) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $test_repeat['name']);
    }
    
    if (count($arr_rep['screen']) > 0)
    {
        if (isset($Mes['screen_order'])) $Mes['screen_order'] .= ", ".Tilte(490)." <b>".Title(347)." (".$_POST['screen_order'].")</b> ".Title(587)." ".((count($test_repeat['screen']) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $test_repeat['screen']);
        else $Mes['screen_order'] = Tilte(490)." <b>".Title(347)." (".$_POST['screen_order'].")</b> ".Title(587)." ".((count($test_repeat['screen']) == 1) ? Title(655) : Title(656))." <b>".implode(", ", $test_repeat['screen']);
    }
}
function TestRepeatValuesForField()
{
    $arr_rep = array("name"=>array(), "screen"=>array());
    foreach ($_SESSION['field_definitions'][$_SESSION['f_k'][0]] as $field_key => $f_params)
    {
        if ($field_key != $_SESSION['f_k'][z1])
        {
            if ($_POST['name'] == $f_params[4]) $arr_rep['name'][] = $field_key;
            if ($f_params[2] != 0 && is_numeric($_POST['screen_order']) && $f_params[2] == (integer)$_POST['screen_order']) $arr_rep['screen'][] = $field_key;
        }
    }
    return $arr_rep;
}
function SetUserLangField($dbh, &$sw_break)
{
	if (AfterLangChoice($dbh, "user_lang_s", "user_lang", $sw_break))
	{
		$_SESSION['compare_mode'] = GetSpecialTexts($dbh, "compare_mode");
		$_SESSION['sort_mode'] = GetSpecialTexts($dbh, "sort_mode");
		$_SESSION['field_align'] = GetSpecialTexts($dbh, "field_align");
		$_SESSION['field_using'] = GetSpecialTexts($dbh, "field_using");
		$_SESSION['field_types'] = GetSpecialTexts($dbh, "field_types");
	}
}
function AfterTableChoice(&$sw_break)
{
	if ($_POST['table_s'] == "") $sw_break = false;
	else $_SESSION['field_param']['table'] = $_POST['table'];
}
function AfterFilterModeCheck()
{
	if ($_SESSION['field_param']['s_mode'])
	{
		$_SESSION['field_param']['filter_md'] = FTM(Title(69));
		$_SESSION['field_param']['sort_sm'] = Title(393);
	}
}
function SetFieldList()
{
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][0] = $_SESSION['field_param']['key'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][4] = $_SESSION['field_param']['name'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][5] = array_search($_SESSION['field_param']['type'], $_SESSION['field_types']);
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][8] = $_SESSION['field_param']['size'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][9] = $_SESSION['field_param']['interval'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][10] = $_SESSION['field_param']['blank'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][6] = $_SESSION['field_param']['unique'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][11] = $_SESSION['field_param']['s_mode'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][12] = $_SESSION['field_param']['table'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][13] = $_SESSION['field_param']['illegals'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][7] = $_SESSION['field_param']['default'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][14] = $_SESSION['field_param']['field_check'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][15] = $_SESSION['field_param']['comm'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][16] = array_search($_SESSION['field_param']['filter_md'], $_SESSION['compare_mode']);
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][17] = array_search($_SESSION['field_param']['sort_sm'], $_SESSION['sort_mode']);
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][3] = $_SESSION['field_param']['using'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][18] = array_search($_SESSION['field_param']['f_align'], $_SESSION['field_align']);
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][19] = $_SESSION['field_param']['t_prc'];
    $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][2] = $_SESSION['field_param']['screen_order'];
}
function FieldDefinitionExit(&$Mes)
{
	if ($_SESSION['user_working_mode'] == 0 || $_SESSION['user_working_mode'] == 1 && TestFieldParameters($Mes))
	{
        SetFieldList();
		$_SESSION['field_param'] = array();
		return true;
	}
	else
	{
	    $test_err = array();
	    if (isset($Mes['name'])) $test_err[] = $Mes['name'];
        if (isset($Mes['type'])) $test_err[] = $Mes['type'];
        if (isset($Mes['size'])) $test_err[] = $Mes['size'];
        if (isset($Mes['table'])) $test_err[] = $Mes['table'];
        if (isset($Mes['filter_md'])) $test_err[] = $Mes['filter_md'];
        if (isset($Mes['sort_sm'])) $test_err[] = $Mes['sort_sm'];
        if (isset($Mes['using'])) $test_err[] = $Mes['using'];
        if (isset($Mes['f_align'])) $test_err[] = $Mes['f_align'];
        if (isset($Mes['t_prc'])) $test_err[] = $Mes['t_prc'];
        if (isset($Mes['screen_order'])) $test_err[] = $Mes['screen_order'];
        if (count($test_err) == 0) $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][20] = "";
        else $_SESSION['field_definitions'][$_SESSION['f_k'][0]][$_SESSION['f_k'][1]][20] = implode("; ", $test_err);
		$Mes['0'] = "<font color='FF0000'>".Title(223)."</font>";
		return false;
	}
}
function PostFieldToParam()
{
	if (isset($_POST['screen_order'])) $_SESSION['field_param']['screen_order'] = $_POST['screen_order'];
	if (isset($_POST['name'])) $_SESSION['field_param']['name'] = $_POST['name'];
	if (isset($_POST['type'])) $_SESSION['field_param']['type'] = $_POST['type'];
	if (isset($_POST['f_align'])) $_SESSION['field_param']['f_align'] = $_POST['f_align'];
	if (isset($_POST['size'])) $_SESSION['field_param']['size'] = $_POST['size'];
	if (isset($_POST['t_prc'])) $_SESSION['field_param']['t_prc'] = $_POST['t_prc'];
	if (isset($_POST['table'])) $_SESSION['field_param']['table'] = $_POST['table'];
	if (isset($_POST['illegals'])) $_SESSION['field_param']['illegals'] = $_POST['illegals'];
	if (isset($_POST['default'])) $_SESSION['field_param']['default'] = $_POST['default'];
	if (isset($_POST['filter_md'])) $_SESSION['field_param']['filter_md'] = $_POST['filter_md'];
	if (isset($_POST['sort_sm'])) $_SESSION['field_param']['sort_sm'] = $_POST['sort_sm'];
}
function GetRefCatalogs()
{
	$catalog_list = array("");
	foreach ($_SESSION['table_definitions'] as $k => $v) if ($v['use_type'] == 3 && $v['second_catalog'] == "") $catalog_list[] = $k;
	return $catalog_list;
}

?>
