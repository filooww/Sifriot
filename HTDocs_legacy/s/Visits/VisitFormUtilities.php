<?php

function ChangeVisitScreen($dbh, $offs, &$Mes)
{
    if (gettype($offs) == 'integer') {
        if ($offs == 1) {
            $_SESSION['portion']++;
            $_SESSION['visit_list'] = GetVisitPortion($dbh, $_SESSION['visit_list']);
        } else {
            if ($_SESSION['portion'] > count($_SESSION['visit_list'])) {
                $_SESSION['portion']--;
            } elseif ($_SESSION['portion'] == 1) {
                $Mes[] = "<font color='#FF0000'>".Title(513).'</font>';
            } elseif (end($_SESSION['visit_list'])[7] > 0) {
                $Mes[] = "<font color='#FF0000'>".Title(513).'</font>';
            } else {
                if (count($_SESSION['visit_list']) == $_SESSION['portion']) {
                    array_pop($_SESSION['visit_list']);
                }
                $_SESSION['portion']--;
            }
        }
    } else {
        if (! is_numeric($offs)) {
            $Mes[] = "<font color='#FF0000'>".Title(177).' '.Title(77).' ('.Title(299).' <b>'.$offs.'</b>)</font>';
        } elseif ((int) $offs > $_SESSION['portion']) {
            $old = $_SESSION['portion'];
            $_SESSION['portion'] = (int) $offs;
            if (count($_SESSION['visit_list']) >= $old) {
                $_SESSION['visit_list'] = GetVisitPortion($dbh, $_SESSION['visit_list']);
            }
        } elseif ((int) $offs < 1 || strpos($offs, '-') !== false || strpos($offs, '.') != false) {
            $Mes[] = "<font color='#FF0000'>".Title(177).' '.Title(512).' ('.Title(299).' <b>'.$offs.'</b>)</font>';
        } elseif ((int) $offs > $_SESSION['portion']) {
            $_SESSION['portion'] = (int) $offs;
            $_SESSION['visit_list'] = GetVisitPortion($dbh, $_SESSION['visit_list']);
        } elseif ((int) $offs < count($_SESSION['visit_list'])) {
            SetVisitPortionSize((int) $offs, $Mes);
        } else {
            $_SESSION['portion'] = (int) $offs;
        }
    }
}
function SetVisitPortionSize($int_offs, &$Mes)
{
    if (count($_SESSION['visit_list']) < $_SESSION['portion']) {
        $_SESSION['portion'] = count($_SESSION['visit_list']);
    }
    $v = end($_SESSION['visit_list']);
    while (count($_SESSION['visit_list']) > $int_offs && TestCondition($v)) {
        array_pop($_SESSION['visit_list']);
        $_SESSION['portion']--;
        $v = end($_SESSION['visit_list']);
    }
    if ($_SESSION['portion'] > $int_offs) {
        $Mes[] = "<font color='#FF0000'>".Title(513).'</font>';
    }
}
function IsMarkedSessions($mark_type)
{
    foreach ($_SESSION['visit_list'] as $k => $v) {
        if ($v[7] == $mark_type) {
            return true;
        }
    }

    return false;
}
function StartCancelSessions($dbh, &$Mes)
{
    $suspend_arr = [];
    foreach ($_SESSION['visit_list'] as $k => $v) {
        if ($v[7] == 1) {
            $arr_key = explode('|', $k);
            $suspend_arr[] = 'id_db = '.$arr_key[0].' AND id_user = '.$arr_key[1];
        }
    }
    if (count($suspend_arr) > 0) {
        $_SESSION['t_start'] = (string) time().'000';
        $_SESSION['end_time'] = bcadd($_SESSION['t_start'], (string) ($_SESSION['close_delay'] * 1000), 0);
        $_SESSION['cancel_started'] = true;
        mysqli_query($dbh, 'UPDATE visits SET rest_time = '.(string) $_SESSION['close_delay'].' WHERE '.implode(' OR ', $suspend_arr));
        $_SESSION['t_rest'] = (string) $_SESSION['close_delay'];
    }
}
function DeleteSessions($dbh, &$Mes)
{
    $fl = false;
    $deleted_set = [];
    foreach ($_SESSION['visit_list'] as $k => $v) {
        if ($v[7] == 2) {
            $arr_key = explode('|', $k);
            $deleted_set[] = 'id_db = '.$arr_key[0].' AND id_user = '.$arr_key[1];
            if (isset($_SESSION['pre_ref']['visits']['p']['db']['v'][(string) $arr_key[0]])) {
                ChangeInvalidRefTable($dbh, 'visits', 'db', (string) $arr_key[0], (string) $arr_key[1]);
            }
            if (isset($_SESSION['pre_ref']['visits']['p']['ui']['v'][(string) $arr_key[1]])) {
                ChangeInvalidRefTable($dbh, 'visits', 'ui', (string) $arr_key[1], (string) $arr_key[0]);
            }
        }
    }
    if (count($deleted_set) > 0) {
        mysqli_query($dbh, 'DELETE FROM visits WHERE '.implode(' OR ', $deleted_set));
        $_SESSION['visit_total_size'] -= count($deleted_set);
        $_SESSION['visit_size'] -= count($deleted_set);
        $fl = true;
    }
    if ($_SESSION['pre_ref']['visits']['over']) {
        unset($_SESSION['pre_ref']['visits']);
        SetReferenceTable($dbh, 'visits', 167, QueryReferencesVisits(), [['db', 0, 1, 545, 544], ['ui', 1, 0, 544, 545]], $_SESSION['pre_ref']);
        ksort($_SESSION['pre_ref']);
        SetInitReplaceIDs('visits', ['db' => 'visit_db_list', 'ui' => '']);
    }

    return $fl;
}
function DeleteSessionFromUserList($dbh, $del_arr, $id_user)
{
    mysqli_query($dbh, 'DELETE FROM visits WHERE id_user = '.(string) $id_user);
    if ($_SESSION['pre_ref']['visits']['over']) {
        unset($_SESSION['pre_ref']['visits']);
        SetReferenceTable($dbh, 'visits', 167, QueryReferencesVisits(), [['db', 0, 1, 545, 544], ['ui', 1, 0, 544, 545]], $_SESSION['pre_ref']);
        ksort($_SESSION['pre_ref']);
    } else {
        foreach ($del_arr as $db) {
            if (isset($_SESSION['pre_ref']['visits']['p']['db']['v'][(string) $db])) {
                ChangeInvalidRefTable($dbh, 'visits', 'db', (string) $db, (string) $id_user);
            }
            if (isset($_SESSION['pre_ref']['visits']['p']['ui']['v'][(string) $id_user])) {
                ChangeInvalidRefTable($dbh, 'visits', 'ui', (string) $id_user, (string) $db);
            }
        }
    }
}
function InsertString($arr_t)
{
    return '('.(string) $arr_t[1].','.(string) $arr_t[3].",'".(string) $arr_t[4]."',".$arr_t[5].','.(string) $arr_t[6].",'',0,0)";
}
function MarkVisit($dbh, $db_id, $user_id)
{
    $k = $db_id.'|'.$user_id;
    $fl = true;
    switch ($_SESSION['visit_list'][$k][7]) {
        case 0: $_SESSION['visit_list'][$k][7] = (strpos($_SESSION['visit_list'][$k][9]['str'], "<font color='#0000FF'>".Title(516).'</font>') === false) ? 2 : 1;
            break;
        case 1: $_SESSION['visit_list'][$k][7] = 2;
            break;
        case 2: $_SESSION['visit_list'][$k][7] = 0;
            break;
        default: $fl = false;
    }
    if ($fl && $_SESSION['cancel_started']) {
        if ($_SESSION['visit_list'][$k][7] == 1 && ! ($_SESSION['visit_list'][$k][9]['err'] || ! $_SESSION['visit_list'][$k][9]['err'] && $_SESSION['visit_list'][$k][6] == -1)) {
            CalcRestTime();
            mysqli_query($dbh, 'UPDATE visits SET rest_time = '.$_SESSION['t_rest'].' WHERE id_db = '.$db_id.' AND id_user = '.$user_id);
        } else {
            mysqli_query($dbh, 'UPDATE visits SET rest_time = 0 WHERE id_db = '.$db_id.' AND id_user = '.$user_id);
        }
    }
}
function ExpiredView($dis_suspend)
{
    for ($i = 1; $i < 4; $i++) {
        switch ($_SESSION['date_format'][$i]) {
            case 'd': CalendSelect('expired_day', $_SESSION['expired_date'][0], 1, CalendMaxDay($_SESSION['expired_date'][1], $_SESSION['expired_date'][2]), 'exp_d_s', 'class_day', 'exp_d_on', $dis_suspend, FTM(Title(29)));
                break;
            case 'm': CalendSelect('expired_month', $_SESSION['expired_date'][1], 1, 12, 'exp_m_s', 'class_month', 'exp_m_on', $dis_suspend, FTM(Title(28)));
                break;
            case 'y': CalendSelect('expired_year', $_SESSION['expired_date'][2], (int) $_SESSION['start_year'], (int) $_SESSION['current_year'], 'exp_y_s', 'class_year', 'exp_y_on', $dis_suspend, FTM(Title(27)));
                break;
        }
    }
}
function AfterVisitLangChoice($dbh, &$sw_break)
{
    $fl = false;
    if (AfterLangChoice($dbh, 'user_lang_s', 'user_lang', $sw_break)) {
        foreach ($_SESSION['visit_list'] as $k => $v) {
            $visit_errors = SetVisitErrors($v[0], $v[2], $v[4], $v[5], $v[6], $v[8], $k);
            if ($v[9]['str'] != '') {
                $_SESSION['visit_list'][$k][9]['str'] = $visit_errors['str'];
            }
        }
        ChangeUserListCategory();
        $fl = true;
    }

    return $fl;
}
function VisitExit($dbh, &$Mes)
{
    if (is_numeric($_POST['user_height']) && (int) $_POST['user_height'] > 0) {
        SaveUserScreenPortion($dbh, $_POST['user_height']);
    }
    if ($_SESSION['alarm']) {
        SimpleSysTableCheck($dbh, 'visits', 'visit_count < 1 OR working_mode < -1 OR working_mode > 1 OR work_start IS NULL AND working_mode >= 0 OR work_start IS NOT NULL AND working_mode = -1');
        if (isset($_SESSION['replace_mes'])) {
            unset($_SESSION['replace_mes']);
        }

        return '../Alarm/CommonAlarmForm';
    }
    if ($_SESSION['user_working_mode'] == 1) {
        $err_flags = TestVisitList();
        if ($err_flags['del']) {
            $Mes[] = "<font color='#FF0000'><b>".FTM(Title(555)).'</b></font>';
        }
        if ($err_flags['err']) {
            $Mes[] = "<font color='#FF0000'><b>".FTM(Title(223)).'</b></font>';
        }
        if ($err_flags['del'] || $err_flags['err']) {
            return '';
        }
    }

    return '../Administrator/MainForm';
}
function VisitSavePost()
{
    if (isset($_POST['visit_db']) && isset($_POST['visit_db_id'])) {
        $_SESSION['filter_db'] = [$_POST['visit_db_id'], $_POST['visit_db']];
    }
    if (isset($_POST['visit_user'])) {
        $_SESSION['filter_user'] = $_POST['visit_user'];
    }
    if (isset($_POST['visit_user_id'])) {
        $_SESSION['filter_user_id'] = $_POST['visit_user_id'];
    }
    if (isset($_POST['visit_category'])) {
        $_SESSION['category'] = SavePostSelect($_SESSION['categories'], $_POST['visit_category'], [169, 135, 136, 137, 232]);
    }
    if (isset($_POST['expired_day']) && isset($_POST['expired_month']) && isset($_POST['expired_year'])) {
        $_SESSION['expired_date'] = [$_POST['expired_day'], $_POST['expired_month'], $_POST['expired_year']];
    }
    if (isset($_POST['session_type_radio'])) {
        $_SESSION['ses_type'] = (int) $_POST['session_type_radio'];
    }
}
function ResetSessionParameters($dbh, $id_db, $id_user)
{
    $k = $id_db.'|'.$id_user;
    $set_update = [];
    $fl_start = SetResetSession($k, 4, null, 'work_start', $set_update);
    $fl_work = SetResetSession($k, 6, -1, 'working_mode', $set_update);
    if (count($set_update) > 0) {
        mysqli_query($dbh, 'UPDATE visits SET '.implode(', ', $set_update).' WHERE id_db = '.$id_db.' AND id_user = '.$id_user);
        if ($fl_work) {
            $_SESSION['admin_mes'] = [0, ''];
        }
        $_SESSION['visit_list'][$k][9] = SetVisitErrors($_SESSION['visit_list'][$k][0], $_SESSION['visit_list'][$k][2], $_SESSION['visit_list'][$k][4], $_SESSION['visit_list'][$k][5], $_SESSION['visit_list'][$k][6], $_SESSION['visit_list'][$k][8], $k);
    }
}
function CompareSessionParameters($v)
{
    if (! is_null($v[4])) {
        return false;
    }
    if ($v[6] != -1) {
        return false;
    }

    return true;
}
function SetResetSession($k, $param_n, $param_v, $param_f, &$set_update)
{
    if ($_SESSION['visit_list'][$k][$param_n] != $param_v) {
        $_SESSION['visit_list'][$k][$param_n] = $param_v;
        $ap = (gettype($param_v) == 'string') ? chr(39) : '';
        $set_update[] = $param_f.' = '.$ap.$param_v.$ap;

        return true;
    } else {
        return false;
    }
}
function TimeCancelSessions($dbh) // inner
{
    $fl = false;
    $all_sessions = [];
    $sessions_type = [];
    CalcRestTime();
    foreach ($_SESSION['visit_list'] as $k => $v) {
        if ($v[7] == 1) {
            $arr_key = explode('|', $k);
            $all_sessions[] = 'id_db = '.$arr_key[0].' AND id_user = '.$arr_key[1];
            $sessions_type[] = $v[8];
        }
    }
    if (count($all_sessions) > 0) {
        if ($_SESSION['t_rest'] == '0') {
            $delete_sessions = [];
            $cancel_sessions = [];
            for ($i = 0; $i < count($all_sessions); $i++) {
                if ($sessions_type[$i] == 0) {
                    $delete_sessions[] = $all_sessions[$i];
                } else {
                    $cancel_sessions[] = $all_sessions[$i];
                }
            }
            if (count($cancel_sessions) > 0) {
                mysqli_query($dbh, 'UPDATE visits SET '.UpdateVisitSet(-1).' WHERE '.implode(' OR ', $cancel_sessions));
                foreach ($_SESSION['visit_list'] as $k => $v) {
                    if ($v[7] == 1) {
                        RestoreSession($k);
                    }
                }
            }
            if (count($delete_sessions) > 0) {
                mysqli_query($dbh, 'DELETE FROM visits WHERE '.implode(' OR ', $delete_sessions));
                $_SESSION['visit_total_size'] -= count($delete_sessions);
                $_SESSION['visit_size'] -= count($delete_sessions);
                $fl = true;
            }
            $_SESSION['cancel_started'] = false;
            $_SESSION['t_start'] = '';
        } else {
            mysqli_query($dbh, 'UPDATE visits SET rest_time = '.$_SESSION['t_rest'].' WHERE '.implode(' OR ', $all_sessions));
        }
    }

    return $fl;
}
function CalcRestTime()
{
    if (isset($_POST['t_curr']) && $_POST['t_curr'] != '') {
        $rest_time = bcsub($_SESSION['end_time'], $_POST['t_curr'], 0);
        if (strlen($rest_time) < 4) {
            $_SESSION['t_rest'] = '0';
        } else {
            $_SESSION['t_rest'] = substr($rest_time, 0, -3);
            if (substr($rest_time, -3) >= '500') {
                $_SESSION['t_rest'] = bcadd($_SESSION['t_rest'], 1, 0);
            }
            if ((int) $_SESSION['t_rest'] % (int) $_SESSION['reminder_interval'] != 0) {
                $_SESSION['t_rest'] = (string) (round((int) $_SESSION['t_rest'] / (int) $_SESSION['reminder_interval']) * (int) $_SESSION['reminder_interval']);
            }
        }
        if (bccomp($rest_time, 1000) < 0) {
            $_SESSION['t_rest'] = '0';
        }
    }
}
