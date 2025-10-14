<head>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
</head>

<?php require_once($_SERVER['DOCUMENT_ROOT']."/s/_DBM/TitleUtilities.php");?>

<table>
	<tr>
		<td><b>Rows:</b></td>
		<td><font class="invisible_button">X</font></td>
		<td>total <b><?php echo (string)$_SESSION['p_count']['total'];?></b></td>
		<td><font class="invisible_button">X</font></td>
		<td>active <b><?php echo (string)$_SESSION['p_count']['active'];?></b></td>
		<td><font class="invisible_button">X</font></td>
		<td>inactive <b><?php echo (string)$_SESSION['p_count']['inactive'];?></b></td>
		<td><font class="invisible_button">X</font></td>
		<td>selected <b><?php echo (string)$_SESSION['p_count']['filter'];?></b></td>
	</tr>
</table>
<table frame="border" width="100%">
	<tr valign="top">
		<td>
			<table>
				<tr><td><button name="item_beg" type="submit" class="i_h" value="*" title="<?php echo Title(35);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("LineFirst", 16, 16);?></button></td></tr>
				<tr><td><button name="item_pg_up" type="submit" class="i_h" value="*" title="<?php echo Title(36);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("PageUp", 16, 16);?></button></td></tr>
				<tr><td><button name="item_ln_up" type="submit" class="i_h" value="*" title="<?php echo Title(37);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("LineUp", 16, 16);?></button></td></tr>
				<tr><td><button name="item_ln_dn" type="submit" class="i_h" value="*" title="<?php echo Title(38);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("LineDown", 16, 16);?></button></td></tr>
				<tr><td><button name="item_pg_dn" type="submit" class="i_h" value="*" title="<?php echo Title(39);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("PageDown", 16, 16);?></button></td></tr>
				<tr><td><button name="item_end" type="submit" class="i_h" value="*" title="<?php echo Title(40);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("LineEnd", 16, 16);?></button></td></tr>
			</table>
		</td>			
		<td>
			<table>			
				<tr valign="top">
					<th width="1%" class="header_button">X</th>
					<th width="1%" class="header_button">X</th>
					<th width="1%" class="header_button">X</th>
					<?php foreach ($_SESSION['main_params']['const'] as $k => $v) if ($v['screen_order'] > 0 && strpos($v['using'], "update") !== false) echo "<th width='".$v['t_prc']."' class='header_text' align='left'><b>".$v['name']."</b></th>";?>
				</tr>
				<?php ViewMTPortion($_SESSION[['db_info']['t_main'], array(), $_SESSION['item_mark_del_question']);?>
			</table>
		</td>
	</tr>
</table>

