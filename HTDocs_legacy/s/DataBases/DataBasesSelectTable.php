<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<table>
	<tr valign="top">
		<td>
			<table>
				<?php
                $table_arr_db = SortDBList();
                foreach ($table_arr_db as $k => $v)
                {
                    $cls = ($_SESSION['db_sel'] != "" && (string)$k == $_SESSION['db_sel']) ? " class='i_r'" : "";
                    echo "<tr".$cls.">";
                        echo "<td align='right'>".(string)$k."</td>";
                        echo "<td><input size='20' disabled class='data_bold' type='text' name='db_name|".(string)$k."' value='".$v['db_name']."'></td>";
                    echo "</tr>";
                }
                ?>
			</table>
		</td>
		<td class="invisible_text">XXXXX</td>
		<td>
			<table bgcolor="#FFFFCC">
                <tr><td><button name="exit_server" type="submit" class="i_h" title="<?php echo Title(409)." ".Title(387)?>" value="*"><?php echo ImgV("Delete", 10, 16)?></button></td><td></td></tr>
				<?php
				for ($i = 0; $i < count($_SESSION['db_server']); $i++)
				{
					echo "<tr>";
						echo "<td><button name='server_db_select|".(integer)$i."' class='w_b' type='submit' title='".Title(256)."' value='*'>...</button></td>";
						echo "<td>".$_SESSION['db_server'][$i]."</td>";
					echo "</tr>";
				}
				?>
			</table>
		</td>
	</tr>
</table>

