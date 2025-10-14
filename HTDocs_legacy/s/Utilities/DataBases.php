<?php
function GetDB($dbname, &$Mes, $coding = "")
{
	$ci = count($Mes);
	if ($dbname == "") $Mes[] = Title(156);
	elseif (!TestSysString($dbname)) $Mes[] = Title(157);
	if (count($Mes) == $ci)
	{
        $dbh = GetOnlyDB($dbname, $coding);
    	if (!$dbh) $Mes[] = Title(1);
	}
	return (count($Mes) == $ci) ? $dbh : false;
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
function GetOnlyDB($dbname, $coding = "utf8")
{
    if (!IsDataBase($dbname)) return false;
	$dbh = mysqli_connect($_SESSION['serv']['host'], $_SESSION['serv']['user'], $_SESSION['serv']['pass'], $dbname);
	if ($dbh && $coding != "") mysqli_query($dbh, "SET NAMES '".$coding."'");
    return $dbh;
}
function GetValueFromTable($res, $f_n)
{
	if (is_array($f_n)) $vc = array_fill(0, count($f_n), "");
	else $vc = "";
	if (mysqli_num_rows($res) > 0)
	{
		$row = mysqli_fetch_row($res);
		if (is_array($f_n)) for ($i = 0; $i < count($f_n); $i++) $vc[$i] = (string)$row[$f_n[$i]];
		else $vc = (string)$row[$f_n];
	}
	return $vc;
}
function GetTableLimit($dbh, $wh, $n, $mc = false, $alias_name = "")
{
	if ($wh == "total") 
	{
		$res = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$_SESSION['Catalog'][$n]['table']);
		if (mysqli_errno($dbh) > 0) return 0;
		if ($res)
		{
            $ctbl = 0;
            if ($row = mysqli_fetch_row($res))
            {
                $ctbl = $row[0];
            }
			mysqli_free_result($res);
		}
		return $ctbl;
	}
	else
	{
		if ($_SESSION['Catalog']['1']['name'] == "" && $n == "0" || $n == "1") return SingleCount($dbh, $wh, $n);
		else return SetsCount($dbh, $mc, $wh, $alias_name);
	}
}
function SingleCount($dbh, $wh, $n)
{
	$ctbl = 0;
	if ($wh == "filter")
	{
		$res = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$_SESSION['Catalog'][$n]['table']." WHERE ".$_SESSION['catalog_param'][$n]['filter_where']);
		if (mysqli_errno($dbh) > 0) $ctbl = 0;
		if ($res)
		{
			if ($row = mysqli_fetch_row($res))
            {
                $ctbl = $row[0];
            }
			mysqli_free_result($res);
		}
	}
	else
	{
		$where_c = ($_SESSION['catalog_param'][$n]['filter_where'] == "") ? $_SESSION['catalog_param'][$n]['search_where'] : $_SESSION['catalog_param'][$n]['filter_where']." AND ".$_SESSION['catalog_param'][$n]['search_where'];
		$res = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$_SESSION['Catalog'][$n]['table']. " WHERE ".$where_c);
		if (mysqli_errno($dbh) > 0) $ctbk = 0;
		if ($res)
		{
			if ($row = mysqli_fetch_row($res))
            {
                $ctbl = $row[0];
            }
			mysqli_free_result($res);
		}
	}
	return $ctbl;
}
function SetsCount($dbh, $mc, $wh, $alias_name)
{
	$ctbl = 0;
	if ($wh == "filter")
	{
		if ($_SESSION['catalog_param']['0']['filter_text'] == "") return 0;
		$f_where = array(MCV($_SESSION['catalog_param']['0']['filter_text'], $mc), $_SESSION['catalog_param']['0']['filter_compare']);
		$s_where = array("");
	}
	else
	{
		if ($_SESSION['catalog_param']['0']['search_text'] == "") return 0;
		if ($_SESSION['catalog_param']['0']['filter_text'] == "")
		{
			$f_where = array("");
			$s_where = array(MCV($_SESSION['catalog_param']['0']['search_text'], $mc), $_SESSION['catalog_param']['0']['search_compare']);
		}
		else
		{
			$f_where = array(MCV($_SESSION['catalog_param']['0']['filter_text'], $mc), $_SESSION['catalog_param']['0']['filter_compare']);
			$s_where = array(MCV($_SESSION['catalog_param']['0']['search_text'], $mc), $_SESSION['catalog_param']['0']['search_compare']);
		}
	}
	$query_text = QueryTextForSet($dbh, $mc, 0, $f_where, $s_where, $alias_name, "", "", "", "COUNT(*)");
	$res = mysqli_query($dbh, $query_text);
	if (mysqli_errno($dbh) > 0) $ctbl = 0;
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
        {
            $ctbl = $row[0];
        }
		mysqli_free_result($res);
	}
	return $ctbl;
}
function GetAutoIncrement($dbh)
{
	$res = mysqli_query($dbh, "SELECT LAST_INSERT_ID()");
	if (mysqli_errno($dbh) > 0) return "";
	if ($res)
	{
		if (mysqli_num_rows($res) > 0) $r = (string)GetValueFromTable($res, 0);
		else $r = "";
		mysqli_free_result($res);
		return $r;
	}
	else return "";
}
function VF($max_lev, $tableSet, $idSet, $valueSet, $tableRef, $idRef, $valueRef, $main_table_ref, $default_valus, $alias_name, $include_table_to_from = true)
{
	if ($max_lev == 0) $req_text = " CASE WHEN ".$main_table_ref." IS NULL THEN ".$default_valus." ELSE ".$main_table_ref." END AS ".$alias_name;
	elseif ($tableSet == "")
	{
		$int_sel = "(SELECT ".$tableRef.".".$valueRef." FROM ".$tableRef." WHERE ".$tableRef.".".$idRef." = ".$main_table_ref." LIMIT 1)";
		$req_text = " CASE";
		if ($main_table_ref != "") $req_text .= " WHEN ".$main_table_ref." IS NULL THEN ".$default_valus;
		$req_text .= " WHEN ".$int_sel." IS NULL THEN ".$default_valus." ELSE ".$int_sel." END AS ".$alias_name;
	}
	else
	{
		if ($main_table_ref == "")
		{
			$to_where = "";
			$to_from = "";
		}
		else
		{
			$to_where = $tableSet.".".$idSet." = ".$main_table_ref." AND ";
			$to_from = ",".$tableSet;
		}
		$req_arr = array();
		for ($i = 0; $i < $max_lev; $i++) $req_arr[] = SetCaseText($tableRef, $idRef, $valueRef, $to_from, $to_where, $tableSet, $valueSet, $i, $main_table_ref, $default_valus, $alias_name);
		$req_text = implode(", ", $req_arr);
	}
	return $req_text;
}
function SetCaseText($tableRef, $idRef, $valueRef, $to_from, $to_where, $tableSet, $valueSet, $i, $main_table_ref, $default_valus, $alias_name)
{
	$str0 = " CASE";
	if ($i == 0) $int_sel = "(SELECT ".$tableRef.".".$valueRef." FROM ".$tableRef.$to_from." WHERE ".$to_where.$tableRef.".".$idRef." = SUBSTRING_INDEX(".$tableSet.".".$valueSet.",',',1) LIMIT 1)";
	else
	{
		$l_str = "SUBSTRING_INDEX(".$tableSet.".".$valueSet.",',',".(string)($i + 1).")";
		$res_str = "SUBSTRING_INDEX(".$l_str.",',',-1)";
		$prev_str = "SUBSTRING_INDEX(".$tableSet.".".$valueSet.",',',".(string)$i.")";
		$int_sel = "(SELECT ".$tableRef.".".$valueRef." FROM ".$tableRef.$to_from." WHERE ".$to_where.$tableRef.".".$idRef." = ".$res_str." AND ".$l_str." <> ".$prev_str." LIMIT 1)";
	}
	if ($main_table_ref != "") $str0 .= " WHEN ".$main_table_ref." IS NULL THEN ".$default_valus;
	$str0 .= " WHEN ".$int_sel." IS NULL THEN ".$default_valus;
	return $str0." ELSE ".$int_sel." END AS ".$alias_name.(string)($i + 1);
}
function OrderForSet($max_lev, $ord_dir, $alias_name, $k, $m_case, $separator)
{
	$add_sort = ($ord_dir == 1) ? "" : " DESC";
	$arr_req_text = array();
	for ($i = 0; $i < $max_lev; $i++) $arr_req_text[] = MCF($alias_name.$k, $m_case).(string)($i + 1);
	if (count($arr_req_text) == 0) return "";
	elseif (count($arr_req_text) == 1) return $arr_req_text[0].$add_sort;
	$req_text = implode(", ".chr(34).$separator.chr(34).", ", $arr_req_text);
	return "CONCAT(".$req_text.")".$add_sort;
}
function ConcatPart($max_lev, $alias_name, $m_case)
{
	$concat_arr = array();
	for ($i = 0; $i < $max_lev; $i++) $concat_arr[] = MCF($alias_name, $m_case).(string)($i + 1);
	if (count($concat_arr) == 0) return "";
	elseif (count($concat_arr) == 1) return $concat_arr[0];
	$req_text = implode(", ".chr(34).$_SESSION['Catalog']['0']['separator'].chr(34).", ", $concat_arr);
	return "CONCAT(".$req_text.")";
}
function QueryTextForSet($dbh_sys, $mc, $ord_dir, $f_arr, $s_arr, $alias_name, $compr = "", $value = "", $limit = "", $f_list = "")
{
	$max_lev = GetMaxSetLevel($dbh_sys, $_SESSION['Catalog']['0']['table']);
	if ($max_lev == 0) return "";
	$rt = VF($max_lev, $_SESSION['Catalog']['0']['table'], $_SESSION['Catalog']['0']['id'], $_SESSION['Catalog']['0']['value'], $_SESSION['Catalog']['1']['table'], $_SESSION['Catalog']['1']['id'], $_SESSION['Catalog']['1']['value'], "", chr(34).chr(34), $alias_name, false);
	if (!$mc) $rt .= ", ".VF($max_lev, $_SESSION['Catalog']['0']['table'], $_SESSION['Catalog']['0']['id'], $_SESSION['Catalog']['0']['value'], $_SESSION['Catalog']['1']['table'], $_SESSION['Catalog']['1']['id'], $_SESSION['Catalog']['1']['value']."_low", "", chr(34).chr(34), $alias_name."_low", false);
	$flist = ($f_list == "") ? "*" : $f_list;
	$ids = ($_SESSION['Catalog']['0']['id'] == "") ? "" : " ".$_SESSION['Catalog']['0']['id'].",";
	$vls = ($_SESSION['Catalog']['0']['value'] == "") ? "" : " ".$_SESSION['Catalog']['0']['value'].",";
	$rt = "SELECT ".$flist." FROM (SELECT".$ids.$vls." ".$rt." FROM ".$_SESSION['Catalog']['0']['table'].") AS T ";
	if ($f_arr[0] != "" || $s_arr[0] != "" || $compr != "") $concat_cl = ConcatPart($max_lev, $alias_name, $mc);
	if ($f_arr[0] != "" && $s_arr[0] != "") $where_fs = GetWhere($concat_cl, $f_arr[0], $f_arr[1])." AND ".GetWhere($concat_cl, $s_arr[0], $s_arr[1]);
	elseif ($f_arr[0] != "" && $s_arr[0] == "") $where_fs = GetWhere($concat_cl, $f_arr[0], $f_arr[1]);
	elseif ($f_arr[0] == "" && $s_arr[0] != "") $where_fs = GetWhere($concat_cl, $s_arr[0], $s_arr[1]);
	else $where_fs = "";
	if ($compr != "") $where_compr = $concat_cl." ".$compr." '".$value."'";
	else $where_compr = "";
	if ($where_fs != "" && $where_compr != "") $rt .= "WHERE ".$where_fs." AND ".$where_compr;
	elseif ($where_fs != "" && $where_compr == "") $rt .= "WHERE ".$where_fs;
	elseif ($where_fs == "" && $where_compr != "") $rt .= "WHERE ".$where_compr;
	if ($ord_dir != 0) $rt .= " ORDER BY ".OrderForSet($max_lev, $ord_dir, $alias_name, "", $mc, $_SESSION['Catalog']['0']['separator']).",".$_SESSION['Catalog']['0']['id'];
	if ($limit != "") $rt .= " LIMIT ".$limit;
	return $rt;
}
function SaveMaxLevel($dbh, $cur_max_level)
{
	$reqMax = mysqli_query($dbh, "SELECT max_level FROM table_definitions WHERE table_name = '".$_SESSION['Catalog']['0']['table']."'");
	if ($reqMax)
	{
		if ($row = mysqli_fetch_row($reqMax))
		{
			if ($cur_max_level > $row[0]) mysqli_query($dbh, "UPDATE table_definitions SET max_level = ".(string)$cur_max_level." WHERE table_name = '".$_SESSION['Catalog']['0']['table']."'");
		}
		else
        {
            mysqli_query($dbh, "ALTER TABLE table_definitions AUTO_INCREMENT = 1");
            mysqli_query($dbh, "INSERT INTO table_definitions (table_name,max_level) VALUES ('".$_SESSION['Catalog']['0']['table']."',".(string)$cur_max_level.")");
        }
		mysqli_free_result($reqMax);
	}
	else mysqli_query($dbh, "INSERT INTO table_definitions (table_name,max_level) VALUES ('".$_SESSION['Catalog']['0']['table']."',".(string)$cur_max_level.")");
}
function GetWhere($v_field, $w_text, $compare_mode)
{
	switch ($compare_mode)
	{
		case 0:  return $v_field." LIKE '".$w_text."%'"; break;
		case 1:  return $v_field." LIKE '%".$w_text."%'"; break;
		case 2:  return $v_field." = '".$w_text."'"; break;
		default: return "";
	}
}
function WhereSet($dbh, $m_case, $value, $compare_mode, $alias_name)
{
	$max_lev = GetMaxSetLevel($dbh, $_SESSION['Catalog']['0']['table']);
	$concat_cl = ConcatPart($max_lev, $alias_name, $m_case);
	return GetWhere($concat_cl, $value, $compare_mode);
}
function GetQueryValueSet($n, $row, $max_level)
{
	$arr_values = array();
	$max_row = ($max_level == 0) ? count($row) : $n + $max_level;
	$max_i = -1;
	for ($i = $n; $i < $max_row; $i++)
	{
		if (is_null($row[$i]) || $row[$i] == "") $arr_values[] = "";
		else
		{
			$arr_values[] = $row[$i];
			$max_i = $i;
		}
	}
	if ($max_i < 0) return "";
	return implode($_SESSION['Catalog']['0']['separator'], array_slice($arr_values, 0, $max_i - $n + 1));
}
function MCF($i_field, $m_case)
{
	if ($m_case) return $i_field;
	else return $i_field."_low";
}
function MCV($source_value, $m_case)
{
	if ($source_value == "") return "";
	if ($m_case) return $source_value;
	else return mb_strtolower($source_value, 'utf-8');
}
function GetFieldByID($dbh, $tabl, $f_name, $id_name, $id_value)
{
	$r = "";
	$res = mysqli_query($dbh, "SELECT ".$f_name." FROM ".$tabl." WHERE ".$id_name." = ".$id_value." LIMIT 1");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
        {
            $r = $row[0];
        }
		mysqli_free_result($res);
	}
	return $r;
}
function GetTableList($dbh)
{
	$arr_sys = array();
	$res = mysqli_query($dbh, "SHOW TABLES");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
        {
            $arr_sys[] = $row[0];
        }
		mysqli_free_result($res);
	}
	return $arr_sys;
}
function GetMaxSetLevel($dbh, $table)
{
	$reqMax = mysqli_query($dbh, "SELECT max_level FROM table_definitions WHERE table_name = '".$table."'");
	if (mysqli_errno($dbh) > 0) return 0;
	if ($reqMax)
	{
		if ($row = mysqli_fetch_row($reqMax))
        {
            $r = $row[0];
        }
		else $r = 0;
		mysqli_free_result($reqMax);
		return $r;
	}
	else return 0;
}
?>
