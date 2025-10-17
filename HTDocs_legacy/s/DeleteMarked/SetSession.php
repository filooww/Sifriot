<?php
function InitConfig($dbh)
{
    $_SESSION['conf']['dest_dir'] = GetConfigValue($dbh, 'db_configs', 'dest_dir', false, 'Address and directory of file storage');
    $_SESSION['URL_p'] = str_replace(chr(92), chr(47), $_SESSION['conf']['dest_dir']);
    $_SESSION['conf']['portion_item'] = (int) GetConfigValue($dbh, 'db_configs', 'portion_item', true, 'Number of records reading from the publications at one time', '100');
    $_SESSION['m_case'] = (GetConfigValue($dbh, 'db_configs', 'match_case', false, 'Match case in FILTER, SEARCH, COMPARISSON', 'Y') == 'Y');
}
function InitSession($dbh)
{
    InitConfig($dbh);
    $_SESSION['PR'] = ReadFieldDefinition($dbh, [], [], 'delmark', $_SESSION['db_info']['t_main']);
    $_SESSION['m_col_v'] = false;
    $_SESSION['ext_call'] = '';
    $_SESSION['p_count'] = ['total' => 0, 'active' => 0, 'inactive' => 0, 'filter' => 0];
    $_SESSION['p_code'] = 0;
    $_SESSION['block'] = ['cat' => false, 'cat_del' => false, 'cat_goto' => false, 'item_del' => false, 'item_exit' => false, 'pad' => false, 'pad_cat' => false];
    $_SESSION['p_start'] = 0;
    $_SESSION['item_arr'] = [];
    $_SESSION['mess'] = [];
}

?>

