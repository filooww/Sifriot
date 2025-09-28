<table width="100%">
	<tr>
		<td width="20%"></td>
		<td width="60%"></td>
		<td width="20%" align="right"><button name="register_OK" type="submit" value="*" class="reg_trial_button"><?php echo Title(13);?></button></td>
	</tr>
	<tr>
		<td width="20%"></td>
		<td width="60%"></td>
		<td width="20%" align="right"><button name="trial_OK" type="submit" value="*" class="reg_trial_button"><?php echo Title(14);?></button></td>
	</tr>
	<tr>
		<td width="20%"></td>
		<td width="60%"><?php if ($_SESSION['registration']) echo "<div align='center'><h2><b>".Title(13)." ".Title(5)."</b></h2></div>";?><td>
		<td width="20%"></td>
	</tr>
</table>

