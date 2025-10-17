<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.db_l {font-size:200%; font-weight:700;}</style>
	<style>.act_b {color:#0033FF; font-size:150%; font-weight:700; background-color:#FFFFFF; border:none;}</style>
</head>
<table>
	<tr><td><font class="db_l"><?php echo Title(127); ?></font></td></tr>
	<?php
    foreach ($_SESSION['arr_db'] as $k => $v) {
        if ($k > 0) {
            echo "<tr><td><button name='db_list-".(string) $k."' type='submit' value='*' class='act_b' ".OnMouseOver('00FF00', '0033FF').'>'.$v['db_comment'].'</button></td></tr>';
        }
    }
	?>
</table>


