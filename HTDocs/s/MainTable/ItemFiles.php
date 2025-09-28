<head>
	<style>.create_item {background-color:#00CC99;}</style>
	<style>.header_button {background-color:#CCCCCC; color:#CCCCCC;}</style>
	<style>.count_button {background-color:#FFFFFF; color:#FFFFFF;}</style>
	<style>.header_text {background-color:#CCCCCC;}</style>
	<style>.del_but {background-color:#FFFFFF}</style>
	<style>.ed_b {background-color:#33CC99;}</style>
	<style>.data_align {text-align:center;}</style>
</head>

<table frame="border" width="100%">
	<tr>
		<td>
			<table>
				<tr>
					<td><b><?php echo $_SESSION['conf']['file_list_title'];?> : </b></td>
					<td><button name="file_add" type="submit" title="add a new file" class="create_item" value="*" <?php echo SetExPar($_SESSION['block']['item_exit'] || $_SESSION['p_code'] == 0, "");?>>Add file</button></td>
					<td class="count_button">X</td>
					<td><b><?php echo (string)count($_SESSION['p_files']['e'])." file".((count($_SESSION['p_files']['e']) == 1) ? "" : "s");?></b></td>
				</tr>
			</table>
			<?php
			if ($_SESSION['p_code'] == 0) echo "<font color='#FF0000'><b>To add files You should save this item</b></font>";
			elseif (count($_SESSION['p_files']['e']) > 0)
			{
				echo "<table>";
					echo "<tr>";
						echo "<th width='3%' class='header_button'>X</th>";
						echo "<th width='3%' class='header_button'>X</th>";
						foreach ($_SESSION['main_params']['const'] as $k => $v)
						{
							if ($v['screen_order'] > 0 && strpos($v['using'], "update") !== false && strpos($v['using'], "files") !== false) echo "<th width='".$v['t_prc']."' class='header_text' align='center'><b>".$v['name']."</b></th>";
						}
					echo "</tr>";
                    $arr = array("");
                    for ($i = $_SESSION['conf']['start_year']; $i <= $_SESSION['cury']; $i++) $arr[] = $i;
					foreach ($_SESSION['p_files']['e'] as $k => $v)
					{
						echo "<tr valign='top'>";
							echo "<td widht='3%' align='center'><button name='delete_file-".$k."' type='submit' title='delete this file' class='del_but' value='*' ".SetExPar($_SESSION['block']['item_exit'], "").">".ImgV("Delete", 16, 16)."</button></td>";
							echo "<td width='3%' align='center'><button name='upload_file-".$k."' type='submit' title='upload file to this row' value='*' ".SetExPar($_SESSION['block']['item_exit'], "").">".ImgV("Upload", 16, 16)."</button></td>";
							if ($_SESSION['spec_fld'][2]['key'] == "") echo "<td></td>";
							else echo "<td width='".$v['t_prc']."' align='center'><b>".$k."</b></td>";
							foreach ($_SESSION['main_params']['const'] as $f_k => $f_v)
							{
								if ($f_v['type'] == "URL"] //~~url
								{
									if ($v['url_file'] == "") echo "<td width='".$f_v['t_prc']."'><font color='#FF0000'><< no destination >></font></td>";
									elseif ($_SESSION['block']['item_exit']) echo "<td width='".$f_v['t_prc']."'><font color='#FF0000'>".$f_v['ref']." (reference blocked)</font></td>";
									elseif (!ResExists($v['url_file'])) echo "<td width='".$v['t_prc']."'><font color='#FF0000'><< no destination >></font></td>";
									else echo "<td width='".$v['t_prc']."'><a name='file_name-".$k."' href='".RefToRes($v['url_file'], "../..".'/')."' target='_blank'>".$v['file_name']."</a></td>";
								}
							
							echo "<td width='5%' align='center'>";
							    SelectTag("file_issue_year-".$k, $arr, $v['file_issue_year'], "", false, "", "", $_SESSION['block']['item_exit']);
//								echo "<select name='file_issue_year-".$k."' ".SetExPar($_SESSION['block']['item_exit'], "").">"; // SelectTag
//									echo OptionTag($v['file_issue_year'], "");
//									for ($y = $_SESSION['conf']['start_year']; $y <= $_SESSION['cury']; $y++) echo OptionTag($v['file_issue_year'], (string)$y); unset($y);
//								echo "</select>";
							echo "</td>";
							echo "<td width='3%' align='center'><input name='file_volume-".$k."' type='text' class='data_align' size='4' value='".ApostropheToValue($v['file_volume'])."' ".SetExPar($_SESSION['block']['item_exit'], "")."/></td>";
							echo "<td width='3%' align='center'><input name='file_number-".$k."' type='text' class='data_align' size='4' value='".ApostropheToValue($v['file_number'])."' ".SetExPar($_SESSION['block']['item_exit'], "")."/></td>";
							echo "<td width='3%' align='center'><input name='file_page-".$k."' type='text' class='data_align' size='4' value='".ApostropheToValue($v['file_page'])."' ".SetExPar($_SESSION['block']['item_exit'], "")."/></td>";
							echo "<td><input name='file_description-".$k."' type='text' size='50' value='".ApostropheToValue($v['file_description'])."' ".SetExPar($_SESSION['block']['item_exit'], "")."/></td>";
						echo "</tr>";
					}
				echo "</table>";
			}
			?>
		</td>
	</tr>
</table>
