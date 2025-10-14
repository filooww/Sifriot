<head>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
</head>

<?php require_once($_SERVER['DOCUMENT_ROOT']."/s/_DBM/TitleUtilities.php");?>

<table>
	<tr>
		<td><b><?php echo Title(50);?>:</b></td>
		<td><font class="invisible_button">X</font></td>
		<td><?php echo Title(51);?> <b><?php echo (string)$_SESSION['p_count']['total'];?></b></td>
		<td><font class="invisible_button">X</font></td>
		<td><?php echo Title(52);?> <b><?php echo (string)$_SESSION['p_count']['filter'];?></b></td>
	</tr>
</table>
<table frame="border" width="100%">
	<tr valign="top">
		<td width="5%">
			<table>
				<tr><td><button name="item_beg" type="submit" class="i_h" title="<?php echo Title(35);?>"><?php echo ImgV("LineFirst", 16, 16);?></button></td></tr>
				<tr><td><button name="item_pg_up" type="submit" class="i_h" title="<?php echo Title(36);?>"><?php echo ImgV("PageUp", 17, 16);?></button></td></tr>
				<tr><td><button name="item_ln_up" type="submit" class="i_h" title="<?php echo Title(37);?>"><?php echo ImgV("LineUp", 16, 16);?></button></td></tr>
				<tr><td><button name="item_ln_dn" type="submit" class="i_h" title="<?php echo Title(38);?>"><?php echo ImgV("LineDown", 16, 16);?></button></td></tr>
				<tr><td><button name="item_pg_dn" type="submit" class="i_h" title="<?php echo Title(39);?>"><?php echo ImgV("PageDown", 16, 16);?></button></td></tr>
				<tr><td><button name="item_end" type="submit" class="i_h" title="<?php echo Title(40);?>"><?php echo ImgV("LineEnd", 16, 16);?></button></td></tr>
			</table>
		</td>
		<td>
			<table>			
				<tr valign="top">
					<?php 
					if ($_SESSION['pri'] > 0) echo "<th width='2%' class='header_button'>X</th>"; 
					foreach ($_SESSION['PR']['con'] as $k => $v) if ($v['screen_order'] > 0 && strpos($v['using'], "view") !== false) echo "<th width='".$v['t_prc']."' class='header_text' align='left'><b>".$v['name']."</b></th>";
					?>
				</tr>
				<?php ViewMTPortion($_SESSION['db_info']['t_main'], $_SESSION['p_files']['e'], false, "../..", $_SESSION['pri'], $_SESSION['f_view']);?>
			</table>
		</td>
	</tr>
</table>
