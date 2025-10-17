<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.exit_from_alg_list {background-color:#FFCC00;}</style>
	<style>.create_alg {background-color:#00CC99;}</style>
	<style>.header_button {background-color:#33FFFF; color:#33FFFF;}</style>
	<style>.header_text {background-color:#33FFFF;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
	<style>.invisible_button {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin}</style>
	<style>.select_to_proc {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>
	<style>.sel_text {font-weight:bold;}</style>
	<style>.show_button {color:#FF0000; background-color:#FFFFFF; font:bold;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
	<style>.odd_row {background-color:#FFFFFF;}</style>
	<style>.ever_row {background-color:#CCFFFF;}</style>
	<style>.delete_button {background-color:#FF0000;}</style>
	<style>.edit_button {background-color:#33CC99;}</style>
	<style>.copy_button {background-color:#999900;}</style>
	<style>.dis_text {color:#CCCCCC;}</style>
	<style>.select_button {background-color:#FFCC00;}</style>
	<style>.cancel_button {background-color:#FFFFFF;}</style>
	<style>.cell_invisible {background-color:#FFFFFF; color:#FFFFFF;}</style>
</head>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/DataBases.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/Common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Utilities/HTML.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Configuration/ConfigUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Visits/VisitUtilities.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/s/Titles/TitleSelect.php';
require_once 'ListUtilities.php';

session_start();
$dbh = GetOnlyDB('db_manager');
if (! $dbh) {
    ExitSession(Title(252).'|FF0000');
}
if (count($_POST) == 0) {
    $_SESSION['file_example'] = '';
    $_SESSION['path_example'] = [];

}

// $_SESSION['ext_call'] = "Algorithms";
if (isset($_POST['alg_exit'])) {
    header('Location: ../'.$_SESSION['return_point']);
}
$_SESSION['arr_alg'] = GetAlgPortion($dbh, $_SESSION['start'], $_SESSION['alg_filter']);
$_SESSION['alg_count'] = NumberOfAlgorithms($dbh);
$_SESSION['del_question'] = false;
$_SESSION['delete_answer'] = false;
$_SESSION['alg_copy'] = [];
$aA = ParseAlgButtons($dbh);
if (isset($aA['act'])) {
    switch ($aA['act']) {
        case 'alg_create': $_SESSION['alg_edit_code'] = '';
            header('Location: Form.php');
            break;
        case 'scr_h': ChangeAlgScreenHeight($dbh, $aA['scr_corr'], $_SESSION['arr_alg'], $_SESSION['alg_start_pos'], $_SESSION['alg_filter']);
            break;
        case 'alg_del': $_SESSION['del_question'] = true;
            $_SESSION['alg_edit_code'] = $aA['code'];
            break;
        case 'alg_ed': $_SESSION['alg_edit_code'] = $aA['code'];
            header('Location: Form.php');
            break;
        case 'alg_copy': CopyAlg($_SESSION['arr_alg'][$aA['code']], $_SESSION['alg_copy']);
            header('Location: Form.php');
            break;
        case 'alg_sel': $_SESSION['arr_alg_seq'][$aA['code']] = $_SESSION['arr_alg'][$aA['code']];
            break;
        case 'alg_can': if (isset($_SESSION['arr_alg_seq'][$aA['code']])) {
            unset($_SESSION['arr_alg_seq'][$aA['code']]);
        } break;
        case 'del_act': if ($aA['ans']) {
            DeleteAlg($dbh, $_SESSION['alg_edit_code'], $_SESSION['arr_alg'], $_SESSION['alg_start_pos'], $_SESSION['alg_count'], $_SESSION['arr_alg_seq'], $_SESSION['alg_filter']);
        } break;
        case 'selected_alg': header('Location: ../'.$_SESSION['return_point']);
            break;
    }
}
?>
<div align="center"><h2><b>Algorithm list</b></h2></div>
<!-- <form action="List.php" method="post"> -->
<form method="post">
	<table>
		<tr>
			<td>
				<button name="alg_exit" type="submit" title="exit from algorithm list" class="exit_from_alg_list" value="*">X</button>
				<button name="alg_create" type="submit" title="create new algorithm" class="create_alg" value="*">Create</button>
			</td>
			<td><font class="invisible_button">X</font></td>
			<?php echo '<td>'.TableAdjustment('alg_minus', 'alg_plus', 'w_b').'</td>'; ?>
			<td><font class="invisible_button">X</font></td>
			<td>Count of algorithms <b><?php echo (string) $_SESSION['alg_count']; ?></b></td>
			<td><font class="invisible_button">X</font></td>
			<td>References filter:</td>
			<td><input name="alg_filter" type="submit" value="Filter" class="show_button"><input name="alg_filter_cancel" type="submit" value="Cancel" class="show_button"></td>
		</tr>
	</table>
	<?php
        if (isset($_POST['alg_filter']) || isset($_POST['alg_filter_cancel'])) {
            $_SESSION['alg_start_pos'] = 0;
            $_SESSION['arr_alg'] = GetAlgPortion($dbh, $_SESSION['alg_start_pos'], $_SESSION['alg_filter']);
        }
?>
	<?php if ($_SESSION['del_question']) {
	    QuestionForm('modal_form', 'Do You sure You want to delete this algoritm?', ['yes_name', 'cancel_name'], ['Yes', 'No']);
	}?>
	<table frame="border" width="100%">
		<tr valign="top">
			<td width="5%">
				<table>
					<tr><td><button name="alg_beg" type="submit" title="move to start of list" style="width:50px">Beg</button></td></tr>
					<tr><td><button name="alg_pg_up" type="submit" title="move to previous page of list" style="width:50px">PgUp</button></td></tr>
					<tr><td><button name="alg_ln_up" type="submit" title="move to previous row of list" style="width:50px">LnUp</button></td></tr>
					<tr><td><button name="alg_ln_dn" type="submit" title="move to next row of list" style="width:50px">LnDn</button></td></tr>
					<tr><td><button name="alg_pg_dn" type="submit" title="move to next page of list" style="50px">PgDn</button></td></tr>
					<tr><td><button name="alg_end" type="submit" title="move to end of list" style="width:50px">End</button></td></tr>
				</table>
			</td>
			<td>
				<table>			
					<tr valign="top">
						<th width="4%" class="header_button">X</th>
						<th width="3%" class="header_button">X</th>
						<th width="3%" class="header_button">X</th>
						<th width="4%" class="header_button">X</th>
						<th width="4%" class="header_button">X</th>
						<th width="4%" class="header_text" align="center"><b>ID</b></th>
						<th width="8%" class="header_text" align="center"><b>Reference</b></th>
						<th width="8%" class="header_text" align="center"><b>Offset in path</b></th>
						<th width="9%" class="header_text" align="center"><b>Begin delimiter</b></th>
						<th width="8%" class="header_text" align="center"><b>Inner delimiter</b></th>
						<th width="8%" class="header_text" align="center"><b>End delimiter</b></th>
						<th width="9%" class="header_text" align="center"><b>Replacements</b></th>
						<th width="10%" class="header_text" align="center"><b>Regular expression</b></th>
						<th width="30%" class="header_text" align="center"><b>Remarks</b></th>
					</tr>
					<?php ViewAlgPortion($_SESSION['arr_alg']); ?>
				</table>
		</tr>
	</table>
	<table>
		<tr>
			<td><b>Selected algorithms:</b></td>
			<td><button name="selected_alg" type="submit" class="select_to_proc" title="select algorithms for processing" value="*">Select</button></td>
		</tr>
	</table>
	<?php echo '<br>'.GetAlgList($_SESSION['arr_alg_seq']); ?>
</form>
