<table>
	<tr>
		<?php
        if ($_SESSION['user_working_mode'] == 1)
        {
            echo "<td align='center'>".ImgV("Delete", 18, 16)."</td>";
            echo "<td align='center'>".ImgV("Edit", 18, 16)."</td>";
        }
        ?>
		<td align="center"><b><?php echo Title(147);?></b></td>
		<td></td>
		<td><b><?php echo Title(234);?></b></td>
	</tr>
	<?php
	if ($_SESSION['algorithm_info'] == "") AlgorithmTableRows("", "", $t_del);
	else
	{
	    AlgorithmTableRows("", $_SESSION['algorithm_info'], $t_del);
		require_once("AlgorithmInfo.php");
        AlgorithmTableRows($_SESSION['algorithm_info'], "", $t_del);
	}
	for ($i = count($_SESSION['arr_alg']); $i < $_SESSION['portion']; $i++) echo (($_SESSION['user_working_mode'] == 0) ? "" : "<tr><td></td><td>")."</td><td align='right' class='dis_text'>".(string)($i + 1)."</td></tr>";
	?>
</table>
