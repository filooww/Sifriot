<?php
function MCV($source_value)
{
	if ($source_value == "") return "";
	if ($_SESSION['match_case']) return $source_value;
	else
	{
		$arr_str = str_split($source_value);
		$low_case = "";
		for ($n = 0; $n < count($arr_str); $n++)
		{
			$ind = array_search(ord($arr_str[$n]), $_SESSION['trans_codes']['b_0']); // ind - current byte index of the source string (search by translate table)
			if ($ind === false) $low_case .= strtolower($arr_str[$n]); // latin letter
			elseif ($n == count($arr_str) - 1) $low_case .= $arr_str[$n]; // non letin single byte letter (it is error in the source string)
			else // non latin letter or special character
			{
				$i = SecondByteSearch($ind, $arr_str, $n); // i - index of second byte in the translate table
				if ($i > -1) // if the second byte found in the translate table
				{
					if ($n == count($arr_str) - 2) // if the double-byte character is at the end of the source string
					{
						$low_case .= ToLowerLetter($_SESSION['trans_codes']['to_lower'][$i], $arr_str[$n].$arr_str[$n + 1]); // add the double-byte character
						$n++; // move one byte
					}
					else // three-byte character is possible
					{
						$j = ThirdByteSearch($i, $arr_str, $n); // search for the third byte second byte search (j - index of third byte in the translate table)
						if ($j > -1) // three-byte character found in the translate table
						{
							$low_case .= ToLowerLetter($_SESSION['trans_codes']['to_lower'][$j], $arr_str[$n].$arr_str[$n + 1].$arr_str[$n + 2]); // add the third-byte character
							$n += 2; // move two bytes
						}
						else // it is double-byte character
						{
							$low_case .= ToLowerLetter($_SESSION['trans_codes']['to_lower'][$i], $arr_str[$n].$arr_str[$n + 1]); // add the double-byte character
							$n++; // move one byte
						}
					}
				}
				else $low_case .= $arr_str[$n]; // if the second byte not found in the translate table (it is error)
			}
		}
		return $low_case;
	}
}
function SecondByteSearch($ind, $arr_str, $n)
{
	for ($i = $ind; $i < count($_SESSION['trans_codes']['b_0']) && $_SESSION['trans_codes']['b_0'][$i] == ord($arr_str[$n]) && $_SESSION['trans_codes']['b_1'][$i] < ord($arr_str[$n + 1]); $i++);
	if ($i < count($_SESSION['trans_codes']['b_0']) && $_SESSION['trans_codes']['b_0'][$i] == ord($arr_str[$n]) && $_SESSION['trans_codes']['b_1'][$i] == ord($arr_str[$n + 1])) return $i;
	else return -1;
}
function ThirdByteSearch($ind, $arr_str, $n)
{
	for ($i = $ind; $i < count($_SESSION['trans_codes']['b_0']) && $_SESSION['trans_codes']['b_0'][$i] == ord($arr_str[$n]) && $_SESSION['trans_codes']['b_1'][$i] == ord($arr_str[$n + 1]) && $_SESSION['trans_codes']['b_2'][$i] < ord($arr_str[$n + 2]); $i++);
	if ($i < count($_SESSION['trans_codes']['b_0']) && $_SESSION['trans_codes']['b_0'][$i] == ord($arr_str[$n]) && $_SESSION['trans_codes']['b_1'][$i] == ord($arr_str[$n + 1]) && $_SESSION['trans_codes']['b_2'][$i] == ord($arr_str[$n + 2])) return $i;
	else return -1;
}
function ToLowerLetter($lower_case_letter, $source_letter)
{
	if ($lower_case_letter == "") return $source_letter;
	else return $lower_case_letter;
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

function GetDB($dbname, $server_name, $user_name, $user_password, $coding = "")
{
	$dbh = mysqli_connect($server_name, $user_name, $user_password, $dbname);
    if ($dbh)
	{
		if ($coding != "") mysqli_query($dbh, "SET NAMES '".$coding."'");
		return $dbh;
	}
	else return false;
}
function SingleFileUpload($s_file, $d_file, $file_name)
{
	if (ResExists($s_file))
	{
		if (copy($s_file, $d_file)) return array("time"=>GetMessageDate(), "text"=>"File <b>".$file_name."</b> uploaded to the server successfully!", "status"=>"statement");
		else return array("time"=>GetMessageDate(), "text"=>"Error while file <b>".$file_name."</b> uploaded into destination<br>(on the server this file has the name <b>".$d_file."</b>)<br>", "status"=>"error", "log"=>false);
	}
	else return array("time"=>GetMessageDate(), "text"=>"File <b>".$file_name."</b> not exists<br>", "status"=>"error", "log"=>false);
}
function GetMessageDate()
{
	date_default_timezone_set("UTC");
	$cdate = getdate();
	return sprintf("%d-%'.02d-%'.02d %'.02d:%'.02d:%'.02d ", $cdate['year'], $cdate['mon'], $cdate['mday'], $cdate['hours'], $cdate['minutes'], $cdate['seconds']);
}
function UploadErrorMessages($errc)
{
	switch ($errc)
	{
		case 1  : return "the uploaded file exceeds the upload_max_filesize directive in php.ini";
		case 2  : return "the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
		case 3  : return "the uploaded file was only partially uploaded";
		case 4  : return "no file was uploaded";
		case 6  : return "missing a temporary folder";
		case 7  : return "failed to write file to disk";
		case 6  : return "a PHP extension stopped the file upload";
		default : return "unknown error";
	}
}
function PutToFileList($file_inf, $i)
{
	if ($file_inf['err_ind'] > 0) $file_inf['name'] .= " - <font color='#FF0000'>".UploadErrorMessages($file_inf['err_ind'])."</font>";
	echo "<b>".(string)($i + 1).". </b>".$file_inf['name']."<span class='separator_invisible'>space</span>";
}
function UpdatePrimaryPublication($dbh, $load_file_i, $primary_dir, $arr_alg_seq, &$Mess)
{
	$source_dir_id = SetPrimaryDirectory($dbh, $primary_dir);
	$file_struct = GetPublicationFile($dbh, $load_file_i['name'], $source_dir_id);
	if ($file_struct['id_pub'] == "")
	{
		if (count($arr_alg_seq) > 0) UpdateOnAlgorithm($dbh, $PR, $arr_alg_seq, $load_file_i['name']);
		$arrIdErr = InsertPublicationPrimary($dbh, $PR);
		if ($arrIdErr['err'] == "")
		{
			$file_struct['copy'] = true;
			$file_struct['id_pub'] = $arrIdErr['id'];
		}
		else
		{
			$Mess[] = array("time"=>GetMessageDate(), "text"=>$arrIdErr['err'], "status"=>"error", "log"=>true);
			$c_f = false;
			$file_struct['id_pub'] = "";
		}
	}
	else
	{
		if (count($arr_alg_seq) > 0) UpdateOnAlgorithm($dbh, $PR, $arr_alg_seq, $load_file_i['name']);
		$err = UpdateSinglePublication($dbh, $PR, $file_struct['id_pub']);
		if ($err != "") 
		{
			$Mess[] = array("time"=>GetMessageDate(), "text"=>$err, "status"=>"error", "log"=>true);
			$c_f = false;
			$file_struct['id_pub'] = "";
		}
	}
	return $file_struct;
}
//--------------
function ResExists($res_adr)
{
	if (substr($res_adr, 0, 4) == "http")
	{
		$URL_res = get_headers($res_adr);
		$arr_rep = explode(" ", $URL_res[0]);
		if (count($arr_res) > 1) return ($arr_rep[1] == "200");
		else return false;
	}
	else return file_exists($res_adr);
}
function GetPublicationFile($dbh, $primary_file, $source_dir_id)
{
	$file_struct = array("copy"=>false, "id_pub"=>0, "file_year"=>"", "file_volume"=>"", "file_number"=>"", "file_page"=>"", "ord_num"=>"", "file_size"=>0, "file_source"=>"", "source_dir_id"=>$source_dir_id);
	$res = mysqli_query($dbh, "SELECT * FROM files WHERE file_name = '".$primary_file."' AND source_dir_id = ".(string)$source_dir_id." LIMIT 1");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res)) 
		{
			$file_struct['id_pub'] = (string)$row[0];
			$file_struct['file_year'] = $row[3];
			$file_struct['file_volume'] = $row[4];
			$file_struct['file_number'] = $row[5];
			$file_struct['file_page'] = $row[6];
			$file_struct['ord_num'] = $row[7];
			$file_struct['file_size'] = $row[9];
			$file_struct['file_source'] = $row[10];
		}
		mysqli_free_result($res);
	}
	return $file_struct;
}

function UpdateOnAlgorithm($dbh, &$PR, $arr_alg_seq, $d_file)
{
	$arr_group = array();
	foreach ($arr_alg_seq as $v) 
	{
		$k_alg = FindAlgInFV($v);
		if ($k_alg != "" && !in_array($k_alg, $arr_group)) $arr_group[$k_alg] = array();
	}
	$file_arr = array_reverse(explode(chr(47), $d_file));
	$file_arr[0] = str_replace(".".pathinfo($file_arr[0], PATHINFO_EXTENSION), "", $file_arr[0]);
	foreach ($arr_alg_seq as $v) 
	{
		$p_f_arr = GetFieldToPub($v, $file_arr[abs($v['offset'])]);
		foreach ($p_f_arr as $z) if (!in_array($z, $arr_group[$v['field']])) $arr_group[$v['field']][] = $z;
	}
	foreach ($arr_group as $k => $v)
	{
		if ($main_params['const'][1][$k]['table'] != "")
		{
			if ($main_params['const'][1][$k]['table1'] != "")
			{
				$ref_codes = array();
				foreach ($v as $z) $ref_codes[] = GetRefCode($dbh, $_SESSION['main_params']['const'][1][$k]['table1'], $_SESSION['main_params']['const'][1][$k]['id1'], $_SESSION['main_params']['const'][1][$k]['v1'], ltrim(rtrim($z)));
				$main_params['var'][1][$k]['code'] = GetRefCode($dbh, $_SESSION['main_params']['const'][1][$k]['table'], $_SESSION['main_params']['const'][1][$k]['c_id'], $_SESSION['main_params']['const'][1][$k]['c_v'], implode(",", $ref_codes));
				if ($main_params['var'][1][$k]['code'] != "") SaveMaxLevel($dbh, $PR['const'][$k]['table'], count($ref_codes));
			}
			else $main_params['var'][1][$k]['code'] = GetRefCode($dbh, $_SESSION['main_params']['const'][1][$k]['table'], $_SESSION['main_params']['const'][1][$k]['c_id'], $_SESSION['main_params']['const'][1][$k]['c_v'], ltrim(rtrim($v[0])));
		}
		else $main_params['var'][1][$k]['code'] = ltrim(rtrim($v[0]));
	}
	if ($main_params['var'][1]['Title']['code'] == "") $main_params['var'][1]['Title']['code'] = $file_arr[0];
}
function InsertPublicationPrimary($dbh, $PR)
{
	$arr = array("id"=>"", "err"=>"");
	$ins_txt = InsertText($PR);
	mysqli_query($dbh, "ALTER TABLE publication AUTO_INCREMENT = 1");
	mysqli_query($dbh, "INSERT INTO publication ".$ins_txt);
	if (mysqli_errno($dbh) > 0) $arr['err'] = mysqli_error($dbh);
	else $arr['id'] = GetAutoIncrement($dbh);
	return $arr;
}
function UpdateSinglePublication($dbh, $PR, $id_pub)
{
	$upd_txt = UpdateText($PR);
	if ($upd_txt == "") return "";
	mysqli_query($dbh, "UPDATE publication SET ".$upd_txt." WHERE id_publication = ".$id_pub);
	if (mysqli_errno($dbh) > 0) return mysqli_error($dbh);
	else return "";
}
function AddPublicationFile($dbh, &$file_struct, $f_name, $p_var)
{
	if ($p_var['FileYear']['code'] == "") $p_var['FileYear']['code'] = "0";
	if ($file_struct['ord_num'] == 0)
	{
		$ord_num = GetFileOrderNumber($dbh, $file_struct['id_pub']);
		$insert_text = InsertFileText($f_name, $p_var, $file_struct['id_pub'], $ord_num, $file_struct['source_dir_id']);
		mysqli_query($dbh, "INSERT INTO files VALUES (".$insert_text.")");
		$file_struct['ord_num'] = $ord_num;
	}
	else
	{
		$update_text = UpdateFileText($p_var, $file_struct);
		if ($update_text != "") mysqli_query($dbh, "UPDATE files SET ".$update_text." WHERE id_publication = ".$file_struct['id_pub']." AND ord_num = ".$file_struct['ord_num']);
	}
}
function SetPrimaryDirectory($dbh, $primary_dir)
{
	$res = mysqli_query($dbh, "SELECT id_dir FROM primary_directories WHERE dir_name = '".$primary_dir."'";
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
        {
            $source_dir_id = $row[0];
        }
		else
		{
            mysqli_query($dbh, "ALTER TABLE primary_directories AUTO_INCREMENT = 1");
			mysqli_query($dbh, "INSERT INTO primary_directories (dir_name) VALUES ('".$primary_dir."')");
			$source_dir_id = (integer)GetAutoIncrement($dbh);
		}
		mysqli_free_result($res);
	}
	else
	{
	    mysqli_query($dbh, "ALTER TABLE primary_directories AUTO_INCREMENT = 1");
		mysqli_query($dbh, "INSERT INTO primary_directories (dir_name) VALUES ('".$primary_dir."')");
		$source_dir_id = (integer)GetAutoIncrement($dbh);
	}
	return $source_dir_id;
}
//***************
function FindAlgInFV($v_alg)
{
	foreach ($_SESSION['main_params']['var'][1] as $k => $v) if ($k == $v_alg['field'] && $v['code'] == "") return $k;
	return "";
}
function GetFieldToPub($v, &$source_field)
{
	if ($v['reg_exp'] != "") $selit = RegExpProc($v['reg_exp'], $v['reg_scr'], $source_field); //    /\d{4}/ year
	else $selit = GetFieldPart($v, $source_field);
	if ($v['inn_del'] == "") $selit_arr = array($selit);
	else $selit_arr = explode($v['inn_del'], $selit);
	if ($v['del_sym'] != "" && !$v['field_only']) SymbRepl($v['del_sym'], $v['ins_sym'], $source_field);
	return $selit_arr;
}
function GetRefCode($dbh, $table, $id_f, $v_f, $p_f)
{
	if ($p_f == "") return "";
	$f_low = MCF($v_f, $_SESSION['match_case'] == "Y");
	$v_low = MCV($p_f, $_SESSION['match_case'] == "Y");
	$res = mysqli_query($dbh, "SELECT ".$id_f." FROM ".$table." WHERE ".$f_lower." = '".$v_low."' LIMIT 1");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
        {
            $cr = (string)$row[0];
        }
		else $cr = "";
		mysqli_free_result($res);
		if ($cr == "")
		{
            mysqli_query($dbh, "ALTER TABLE ".$table." AUTO_INCREMENT = 1");
			mysqli_query($dbh, "INSERT INTO ".$table." (".$v_f.", ".$v_f."_low) VALUES ('".$p_f."','".$v_low."')");
			if (mysqli_errno($dbh) == 0) return GetAutoIncrement($dbh);
			else return "";
		}
		else return $cr;
	}
	else
	{
	    mysqli_query($dbh, "ALTER TABLE ".$table." AUTO_INCREMENT = 1");
		mysqli_query($dbh, "INSERT INTO ".$table." (".$v_f.", ".$v_f."_low) VALUES ('".$p_f."','".$v_low."')");
		if (mysqli_errno($dbh) == 0) return GetAutoIncrement($dbh);
		else return "";
	}
}
function SaveMaxLevel($dbh, $tableSet, $cur_max_level)
{
	$reqMax = mysqli_query($dbh, "SELECT max_level FROM table_definitions WHERE table_name = '".$tableSet."' LIMIT 1");
	if ($reqMax)
	{
		if ($row = mysqli_fetch_row($reqMax))
		{
			if ($cur_max_level > $row[0]) mysqli_query($dbh, "UPDATE table_definitions SET max_level = ".(string)$cur_max_level." WHERE table_name = '".$tableSet."'");
		}
		else mysqli_query($dbh, "INSERT INTO table_definitions (table_name,max_level) VALUES ('".$tableSet."',".(string)$cur_max_level.")");
		mysqli_free_result($reqMax);
	}
	else mysqli_query($dbh, "INSERT INTO table_definitions (table_name,max_level) VALUES ('".$tableSet."',".(string)$cur_max_level.")");
}
function InsertText($PR)
{
	$arr_field = array();
	$arr_value = array();
	$k_comm = "";
	foreach ($main_params['const'][1] as $k_FV => $value_FV)
	{
		if ($value_FV['comm']) $k_comm = $k_FV;
		if (!$value_FV['comm'] && $main_params['var'][1][$k_FV]['code'] != "" && !$value_FV['file'])
		{
			$arr_field[] = "`".$value_FV['ref']."`";
			$b = ($value_FV['type'] == "string") ? "'" : "";
			$arr_value[] = $b.$main_params['var'][1][$k_FV]['code'].$b;
		}
	}
	if ($k_comm != "")
	{
		if ($main_params['var'][1][$k_comm]['value'] != "")
		{
			$i = array_search("title", $arr_field);
			if ($i === false)
			{
				$arr_field[] = "`title`"; 
				$arr_value[] = "'".$main_params['var'][1][$k_comm]['value']."'";
			}
			else
			{
				$arr_field[$i] = "`title`"; 
				$arr_value[$l] = "'".$main_params['var'][1][$k_comm]['value']."'";
			}
		}
	}
	$arr_field[] = "`title_low`"; 
	$arr_value[] = "'".MCV($main_params['var'][1]['Title']['code'], false)."'";
	$arr_field[] = "`upload_date`"; 
	$arr_value[] = "NOW()";
	return "(".implode(",", $arr_field).") VALUES (".implode(",", $arr_value).")";
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
function UpdateText($PR)
{
	$arr_update = array();
	foreach ($main_params['const'][1] as $k_FV => $value_FV)
	{
		if ($main_params['var'][1][$k_FV]['code'] != "" && !$value_FV['file'])
		{
			$b = (isset($value_FV['type']) && $value_FV['type'] == "string") ? "'" : "";
			$arr_update[] = "`".$value_FV['ref']."` = ".$b.$main_params['var'][1][$k_FV]['code'].$b;
		}
	}
	return implode(",", $arr_update);
}
function GetFileOrderNumber($dbh, $id_pub)
{
	$resfile = mysqli_query($dbh, "SELECT ord_num FROM files WHERE id_publication = ".$id_pub." ORDER BY ord_num DESC");
	if ($resfile)
	{
		if ($rowfile = mysqli_fetch_row($resfile))
        {
            $file_num = $rowfile[0] + 1;
        }
		else $file_num = 1;
		mysqli_free_result($resfile);
	}
	else $file_num = 1;
	return $file_num;
}
function UpdateFileText($p_var, $file_struct)
{
	$arr_update = array();
	if ($p_var['FileYear']['code'] != "" && $p_var['FileYear']['code'] != $file_struct['file_year']) $arr_update[] = "file_issue_year = ".$p_var['FileYear']['code'];
	if ($p_var['FileVolume']['code'] != "" && $p_var['FileVolume']['code'] != $file_struct['file_volume']) $arr_update[] = "file_volume = '".$p_var['FileVolume']['code']."'";
	if ($p_var['FileNumber']['code'] != "" && $p_var['FileNumuber'] != $file_struct['file_number']) $arr_update[] = "file_number = '".$p_var['FileNumber']['code']."'";
	if ($p_var['FilePage']['code'] != "" && $p_var['FilePage']['code'] != $file_struct['file_page']) $arr_update[] = "file_page = '".$p_var['FilePage']['code']."'";
	return implode(",", $arr_update);
}
function InsertFileText($f_name, $p_var, $id_pub, $ord_num, $source_dir_id)
{
	$arr_insert = array();
	$arr_insert[] = $id_pub;
	$arr_insert[] = "'".$f_name."'";
	$arr_insert[] = "''";
	$arr_insert[] = "'".$p_var['FileYear']['code']."'";
	$arr_insert[] = "'".$p_var['FileVolume']['code']."'";
	$arr_insert[] = "'".$p_var['FileNumber']['code']."'";
	$arr_insert[] = "'".$p_var['FilePage']['code']."'";
	$arr_insert[] = (string)$ord_num;
	$arr_insert[] = "'".MCV($f_name, false)."'";
	$arr_insert[] = "0";
	$arr_insert[] = "''";
	$arr_insert[] = $source_dir_id;
	return implode(",", $arr_insert);
}

//@@@@@@@@@@@@@@@@@@@@@@
function RegExpProc($reg, $regscr, &$source_field)
{
	$matches = array();
	$p = preg_match_all($reg, $source_field, $matches, PREG_OFFSET_CAPTURE);
	$res_reg = "";
	if ($p > 0) 
	{
		$res_reg = $matches[0][$p - 1][0];
		if ($regscr) $source_field = str_replace($res_reg, "", $source_field);
	}
	return $res_reg;
}
function GetFieldPart($v, &$source_field)
{
	$beg_del_arr = ($v['beg_del'] == "") ? array() : explode("|", $v['beg_del']);
	$end_del_arr = ($v['end_del'] == "") ? array() : explode("|", $v['end_del']);
	$pos_beg = GetPos($beg_del_arr, $source_field, $v['beg_num']);
	$pos_end = GetPos($end_del_arr, $source_field, $v['end_num'], $pos_beg['pos']);
	if (count($beg_del_arr) > 0 && $pos_beg['pos'] < 0 || count($end_del_arr) > 0 && $pos_end['pos'] < 0) $selit = "";
	else
	{
		$pos1 = ($pos_beg['delim'] == "") ? 0 : $pos_beg['pos'] + (($v['beg_inc']) ? 0 : strlen($pos_beg['delim']));
		$pos2 = ($pos_end['delim'] == "") ? strlen($source_field) : $pos_end['pos'] + (($v['end_inc']) ? strlen($pos_end['delim']) : 0);
		$selit = substr($source_field, $pos1, $pos2 - $pos1);
		if ($v['del_sym'] != "" && $v['field_only']) SymbRepl($v['del_sym'], $v['ins_sym'], $selit);
		if ($v['end_scr'] && $pos_end['delim'] != "") $source_field = substr($source_field, 0, $pos_end['pos']).substr($source_field, $pos_end['pos'] + strlen($pos_end['delim']));
		if ($v['del_from_source']) $source_field = str_replace($selit, "", $source_field);
		if ($v['beg_scr'] && $pos_beg['delim'] != "") $source_field = substr($source_field, 0, $pos_beg['pos']).substr($source_field, $pos_beg['pos'] + strlen($pos_beg['delim']));
	}
	return $selit;
}
function SymbRepl($del_sym, $ins_sym, &$repl_field)
{
	$del_arr = ($del_sym == "") ? array() : explode("|", $del_sym);
	$ins_arr = ($ins_sym == "") ? array() : explode("|", $ins_sym);
	for ($i = 0; $i < count($del_arr); $i++)
	{
		if ($i < count($ins_arr)) $repl_field = str_replace($del_arr[$i], $ins_arr[$i], $repl_field);
		else $repl_field = str_replace($del_arr[$i], "", $repl_field);
	}
}
function MCF($i_field)
{
	if ($_SESSION ['m_case']) return $i_field;
	else return $i_field."_low";
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
function GetPos($delim_arr, $str, $delim_number = 0, $init_pos = -2)
{
	$arr_pos_struct = array();
	foreach ($delim_arr as $z) 
	{
		if ($z != "")
		{
			if ($delim_number == 0)
			{
				$pos = strpos($str, $z, ($init_pos == -2) ? 0 : $init_pos + 1);
				if ($pos !== false) $arr_pos_struct[] = array("pos" => $pos, "delim" => $z);
			}
			else
			{
				$pos_arr = AllPosition($str, $z);
				if ($delim_number <= count($pos_arr)) $arr_pos_struct[] = array("pos" => $pos_arr[$delim_number - 1], "delim" => $z);
			}
		}
	}
	return GetMinPos($arr_pos_struct);
}
function GetMinPos($arr_pos_struct)
{
	$res_struct = array("pos" => -1, "delim" => "");
	foreach ($arr_pos_struct as $z) 
	{
		if ($res_struct['pos'] == -1 || $res_struct['pos'] >= 0 && $z['pos'] < $res_struct['pos']) 
		{
			$res_struct['pos'] = $z['pos'];
			$res_struct['delim'] = $z['delim'];
		}
	}
	return $res_struct;
}
function GetLocalCodes($dbh, $id_lang = -1)
{
	$arr = array("lang_code"=>array(), "letter"=>array(), "b_0"=>array(), "b_1"=>array(), "b_2"=>array(), "to_lower"=>array(), "full_code"=>array());
	if ($id_lang == -1) $res = mysqli_query($dbh, "SELECT * FROM translate_table ORDER BY letter");
	else $res = mysqli_query($dbh, "SELECT * FROM translate_table WHERE id_lang = ".(string)$id_lang." ORDER BY letter");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res)) 
		{
			$arr['lang_code'][] = $row[0];
			$arr['letter'][] = $row[1];
			$arr['b_0'][] = ord(substr($row[1], 0));
			$arr['b_1'][] = ord(substr($row[1], 1));
			$arr['b_2'][] = ord(substr($row[1], 2));
			$arr['to_lower'][] = $row[2];
			$f_code = 1000000 * end($arr['b_0']) + 1000 * end($arr['b_1']) + end($arr['b_2']);
			$arr['full_code'][] = $f_code;
			if ($row[3] != $f_code) mysqli_query($dbh, "UPDATE translate_table SET letter = ".(string)$f_code." WHERE letter = '".$row[1]."'");
		}
		mysqli_free_result($res);
	}
	return $arr;
}
function TestBool($b)
{
	if ($b) return "Y";
	else return "N";
}

//%%%%%%%%%%%%%%
?>
