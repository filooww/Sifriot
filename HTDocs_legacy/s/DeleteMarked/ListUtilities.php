<head>
	<style>.odd_row {background-color:#FFFFFF;}</style>
	<style>.even_row {background-color:#CCFFFF;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
</head>
<?php
function ViewDelQuestion($cur_k, $n, $count_v)
{
    echo '<tr>';
    echo '<td></td>';
    echo '<td></td>';
    for ($i = 0; $i < $n; $i++) {
        echo '<td></td>';
    }
    echo '<td></td>';
    echo "<td class='modal_form'>";
    echo '<b>Delete item with code <b>'.$cur_k.'</b> permanently?</b>';
    echo "<input size='10' name='yes_del' type='submit' value='Yes'>";
    echo "<input size='10' name='no_del' type='submit' value='No'>";
    echo '</td>';
    for ($i = 0; $i < $count_v - $n - 1; $i++) {
        echo '<td></td>';
    }
    echo '</tr>';
}

?>
