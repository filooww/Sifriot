<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.form_exit {background-color:#FFCC00;}</style>
	<style>.form_save {background-color:#CCFF00;}</style>
	<style>.data_title {font-weight:bold;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
	<style>.invisible_button {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin;}</style>
	<style>.data_numeric {text-align:right;}</style>
	<style>.data_actuality {text-align:center;}</style>
	<style>.cancel_button {background-color:#FFFFFF;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
</head>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Calendar.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Algorithms/ListUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LogProcessing/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/SelectFromCatalog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/DoubleCatalog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/CopyPasteBranch.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Update.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Test.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Test.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tree/TreeCatalogs.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Screen.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Filter.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Search.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/Navigation.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/FormUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Catalogs/CatalogButtons.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/LoadToServer/Utilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once 'ItemUtilities.php';
require_once 'ItemFilesUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/MainTable/SetSession.php';
require_once 'ItemFormUtilities.php';
require_once 'ListSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableUpdate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Tables/TableTest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/ConfigUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitUtilities.php';

session_start();
$Mes = [];
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (! $dbh) {
    ExitSession(Title(1).' <b>'.$_SESSION['db_info']['name'].'</b>`'.implode('`', $Mes).'|FF0000', $_SESSION['db_info']['id']);
}
$_SESSION['mes'] = ['0' => [], '1' => [], 'c' => [], 'm' => [], 'u' => []];
$l_selected = ['value' => '', 'code' => 0];
$smes = '';
$aA = ParseItemFormButtons($dbh);
if (count($aA) == 0) {
    $aA = ParseCatalogButtons($dbh);
}
$_SESSION['item_exit_question'] = 0;
$n = (isset($aA['cat_num'])) ? $aA['cat_num'] : '';
if (count($aA) > 0) {
    ToButtonOff($aA);
    if (ToCopyBranch($aA)) {
        $_SESSION['copy_paste'] = ['copy_id' => '', 'parent_value' => '', 'parent_value_text' => '', 'copy_value' => '', 'copy_text_value' => ''];
    }
    if ($_SESSION['cur_cat'] != '' && ToTest($aA)) {
        TestCatalogs($dbh);
    }
    ItemSavePost($_SESSION['PR']['const'], $_SESSION['item_row']['e'], $_SESSION['p_files']['e']);
    $sw_break = true;
    switch ($aA['act']) {
        case 'db_save': $sw_break = false;
            if (SaveItem($dbh, $_SESSION)) {
                $smes = 'Item saved';
            } break;
        case 'line_select': $sw_break = false;
            LineUpdateSelect($dbh, $aA['code'], $_SESSION['cur_cat'], $l_selected, $_SESSION['item_row']['e'], $_SESSION['mes']['m']);
            break;
        case 'delete_file': $sw_break = false;
            unset($_SESSION['p_files']['e'][$aA['file_number']]);
            break;
        case 'upload_file': $sw_break = false;
            if (FileUploading($_SESSION, $aA['file_number'], $_SESSION['mes']['m'])) {
                $_SESSION['file_upload_return'] = 'FileUpload.php';
                header('Location: FileSelection.php');
            } break;
        case 'db_form_exit': $sw_break = false;
            $_SESSION['item_exit_question'] = (ItemFormCompare($_SESSION['item_row'], $_SESSION['p_files'])) ? 1 : 2;
            break;
        case 'item_exit_save': $sw_break = false;
            if (ItemFormExit($dbh, $_SESSION, 'save')) {
                header('Location: List.php');
            } break;
        case 'item_exit_no_save': $sw_break = false;
            if (ItemFormExit($dbh, $_SESSION)) {
                header('Location: List.php');
            } break;
        case 'continue_edit': $sw_break = false;
            $_SESSION['block']['item_exit'] = false;
            break;
        default: $sw_break = false;
    }
    if (! $sw_break) {
        CatalogActions($dbh, $n, $aA, CatalogSelectedValueUpdate($aA), $_SESSION['cur_value'], $_SESSION['mes']);
    }
}
?>
<!-- <form action="ItemForm.php" method="post"> -->
<form method="post">
	<?php
    if ($_SESSION['item_exit_question'] == 1) {
        QuestionForm('modal_form', "You haven't made any changes to the data", ['item_exit_no_save', 'continue_edit'], ['Exit', 'Continue editing']);
        $_SESSION['block']['item_exit'] = true;
    } elseif ($_SESSION['item_exit_question'] == 2) {
        QuestionForm('modal_form', 'Save changes?', ['item_exit_save', 'item_exit_no_save', 'continue_edit'], ['Yes', 'No', 'Continue editing']);
        $_SESSION['block']['item_exit'] = true;
    }
echo '<table>';
echo '<tr>';
echo "<td><button name='db_form_exit' type='submit' title='exit from item form' class='form_exit' value='*' ".FormExPar($_SESSION['block']).'>'.ImgV('Close', 16, 16).'</button></td>';
echo "<td><button name='db_save' type='submit' title='save this item' class='form_save' value='*' ".FormExPar($_SESSION['block']).'>'.ImgV('Save', 16, 16).'</button></td>';
echo "<td><font color='#0000FF' style='font:bold'>".$smes.'</font></td>';
echo '</tr>';
echo '</table>';
echo "<hr noshade='noshade' />";
echo "<table width='100%'>";
echo "<tr><td><input autofocus type='text' name='Title' class='data_title' size='".(string) $_SESSION['conf']['w_01']."' value='".$_SESSION['item_row']['e']['Title']."'".FormExPar($_SESSION['block']).'></td></tr>';
echo '</table>';
echo "<hr noshade='noshade' />";
echo '<table>';
if ($_SESSION['cat_view']) {
    echo "<tr valign='top'>";
    echo '<td>';
    echo '<table';
    ItemHTMLForm($_SESSION['t_main'], $_SESSION['PR']['const'], $_SESSION['item_row']['e'], $_SESSION['block'], $_SESSION['conf']['start_year'], $_SESSION['cury']);
    echo '</table>';
    if (isset($aA['act'])) {
        ViewMessages(false, '', $_SESSION['number_warn'], $_SESSION['Catalog'], $_SESSION['del_row'], $_SESSION['block'], ToSave($aA['act']));
    }
    echo '</td>';
    echo '<td>';
    ViewCoupleCatalog();
    echo '</td>';
    echo '</tr>';
} else {
    ItemHTMLForm($_SESSION['t_main'], $_SESSION['PR']['const'], $_SESSION['item_row']['e'], $_SESSION['block'], $_SESSION['conf']['start_year'], $_SESSION['cury']);
    echo '<tr><td>';
    require_once 'ItemFiles.php';
    echo '</td></tr>';
    echo '<tr><td>';
    if (isset($aA['act'])) {
        ViewMessages(false, '', $_SESSION['number_warn'], [], [], $_SESSION['block'], ToSave($aA['act']));
    } echo '</td></tr>';
}
echo '</table>';
?>
</form>

