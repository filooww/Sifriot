<?php
function SetBlock()
{
	return $_SESSION['block']['item_del'] || $_SESSION['block']['pad'] || $_SESSION['block']['pad_cat'];
}
function FormExPar($block, $cl = "", $use = false)
{
	return SetExPar($block['cat'] || $block['cat_del'] || $block['cat_goto'] || $block['item_exit'], $cl, $use);
}
function SetExPar($block, $cl, $use = false)
{
	$arr_par = array();
	if ($block) $arr_par[] = "disabled";
	if ($cl != "") $arr_par[] = "class='".$cl."'";
	if ($use) $arr_par[] = "checked";
	if (count($arr_par) == 0) return "";
	else return " ".implode(" ", $arr_par);
}
function OptionTag($selected_value, $option_value, $disables_value = false)
{
	if ($option_value != $selected_value && !$disables_value) return "<option>".$option_value."</option>";
	if ($option_value != $selected_value && $disables_value) return "<option disabled>".$option_value."</option>";
	if ($option_value == $selected_value && !$disables_value) return "<option selected>".$option_value."</option>";
	return "<option selected disabled>".$option_value."</option>";
}
function QuestionForm($table_class, $text, $button_name_arr, $button_text_arr)
{
	echo "<table width='100%'>";
		echo "<tr>";
			echo "<td align='center'>";
				$t_cl = ($table_class == "") ? "" : " class='".$table_class."'";
				echo "<table".$t_cl.">";
					if (is_array($text)) foreach ($text as $v) echo "<tr align='center'><td><p align='center'><b>".$v."</b></p></td></tr>";
					else echo "<tr align='center'><td><p align='center'><b>".$text."</b></p></td></tr>";
					echo "<tr align='center'>";
						echo "<td align='center'>";
							for ($i = 0; $i < count($button_name_arr); $i++) echo "<input size='10' name='".$button_name_arr[$i]."' type='submit' value='".$button_text_arr[$i]."'>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";
}
function ImgV($img_name, $w, $h, $block = false) 
{
	$after_img = ($block) ? "Block" : "";
	return "<img src='".$_SESSION['image_dir']."/".$img_name.$after_img.".bmp' width='".(string)$w."' height='".(string)$h."'>";
}
function CheckImg($flag, $w, $h, $block = false) 
{
	if ($flag) return ImgV("CheckBorder", $w, $h, $block);
	else return ImgV("BlankBorder", $w, $h, $block);
}
function BSize($w, $h) 
{
	return "style='width:".(string)$w."px;height:".(string)$h."px;'";
}
function SelectTag($select_name, $options, $selected_value, $h_name = "", $by_key = false, $value_key = "", $on_func = "", $disabled_user = false, $display_invalid = true, $tag_class = "", $tag_title = "")
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
function RadioTag($radio_name, $checked_value, $checked_texts, $bg_class, $after = false, $on_func = "", $flag_name = "", $dis = false)
{
	$on_part = ($on_func == "") ? "" : " onchange='".$on_func."()'";
	for ($i = 0; $i < count($checked_texts); $i++)
	{
		if ($after) echo $checked_texts[$i]."<input type='radio' name='".$radio_name."'".$on_part." value='".(string)$i."'".(($i == $checked_value) ? " checked" : "").(($dis) ? " disabled" : "").">";
		else echo "<input type='radio' name='".$radio_name."'".$on_part." value='".(string)$i."'".(($i == $checked_value) ? " checked" : "").(($dis) ? " disabled" : "").">".$checked_texts[$i];
		echo "<font class='".$bg_class."'>X</font>";
	}
	if ($flag_name != "") echo "<input type='hidden' name='".$flag_name."' value=''>";
}
function AfterLangChoice($dbh, $hide_name, $lang_name, &$sw_break)
{
	$cf = false;
	if ($_POST[$hide_name] == "") $sw_break = false;
	else
	{
		$lang_key = array_search($_POST[$lang_name], $_SESSION['user_langs']);
		if ($lang_key !== false)
		{
		    $_SESSION['user_lang'] = array($lang_key, $_POST[$lang_name]);
		    $_SESSION['titles'] = GetTitlesByLanguage($dbh, (integer)$lang_key);
		    if (isset($_SESSION['admin_mes'])) TextAdminMessage($_SESSION['admin_mes']);
	        $cf = true;
        }
	}
	return $cf;
}
function TextAdminMessage(&$struct_mes)
{
	switch ($struct_mes[0])
	{
		case 1: case 3: $struct_mes[1] = Title(53).". ".Title(198); break;
		case 2        : $struct_mes[1] = Title(92).". ".Title(198); break;
		case 4        : $struct_mes[1] = Title(161).". ".Title(198); break;
		case 5        : $struct_mes[1] = Title(206).". ".Title(236); break;
		case 6        : $struct_mes[1] = Title(188).". ".Title(236); break;
		default       : $struct_mes[1] = "";
	}
}
function OnMouseOver($color_over, $color_out)
{
	$str_over = "onmouseover=".chr(34)."this.style.color=".chr(39)."#".$color_over.chr(39).chr(34);
	$str_out = "onmouseout=".chr(34)."this.style.color=".chr(39)."#".$color_out.chr(39).chr(34);
	return $str_over." ".$str_out;
}
function TableAdjustment($m_name, $p_name, $button_class, $disabl = "")
{
	$strAdj = Title(17)."<font color='#FFFFFF'>:</font>";
	$strAdj .= "<input name='".$m_name."' class='".$button_class."' type='submit' title='".Title(18)."' value='-'".$disabl.">";
	$strAdj .= "<input name='".$p_name."' class='".$button_class."' type='submit' title='".Title(19)."' value='+'".$disabl.">";
	return $strAdj;
}
function GetRadioType($cat_where)
{
	$sc = substr_count($cat_where, "%");
	switch ($sc)
	{
		case 1 : return 2;
		case 2 : return 3;
		default: return 1;
	}
}
function ViewDBErrors()
{
    echo "<table bordercolor='red' border='2' width='100%'>";
    foreach ($_SESSION['db_errors'] as $err)
    {
        echo "<tr valign='top'>";
            echo "<td>".$err."</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
