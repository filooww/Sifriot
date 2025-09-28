<?php
function CanCopy()
{
	foreach ($_SESSION['cat_arr']['0'] as $k => $v) if (is_numeric($k) && ($v[1] || $v[2])) return false; // new??
	return true;
}
function CopyBranch($to_id, $to_ind, $set_inf, $separator)
{
	$s_copy_paste = array("copy_id"=>"", "parent_value"=>"", "parent_value_text"=>"", "copy_value"=>"", "copy_text_value"=>"");
	if (CanCopy())
	{
		$s_copy_paste['copy_id'] = $to_id;
		$arr_copy = explode(",", $set_inf[3]);
		$arr_text_copy = explode($separator, $set_inf[4]);
		$s_copy_paste['parent_value'] = implode(",", array_slice($arr_copy, 0, (integer)$to_ind));
		$s_copy_paste['parent_value_text'] = implode($separator, array_slice($arr_text_copy, 0, (integer)$to_ind));
		$s_copy_paste['copy_value'] = implode(",", array_slice($arr_copy, (integer)$to_ind));
		$s_copy_paste['copy_text_value'] = implode($separator, array_slice($arr_text_copy, (integer)$to_ind));
	}
	else $_SESSION['mes']['0'][] = array("time"=>"", "text"=>IsChangesAndErrors("0", Title(501)." ", " ".Title(470)), "status"=>"warning", "now"=>true);
	return $s_copy_paste;
}
function PasteBranch($dbh, $to_id, $s_copy_paste)
{
	$move_fl = false;
	if ($s_copy_paste['copy_id'] == "") $_SESSION['mes']['0'][] = array("time"=>"", "text"=>Title(502), "status"=>"warning", "now"=>true);
	elseif ($to_id == $s_copy_paste['copy_id']) $_SESSION['mes']['0'][] = array("time"=>"", "text"=>Title(503), "status"=>"error", "now"=>true);
	else
	{
		$max_lev = GetMaxSetLevel($dbh, $_SESSION['Catalog']['0']['table']);
		if ($s_copy_paste['panent_value'] == "") $broot = $s_copy_paste['copy_value'];
		else $broot = $s_copy_paste['parent_value'].",".$s_copy_paste['copy_value'];
		$q_text = VF($max_lev, $_SESSION['Catalog']['0']['table'], $_SESSION['Catalog']['0']['id'], $_SESSION['Catalog']['0']['value'], $_SESSION['Catalog']['1']['table'], $_SESSION['Catalog']['1']['id'], $_SESSION['Catalog']['1']['value'], "", chr(34).chr(34), "f");
		$qt = "SELECT * FROM (SELECT ".$_SESSION['Catalog']['0']['id'].", ".$_SESSION['Catalog']['0']['value'].", ".$qt." FROM ".$_SESSION['Catalog']['0']['table'].") AS T WHERE ".$_SESSION['Catalog']['0']['value']." = ''".$broot."' OR ".$_SESSION['Catalog']['0']['value']." LIKE '".$broot.",%'";
		$res = mysqli_query($dbh, $qt);
		if ($res)
		{
			while ($row = mysqli_fetch_row($res))
            {
                ChangeBranch($dbh, $s_copy_paste, ($to_id == "") ? array() : $_SESSION['cat_arr']['0'][$to_id], $row, $max_lev);
            }
			mysqli_free_result($res);
			SaveMaxLevel($dbh, $max_lev);
		}
		$_SESSION['cat_arr']['0'] = GetCatalogPortion($dbh, "0", $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion']);
		$move_fl = true;
	}
	return $move_fl;
}
function ChangeBranch($dbh, $s_copy_paste, $root_row, $copy_row, &$max_lev)
{
	$new_str = GetNewPasteValue($root_row, $copy_row, $max_lev);
	if ($new_str['err_row'] == "")
	{
		mysqli_query($dbh, "UPDATE ".$_SESSION['Catalog']['0']['table']." SET ".$_SESSION['Catalog']['0']['value']." = '".$new_str['new_value']."' WHERE ".$_SESSION['Catalog']['0']['id']." = ".$row[0]);
		if (mysqli_errno($dbh) == 0)
		{
			if ($_SESSION['collapse'][$_SESSION['Catalog']['0']['table']]['id'] == $row[0]) $_SESSION['collapse'][$_SESSION['Catalog']['0']['table']][$row[0]]['set'] = $new_str['new_value'];
			$cn = count(explode(",", $new_str['new_value']));
			if ($cn > $max_lev) $max_lev = $cn;
		}
		else $_SESSION['mes']['0'][] = array("time"=>"", "text"=>mysqli_error($dbh).": ".Title(221)." <b>".$s_copy_paste['copy_text_value']."</b> ".Title(504), "status"=>"error", "now"=>true);
	}
	else $_SESSION['mes']['0'][] = array("time"=>"", "text"=>Title(505)." <b>".$new_str['err_row']."<b>. ".Title(506), "status"=>"error", "now"=>true);
}
function GetNewPasteValue($root_row, $copy_row, $max_lev)
{
	$new_str = array("new_value"=>"", "err_row"=>"");
	if (count($root_row) == 0)
	{
		$new_arr = explode(",", $copy_row[1]);
		if (count(array_unique($new_arr)) == count($new_arr)) $new_str['new_value'] = $copy_row[1];
		else $new_str['err_row'] = GetQueryValueSet(2, $copy_row, $max_lev);
	}
	else
	{
		$new_arr = array_merge(explode(",", $root_row[3]), explode(",", $copy_row[1]));
		if (count(array_unique($new_arr)) == count($new_arr)) $new_str['new_value'] = $root_row[3].",".$copy_row[1];
		else $new_str['err_row'] = $root_row[4].$_SESSION['Catalog']['0']['separator'].GetQueryValueSet(2, $copy_row, $max_lev);
	}
	return $new_str;
}

?>
