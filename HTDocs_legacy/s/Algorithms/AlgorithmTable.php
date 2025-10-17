<?php $t_del = ((! $_SESSION['algorithm_delete'] && $_SESSION['edit_algorithm'] == '') ? '' : ' disabled'); ?>
<table width="100%">
	<tr valign="top">
		<td width="3%">
			<table>
				<tr><td><font color="#FFFFFF">X</font></td></tr>
				<tr><td><button name="algorithm_navigation|beg"<?php echo $t_del; ?> type="submit" class="i_h" title="<?php echo Title(35); ?>"><?php echo SysImage('LineFirst', 16, 16); ?></button></td></tr>
				<tr><td><button name="algorithm_navigation|pgup"<?php echo $t_del; ?> type="submit" class="i_h" title="<?php echo Title(36); ?>"><?php echo SysImage('PageUp', 16, 16); ?></button></td></tr>
				<tr><td><button name="algorithm_navigation|lnup"<?php echo $t_del; ?> type="submit" class="i_h" title="<?php echo Title(37); ?>"><?php echo SysImage('LineUp', 16, 16); ?></button></td></tr>
				<tr><td><button name="algorithm_navigation|lndn"<?php echo $t_del; ?> type="submit" class="i_h" title="<?php echo Title(38); ?>"><?php echo SysImage('LineDown', 16, 16); ?></button></td></tr>
				<tr><td><button name="algorithm_navigation|pgdn"<?php echo $t_del; ?> type="submit" class="i_h" title="<?php echo Title(39); ?>"><?php echo SysImage('PageDown', 16, 16); ?></button></td></tr>
				<tr><td><button name="algorithm_navigation|end"<?php echo $t_del; ?> type="submit" class="i_h" title="<?php echo Title(40); ?>"><?php echo SysImage('LineEnd', 16, 16); ?></button></td></tr>
			</table>
		</td>
		<td>
	        <?php
            if ($_SESSION['algorithm_info'] == '') {
                AlgorithmTableRows('', '', $t_del);
            } else {
                AlgorithmTableRows('', $_SESSION['algorithm_info'], $t_del);
                if ($_SESSION['path_parse']) {
                    require_once 'AlgorithmParse.php';
                }
                require_once 'AlgorithmInfo.php';
                AlgorithmTableRows($_SESSION['algorithm_info'], '', $t_del);
            }
?>
		</td>
	</tr>
</table>
