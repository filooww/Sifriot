<?php

function SetCodingList($dbh, $param = false)
{
    $coding_arr = [];
    $res = mysqli_query($dbh, 'SELECT * FROM coding_table');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            if ($param) {
                $coding_arr[$row[0]] = [$row[1], $row[1], '', false];
            } else {
                $coding_arr[$row[0]] = $row[1];
            }
        }
        mysqli_free_result($res);
    }
    ksort($coding_arr);
    if ($param) {
        $coding_arr['new'] = ['', '', '', false];
    }

    return $coding_arr;
}
function CorrectCodingList($dbh)
{
    $fl_change = ['ascii' => false];
    UpdateSystemCoding($fl_change['ascii']);
    if ($fl_change['ascii']) {
        ksort($_SESSION['coding_list']);
        mysqli_query($dbh, 'DELETE FROM coding_table');
        mysqli_query($dbh, 'ALTER TABLE coding_table AUTO_INCREMENT = 1');
        $ins_arr = [];
        foreach ($_SESSION['coding_list'] as $k => $v) {
            $ins_arr[] = '('.(string) $k.",'".VValue($v)."')";
        }
        mysqli_query($dbh, 'INSERT INTO coding_table VALUES '.implode(',', $ins_arr));
    }
}
function UpdateSystemCoding(&$fl_change)
{
    if (! isset($_SESSION['coding_list'][1])) {
        $k = array_search('ASCII', $_SESSION['coding_list']);
        if ($k !== false) {
            AddInvalidCoding($k);
        }
        AddSystemCoding();
        $fl_change = true;
    } elseif ($_SESSION['coding_list'][1] != 'ASCII') {
        AddInvalidCoding(1);
        $k = array_search('ASCII', $_SESSION['coding_list']);
        if ($k !== false) {
            AddInvalidCoding($k);
        }
        AddSystemCoding();
        $fl_change = true;
    }
}
function AddInvalidCoding($k)
{
    $new_id = NewTableID($_SESSION['coding_list'], 1);
    $_SESSION['coding_list'][$new_id] = 'ASCII~';
    ksort($_SESSION['coding_list']);
    $_SESSION['pre_coding_err'][$new_id] = ['*-330', '<b>ASCII</b>', '*697', '*-147', '<b>'.(string) $k.'</b>, ', '*440', '*468', '*82', '*-147', '*624', '<b>'.$new_id.'</b>', '*82', '<b>ASCII~</b>', '*534'];
}
function AddSystemCoding()
{
    $_SESSION['coding_list'][1] = 'ASCII';
    $_SESSION['pre_coding_err'][1] = ['*536'];
}
function ChangeInvalidConfig($k)
{
    $_SESSION['coding_list'][$k] = 'ASCII'.'~';
    $_SESSION['pre_coding_err'][$k] = ['*-330', '<b>'.'ASCII'.'</b>', '*623', '<b>'.$_SESSION['coding_list'][$k].'</b>'];
}
