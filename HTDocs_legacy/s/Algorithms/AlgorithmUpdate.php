<?php
function EditAlgorithm($dbh, $k_c, $v_c, &$Mes)
{
	if ($_SESSION['edit_algorithm'] == "")
    {
        $_SESSION['edit_algorithm'] = $k_c;
        $_SESSION['algorithm_info'] = $k_c;
    }
	else
	{
		ChangeAlgorithmParameters($k_c, $v_c);
		TestAlgorithm($k_c, $_SESSION['arr_alg'][$k_c]);
        if ($_SESSION['algorithm_insert']) AlgorithmInsert($dbh, $k_c, $Mes);
		else UpdateAlgorithm($dbh, $k_c);
		$_SESSION['edit_algorithm'] = "";
		UnsetReplaces($k_c);
		ResetAlgorithmParse();
        if ($_SESSION['algorithm_insert'])
        {
            GetAlgorithmPortion($dbh);
            $_SESSION['algorithm_insert'] = false;
        }
        foreach ($_SESSION['arr_alg'] as $k => $v) TestAlgorithm($k, $v);
	}
}
function AlgorithmInsert($dbh, $k, &$Mes)
{
    $insert_arr = array("fields"=>array(), "values"=>array());
    if (!$_SESSION['alg_flag'][$k]['offset']) SetAlgorithmInserting($k, "alg_offset", "integer", "offset", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['del_from_source']) SetAlgorithmInserting($k, "del_from_source", "integer", "del_from_source", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['beg_del']) SetAlgorithmInserting($k, "beg_limit_set", "string", "beg_del", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['beg_num']) SetAlgorithmInserting($k, "beg_number", "integer", "beg_num", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['beg_inc']) SetAlgorithmInserting($k, "beg_inc", "integer", "beg_inc", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['beg_scr']) SetAlgorithmInserting($k, "beg_scr", "integer", "beg_scr", $insert_arr);
    if (!$_SESSION['alg_flag'][$k]['inn_del']) SetAlgorithmInserting($k, "inner_limit_set", "string", "inn_del", $insert_arr);
    if (!$_SESSION['alg_flag'][$k]['end_del']) SetAlgorithmInserting($k, "end_limit_set", "string", "end_del", $insert_arr);
    if (!$_SESSION['alg_flag'][$k]['end_num']) SetAlgorithmInserting($k, "end_number", "integer", "end_num", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['end_inc']) SetAlgorithmInserting($k, "end_inc", "integer", "end_inc", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['end_scr']) SetAlgorithmInserting($k, "end_scr", "check", "end_scr", $insert_arr);
    if (!$_SESSION['alg_flag'][$k]['del_sym']) SetAlgorithmInserting($k, "del_symbols", "string", "del_sym", $insert_arr);
    if (!$_SESSION['alg_flag'][$k]['ins_sym']) SetAlgorithmInserting($k, "ins_symbols", "string", "ins_sym", $insert_arr);
    if (!$_SESSION['alg_flag'][$k]['field_only']) SetAlgorithmInserting($k, "field_only", "integer", "field_only", $insert_arr);
    if (!$_SESSION['alg_flag'][$k]['reg_exp']) SetAlgorithmInserting($k, "reg_expression", "string", "reg_exp", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['reg_scr']) SetAlgorithmInserting($k, "reg_scr", "integer", "end_scr", $insert_arr);
	if (!$_SESSION['alg_flag'][$k]['remarks']) SetAlgorithmInserting($k, "alg_remarks", "string", "remarks", $insert_arr);
	mysqli_query($dbh, "ALTER TABLE algorithms AUTO_INCREMENT = 1");
	if (count($insert_arr['fields']) == 0) mysqli_query($dbh, "INSERT INTO algorithms (id_algorithm) VALUES (".$k.")");
    else mysqli_query($dbh, "INSERT INTO algorithms (id_algorithm,".implode(",", $insert_arr['fields']).") VALUES (".$k.",".implode(",", $insert_arr['values']).")");
    if (mysqli_errno($dbh) == 1062) $Mes[] = Title(316);
	else $_SESSION['algorithm_size']++;
}
function SetAlgorithmInserting($k, $field_name, $field_type, $param_name, &$insert_arr)
{
    $ap = ($field_type == "string") ? "'" : "";
    $insert_arr['fields'][] = $field_name;
    $insert_arr['values'][] = $ap.$_SESSION['arr_alg'][$k][$param_name].$ap;
}
function UpdateAlgorithm($dbh, $k)
{
	$update_arr = array();
	$res = mysqli_query($dbh, "SELECT * FROM algorithms WHERE id_algorithm = ".$k);
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if (!$_SESSION['alg_flag'][$k]['offset'] && $_SESSION['arr_alg'][$k]['offset'] != $row[1]) SetAlgorithmUpdating($k, "alg_offset", "integer", "offset", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['del_from_source'] && $_SESSION['arr_alg'][$k]['del_from_source'] != $row[2]) SetAlgorithmUpdating($k, "del_from_source", "integer", "del_from_source", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['beg_del'] && $_SESSION['arr_alg'][$k]['beg_del'] != $row[3]) SetAlgorithmUpdating($k, "beg_limit_set", "string", "beg_del", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['beg_num'] && $_SESSION['arr_alg'][$k]['beg_num'] != $row[4]) SetAlgorithmUpdating($k, "beg_number", "integer", "beg_num", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['beg_inc'] && $_SESSION['arr_alg'][$k]['beg_inc'] != $row[5]) SetAlgorithmUpdating($k, "beg_inc", "integer", "beg_inc", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['beg_scr'] && $_SESSION['arr_alg'][$k]['beg_scr'] != $row[6]) SetAlgorithmUpdating($k, "beg_scr", "integer", "beg_scr", $update_arr);
            if (!$_SESSION['alg_flag'][$k]['inn_del'] && $_SESSION['arr_alg'][$k]['inn_del'] != $row[7]) SetAlgorithmUpdating($k, "inner_limit_set", "string", "inn_del", $update_arr);
            if (!$_SESSION['alg_flag'][$k]['end_del'] && $_SESSION['arr_alg'][$k]['end_del'] != $row[8]) SetAlgorithmUpdating($k, "end_limit_set", "string", "end_del", $update_arr);
            if (!$_SESSION['alg_flag'][$k]['end_num'] && $_SESSION['arr_alg'][$k]['end_num'] != $row[9]) SetAlgorithmUpdating($k, "end_number", "integer", "end_num", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['end_inc'] && $_SESSION['arr_alg'][$k]['end_inc'] != $row[10]) SetAlgorithmUpdating($k, "end_inc", "integer", "end_inc", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['end_scr'] && $_SESSION['arr_alg'][$k]['end_scr'] != $row[11]) SetAlgorithmUpdating($k, "end_scr", "integer", "end_scr", $update_arr);
            if (!$_SESSION['alg_flag'][$k]['del_sym'] && $_SESSION['arr_alg'][$k]['del_sym'] != $row[12]) SetAlgorithmUpdating($k, "del_symbols", "string", "del_sym", $update_arr);
            if (!$_SESSION['alg_flag'][$k]['ins_sym'] && $_SESSION['arr_alg'][$k]['ins_sym'] != $row[13]) SetAlgorithmUpdating($k, "ins_symbols", "string", "ins_sym", $update_arr);
            if (!$_SESSION['alg_flag'][$k]['field_only'] && $_SESSION['arr_alg'][$k]['field_only'] != $row[14]) SetAlgorithmUpdating($k, "field_only", "integer", "field_only", $update_arr);
            if (!$_SESSION['alg_flag'][$k]['reg_exp'] && $_SESSION['arr_alg'][$k]['reg_exp'] != $row[15]) SetAlgorithmUpdating($k, "reg_expression", "string", "reg_exp", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['reg_scr'] && $_SESSION['arr_alg'][$k]['reg_scr'] != $row[16]) SetAlgorithmUpdating($k, "reg_scr", "integer", "end_scr", $update_arr);
			if (!$_SESSION['alg_flag'][$k]['remarks'] && $_SESSION['arr_alg'][$k]['remarks'] != $row[17]) SetAlgorithmUpdating($k, "alg_remarks", "string", "remarks", $update_arr);
		}
		mysqli_free_result($res);
		if (count($update_arr) > 0) mysqli_query($dbh, "UPDATE algorithms SET ".implode(",", $update_arr)." WHERE id_algorithm = ".$k);
	}
}
function SetAlgorithmUpdating($k, $field_name, $field_type, $param_name, &$param_list)
{
    if ($field_type == "string") $param_list[] = $field_name." = '".$_SESSION['arr_alg'][$k][$param_name]."'";
    else $param_list[] = $field_name." = ".$_SESSION['arr_alg'][$k][$param_name];
}
function ChangeAlgorithmFromPost($post_prefix, $k, $v_name, $f_type = "")
{
	if ($f_type == "check")
	{
	    if (isset($_POST[$post_prefix."|".$k]) && $_SESSION['arr_alg'][$k][$v_name] != 1) $_SESSION['arr_alg'][$k][$v_name] = 1;
	    elseif (!isset($_POST[$post_prefix."|".$k]) && $_SESSION['arr_alg'][$k][$v_name] != 0) $_SESSION['arr_alg'][$k][$v_name] = 0;
	}
	else $_SESSION['arr_alg'][$k][$v_name] = $_POST[$post_prefix."|".$k];
}
function ChangeAlgorithmParameters($k, $v)
{
	if ($_POST["remarks|".$k] != $v['remarks']) ChangeAlgorithmFromPost("remarks", $k, "remarks");
	if ($_POST["alg_offset|".$k] != $v['offset']) ChangeAlgorithmFromPost("alg_offset", $k, "offset");
	ChangeAlgorithmFromPost("del_from_source", $k, "del_from_source", "check");
	if ($_POST["alg_beg_delim|".$k] != $v['beg_del']) ChangeAlgorithmFromPost("alg_beg_delim", $k, "beg_del");
	if ($_POST["alg_beg_number|".$k] != $v['beg_num']) ChangeAlgorithmFromPost("alg_beg_number", $k, "beg_num");
    ChangeAlgorithmFromPost("beg_inc", $k, "beg_inc", "check");
    ChangeAlgorithmFromPost("beg_scr", $k, "beg_scr", "check");
	if ($_POST["inn_del|".$k] != $v['inn_del']) ChangeAlgorithmFromPost("inn_del", $k, "inn_del");
    if ($_POST["alg_end_delim|".$k] != $v['end_del']) ChangeAlgorithmFromPost("alg_end_delim", $k, "end_del");
    if ($_POST["alg_end_number|".$k] != $v['end_num']) ChangeAlgorithmFromPost("alg_end_number", $k, "end_num");
    ChangeAlgorithmFromPost("end_inc", $k, "end_inc", "check");
    ChangeAlgorithmFromPost("end_scr", $k, "end_scr", "check");
    if ($_POST["del_sym|".$k] != $v['del_sym']) ChangeAlgorithmFromPost("del_sym", $k, "del_sym");
    if ($_POST["ins_sym|".$k] != $v['ins_sym']) ChangeAlgorithmFromPost("ins_sym", $k, "ins_sym");
    ChangeAlgorithmFromPost("field_only", $k, "field_only", "check");
    if ($_POST["reg_exp|".$k] != $v['reg_exp']) ChangeAlgorithmFromPost("reg_exp", $k, "reg_exp");
    ChangeAlgorithmFromPost("reg_scr", $k, "reg_scr", "check");
}
function DeleteMarkedAlgorithms($dbh)
{
	$del_ids = array();
	mysqli_query($dbh, "DELETE FROM algorithms WHERE id_algorithm IN (".implode(",", $_SESSION['alg_del']).")");
	mysqli_query($dbh, "ALTER TABLE algorithms AUTO_INCREMENT = 1");
    foreach ($_SESSION['alg_del'] as $k) if (isset($_SESSION['update_replace'][$k])) unset($_SESSION['update_replace'][$k]);
    $_SESSION['alg_del'] = array();
    $_SESSION['algorithm_size'] = AlgorithmSize($dbh);
	GetAlgorithmPortion($dbh);
	foreach ($_SESSION['arr_alg'] as $k => $v) TestAlgorithm($k, $v);
}
function AlgorithmQuestionString()
{
    $arr = array();
    foreach ($_SESSION['alg_del'] as $k) $arr[] = $k;
    return $arr;
}
function AddNewAlgorithm($dbh, $copy_id, &$Mes)
{
	$_SESSION['algorithm_insert'] = true;
    AlgorithmNavigation($dbh, "end", $Mes);
    if (count($_SESSION['arr_alg']) == 0) $new_alg_id = 1;
    else
    {
        reset($_SESSION['arr_alg']);
        $new_alg_id = (integer)key($_SESSION['arr_alg']) + 1;
    }
    NewAlgStructure($new_alg_id, $copy_id);
    TestAlgorithm((string)$new_alg_id, $_SESSION['arr_alg'][(string)$new_alg_id]);
	$_SESSION['edit_algorithm'] = (string)$new_alg_id;
	$_SESSION['algorithm_info'] = (string)$new_alg_id;
}
function NewAlgStructure($new_alg_id, $copy_id)
{
	$_SESSION['arr_alg'][(string)$new_alg_id]['offset'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['offset'] : 0);
	$_SESSION['arr_alg'][(string)$new_alg_id]['del_from_source'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['del_from_source'] : 0);
	$_SESSION['arr_alg'][(string)$new_alg_id]['beg_del'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['beg_del'] : "");
    $_SESSION['arr_alg'][(string)$new_alg_id]['beg_num'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['beg_num'] : 0);
    $_SESSION['arr_alg'][(string)$new_alg_id]['beg_inc'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['beg_inc'] : 0);
    $_SESSION['arr_alg'][(string)$new_alg_id]['beg_scr'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['beg_scr'] : 0);
    $_SESSION['arr_alg'][(string)$new_alg_id]['inn_del'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['inn_del'] : "");
    $_SESSION['arr_alg'][(string)$new_alg_id]['end_del'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['end_del'] : "");
    $_SESSION['arr_alg'][(string)$new_alg_id]['end_num'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['end_num'] : 0);
    $_SESSION['arr_alg'][(string)$new_alg_id]['end_inc'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['end_inc'] : 0);
    $_SESSION['arr_alg'][(string)$new_alg_id]['end_scr'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['end_scr'] : 0);
    $_SESSION['arr_alg'][(string)$new_alg_id]['del_sym'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['del_sym'] : "");
    $_SESSION['arr_alg'][(string)$new_alg_id]['ins_sym'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['ins_sym'] : "");
    $_SESSION['arr_alg'][(string)$new_alg_id]['field_only'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['field_only'] : 0);
    $_SESSION['arr_alg'][(string)$new_alg_id]['reg_exp'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['reg_exp'] : "");
    $_SESSION['arr_alg'][(string)$new_alg_id]['reg_scr'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['reg_scr'] : 0);
    $_SESSION['arr_alg'][(string)$new_alg_id]['remarks'] = (($copy_id) ? $_SESSION['arr_alg'][$_SESSION['algorithm_info']]['remarks'] : "New algorithm");
}
function UnsetReplaces($k)
{
    $arr_unset = array();
    foreach ($_SESSION['algorithm_err']['replace_fields'] as $fld) if (isset($_SESSION['update_replace'][$k][$fld]) && !$_SESSION['alg_flag'][$k][$fld]) $arr_unset[$k] = $fld;
    foreach ($arr_unset as $k => $fld) unset($_SESSION['update_replace'][$k][$fld]);
}

?>
