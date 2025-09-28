<head>
	<style>.mark_button {background-color:#FFFFFF; color:#FF0000}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
</head>
<td><button name="db_create" type="submit" title="create a new publication" class="new_publication" value="*" <?php echo SetExPar(SetBlock(), "").">".ImgV("Create", 16, 16)?></button></td>
<td><font class="invisible_button">X</font></td>
<td><?php if (!$_SESSION['block']['pad'] && !$_SESSION['block']['pad_cat']) echo TableAdjustment("list_minus", "list_plus", "w_b");?></td>
<td>
	<input name="list_height_v" size="6" type="text" title="<?php echo Title(15);?>" style="text-align:right" value="<?php echo (string)$_SESSION['conf']['portion_item'];?>">
	<input name="list_height_b" type="submit" title="<?php echo Title(32);?>" value="...">
</td>
<td><font class="invisible_button">X</font></td>
<td><input name="item_config" type="submit" title="call the configuration settings form" value="Configuration" <?php echo SetExPar(SetBlock(), "");?>></td>
<td><font class="invisible_button">X</font></td>
<td><input name="cat_system" type="submit" title="catalog system" value="Catalogs" <?php echo SetExPar(SetBlock(), "");?>></td>
<td><font class="invisible_button">X</font></td>
<td><button name="multi_col" type="submit" title="<?php echo (($_SESSION['m_col_v']) ? Title(73) : Title(74));?>"><?php echo ImgV("ShowList", 16, 16);?></button></td>
<td><font class="invisible_button">X</font></td>
<td>Publication view mode</font></td>
<td>
	<select name="view_mode"> <!-- // SelectTag -->
		<?php
        SelectTag("view_mode", $_SESSION['view_modes'], $_SESSION['item_view_mode']);
//        for ($i = 0; $i < count($_SESSION['view_modes']); $i++) echo OptionTag($_SESSION['item_view_mode'], $_SESSION['view_modes'][$i]); unset($i);
        ?>
	</select>
	<button name="sel_view" type="submit" value="*">...</button>
</td>
<td><font class="invisible_button">X</font></td>
<td><?php echo Title(33);?></td>
<td><button type="submit" name="match_case_find" title="switch this flag" style="width:30px;height:20px;" value="*" class="switch_bg"><?php echo CheckImg($_SESSION['match_case'], 16, 15);?></button></td>
<td><font class="invisible_button">X</font></td>
<td><?php echo Title(34);?></td>
<td><button type="submit" name="hide_list_flag" title="switch this flag" style="width:30px;height:20px;" value="*" class="switch_bg"><?php echo CheckImg($_SESSION'hide_list'], 16, 15);?></button></td>

