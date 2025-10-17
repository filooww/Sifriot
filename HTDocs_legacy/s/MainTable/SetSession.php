<?php
function RewriteConfSession($dbh)
{
    InitConfig($dbh);
    $_SESSION['select_arrays'] = [GetSelectYears($_SESSION['conf']['start_year'], $_SESSION['cury'])];
    $_SESSION['main_params'] = ReadFieldDefinition($dbh);
    $_SESSION['catalog_list'] = FormCatalogList($_SESSION['PR']['const']);
    ClearCatalogs();
}
function InitConfig($dbh)
{
    if (! isset($_SESSION['conf']['list_title'])) {
        $_SESSION['conf']['list_title'] = GetConfigValue($dbh, 'db_configs', 'list_title', false, 'Main list title', 'Item list');
    }
    if (! isset($_SESSION['conf']['form_title'])) {
        $_SESSION['conf']['form_title'] = GetConfigValue($dbh, 'db_configs', 'form_title', false, 'Item form title', 'Item form');
    }
    if (! isset($_SESSION['conf']['file_list_title'])) {
        $_SESSION['conf']['file_list_title'] = GetConfigValue($dbh, 'db_configs', 'file_list_title', false, 'Item file list title', 'Item file list');
    }
    if (! isset($_SESSION['conf']['dest_dir'])) {
        $_SESSION['conf']['dest_dir'] = GetConfigValue($dbh, 'db_configs', 'dest_dir', false, 'Address and directory of file storage');
    }
    $_SESSION['URL_p'] = str_replace(chr(92), chr(47), $_SESSION['conf']['dest_dir']);
    if (! isset($_SESSION['conf']['portion_item'])) {
        $_SESSION['conf']['portion_item'] = (int) GetConfigValue($dbh, 'db_configs', 'portion_item', true, 'Number of records reading from the main list at one time', '100');
    }
    if (! isset($_SESSION['conf']['start_year'])) {
        $_SESSION['conf']['start_year'] = (int) GetConfigValue($dbh, 'db_configs', 'start_year', true, 'Initial year', '1900');
    }
    if (! isset($_SESSION['conf']['screen_saver'])) {
        $_SESSION['conf']['screen_saver'] = GetConfigValue($dbh, 'db_configs', 'screen_saver', false, 'Site screen saver');
    }
    if (! isset($_SESSION['conf']['screen_saver_height'])) {
        $_SESSION['conf']['screen_saver_height'] = (int) GetConfigValue($dbh, 'db_configs', 'screen_saver_height', true, 'Height of site screen saver');
    }
    if (! isset($_SESSION['conf']['w_01'])) {
        $_SESSION['conf']['w_01'] = GetConfigValue($dbh, 'db_configs', 'w_01', true, 'Size of fields TITLE of item', '200');
    }
    if (! isset($_SESSION['conf']['w_03'])) {
        $_SESSION['conf']['w_03'] = GetConfigValue($dbh, 'db_configs', 'w_03', false, 'Width of placeholder for parameter name inside table for upload settings', '9%');
    }
    if (! isset($_SESSION['conf']['w_04'])) {
        $_SESSION['conf']['w_04'] = GetConfigValue($dbh, 'db_configs', 'w_04', false, 'Widht of placeholder for button CALL CATALOG inside table for upload settings', '3%');
    }
    if (! isset($_SESSION['conf']['w_05'])) {
        $_SESSION['conf']['w_05'] = GetConfigValue($dbh, 'db_configs', 'w_05', false, 'Width of placeholder for catalog code', '5%');
    }
    if (! isset($_SESSION['conf']['w_06'])) {
        $_SESSION['conf']['w_06'] = GetConfigValue($dbh, 'db_configs', 'w_06', false, 'Width of catalog code', '60px');
    }
    if (! isset($_SESSION['conf']['w_07'])) {
        $_SESSION['conf']['w_07'] = GetConfigValue($dbh, 'db_configs', 'w_07', true, 'Size of selection criteria', '44');
    }
    if (! isset($_SESSION['conf']['w_08'])) {
        $_SESSION['conf']['w_08'] = GetConfigValue($dbh, 'db_configs', 'w_08', true, 'Size of catalog value', '44');
    }
    if (! isset($_SESSION['conf']['w_09'])) {
        $_SESSION['conf']['w_09'] = GetConfigValue($dbh, 'db_configs', 'w_09', false, 'Width of list settings pad', '64%');
    }
}
function InitSession($dbh, $par_mode)
{
    InitConfig($dbh);
    $_SESSION['cat_flag'] = false;
    $_SESSION['cury'] = TimeZoneYear();
    $_SESSION['calend_date'] = ['id_key' => 'UploadDate', 'id_filter' => '', 'value' => ''];
    $_SESSION['calend_mes'] = [];
    $_SESSION['m_col_v'] = false;
    $_SESSION['select_arrays'] = [GetSelectYears($_SESSION['conf']['start_year'], $_SESSION['cury'])];
    if ($par_mode == 'view') {
        $_SESSION['main_params'] = ReadFieldDefinition($dbh);
        $_SESSION['f_view'] = false;
        $_SESSION['user_name'] = '';
        $_SESSION['pri'] = 0;
        $_SESSION['cur_lang'] = [0, ''];
        $_SESSION['langs'] = SetLanguages($dbh, $_SESSION['cur_lang']);
        $_SESSION['el_titles'] = SetElementTitles($dbh, $_SESSION['cur_lang'][0]);
    } else {
        $_SESSION['main_params'] = ReadFieldDefinition($dbh);
        $_SESSION['del_row'] = ['0' => [], '1' => []];
        $_SESSION['item_row'] = ['e' => [], 'i' => []];
        $_SESSION['ext_call'] = '';
        $_SESSION['DB_load_point'] = '/LiterSite/DBUpdate/LoadToDB.php';
        $_SESSION['server_load_point'] = '/LoadToServer/FileSelection.php';
        $_SESSION['file_upload_return'] = '';
        $_SESSION['load_file_number'] = '';
        $_SESSION['file_update'] = false;
        $_SESSION['saving'] = false;
        $_SESSION['item_mark_del_question'] = false;
        $_SESSION['item_view_mode'] = 'all';
        $_SESSION['view_modes'] = ['all', 'active', 'inactive'];
    }
    $_SESSION['catalog_list'] = FormCatalogList($_SESSION['PR']['const']);
    ClearCatalogs();
    $_SESSION['p_count'] = ['total' => 0, 'active' => 0, 'inactive' => 0, 'filter' => 0];
    $_SESSION['p_code'] = 0;
    $_SESSION['old_p_code'] = 0;
    $_SESSION['block'] = ['cat' => false, 'cat_del' => false, 'cat_goto' => false, 'item_del' => false, 'item_exit' => false, 'pad' => false, 'pad_cat' => false];
    $_SESSION['cat_tree'] = false;
    $_SESSION['cur_cat'] = '';
    $_SESSION['cur_value'] = '';
    $_SESSION['p_start'] = 0;
    $_SESSION['p_files'] = ['e' => [], 'i' => []];
    $_SESSION['item_arr'] = [];
    $_SESSION['set_pad'] = false;
    $_SESSION['cat_view'] = false;
    $_SESSION['mult'] = false;
    $_SESSION['mes'] = ['0' => [], '1' => [], 'c' => [], 'm' => [], 'u' => []];
}
function ParseItemFormButtons($dbh)
{
    foreach ($_POST as $str_key => $str_v) {
        $sw_break = true;
        $arr_key = explode('-', $str_key);
        $s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
        switch ($s_k) {
            case 'db_form_exit': return ['act' => 'db_form_exit'];
            case 'db_save': return ['act' => 'db_save'];
            case 'db_delete': return ['act' => 'db_delete'];
            case 'file_add': return ['act' => 'upload_file', 'file_number' => ''];
            case 'delete_file': return ['act' => 'delete_file', 'file_number' => $arr_key[1]];
            case 'upload_file': return ['act' => 'upload_file', 'file_number' => $arr_key[1]];
            case 'continue_edit': return ['act' => 'continue_edit'];
            case 'item_exit_save': return ['act' => 'item_exit_save'];
            case 'item_exit_no_save': return ['act' => 'item_exit_no_save'];
            default: $sw_break = false;
        }
        if ($sw_break) {
            break;
        }
    }

    return [];
}
function LineUpdateSelect($dbh, $aA_code, $cur_cat, &$l_selected, &$item_row, &$Mes)
{
    $cc = count($Mes);
    if (LineSelect($dbh, $aA_code, $l_selected, $_SESSION['cur_value'], $Mes)) {
        $_SESSION['cat_view'] = false;
        $_SESSION['block']['cat'] = false;
    }
    if (count($Mes) == $cc) {
        $item_row[$cur_cat] = $l_selected['value'];
        $item_row[$cur_cat.'_code'] = $l_selected['code'];
    }
}
function CatalogSelectedValueUpdate($aA)
{
    if ($aA['act'] == 'cat') {
        return $_SESSION['item_row']['e'][$aA['s_n']];
    } else {
        return '';
    }
}
function FormCatalogList($p_con)
{
    $catalog_list = [];
    foreach ($p_con as $k => $v) {
        if ($v['table'] != '') {
            $catalog_list[$k] = $v['name'];
        }
    }

    return $catalog_list;
}
?>

