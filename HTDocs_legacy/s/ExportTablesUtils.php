<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/TestScripts.php");

function ExportToFile()
{
    $Mes = "";
    if ($_POST['db'] == "") return "Database not selected";
    $dbh = GetDB($_POST['db'], "_Credentials.txt", $Mes);
    if (!$dbh) return $Mes;
    else
    {
        $mes_arr = array();
        $chat_set = GetDBParams($dbh, "SELECT @@character_set_database");
        if ($chat_set == "") $mes_arr[] = "no character set database";
        $coll = GetDBParams($dbh, "SELECT @@collation_database");
        if ($coll == "") $mes_arr[] = "no collation database";
        $f_name = ($_POST['table'] == "") ? $_POST['db'] : $_POST['table'];
	    $fh = fopen($_SERVER['DOCUMENT_ROOT']."/s/".$f_name.".sql", "w");
	    fwrite($fh, "CREATE DATABASE IF NOT EXISTS ".$_POST['db']." DEFAULT CHARACTER SET ".$chat_set." COLLATE ".$coll.";\r\n");
	    fwrite($fh, "USE ".$_POST['db'].";\r\n");
	    fclose($fh);
        if ($_POST['table'] != "") ExportTable($dbh, $f_name, $_POST['table'], $chat_set);
        else foreach ($_SESSION['table_list'] as $tb) if ($tb != "") ExportTable($dbh, $f_name, $tb, $chat_set);
    }
    if (count($mes_arr) == 0) return "The result is in the file <b>".$_SERVER['DOCUMENT_ROOT']."/s/".$f_name.".sql</b>";
    else return implode("; ", $mes_arr);
}
function ExportTable($dbh, $f_name, $table, $chat_set)
{
	$fh = fopen($_SERVER['DOCUMENT_ROOT']."/s/".$f_name.".sql", "a");
	fwrite($fh, "\r\n");
    fclose($fh);
    $arr_fields = InsertFieldStructure($dbh, $f_name, $table, $chat_set);
    InsertTableRows($dbh, $f_name, $table, $arr_fields['fields']);
    InsertIndexInformation($dbh, $f_name, $table, $arr_fields['auto_inc']);
}
function GetDBParams($dbh, $set_param)
{
    $param = "";
    $res = mysqli_query($dbh, $set_param);
    if ($res)
    {
        if ($row = mysqli_fetch_row($res))
        {
            $param = $row[0];
        }
		mysqli_free_result($res);
	}
    return $param;
}
function InsertFieldStructure($dbh, $f_name, $table, $chat_set)
{
    $fh = fopen($f_name.".sql", "a");
    fwrite($fh, "CREATE TABLE ".$table."\r\n");
    fwrite($fh, "(\r\n");
    $arr = array("fields"=>array(), "auto_inc"=>"");
    $res = mysqli_query($dbh, "SHOW FULL COLUMNS FROM ".$table);
    if ($res)
    {
		while ($row = mysqli_fetch_row($res))
		{
            $arr['fields'][] = $row;
		}
		mysqli_free_result($res);
    }
    for ($i = 0; $i < count($arr['fields']); $i++)
    {
        $str = $arr['fields'][$i][0]." ".$arr['fields'][$i][1];
        if (!is_null($arr['fields'][$i][2]) && $arr['fields'][$i][2] != "") $str .= " CHARACTER SET ".$chat_set." COLLATE ".$arr['fields'][$i][2];
        if (!is_null($arr['fields'][$i][3]) && $arr['fields'][$i][3] != "") $str .= (($arr['fields'][$i][3] == "NO") ? " NOT NULL" : " NULL");
        if ($arr['fields'][$i][3] == "YES" && is_null($arr['fields'][$i][5])) $str .= " DEFAULT NULL";
        elseif (!is_null($arr['fields'][$i][5]) && $arr['fields'][$i][5] != "") $str .= " DEFAULT '".$arr['fields'][$i][5]."'";
        if (!is_null($arr['fields'][$i][6]) && $arr['fields'][$i][6] != "")
        {
            $arr['auto_inc'] = $auto_inc = $arr['fields'][$i][0]." ".$arr['fields'][$i][1];
	        if (!is_null($arr['fields'][$i][3]) && $arr['fields'][$i][3] != "")
            {
                if ($arr['fields'][$i][3] == "NO") $arr['auto_inc'] .= " NOT NULL";
                else $arr['auto_inc'] .= " NULL";
            }
        }
        if ($i < count($arr['fields']) - 1) $str .= ",";
		fwrite($fh, "  ".$str."\r\n");
	}
	fwrite($fh, ") ENGINE=InnoDB DEFAULT CHARSET=".$chat_set.";\r\n");
    fclose($fh);
    return $arr;
}
function InsertTableRows($dbh, $f_name, $table, $arr_fields)
{
    $c = 0;
    $res = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$table);
    if ($res)
    {
		if ($row = mysqli_fetch_row($res))
		{
            $c = $row[0];
		}
		mysqli_free_result($res);
    }
    if ($c > 0)
    {
        $i = 1;
	    $fh = fopen($f_name.".sql", "a");
	    fwrite($fh, "INSERT INTO ".$table." VALUES\r\n");
        $res = mysqli_query($dbh, "SELECT * FROM ".$table);
        if ($res)
        {
		    while ($row = mysqli_fetch_row($res))
		    {
		        $arr_row = InsertRow($row, $arr_fields);
		        if ($i == $c) fwrite($fh, "(".implode(",", $arr_row).");\r\n");
		        else fwrite($fh, "(".implode(",", $arr_row)."),\r\n");
		        $i++;
  		    }
		    mysqli_free_result($res);
		}
        fclose($fh);
    }
}
function InsertRow($row, $arr_fields)
{
    $arr_row = array();
    $i = 0;
    foreach ($row as $v)
    {
        if (is_null($v)) $arr_row[] = "NULL";
        elseif (!is_null($arr_fields[$i][2]) && $arr_fields[$i][2] != "") $arr_row[] = "'".$v."'";
        else $arr_row[] = $v;
        $i++;
    }
    return $arr_row;
}
function InsertIndexInformation($dbh, $f_name, $table, $auto_inc)
{
    $ind = array();
    $res = mysqli_query($dbh, "SHOW INDEXES FROM ".$table);
    if ($res)
    {
		while ($row = mysqli_fetch_row($res))
		{
            $ind[] = $row;
		}
		mysqli_free_result($res);
    }
    if (count($ind) > 0)
    {
        $ind_names = array();
	    $fh = fopen($f_name.".sql", "a");
	    fwrite($fh, "ALTER TABLE ".$ind[0][0]."\r\n");
	    foreach ($ind as $ind_v) if (!in_array($ind_v[2], $ind_names)) $ind_names[$ind_v[2]] = $ind_v[1];
	    $last_key = array_key_last($ind_names);
        foreach ($ind_names as $k => $uniq)
        {
            $ind_field_arr = array();
            foreach ($ind as $ind_v) if ($ind_v[2] == $k) $ind_field_arr[$ind_v[6]] = $ind_v[4];
            ksort($ind_field_arr);
            if ($k == "PRIMARY") $str = "PRIMARY KEY (".implode(",", $ind_field_arr).")";
            elseif ($uniq == 0) $str = "UNIQUE KEY ".$k." (".implode(",", $ind_field_arr).")";
            else $str = "KEY ".$k." (".implode(",", $ind_field_arr).")";
            if ($k == $last_key) $str .= ";";
            else $str .= ",";
            fwrite($fh, "  ADD ".$str."\r\n");
        }
    }
    if ($auto_inc != "")
    {
        fwrite($fh, "ALTER TABLE ".$ind[0][0]."\r\n");
        fwrite($fh, "  MODIFY ".$auto_inc." AUTO_INCREMENT, AUTO_INCREMENT=1;\r\n");
    }
    fclose($fh);
}

function GetDB($dbname, $pass_file, &$Mes, $coding = "utf8")
{
    $fl = false;
	if (file_exists($pass_file))
	{
		$arrAuth = file($pass_file, FILE_IGNORE_NEW_LINES);
		if (count($arrAuth) > 2)
		{
	        $conn = mysqli_connect($arrAuth[0], $arrAuth[1], $arrAuth[2]);
	        if ($conn)
	        {
                if (IsDB($conn, $dbname))
                {
  	                $dbh = mysqli_connect($arrAuth[0], $arrAuth[1], $arrAuth[2], $dbname);
	                if ($dbh)
                    {
                        $fl = true;
                        if ($coding != "") mysqli_query($dbh, "SET NAMES '".$coding."'");
			        }
			        else $Mes = "Error connecting to DB";
			     }
			     else $Mes = "There is no such DB";
		    }
		    else $Mes = "Failed to establish connection to DB server";
		}
		else $Mes = "Invalid content of credential file";
	}
	else $Mes = "No credential file";
	if ($fl) return $dbh;
	else return false;
}
function IsDB($conn, $db_name)
{
    $fl = false;
    $res = mysqli_query($conn, "SHOW DATABASES");
    if ($res)
    {
        while ($row = mysqli_fetch_row($res))
        {
            if ($row[0] == $db_name)
            {
                $fl = true;
            }
        }
        mysqli_free_result($res);
    }
    return $fl;
}
function GetListDBs($pass_file, &$Mes)
{
    $_SESSION['db_list'] = array("");
	$Mes = "";
	if (file_exists($pass_file))
	{
		$arrAuth = file($pass_file, FILE_IGNORE_NEW_LINES);
		if (count($arrAuth) > 2)
		{
	        $conn = mysqli_connect($arrAuth[0], $arrAuth[1], $arrAuth[2]);
	        if ($conn)
	        {
                $res = mysqli_query($conn, "SHOW DATABASES");
                if ($res)
                {
                    while ($row = mysqli_fetch_row($res))
                    {
                        $_SESSION['db_list'][] = $row[0];
                    }
                    mysqli_free_result($res);
                }
		    }
		    else $Mes = "Failed to establish connection to DB server";
		}
		else $Mes = "Invalid content of credential file";
	}
	else $Mes = "No credential file";
}
function AfterSelectDBList(&$sw_break, &$Mes)
{
    if ($_POST['db_list_s'] == "") $sw_break = false;
    else
    {
        $_SESSION['db'] = $_POST['db'];
        $dbh = GetDB($_POST['db'], "_Credentials.txt", $Mes);
        if ($dbh)
        {
            $_SESSION['table_list'] = array("");
	        $res = mysqli_query($dbh, "SHOW TABLES");
	        if ($res)
	        {
		        while ($row = mysqli_fetch_row($res))
                {
                    $_SESSION['table_list'][] = $row[0];
                }
		        mysqli_free_result($res);
	        }
        }
    }
}

?>

