<?php
function ChangeAlgorithmScreen($dbh, $offs)
{
	$mes_screen = "";
	if (gettype($offs) == "integer")
	{
		if ($offs == 1)
		{
			$_SESSION['portion']++;
			GetAlgorithmPortion($dbh);
		}
		else
		{
			if ($_SESSION['portion'] > count($_SESSION['arr_alg'])) $_SESSION['portion']--;
			elseif ($_SESSION['portion'] == 1) $mes_screen = "<font color='#FF0000'>".Title(178)."</font>";
			else
			{
				end($_SESSION['arr_alg']);
				if (PermitRowAction(key($_SESSION['arr_alg'])))
				{
					if (count($_SESSION['arr_alg']) == $_SESSION['portion']) array_pop($_SESSION['arr_alg']);
					$_SESSION['portion']--;
				}
				else $mes_screen = "<font color='#FF0000'>".Title(237)." ".Title(471)."</font>";
			}
		}
	}
	else
	{
		if (!is_numeric($offs)) $mes_screen = "<font color='#FF0000'>".Title(177)." ".Title(77)."</font>";
		elseif ((integer)$offs < 1 || strpos($offs, "-") !== false || strpos($offs, ".") != false) $mes_screen = "<font color='#FF0000'>".Title(177)." ".Title(512)." (".Title(299)." <b>".$offs."</b>)</font>";
		elseif ((integer)$offs > count($_SESSION['arr_alg']))
		{
			$old = $_SESSION['portion'];
			$_SESSION['portion'] = (integer)$offs;
			if (count($_SESSION['arr_alg']) >=  $old) GetAlgorithmPortion($dbh);
		}
		elseif ($_SESSION['alarm']) SetAlarmAlgorithmPortionSize((integer)$offs);
        else $mes_screen = SetAlgorithmPortionSize((integer)$offs);
	}
	foreach ($_SESSION['arr_alg'] as $k => $v) TestAlgorithm($k, $v);
	return $mes_screen;
}
function AlgorithmExit($dbh, &$Mes)
{
	if (is_numeric($_POST['algorithm_height']) && (integer)$_POST['algorithm_height'] > 0) SaveUserScreenPortion($dbh, $_POST['algorithm_height']);
	if ($_SESSION['alarm'])
	{
	    ResetAlgorithmParse();
		AlgorithmsCheck($dbh);
		AlgorithmUnset();
        return "../Alarm/CommonAlarmForm";
	}
   	if ($_SESSION['user_working_mode'] == 1)
    {
        foreach ($_SESSION['arr_alg'] as $k => $v) TestAlgorithm($k, $v);
	    if (count($_SESSION['alg_del']) > 0)
        {
            $Mes[] = "<font color='#FF0000'><b>".Title(555)."</b></font>";
            return "";
        }
    }
    ResetAlgorithmParse();
    AlgorithmUnset();
    return "../Administrator/MainForm";
}
function AlgorithmColor($k, $err_class, $norm_class, $err_types)
{
    if (AlgorithmErrorsFree($k, $err_types)) return (($norm_class == "") ? "" : " class='".$norm_class."'");
    return (($err_class == "") ? "" : " class='".$err_class."'");
}
function AlgorithmImage($k, $unhide_img, $no_err_img, $err_img, $err_types)
{
    if ($_SESSION['algorithm_info'] == $k && $_SESSION['algorithm_info'] != "") return $unhide_img;
    if (AlgorithmErrorsFree($k, $err_types)) return $no_err_img;
    return $err_img;
}
function PermitRowAction($k)
{
	if ($_SESSION['alarm'])
	{
		if (in_array($k, $_SESSION['alg_del'])) return false;
		return true;
	}
	return AlgorithmErrorsFree($k, "full");
}
function GetAlgoritmTitle($k, $flag_name)
{
    if ($flag_name == "id") return ($_SESSION['alg_flag'][$k]['id']) ? Title(175) : "";
	if ($flag_name == "beg_del") return (isset($_SESSION['update_replace'][$k]['beg_del'])) ? Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'] : "";
    if ($flag_name == "inn_del") return (isset($_SESSION['update_replace'][$k]['inn_del'])) ? Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'] : "";
    if ($flag_name == "end_del") return (isset($_SESSION['update_replace'][$k]['end_del'])) ? Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'] : "";
    if ($flag_name == "del_sym") return (isset($_SESSION['update_replace'][$k]['del_sym'])) ? Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'] : "";
    if ($flag_name == "ins_sym") return (isset($_SESSION['update_replace'][$k]['ins_sym'])) ? Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'] : "";
    if ($flag_name == "reg_exp") return (isset($_SESSION['update_replace'][$k]['reg_exp'])) ? Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'] : "";
    if ($flag_name == "remarks")
    {
        if (isset($_SESSION['update_replace'][$k]['remarks'])) return Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'];
        return ($_SESSION['alg_flag'][$k]['remarks']) ? Title(174) : $_SESSION['arr_alg'][$k]['remarks'];
    }
	return "";
}
function HideUnhideAlgorithmInfo($dbh, $k)
{
    if ($_SESSION['algorithm_info'] == "") $_SESSION['algorithm_info'] = $k;
    else
    {
        if ($k == $_SESSION['algorithm_info'])
        {
            $fl = false;
	        $res = mysqli_query($dbh, "SELECT COUNT(*) FROM algorithms WHERE id_algorithm = ".$_SESSION['algorithm_info']);
	        if ($res)
	        {
                if ($row = mysqli_fetch_row($res))
                {
                    $fl = ($row[0] > 0);
                }
	            mysqli_free_result($res);
	        }
            if (!$fl && isset($_SESSION['arr_alg'][$_SESSION['algorithm_info']])) unset($_SESSION['arr_alg'][$_SESSION['algorithm_info']]);
            ResetAlgorithmParse();
            $_SESSION['edit_algorithm'] = "";
	        $_SESSION['algorithm_info'] = "";
	        $_SESSION['algorithm_insert'] = false;
        }
        else $_SESSION['algorithm_info'] = $k;
    }
    if ($k != $_SESSION['edit_algorithm']) $_SESSION['edit_algorithm'] = "";
}
function AfterErrorsFilterChoice($dbh, &$Mes, &$sw_break)
{
	if ($_POST['list_errors_s'] == "")
    {
        $sw_break = false;
        return false;
    }
    $_SESSION['filter_error'] = !$_SESSION['filter_error'];
    $_SESSION['algorithm_info'] = "";
    return true;
}
function AlgorithmCheckBox($k, $v_check, $flag_name, $dis_alg)
{
    echo "<input type='checkbox' name='".$flag_name."|".$k."' title='".GetAlgoritmTitle($k, $flag_name)."' value='*'".$dis_alg.(($v_check == 1) ? " checked" : "").">";
    if (isset($_SESSION['update_replace'][$k][$flag_name]) && $_SESSION['update_replace'][$k][$flag_name] != "") echo "<font color='#FFFFFF'>:</font><font color='#FF0000'><b>".$_SESSION['update_replace'][$k][$flag_name]."</b></font>";
}
function AlgStrTitle($k, $flag_name)
{
    if (isset($_SESSION['update_replace'][$k][$flag_name]) && $_SESSION['update_replace'][$k][$flag_name] != "") return Title(120)." ".Title(469)." ".FTM(Title(277))." ".$_SESSION['apostrophe_replace'];
    return "";
}
function AlgorithmInitial($dbh)
{
	$_SESSION['start'] = 0;
    $_SESSION['file_example'] = "";
    $_SESSION['path_example'] = array();
    $_SESSION['alg_del'] = array();
    $_SESSION['edit_algorithm'] = "";
    $_SESSION['algorithm_info'] = "";
    $_SESSION['algorithm_insert'] = false;
    $_SESSION['algorithm_delete'] = false;
    ResetAlgorithmParse();
    GetAlgorithmPortion($dbh);
    $_SESSION['algorithm_size'] = AlgorithmSize($dbh);
    $_SESSION['algorithm_err'] = SetAlgorithmErrors();
}
function ResetAlgorithmParse()
{
    $_SESSION['path_parse'] = false;
    $_SESSION['file_example'] = "";
    $_SESSION['path_example'] = array();
    $_SESSION['perform_parse'] = false;
}
function SetAlgorithmErrors()
{
    $alg_err['full'] = array(array("id", "offset", "del_from_source", "beg_del", "beg_num", "beg_inc", "beg_scr", "inn_del", "end_del", "end_num", "end_inc", "end_scr", "del_sym", "ins_sym", "field_only", "reg_exp", "reg_scr", "remarks"), array("beg_del", "inn_del", "end_del", "del_sym", "ins_sym", "reg_exp", "remarks"));
    $alg_err['id'] = array(array("id"), array());
    $alg_err['remarks'] = array(array("remarks"), array("remarks"));
    $alg_err['add'] = array(array("offset", "del_from_source", "beg_del", "beg_num", "beg_inc", "beg_scr", "inn_del", "end_del", "end_num", "end_inc", "end_scr", "del_sym", "ins_sym", "field_only", "reg_exp", "reg_scr"), array("beg_del", "inn_del", "end_del", "del_sym", "ins_sym", "reg_exp"));
    $alg_err['replace_fields'] = array("del_from_source", "beg_del", "beg_inc", "beg_scr", "inn_del", "end_del", "end_inc", "end_scr", "del_sym", "ins_sym", "field_only", "reg_exp", "reg_scr");
    return $alg_err;
}
function GetAlgorithmTableLimits($init_k, $end_k, $ark)
{
    $i_lim = array(-1, -1);
    if ($init_k == "") $i_lim[0] = 0;
    else
    {
        $i = array_search((integer)$init_k, $ark);
        if ($i !== false && $i < count($ark) - 1) $i_lim[0] = $i + 1;
    }
    if ($end_k == "") $i_lim[1] = count($ark) - 1;
    else
    {
        $i = array_search((integer)$end_k, $ark);
        if ($i !== false) $i_lim[1] = $i;
    }
    return $i_lim;
}
function ViewAlgorithmTableHeader()
{
    echo "<tr>";
        if ($_SESSION['user_working_mode'] == 1)
        {
            echo "<td align='center'>".ImgV("Delete", 18, 16)."</td>";
            echo "<td align='center'>".ImgV("Edit", 18, 16)."</td>";
        }
	    echo "<td align='center'><b>".Title(147)."</b></td>";
	    echo "<td></td>";
	    echo "<td><b>".Title(234)."</b></td>";
    echo "</tr>";
}
function AlgorithmTableRows($init_k, $end_k, $t_del)
{
    $ark = array_keys($_SESSION['arr_alg']);
    $i_lim = GetAlgorithmTableLimits($init_k, $end_k, $ark);
    if ($i_lim[0] > -1 && $i_lim[1] > -1)
    {
        echo "<table>";
	        if ($init_k == "") ViewAlgorithmTableHeader();
            for ($i = $i_lim[0]; $i < count($ark) && $i <= $i_lim[1]; $i++)
            {
                $cls = ($ark[$i] == $_SESSION['edit_algorithm']) ? "class=' edit_line'" : "";
                echo "<tr valign='top'".$cls.">";
                    $t_algorithm = (AlgorithmDisabled($ark[$i])) ? " disabled" : "";
                    $button_image = ($_SESSION['algorithm_info'] != $ark[$i]);
                    $button_title = ($button_image) ? Title(597)." ".Title(249) : FTM(Title(431))." ".Title(249);
                    $id_block = (!$_SESSION['algorithm_delete'] && $_SESSION['algorithm_insert']) ? "" : " disabled";
                    if ($_SESSION['user_working_mode'] == 0)
                    {
                        $t_edit = "";
                        $f_button_edit = false;
                    }
                    else
			        {
				        $f_button_edit = ($_SESSION['algorithm_delete'] || ($_SESSION['edit_algorithm'] != "" && !($ark[$i] == $_SESSION['edit_algorithm']) || in_array($ark[$i], $_SESSION['alg_del'])));
				        $t_edit = (($f_button_edit) ? " disabled" : "");
				        echo "<td><button name='algorithm_mark_del|".$ark[$i]."' type='submit' title='".Title(391)."' class='button_class' value='*'".$t_del.">".SysImage(((in_array($ark[$i], $_SESSION['alg_del'])) ? "CheckBorder" : "BlankBorder"), 16, 16, ($_SESSION['algorithm_delete'] || $_SESSION['edit_algorithm'] != ""))."</button></td>";
				        echo "<td><button name='algorithm_edit|".$ark[$i]."' type='submit' title='".Title(251)."' class='button_class' value='*'".$t_edit.">".SysImage((($ark[$i] == $_SESSION['edit_algorithm'] && $_SESSION['edit_algorithm'] != "") ? "CheckBorder" : "BlankBorder"), 16, 16, $f_button_edit)."</button></td>";
			        }
			        echo "<td><input size='12'".$id_block." title='".GetAlgoritmTitle($ark[$i], "id")."' ".AlgorithmColor($ark[$i], "data_error_id", "data_numeric", "id")." type='text' name='algorithm_numb|".$ark[$i]."' value='".$ark[$i]."'></td>";
			        echo "<td><button name='algorithm_info|".$ark[$i]."' type='submit' title='".$button_title."' class='button_class' value='*'".$t_edit.">".SysImage(AlgorithmImage($ark[$i], "CheckBorder", "BlankBorder", "RoseBorder", "add"), 16, 16, $f_button_edit)."</button></td>";
			        echo "<td><input size='120' type='text' title='".GetAlgoritmTitle($ark[$i], "remarks")."' ".AlgorithmColor($ark[$i], "data_error", "", "remarks")." name='remarks|".$ark[$i]."' value='".$_SESSION['arr_alg'][$ark[$i]]['remarks']."'".$t_algorithm."></td>";
		        echo "</tr>";
	        }
	        if ($end_k == "") for ($i = count($_SESSION['arr_alg']); $i < $_SESSION['portion']; $i++) echo (($_SESSION['user_working_mode'] == 0) ? "" : "<tr><td></td><td>")."</td><td align='right' class='dis_text'>".(string)($i + 1)."</td></tr>";
        echo "</table>";
	}
}
function AlgorithmDisabled($k)
{
    if ($_SESSION['user_working_mode'] == 0 || !$_SESSION['algorithm_delete'] && $k != $_SESSION['edit_algorithm']) return true;
    return false;
}
function PathOutPut($post_example, &$file_example)
{
	if ($post_example == "") return array();
	$file_example = str_replace(chr(39), chr(96), $post_example);
	$path_example = explode(chr(92), $file_example);
	return array_reverse($path_example);
}
function AlgColor($k, $err_class, $norm_class, $flag_name)
{
    if ($_SESSION['alg_flag'][$k][$flag_name]) return " class='".$err_class."'";
    if (isset($_SESSION['update_replace'][$k][$flag_name]) && $_SESSION['update_replace'][$k][$flag_name]) return " class='".$err_class."'";
    return ($norm_class == "") ? "" : " class='".$norm_class."'";
}
function AlgorithmUnset()
{
    if (isset($_SESSION['algorithm_info'])) unset($_SESSION['algorithm_info']);
    if (isset($_SESSION['alg_del'])) unset($_SESSION['alg_del']);
    if (isset($_SESSION['arr_alg'])) unset($_SESSION['arr_alg']);
    if (isset($_SESSION['path_parse'])) unset($_SESSION['path_parse']);
    if (isset($_SESSION['perform_parse'])) unset($_SESSION['perform_parse']);
    if (isset($_SESSION['path_example'])) unset($_SESSION['path_example']);
    if (isset($_SESSION['file_example'])) unset($_SESSION['file_example']);
    if (isset($_SESSION['edit_algorithm'])) unset($_SESSION['edit_algorithm']);
    if (isset($_SESSION['algorithm_insert'])) unset($_SESSION['algorithm_insert']);
    if (isset($_SESSION['alg_flag'])) unset($_SESSION['alg_flag']);
    if (isset($_SESSION['algorithm_size'])) unset($_SESSION['algorithm_size']);
    if (isset($_SESSION['algorithm_err'])) unset($_SESSION['algorithm_err']);
    if (isset($_SESSION['update_replace'])) unset($_SESSION['update_replace']);
}
?>
