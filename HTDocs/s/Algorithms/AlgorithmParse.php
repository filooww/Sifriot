<table frame="border">
    <tr>
        <td>
            <table><tr><td><b><?php echo Title(253);?></b></td></tr></table>
            <table>
                <tr>
                    <td><button name="path_parse" type="submit" class="i_h" title="<?php echo Title(295);?>"><?php echo SysImage("LineDown", 16, 16);?></button></td>
                    <td><input size="150" name="file_example" type="text" title="<?php echo Title(287);?>" value="<?php echo $_SESSION['file_example'];?>" /></td>
	            </tr>
	            <?php
	            if ($_SESSION['perform_parse'])
	            {
	                for ($i = 0; $i < count($_SESSION['path_example']); $i++)
	                {
		                echo "<tr>";
			                echo "<td class='data_numeric'><b>".(string)$i."</b></td>";
			                echo "<td>".$_SESSION['path_example'][$i]."</td>";
			                echo "<td></td>";
                        echo "</tr>";
                    }
	            }
	            ?>
            </table>
        </td>
    </tr>
</table>

