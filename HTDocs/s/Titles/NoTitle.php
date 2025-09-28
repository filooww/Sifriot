<?php
function NoTitleView()
{
    $arr_view = array();
    if (count($_SESSION['no_title']) > $_SESSION['number_warn'])
    {
        $arr_view = array_slice($_SESSION['no_title'], 0, $_SESSION['number_warn']);
        $arr_view[] = "...";
        return $arr_view;
    }
    return $_SESSION['no_title'];
}
function TestTitleByLanguage()
{
    $Mes = "";
    if (count($_SESSION['titles']) == 0) $Mes = "The system does not all interface texts";
    else
    {
        $no_title = array();
        foreach ($_SESSION['scripts_title_ids'] as $t_id) if (!in_array($t_id, $_SESSION['titles'])) $no_title[] = $t_id;
        foreach ($_SESSION['ex_title_ids'] as $t_id) if (!in_array($t_id, $_SESSION['titles'])) $no_title[] = $t_id;
        sort($no_title);
        $arr = array_slice($no_title, 0, $_SESSION['number_warn']);
        $Mes = "The system does not have interface text numbers ".implode(",", $no_title);
    }
    return $Mes;
}
?>
