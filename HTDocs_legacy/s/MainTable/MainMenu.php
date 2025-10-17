<head>
	<style>.invisible_button {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin;}</style>
	<style>.list_form_exit {background-color:#FFCC00;}</style>
	<style>.new_publication {background-color:#33CC99;}</style>
</head>

<?php
function TitleSetPad()
{
    if ($_SESSION['set_pad']) {
        return FTM(Title(49));
    } else {
        return Title(41);
    }
}
?>
<table>
	<tr>
		<td><button name="db_exit" type="submit" title="<?php echo Title(8); ?>" class="list_form_exit" value="*" <?php echo SetExPar($_SESSION['block']['item_del'], '').'>'.ImgV('Close', 16, 16); ?></button></td>
		<td><font class="invisible_button">X</font></td>
		<td><input name="sort_find" type="submit" value="List settings" title="<?php echo TitleSetPad(); ?>" <?php echo SetExPar($_SESSION['block']['item_del'] || $_SESSION['block']['pad_cat'], ''); ?>></td> <!--  //titles #49 -->
		<td><font class="invisible_button">X</font></td>
		<?php if (! $_SESSION['block']['pad'] && ! $_SESSION['block']['pad_cat']) {
		    require_once 'SettingsButtons.php';
		}?>	
	</tr>
</table>


