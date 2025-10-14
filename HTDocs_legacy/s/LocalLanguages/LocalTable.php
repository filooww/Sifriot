<table>
    <tr>
		<?php if ($_SESSION['user_working_mode'] == 1) echo "<td width='1%' align='center'>".ImgV("Delete", 18, 16)."</td>"; ?>
		<td width="8%" align="center" class="table_header"><b><?php echo Title(277);?></b></td>
		<td width="6%" align="center" class="table_header"><b><?php echo Title(280);?> 0</b></td>
		<td width="6%" align="center" class="table_header"><b><?php echo Title(280);?> 1</b></td>
		<td width="6%" align="center" class="table_header"><b><?php echo Title(280);?> 2</b></td>
		<td width="20%" class="table_header"><b><?php echo Title(285);?></b></td>
		<?php
        if (gettype($_SESSION['sel_local_lang'][0]) == "string")
        {
            echo "<td width='8%' class='table_header'><b>".Title(669)." ".Title(670).":</b></td>";
            echo "<td width='8%' class='table_header'><b>".Title(669)." ".Title(670).":</b></td>";
        }
		else echo "<td width='8%' class='table_header'><b>".Title(669)." ".Title(670).":</b></td>";
		?>
		<td width="1%" class="table_header"></td>
		<td class="table_header"></td>
	</tr>
	<?php
    $even = true;
    foreach ($_SESSION['trans_codes'] as $k => $v)
    {
        if (isset($_SESSION['change_lang_letter']) && $k == $_SESSION['change_lang_letter'][0]) $cl_class = "cur_line";
        else $cl_class = ($even) ? "even_row" : "";
        ViewLocalLanguage((string)$k, $v, $cl_class);
        $even = !$even;
    }
    ?>
</table>



