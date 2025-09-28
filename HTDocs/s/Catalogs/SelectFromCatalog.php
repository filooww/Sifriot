<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/UserList.php");
function OnOffUseButton($k, &$PR, &$Mes)
{
	if ($main_params['const'][$k]['table'] != "")
	{
		if (!$main_params['var'][$k]['use'] && $main_params['var'][$k]['value'] == "")
		{
			$main_params['var'][$k]['code'] = "";
			$Mes[] = array("time"=>"", "text"=>"The value for <b>".strtolower($main_params['const'][$k]['name'])."</b> not selected", "status"=>"error");
		}
		elseif (!$main_params['var'][$k]['use'] && $main_params['var'][$k]['value'] != "") $main_params['var'][$k]['use'] = true;
		elseif ($main_params['var'][$k]['use'] && $main_params['var'][$k]['value'] == "")
		{
			$main_params['var'][$k]['use'] = false;
			$main_params['var'][$k]['code'] = "";
		}
		else 
		{
			$main_params['var'][$k]['use'] = false;
			$main_params['var'][$k]['code'] = "";
			$main_params['var'][$k]['value'] = "";
		}
	}
	else
	{
		if ($main_params['var'][$k]['value'] == "") $Mes[] = array("time"=>"", "text"=>"The value for <b>".strtolower($main_params['const'][$k]['name'])."</b> not selected", "status"=>"error");
		else $main_params['var'][$k]['use'] = !$main_params['var'][$k]['use'];
	}
}
function LineSelect($dbh, $f, &$l_selected, &$cur_value, &$Mes)
{
	$fl_select = false;
	if ($_SESSION['cat_arr']['0'][$f][1] || $_SESSION['cat_arr']['0'][$f][2]) $Mes[] = array("time"=>"", "text"=>"To select this value from catalog You must ".(($_SESSION['cat_arr']['0'][$f][2]) ? "correct" : "save")." it", "status"=>"error");
	else
	{
		$ff = ($_SESSION['cat_arr']['0'][$f][1]) ? NoUpdateSelectedValue($dbh, $f, $Mes) : false;
		if (!$ff)
		{
			if ($_SESSION['Catalog']['0']['cat_type'] == 1 && !SingleNode($dbh, $_SESSION['cat_arr']['0'][$f][3])) $Mes[] = array("time"=>"", "text"=>"This value is a group value and cannot be selected", "status"=>"error");
			else
			{
				$l_selected['value'] = end($_SESSION['cat_arr']['0'][$f]);
				$l_selected['code'] = $f;
				if (isset($l_selected['use'])) $l_selected['use'] = true;
				$cur_value = end($_SESSION['cat_arr']['0'][$f]);
				$fl_select = true;
			}
		}
	}
	return $fl_select;
}
function NoUpdateSelectedValue($dbh, $from_code, &$Mes)
{
	$db_t = GetCorrectEditedValue($_SESSION['Catalog']['0']['illegal_symbols'], $_SESSION['cat_arr']['0'][$from_code][3]);
	$res_arr = SaveCatalogRow($dbh, $from_code, $db_t, "0", $Mes);
	$_SESSION['cat_arr']['0'][$from_code][1] = $res_arr['err'];
	$_SESSION['cat_arr']['0'][$from_code][2] = $res_arr['err'];
	return $res_arr['err'];
}
function CancelSelect(&$p_ref)
{
	$p_ref['value'] = "";
	$p_ref['code'] = "";
	$p_ref['use'] = false;
}
?>
