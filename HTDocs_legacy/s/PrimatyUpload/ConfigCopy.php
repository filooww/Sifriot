<?php

require_once 'Utils.php';

$dbh = GetOnlyDB('db_manager');
if (! $dbh) {
    echo 'No connection to DB';
}

$res_ord = mysqli_query($dbh, 'SELECT * FROM ord_table WHERE id_lang <> 1');
if ($res_ord) {
    while ($row = mysqli_fetch_row($res_ord)) {
        $res_code = mysqli_query($dbh, "SELECT * FROM translate_table WHERE letter = '".$row[1]."'");
        if ($row_code = mysqli_fetch_row($res_code)) {
            mysqli_query($dbh, 'UPDATE translate_table SET ascii_code = '.(string) $row[3].', html_code = '.(string) $row[4].', letter = '.(string) $row[2]." WHERE letter ='".$row[1]."'");
            mysqli_free_result($res_code);
        } else {
            mysqli_query($dbh, 'INSERT INTO translate_table VALUES ('.(string) $row[0].','.(string) $row[3].",'".$row[1]."',".(string) $row[4].',0,0,0,'.(string) $row[2].')');
        }
    }
    mysqli_free_result($res_ord);
}
