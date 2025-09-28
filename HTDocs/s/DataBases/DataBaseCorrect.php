<?php
function SearchDBname($db_name, $arr_db)
{
    foreach ($arr_db as $k => $v) if ($v['db_name'] == $db_name) return $k;
    return false;
}
function CorrectDBList($dbh_sys)
{
    $fl_change = false;
    UpdateSystemDB($fl_change);
    if ($fl_change)
    {
	    mysqli_query($dbh_sys, "DELETE FROM db_list");
	    mysqli_query($dbh_sys, "ALTER TABLE db_list AUTO_INCREMENT = 1");
	    $ins_arr = array();
        foreach ($_SESSION['arr_db'] as $k => $v) $ins_arr[] = "(".(string)$k.",'".$v['db_name']."','".$v['db_coding']."','".$v['db_comment']."')";
        mysqli_query($dbh_sys, "INSERT INTO db_list VALUES ".implode(",", $ins_arr));
    }
}
function UpdateSystemDB(&$fl_change)
{
    if (!isset($_SESSION['arr_db'][0]))
    {
        $k = SearchDBname("db_manager", $_SESSION['arr_db']);
        if ($k !== false) ChangeInvalidDB($k);
        AddSystemDB();
        $fl_change = true;
    }
    elseif ($_SESSION['arr_db'][0]['db_name'] != "db_manager")
    {
        AddInvalidDB($_SESSION['arr_db'][0]['db_name']);
        $k = SearchDBname("db_manager", $_SESSION['arr_db']);
        if ($k !== false) ChangeInvalidDB($k);
        AddSystemDB();
        $fl_change = true;
    }
}
function AddInvalidDB($v)
{
    $new_id = NewTableID($_SESSION['arr_db'], 0);
    $_SESSION['arr_db'][$new_id] = array("db_name"=>$v."~", "db_coding"=>$_SESSION['arr_db'][0]['db_coding'], "db_comment"=>$_SESSION['arr_db'][0]['db_comment'], "del"=>false, "db_err"=>TestDatabase($v."~", $_SESSION['arr_db'][0]['db_coding'], (string)$new_id));
    ksort($_SESSION['arr_db']);
    $_SESSION['pre_db_err'][$new_id] = FTM(Title(163))." <b>".$v."</b> ".Title(201)." ".FTM(Title(147))." <b>0</b>, ".Title(439)." ".Title(468)." ".Title(82)." ".FTM(Title(147))." ".Title(624)." <b>".(string)$new_id."</b> ".Title(82)." <b>".$v."~</b> ".Title(534);
}
function AddSystemDB()
{
    $_SESSION['arr_db'][0] = array("db_name"=>"db_manager", "db_coding"=>"utf8", "db_comment"=>"system DB", "del"=>false, "db_err"=>array());
    ksort($_SESSION['arr_db']);
    $_SESSION['pre_db_err'][0] = Title(276);
}
function ChangeInvalidDB($k)
{
    $_SESSION['arr_db'][$k]['db_name'] = "db_manager~";
    ksort($_SESSION['arr_db']);
    $_SESSION['pre_db_err'][$k] = FTM(Title(163))." <b>db_manager</b> ".Title(469)." <b>".$_SESSION['arr_db'][$k]['db_name']."</b>";
}

?>
