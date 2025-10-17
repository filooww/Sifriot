<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/ConfigUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/SelectFromCatalog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/DoubleCatalog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/CopyPasteBranch.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Update.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Test.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tree/TreeCatalogs.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Screen.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Filter.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Search.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Algorithms/ListUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Algorithms/ProcessingUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Languages/LanguageUtilities.php';

function CodeButton($n, $code)
{
    if ($n == '0') {
        return ['action' => 'line_select', 'code' => (int) $code];
    } elseif (! is_numeric($code)) {
        return [];
    } // new??
    else {
        return ['action' => 'line_insert', 'code' => $code, 'inserted' => $_POST['cat_text|'.$n.'|'.$code]];
    }
}
function CatalogControls(&$sw_break, $arr_key)
{
    switch ($arr_key[0]) {
        case 'cat_exit': return ['action' => 'cat_exit'];
        case 'cat_rest': return ['action' => 'cat_restore'];
        case 'cat_save': return ['action' => 'cat_save'];
        case 'cat_test': return ['action' => 'cat_test'];
        case 'cat_height': return ['action' => 'cat_scr_height', 'direction' => (($arr_key[1] == 'minus') ? -1 : 1)];
        case 'answer_yes': return ['action' => 'answer_yes'];
        case 'answer_no': return ['action' => 'answer_no'];
        case 'topos': return ['action' => 'go_to_pos', 'pos' => $arr_key[1], 'cat_num' => $arr_key[2]];
        case 'cat_tree': return ['action' => 'cat_tree'];
        case 'cat_tree_collapse': return ['action' => 'cat_tree_col'];
        case 'cat_tree_extend': return ['action' => 'cat_tree_ext'];
        case 'cat_filter': return ['action' => 'cat_filter_call', 'cat_num' => $arr_key[1]];
        case 'cat_search': return ['action' => 'cat_search', 'cat_num' => $arr_key[1]];
        case 'cat_search_prev': return ['action' => 'cat_search_move', 'cat_num' => $arr_key[1], 'dir' => 'back'];
        case 'cat_search_next': return ['action' => 'cat_search_move', 'cat_num' => $arr_key[1], 'dir' => 'forward'];
        case 'cat_search_hide': return ['action' => 'cat_search_hide', 'cat_num' => $arr_key[1]];
        case 'cat_fill': return ['action' => 'cat_fill', 'table' => $arr_key[1]];
        case 'filter_start': return ['action' => 'apply_filter', 'cat_num' => $arr_key[1], 'text' => $_POST['filter_text0'], 'radio' => 'f_mode'];
        case 'search_start': return ['action' => 'start_search', 'cat_num' => $arr_key[1], 'text' => $_POST['search_text0'], 'radio' => 's_mode'];
        case 'cat_beg': return ['action' => 'cat_navigation', 'nv' => 'cat_beg', 'cat_num' => $arr_key[1]];
        case 'cat_pg_up': return ['action' => 'cat_navigation', 'nv' => 'cat_pg_up', 'cat_num' => $arr_key[1]];
        case 'cat_ln_up': return ['action' => 'cat_navigation', 'nv' => 'cat_ln_up', 'cat_num' => $arr_key[1]];
        case 'cat_ln_dn': return ['action' => 'cat_navigation', 'nv' => 'cat_ln_dn', 'cat_num' => $arr_key[1]];
        case 'cat_pg_dn': return ['action' => 'cat_navigation', 'nv' => 'cat_pg_dn', 'cat_num' => $arr_key[1]];
        case 'cat_end': return ['action' => 'cat_navigation', 'nv' => 'cat_end', 'cat_num' => $arr_key[1]];
        case 'cat_code': return CodeButton($arr_key[1], $arr_key[2]);
        case 'collapse': return ['action' => 'collapse', 'code' => $arr_key[2], 'set' => $arr_key[3], 'value' => $_POST['collapse0|'.$arr_key[2].'|'.$arr_key[3]]];
        case 'set_vis': return ['action' => 'set_vis', 'code' => $arr_key[2]];
        case 'cat_mark': return ['action' => 'line_mark', 'code' => $arr_key[2]];
        case 'cat_copy': return ['action' => 'copy_branch', 'act_id' => $arr_key[2], 'act_ind' => $arr_key[3]];
        case 'cat_paste': return ['action' => 'paste_branch', 'act_id' => $arr_key[2]];
        case 'cat_paste_root': return ['action' => 'paste_branch', 'act_id' => ''];
        case 'del_hier_set': return ['action' => 'del_hier_set', 'code' => $arr_key[2]];
        case 'del_from_set': return (! is_numeric($arr_key[2])) ? [] : ['action' => 'del_from_set', 'code' => $arr_key[2]]; // new??
        default: $sw_break = false;
    }
}

?>

