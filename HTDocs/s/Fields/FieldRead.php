<?php
function ReadFieldDefinition($dbh)
{
	$PR = array("const"=>array(), "var"=>array());
	$_SESSION['spec_fld'][1] = array("key"=>"", "URL_file"=>"", "URL_link"=>"", "del_mark"=>"", "refer"=>"");
	$_SESSION['spec_fld'][2] = array("key"=>"", "URL_file"=>"", "URL_link"=>"", "del_mark"=>"", "refer"=>"");
	$res = mysqli_query($dbh, "SELECT * FROM field_config");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
			$PR['const'][$row[0]][$row[1]]['key'] = ($row[2] == 1);
			$PR['const'][$row[0]][$row[1]]['name'] = $row[3];
			$PR['const'][$row[0]][$row[1]]['type'] = $row[4];
			$PR['const'][$row[0]][$row[1]]['size'] = $row[5];
			$PR['const'][$row[0]][$row[1]]['interval'] = ($row[6] == 1);
			$PR['const'][$row[0]][$row[1]]['blank'] = ($row[7] == 1);
			$PR['const'][$row[0]][$row[1]]['unique'] = ($row[8] == 1);
			$PR['const'][$row[0]][$row[1]]['s_mode'] = ($row[9] == 1);
			$PR['const'][$row[0]][$row[1]]['table'] = $row[10];
			$PR['const'][$row[0]][$row[1]]['illegals'] = $row[11];
			$PR['const'][$row[0]][$row[1]]['default'] = $row[12];
			$PR['const'][$row[0]][$row[1]]['field_check'] = ($row[13] == 1);
			$PR['const'][$row[0]][$row[1]]['comm'] = ($row[14] == 1);
			$PR['const'][$row[0]][$row[1]]['using'] = $row[17];
			$PR['const'][$row[0]][$row[1]]['f_align'] = $row[18];
			$PR['const'][$row[0]][$row[1]]['t_prc'] = $row[19];
			$PR['const'][$row[0]][$row[1]]['screen_order'] = $row[20];
            if ($row[2] == 1) $_SESSION['spec_fld']['key'][$row[0]] = $row[1];
            if ($row[4] == 5) $_SESSION['spec_fld']['URL_file'][$row[0]] = $row[1];
            if ($row[4] == 6) $_SESSION['spec_fld']['URL_link'][$row[0]] = $row[1];
            if ($row[4] == 7) $_SESSION['spec_fld']['del_mark'][$row[0]] = $row[1];
            if ($row[4] == 8) $_SESSION['spec_fld']['refer'][$row[0]] = $row[1];
			$PR['var'][$row[0]][$row[1]]['filter']['text'] = "";
			$PR['var'][$row[0]][$row[1]]['filter']['to'] = "";
			$PR['var'][$row[0]][$row[1]]['filter']['md'] = $row[15]; // md??? = At begin, Anywhere, Exact
			$PR['var'][$row[0]][$row[1]]['filter']['iv'] = false;
			$PR['var'][$row[0]][$row[1]]['sort']['sort_order'] = 0;
			$PR['var'][$row[0]][$row[1]]['sort']['sort_mode'] = $row[16];
			$PR['var'][$row[0]][$row[1]]['code'] = "";
			$PR['var'][$row[0]][$row[1]]['value'] = "";
			$PR['var'][$row[0]][$row[1]]['use'] = false;
		}
		mysqli_free_result($res);
	}
	return $PR;
}
function ReadPrimaryFields($dbh) // $_SESSION['p_ref'] !!!
{
	$field_param = array();
	$res = mysqli_query($dbh, "SELECT * FROM field_config WHERE f_using LIKE '%primary%'");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
			$field_param[$row[1]]['ref'] = $row[4];				//$PR['const'][$row[1]]['ref']
			$field_param[$row[1]]['table'] = $row[12];			//$PR['const'][$row[1]]['table']
			$field_param[$row[1]]['code'] = ""; //!!!			//$PR['var'][$row[1]]['code']
			$field_param[$row[1]]['c_id'] = $row[13];			//$PR['const'][$row[1]]['c_id']
			$field_param[$row[1]]['c_v'] = $row[14];			//$PR['const'][$row[1]]['c_v']
			$field_param[$row[1]]['table1'] = $row[16];			//$PR['const'][$row[1]]['table1'] = $row[16];
			$field_param[$row[1]]['id1'] = $row[17];			//$PR['const'][$row[1]]['id1']
			$field_param[$row[1]]['v1'] = $row[18];				//$PR['const'][$row[1]]['v1']
			$field_param[$row[1]]['file'] = ($row[0] == "files");//$PR['var'][$row[1]]['file']
			$field_param[$row[1]]['value'] = ""; //!!!			//$PR['var'][$row[1]]['value']
			$field_param[$row[1]]['use'] = false; //!!!			$PR['var'][$row[1]]['use']
			$field_param[$row[1]]['title'] = $row[3];			//$PR['const'][$row[1]]['name']
			$field_param[$row[1]]['comm'] = ($row[24] == 1);	//$PR['const'][$row[1]]['comm']
		}
		mysqli_free_result($res);
	}
	return $field_param;
}
function ReadFileFields($dbh, $arr_default, $own) // $_SESSION['p_files'] ??? more ...
{
	$p_files = array();
	$res = mysqli_query($dbh, "SELECT * FROM field_config WHERE f_using LIKE '%update%' AND own_table = '".$own."'");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
			$p_files[$row[1]]['key'] = ($row[2] == 1);
			$p_files[$row[1]]['name'] = $row[2];
			$p_files[$row[1]]['ref'] = $row[4];
			$p_files[$row[1]]['type'] = $row[5];
			$p_files[$row[1]]['size'] = $row[6];
			$p_files[$row[1]]['interval'] = ($row[7] == 1);
			$p_files[$row[1]]['blank'] = ($row[8] == 1);
			$p_files[$row[1]]['unique'] = ($row[9] == 1);
			$p_files[$row[1]]['s_mode'] = ($row[10] == 1);
			$p_files[$row[1]]['table_name'] = $row[11];
			$p_files[$row[1]]['table'] = $row[12];
			$p_files[$row[1]]['c_id'] = $row[13];
			$p_files[$row[1]]['c_v'] = $row[14];
			$p_files[$row[1]]['table1_name'] = $row[15];
			$p_files[$row[1]]['table1'] = $row[16];
			$p_files[$row[1]]['id1'] = $row[17];
			$p_files[$row[1]]['v1'] = $row[18];
			$p_files[$row[1]]['low'] = $row[19];
			$p_files[$row[1]]['illegals'] = GetTableValue($dbh, $row[20], "illegal_symbols");
			$p_files[$row[1]]['default'] = (($row[21] > -1 && $row[21] < count($arr_default)) ? $arr_default[$row[21]] : "");
			$p_files[$row[1]]['field_check'] = ($row[22] == 1);
			$p_files[$row[1]]['cat_type'] = ($row[23] == 1);
			$p_files[$row[1]]['comm'] = ($row[24] == 1);
			$p_files[$row[1]]['filter']['text'] = $row[26];
			$p_files[$row[1]]['filter']['to'] = $row[27];
			$p_files[$row[1]]['filter']['iv'] = ($row[28] == 1);
			$p_files[$row[1]]['filter']['auto'] = ($row[29] == 1);
			$p_files[$row[1]]['filter']['md'] = $row[30]; // md???
			$p_files[$row[1]]['sort']['sort_order'] = $row[31];
			$p_files[$row[1]]['sort']['sort_mode'] = $row[32];
		}
		mysqli_free_result($res);
	}
	return $p_files;
}
function ReadCatalogFieldDefinitions($dbh, $catalog_ID) // ParseCatalogButtons
{
	$C = array();
	$res = mysqli_query($dbh, "SELECT * FROM field_config WHERE f_ID = '".$catalog_ID."' LIMIT 1");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			$C['0']['name'] = $row[11]; // table_definitions - table_title
			$C['0']['table'] = $row[12]; // table_definitions - table_name
			$C['0']['id'] = $row[13]; // table_definitions - catalog_id
			$C['0']['value'] = $row[14]; // table_definitions - catalog_value
			$C['0']['cat_type'] = $row[23]; // table_definitions - group_catalog_type
			$C['0']['separator'] = GetTableValue($dbh, $row[20], "separators", false); // table_definitions - separators
			$C['0']['illegal_symbols'] = GetTableValue($dbh, $row[20], "illegal_symbols"); // table_definitions - illegal_symbols
			
			$C['1']['name'] = $row[15]; //16	f_table1_name	varchar(255)	utf8_bin		No			
			$C['1']['table'] = $row[16]; //17	f_table1	varchar(255)	utf8_bin		No	None
			$C['1']['id'] = $row[17]; //18	f_id1	varchar(255)	utf8_bin		No	None		
			$C['1']['value'] = $row[18]; //19	f_v1	varchar(255)	utf8_bin		No	None
			
			$C['1']['illegal_symbols'] = GetTableValue($dbh, $row[20], "illegal_symbols"); //22	f_illegals	varchar(255)	utf8_bin		No	None		from table-definition
		}
		mysqli_free_result($res);
	}
	return $C;
}
function GetCalendStructs($p_con)
{
	$calend_date = array();
	foreach ($p_con as $k => $v) if ($p_con[$k]['type'] == "date") $calend_date[$k] = array("id_filter"=>"", "value"=>"");
	return $calend_date;	
}
					   
?>
