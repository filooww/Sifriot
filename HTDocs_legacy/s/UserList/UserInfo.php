<?php
$k = $_SESSION['user_info'];
$v = $_SESSION['user_list'][$k];
$t_user = ($_SESSION['user_flag'][$k][10]) ? " disabled" : "";
?>
<table><tr><td align="center"><font size="+2"><?php echo Title(54)." <b>".$v[0]." (".$k.")</b>";?></b></font></td></tr></table>
<table>
    <tr><td><font color="#FFFFFF">:</font></td><td></td><td></td></tr>
    <tr>
        <td><b><?php echo Title(181);?></b></td>
        <td><?php SelectTag("pref|li|3|4|".$k, $_SESSION['user_langs'], $v[3], "", true, "", "", $_SESSION['user_flag'][$k][10], true, UserDataColor($k, "data_error", "", 4), GetDataTitle($k, 4));?></td>
        <td><font color='#FF0000'><?php echo (($_SESSION['user_flag'][$k][4]) ? Title(614)." <b>".(string)$v[3]."</b>" : ""); ?></b></font></td>
    </tr>
    <tr><td><font color="#FFFFFF">:</font></td><td></td><td></td></tr>
    <tr>
		<td><b><?php echo Title(530);?></b></td>
        <?php
        echo "<td><input size='5' type='text' name='user_portion|".$k."' title='".GetDataTitle($k, 5)."' ".UserDataColor($k, "data_error_num", "data_num", 5)." value='".$v[4]."'".$t_user."></td>";
        ?>
        <td></td>
    </tr>
    <tr><td><font color="#FFFFFF">:</font></td><td></td><td></td></tr>
    <tr>
		<td><b><?php echo Title(163);?></b></td>
        <td><?php SelectTag("pref|db|5|6|".$k, $_SESSION['list_db'], $v[5], "", true, "", "", $_SESSION['user_flag'][$k][10], true, UserDataColor($k, "data_error", "", 6), GetDataTitle($k, 6));?></td>
        <td><font color='#FF0000'><?php echo (($_SESSION['user_flag'][$k][6]) ? Title(614)." <b>".$v[5]."</b>" : ""); ?></b></font></td>
    </tr>
    <tr><td><font color="#FFFFFF">:</font></td><td></td><td></td></tr>
    <tr>
		<td><b><?php echo Title(531);?></b></td>
        <?php echo "<td><input size='10' type='text' name='pref_date|".$k."' title='".GetDataTitle($k, 7)."' ".UserDataColor($k, "data_error_date", "data_date", 7)." value='".(string)$v[6]."'".$t_user."></td>";?>
        <td></td>
    </tr>
    <tr><td><font color="#FFFFFF">:</font></td><td></td><td></td></tr>
    <tr>
        <td><b><?php echo Title(49);?></b></td>
        <td><?php UserCheckBox($k, $v[7], "hide_list", 8, $t_user);?></td>
        <td></td>
    </tr>
    <tr><td><font color="#FFFFFF">:</font></td><td></td><td></td></tr>
    <tr>
        <td><b><?php echo Title(595);?></b></td>
        <td><?php UserCheckBox($k, $v[8], "match_case", 9, $t_user);?></td>
        <td></td>
    </tr>
</table>

