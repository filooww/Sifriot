<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<table>
	<tr valign="top">
		<?php 
		if ($_SESSION['user_working_mode'] == 1)
		{
			echo "<td width='1%' align='center'>".ImgV("Delete", 18, 16)."</td>";
			echo "<td width='1%' align='center'>".ImgV("Edit", 18, 16)."</td>";
		}
		?>
		<td width="1%"></td>
		<td width='12%'><b><?php echo Title(257);?></b></td>
		<td width='10%'><b><?php echo Title(261);?></b></td>
		<td width='30%'><b><?php echo Title(262);?></b></td>
		<td></td>
	</tr>
	<?php
	$table_arr_db = SortDBList();
	foreach ($table_arr_db as $k => $v)
	{
        $dis_f = ($_SESSION['server_table'] == "" && $_SESSION['user_working_mode'] == 1 && $k != 0) ? "" : " disabled";
        $cls = ($_SESSION['db_sel'] != "" && (string)$k == $_SESSION['db_sel']) ? " class='i_r'" : "";
	    echo "<tr valign='top'>";
		    if ($_SESSION['user_working_mode'] == 1)
		    {
                if ($k == 0) echo "<td></td><td></td>";
                else
                {
                    echo "<td><button name='db_mark_del|".(string)$k."' type='submit' title='".Title(159)."' class='button_class' value='*'>".SysImage((($v['del']) ? "CheckBorder" : "BlankBorder"), 16, 16)."</button></td>";
				    echo "<td><button name='db_select|".(string)$k."' class='w_b' type='submit' title='".Title(256)."' value='*'".(($v['del']) ? " disabled" : "").">...</button></td>";
				}
			}
			echo "<td align='right'".$cls."><b>".(string)$k."</b></td>";
			echo "<td".$cls."><input size='20' disabled type='text' class='data_bold' name='db_name|".(string)$k."' value='".$v['db_name']."'></td>";
			echo "<td><input size='13' type='text' name='db_coding|".(string)$k."' value='".$v['db_coding']."'".$dis_f."></td>";
			echo "<td><input size='50' type='text' name='db_comment|".(string)$k."' value='".$v['db_comment']."'".$dis_f."></td>";
			echo "<td>".implode("; ", $v['db_err'])."</td>";
        echo "</tr>";
	}
	?>
</table>
