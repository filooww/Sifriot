<?php
function ViewItem($dbh, $u_p, &$f_view, &$p_code, $item_id, &$p_files, $u_id, $p_con)
{
	if ($p_code == $item_id)
	{
		$f_view = !$f_view;
		$p_files = ($f_view) ? ReadItemFiles($dbh, "files", $item_id, $u_p, $p_con) : array();
	}
	else
	{
		$f_view = true;
		$p_files = ReadItemFiles($dbh, "files", $item_id, $u_p, $p_con);
		$p_code = $item_id;
	}
}
function ChangeColumnsView($dbh, &$m_col_v, $f_view, $p_code, $u_p, $u_id, &$p_files, $p_con)
{
	$m_col_v = !$m_col_v; 
	if ($f_view) $p_files = ReadItemFiles($dbh, "files", $p_code, $u_p, $p_con);
}
function SetLanguage($dbh, $post_lang, $langs, $u_id, &$cur_lang, &$el_titles)
{
	if ($cur_lang[1] != $post_lang)
	{
		$cur_key = array_search($post_lang, $langs);
		if ($cur_key !== false)
		{
			$cur_lang = array((integer)$cur_key, $post_lang);
			$el_titles = SetElementTitles($dbh, $cur_key);
		}
	}
}
?>
