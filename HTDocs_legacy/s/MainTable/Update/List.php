<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.header_button {background-color:#CCCCCC; color:#CCCCCC;}</style>
	<style>.header_text {background-color:#CCCCCC;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
	<style>
		.blink_class {animation: blinker-two 3s linear infinite;}
		@keyframes blinker-two {100% {opacity: 0;}}
	</style>
</head>
<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once 'ListForm.php';
require_once 'SetSession.php';
require_once 'ListUtilities.php';
require_once 'PubFilesUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/UserSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/UserList.php';
require_once 'ListSettings.php';
require_once 'CommonUtilities.php';
require_once 'MTRequests.php';

require_once 'ItemUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/FormPubSelect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Forms.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Navigation.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Screen.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Filter.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Search.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/TableUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/_DBM/ConfigUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/_DBM/VisitUtilities.php';
require_once 'PubFormUtilities.php';

session_start();
$Mes = [];
$dbh = GetUserDB($_SESSION['arr_db'][$_SESSION['db_key']], $Mes);
if (! $dbh) {
    ExitSession(Title(1).' <b>'.$_SESSION['S']['arr_db'][$_SESSION['db_key']]['db_name'].'</b>`'.implode('`', $Mes).'|FF0000', (int) $_SESSION['db_key']);
}
$_SESSION['mes'] = ['0' => [], '1' => [], 'c' => [], 'm' => [], 'u' => []];
$_SESSION['item_mark_del_question'] = false;
$pvm = $_SESSION['item_view_mode'];
$up = $_SESSION['URL_p'];
if ($_SESSION['ext_call'] == 'Config') {
    RewriteConfSession($dbh);
    $_SESSION['item_arr'] = GetMTPortion($dbh, $_SESSION['db_info']['t_main'], $_SESSION['p_start'], $_SESSION['conf']['portion_item'], $_SESSION['PR']);
    $_SESSION['p_count']['filter'] = GetMTLimit($dbh, $_SESSION['db_info']['t_main'], 'filter', $_SESSION['PR']);
}
foreach ($_POST as $str_key => $str_v) {
    $sw_break = true;
    $arr_key = explode('-', $str_key);
    $s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
    switch ($s_k) {
        case 'db_exit':			SetUpdateFlag($dbh, 0);
            ExitSession('', (int) $_SESSION['db_key']);
            break;
        case 'db_create':		ItemCreate($dbh);
            header('Location: PubForm.php');
            break;
        case 'list_minus'     :	ChangeMTScreenHeight($dbh, -1, $_SESSION['mes']['m'], (int) $_SESSION['user_id']);
            break;
        case 'list_plus'      :	ChangeMTScreenHeight($dbh, 1, $_SESSION['mes']['m'], (int) $_SESSION['user_id']);
            break;
        case 'list_height_b'  :	ChangeMTScreenHeight($dbh, $_POST['list_height_v'], $_SESSION['mes']['m'], (int) $_SESSION['user_id']);
            break;
        case 'match_case_find':	$_SESSION['item_arr'] = ChangeMatchCase($dbh);
            break;
        case 'hide_list_flag':	$_SESSION['hide_list'] = ! $_SESSION['hide_list'];
            break;
        case 'sort_find':		SortFind();
            break;
        case 'item_config':		$_SESSION['return_point'] = '/List.php';
            header('Location: ../../Configuration/PreForm.php');
            break;
        case 'cat_system':		$_SESSION['return_point'] = '/List.php';
            header('Location: ../../Catalogs/MainForm.php');
            break;
        case 'multi_col':		$_SESSION['m_col_v'] = ! $_SESSION['m_col_v'];
            break;
        case 'item_beg':		MTNavigation($dbh, 'beg', $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], 0, $pvm);
            break;
        case 'item_pg_up':		MTNavigation($dbh, 'pgup', $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], 0, $pvm);
            break;
        case 'item_ln_up':		MTNavigation($dbh, 'lnup', $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], 0, $pvm);
            break;
        case 'item_ln_dn':		MTNavigation($dbh, 'lndn', $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], 0, $pvm);
            break;
        case 'item_pg_dn':		MTNavigation($dbh, 'pgdn', $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], 0, $pvm);
            break;
        case 'item_end':		MTNavigation($dbh, 'end', $_SESSION['db_info']['t_main'], $_SESSION['conf']['portion_item'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], $_SESSION['p_code'], 0, $pvm);
            break;
        case 'mark_item':		$_SESSION['item_mark_del_question'] = true;
            $_SESSION['p_code'] = (int) $arr_key[1];
            break;
        case 'yes_mark':		PubMarkDelete($dbh, $_SESSION);
            break;
        case 'no_mark':			break;
        case 'sel_view':		SetViewMode($dbh, $_SESSION, $_POST['view_mode']);
            break;
        case 'edit_item':		EditCopyItem($dbh, (int) $arr_key[1], $_SESSION);
            header('Location: PubForm.php');
            break; // ???
        case 'copy_item':		EditCopyItem($dbh, (int) $arr_key[1], $_SESSION);
            header('Location: PubForm.php');
            break; // ???
        case 'this_item':		$_SESSION['p_code'] = (int) $arr_key[1];
            break;
        default:				$sw_break = false;
    }
    if ($sw_break) {
        break;
    } else {
        $sw_cat_break = ListSettingActions($dbh, $arr_key, $_SESSION, $_SESSION['mes']['m']);
        if ($sw_cat_break) {
            break;
        }
    }
}
$_SESSION['ext_call'] = '';
?>
<div align="center"><h2><b><?php echo $_SESSION['conf']['list_title']; ?></b></h2></div>
<hr align="left" size="2" noshade="noshade" color="#000000" >
<!-- <form action="List.php" method="post"> -->
<form method="post">
	<?php
    require_once 'MainMenu.php';
if ($_SESSION['set_pad']) {
    ListSettingsPad();
}
if ($_SESSION['conf']['hide_list']) {
    if (! $_SESSION['set_pad']) {
        require_once 'ItemPortion.php';
    }
    ViewMessages($_SESSION['mes'], false, '', $_SESSION['conf']['number_warning'], [], [], $_SESSION['block'], false);
} else {
    ViewMessages($_SESSION['mes'], false, '', $_SESSION['conf']['number_warning'], [], [], $_SESSION['block'], false);
    require_once 'ItemPortion.php';
}
?>
</form>
