<head>
	<style>.odd_row {background-color:#FFFFFF;}</style>
	<style>.even_row {background-color:#CCFFFF;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
</head>
<?php
function ViewDelMTPortion($item_arr, $p_con, $cur_k, $block, $item_del_question, $select_align)
{
	$cl = true;
	$bl = $block['item_del'];
	$n_max = MaxWidth("5"); // delmark
	foreach ($item_arr as $k => $v)
	{
		$bgcl = ($cl) ? "class='odd_row'" : "class='even_row'";
		echo "<tr valign='top' ".$bgcl.">";
			echo "<td><button name='del_item-".$k."' type='submit' title='delete this item permanently' class='mark_button' value='*' style='text-align:center' ".SetExPar($bl, "").">".ImgV("Delete", 18, 16)."</button></td>";
			echo "<td><button name='rest_item-".$k."' type='submit' title='cancel marked flag' value='*'>".ImgV("Reset", 18, 16)."</button></td>";
			foreach ($v as $k_v => $v_v)
			{
				if ($p_con[$k_v]['screen_order'] > 0)
				{
					$v_out = ($v_v[1]) ? "<font color='#FF0000'>Broken link</font>" : $v_v[0];
					echo "<td align='".$select_align[$p_con[$k_v]['f_align']]."'>".$v_out."</td>";
				}
			}
		echo "</tr>";
		$cl = !$cl;
		if ($k == (string)$cur_k && $item_del_question) ViewDelQuestion($cur_k, $n_max, count($v));
	}
	for ($i = count($item_arr); $i < $_SESSION['portion']; $i++)
	{
		echo "<tr>";
			echo "<td align='right' class='dis_text'>".(string)($i + 1)."</td>";
		echo "</tr>";
	}
}
function ViewDelQuestion($cur_k, $n, $count_v)
{
	echo "<tr>";
		echo "<td></td>";
		echo "<td></td>";
		for ($i = 0; $i < $n; $i++) echo "<td></td>";
		echo "<td></td>";
		echo "<td class='modal_form'>";
			echo "<b>Delete item with code <b>".$cur_k."</b> permanently?</b>";
			echo "<input size='10' name='yes_del' type='submit' value='Yes'>";
			echo "<input size='10' name='no_del' type='submit' value='No'>";
		echo "</td>";
		for ($i = 0; $i < $count_v - $n - 1; $i++) echo "<td></td>";
	echo "</tr>";
}
function ItemCreate($dbh, &$s)
{
	$_SESSION['mes'] = array("0"=>array(), "1"=>array(), "c"=>array(), "m"=>array(), "u"=>array());
	$_SESSION['old_p_code'] = $_SESSION['p_code'];
	$_SESSION['p_code'] = 0;
	$_SESSION['item_row'] = InitItem($dbh, $_SESSION['cury'], $_SESSION['item_arr'], 0);
	$_SESSION['p_files'] = InitFiles($dbh, $_SESSION['URL_p'], 0, $_SESSION['mes']['m']);
}
function EditCopyItem($dbh, $p_code, &$s)
{
	$_SESSION['mes'] = array("0"=>array(), "1"=>array(), "c"=>array(), "m"=>array(), "u"=>array());
	$_SESSION['old_p_code'] = $_SESSION['p_code'];
	$_SESSION['p_code'] = 0;
	$_SESSION['item_row'] = InitItem($dbh, $_SESSION['cury'], $_SESSION['item_arr'], $p_code);
	$_SESSION['item_row']['i']['UploadDate'] = GetCurrentDate();
	$_SESSION['item_row']['e']['UploadDate'] = GetCurrentDate();
	$_SESSION['p_files'] = InitFiles($dbh, $_SESSION['URL_p'], $p_code, $_SESSION['mes']['m']);
}

?>
