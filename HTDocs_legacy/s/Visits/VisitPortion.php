<?php

function GetVisitPortion($dbh, $old_visit_param)
{
    $visit_param = [];
    $res = mysqli_query($dbh, 'SELECT * FROM ('.VisitRequestText().') AS T'.$_SESSION['visit_filter'].' ORDER BY T.db_name, T.id_db, T.user_name, T.id_user LIMIT '.(string) $_SESSION['start'].','.(string) $_SESSION['portion']);
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $k = (string) $row[0].'|'.(string) $row[2];
            $visit_param[$k] = [$row[1], $row[0], $row[3], $row[2], $row[4], (string) $row[5], $row[6], (array_key_exists($k, $old_visit_param)) ? $old_visit_param[$k][7] : 0, $row[7], SetVisitErrors($row[1], $row[3], $row[4], $row[5], $row[6], $row[7], $k)];
        }
        mysqli_free_result($res);
    }

    return $visit_param;
}
function VisitRequestText()
{
    $q_text = 'SELECT ';
    $q_text .= 'id_db, ';           // 0
    $q_text .= 'CASE ';
    $q_text .= "WHEN (SELECT db_list.DB_name FROM db_list WHERE db_list.db_id = visits.id_db LIMIT 1) IS NULL THEN '' ";
    $q_text .= 'ELSE (SELECT db_list.DB_name FROM db_list WHERE db_list.db_id = visits.id_db LIMIT 1) ';
    $q_text .= 'END AS db_name, ';	// 1
    $q_text .= 'id_user, ';			// 2
    $q_text .= 'CASE ';
    $q_text .= "WHEN (SELECT user_ident.name FROM user_ident WHERE user_ident.id_user = visits.id_user LIMIT 1) IS NULL THEN '' ";
    $q_text .= 'ELSE (SELECT user_ident.name FROM user_ident WHERE user_ident.id_user = visits.id_user LIMIT 1) ';
    $q_text .= 'END AS user_name, '; // 3
    $q_text .= 'work_start, ';		// 4
    $q_text .= 'visit_count, ';		// 5
    $q_text .= 'working_mode, ';	// 6
    $q_text .= 'CASE ';
    $q_text .= "WHEN (SELECT user_ident.user_priority FROM user_ident WHERE user_ident.id_user = visits.id_user LIMIT 1) IS NULL THEN '' ";
    $q_text .= 'ELSE (SELECT user_ident.user_priority FROM user_ident WHERE user_ident.id_user = visits.id_user LIMIT 1) ';
    $q_text .= 'END AS user_p ';	// 7
    $q_text .= 'FROM visits';

    return $q_text;
}
function VisitSize($dbh)
{
    $visit_count = 0;
    $res = mysqli_query($dbh, 'SELECT COUNT(*) FROM ('.VisitRequestText().') AS T'.$_SESSION['visit_filter']);
    if ($res) {
        if ($row = mysqli_fetch_row($res)) {
            $visit_count = $row[0];
        }
        mysqli_free_result($res);
    }

    return $visit_count;
}
function VisitListNavigation($dbh, $act, &$Mes)
{
    if (TestVisitNavigation($act)) {
        $s_p = ListNewStartPosition($act, $_SESSION['visit_size']);
        if ($s_p != $_SESSION['start']) {
            $_SESSION['start'] = $s_p;
            $_SESSION['visit_list'] = GetVisitPortion($dbh, $_SESSION['visit_list']);
        }
    } else {
        if (isset($_SESSION['pre_ref']['visits'])) {
            $Mes[] = "<font color='#FF0000'>".Title(240).' '.Title(570).'</font>';
        } // !@#$%^&
        else {
            $Mes[] = "<font color='#FF0000'>".Title(240).' '.Title(571).'</font>';
        }
    }
}
