<head>
	<style>.dis_text {color:#CCCCCC;}</style>
	<style>.exit_from_catalog {background-color:#FFCC00;}</style>
	<style>.invisible_button {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin}</style>
</head>

<?php
function CatalogSelectCall($dbh, $conf, $k, $user_id, $sel_value, &$cur_value)
{
	$_SESSION['Catalog']['0'] = array();
	switch ($k)
	{
		case "Authors":		$C['0'] = array("name"=>"Author sets", "tbl"=>"author_groups", "id"=>"id_author_group", "vl"=>"author_set", "spr"=>GetTableValue($dbh, "author_groups", "separators", false), "cat_type"=>"abc");
							$C['1'] = array("name"=>"Authors", "tbl"=>"authors", "id"=>"id_author", "vl"=>"author", "cat_type"=>""); break;
		case "Themes":		$C['0'] = array("name"=>"Theme sets", "tbl"=>"theme_sets", "id"=>"id_theme_set", "vl"=>"theme_set", "spr"=>GetTableValue($dbh, "author_groups", "separators", false), "cat_type"=>"ordinary");
							$C['1'] = array("name"=>"Themes", "tbl"=>"themes", "id"=>"id_theme", "vl"=>"theme", "cat_type"=>""); break;
		case "Publishing":	$C['0'] = array("name"=>"Publishings", "tbl"=>"publishings", "id"=>"id_publishing", "vl"=>"publishing", "spr"=>"", "cat_type"=>"");
							$C['1'] = array("name"=>"", "tbl"=>"", "id"=>"", "vl"=>"", "cat_type"=>""); break;
		case "Series":		$C['0'] = array("name"=>"Series sets", "tbl"=>"part_sets", "id"=>"id_part_set", "vl"=>"part_set", "spr"=>GetTableValue($dbh, "part_sets", "separators", false), "cat_type"=>"hierarchic");
							$C['1'] = array("name"=>"Series", "tbl"=>"parts", "id"=>"id_part", "vl"=>"part", "cat_type"=>""); break;
		case "IssueType":	$C['0'] = array("name"=>"Issue types", "tbl"=>"issue_types", "id"=>"id_issue_type", "vl"=>"issue_type", "spr"=>"", "cat_type"=>"");
							$C['1'] = array("name"=>"", "tbl"=>"", "id"=>"", "vl"=>"", "cat_type"=>""); break;
		case "Magazine":	$C['0'] = array("name"=>"Magazines", "tbl"=>"magazines", "id"=>"id_magazine", "vl"=>"magazine", "spr"=>"", "cat_type"=>"");
							$C['1'] = array("name"=>"", "tbl"=>"", "id"=>"", "vl"=>"", "cat_type"=>""); break;
	}
	if (count($_SESSION['Catalog']['0']) > 0)
	{
		if ($sel_value != "")
		{
			$np = GetOffsetInTable($dbh, "0", $sel_value);
			if ($np > -1 && ($np < $_SESSION['catalog_param']['0']['start_pos'] || $np > $_SESSION['catalog_param']['0']['start_pos'] + (integer)$_SESSION['portion'] - 1)) $_SESSION['catalog_param']['0']['start_pos'] = $np;
		}
		$_SESSION['cat_arr'] = array("0"=>GetCatalogPortion($dbh, "0", $_SESSION['catalog_param']['0']['start_pos'], $_SESSION['portion'], true), "1"=>array());
		$_SESSION['catalog_param']['0']['total_count'] = GetTableLimit($dbh, "total", "0");
		if ($_SESSION['Catalog']['0']['cat_type'] == 1) $_SESSION['catalog_param']['tree_total_count'] = TreeCounts($_SESSION['catalog_param']['total_count']);
		$cur_value = $sel_value;
		return true;
	}
	else return false;
}
function ViewCatalogForSelect($cat_vis, $conf, $block, $tree, $moda, $sel_modes, $cur_value, $ccv)
{
	echo "<table frame='border'>";
		echo "<tr>";
			echo "<td>";
				echo "<table>";
					echo "<tr>";
						echo "<td><button name='cat_exit' type='submit' title='".Title(101)."' class='exit_from_catalog' value='*'>".ImgV("Close", 16, 16)."</button></td>";
						echo "<td>".TableAdjustment("cat_height_minus", "cat_height_plus", "w_b")."</td>";
						if ($_SESSION['Catalog']['0']['cat_type'] == 1)
						{
							echo "<td><button name='cat_tree' type='submit' title='".Title(102)."' value='*'".SetExPar($block['cat_del'] || $block['cat_goto'], "").">".ImgV("Tree", 16, 16)."</button></td>";
							echo "<td><button name='cat_tree_collapse' type='submit' title='".Title(105)."' value='*'".SetExPar($block['cat_del'] || $block['cat_goto'], "").">".ImgV("TreeCollapse", 16, 16)."</button></td>";
							echo "<td><button name='cat_tree_extend' type='submit' title='".Title(106)."' value='*'".SetExPar($block['cat_del'] || $block['cat_goto'], "").">".ImgV("TreeExtend", 16, 16)."</button></td>";
						}
					echo "</tr>";
				echo "</table>";
			echo "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>";
				echo "<table frame='border'>";
					echo "<tr valign='top'>";
						echo "<td>";
							ViewTableForSelect($cat_vis, $conf, $block, $tree, $moda, $sel_modes, $cur_value, $ccv);
						echo "</td>";
					echo "<tr>";
				echo "</table>";		
			echo "</td>";
		echo "</tr>";
	echo "</table>";
}
function ViewTableForSelect($cat_vis, $cg, $block, $tree, $moda, $sel_modes, $cur_value, $ccv)
{
	if ($_SESSION['catalog_param']['0']['filter_on']) FormForSelection(Title(99), "FFCC00", "filter_text0", "filter_start0", "apply filter", "filter_mode0", "0", "filter_text", "filter_mode_values", "filter_compare");
	if ($_SESSION['catalog_param']['0']['search_on']) FormForSelection(Title(83), "00FFFF", "search_text0", "search_start0", "start search", "search_mode0", "0", "search_text", "search_mode_values", "search_compare");
	ViewTitleMenu("0");
	echo "<table frame='border'>";
		echo "<tr valign='top'>";
			echo "<td>";
				echo "<table>";
					ViewNavigation("0");
				echo "</table>";
			echo "</td>";
			echo "<td>";
				echo "<table>";
					ViewPortionForSelect($_SESSION['catalog_param']['0'], GetRadioType($_SESSION['catalog_param']['0']['search_where']), $cg, $cat_vis, $tree, $cur_value, $ccv);
				echo "</table>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";
}
function ViewPortionForSelect($s_type, $conf, $cat_vis, $tree, $cur_value, $ccv)
{
	$blks = SetExPar($_SESSION['block']['cat_del'] || $_SESSION['block']['cat_goto'] || $_SESSION['catalog_param']['0']['search_on'], "");
	foreach ($_SESSION['cat_arr']['0'] as $k_ref => $v_ref)
	{
		echo "<tr valign='top'>";
			$cc = "0|".$k_ref;
			$cl = (end($v_ref) == $cur_value) ? "class='".$ccv."'" : "";
			echo "<td width='".$conf['w_05']."'><button style='width:".$conf['w_06'].";text-align:right' name='cat_code0|".$k_ref."' type='submit' title='".Title(103)."' value='".$k_ref."'>".$k_ref."</button></td>";
			if ($_SESSION['Catalog']['0']['cat_type'] == 1 && $tree) echo "<td><input type='text' name='cat_text".$cc."' value='".TreeViewRow(end($v_ref))."' size=".$conf['w_08']."' readonly ".$cl."></td>";
			else echo "<td><input type='text' name='cat_text".$cc."' value='".end($v_ref)."' size=".$conf['w_08']."' readonly ".$cl."></td>";
			if ($_SESSION['catalog_param']['0']['found_count'] > 0 && $_SESSION['catalog_param']['0']['view_search']) echo "<td>".RowForOutput(end($v_ref), "0", $s_type)."</td>";
		echo "</tr>";
		if ($cat_vis['b_on'] && $k_ref == $cat_vis['bc'] && end($v_ref) != "") SetByRows($k_ref, $v_ref, $blks);
	}
	for ($i = count($_SESSION['cat_arr']['0']); $i < $_SESSION['portion']; $i++) echo "<tr><td align='right' class='dis_text'>".(string)($i + 1)."</td></tr>";
}

?>
