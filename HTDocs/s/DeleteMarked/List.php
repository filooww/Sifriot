<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.header_button {background-color:#CCCCCC; color:#CCCCCC;}</style>
	<style>.header_text {background-color:#CCCCCC;}</style>
	<style>.modal_form {background-color:#CCCCFF; border:solid;}</style>
	<style>.invisible_button {color:#FFFFFF; background-color:#FFFFFF; border-color:#FFFFFF; border:thin;}</style>
	<style>.list_form_exit {background-color:#FFCC00;}</style>
	<style>.delete_all {color: black; font-size: 100%; font-weight: 700; border: 1px solid rgb(250,172,17); border-radius: 7px; background: rgb(255,212,3) linear-gradient(rgb(255,212,3), rgb(248,157,23));}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.w_b {background-color:#FFFFFF; border-width:thin;}</style>
</head>
<?php

require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Calendar.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/SetSession.php");
require_once("ListUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/UserSettings.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/UserList.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/ListSettings.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/CommonUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/MTRequests.php");
require_once("ItemUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tree/TreeCatalogs.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/FormPubSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/FormUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Navigation.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Screen.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Filter.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Catalogs/Search.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Tables/TableUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleUtilsSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");

session_start();
$Mes = array();
if (count($_POST) == 0) $_SESSION['db_info'] = SelectUserDB($dbh_sys);
$dbh = GetDB($_SESSION['db_info']['db_name'], $_SESSION['db_info']['db_coding'], $Mes);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['db_name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['user_id'], $_SESSION['db_key']);
$_SESSION['mess'] = array();
$_SESSION['item_del_question'] = false;
$_SESSION['all_item_del_question'] = false;
$t_attach = $_SESSION[['db_info']['t_attach'];
foreach ($_POST as $str_key => $str_v)
{
	$sw_break = true;
	$arr_key = explode("-", $str_key);
	$s_k = (count($arr_key) == 1) ? $str_key : $arr_key[0];
	switch ($s_k)
	{
		case "db_exit":			SetUpdateFlag($dbh, 0); ExitSession("", $_SESSION['user_id'], $_SESSION['db_key']); break;
		case "list_minus":		ChangeMTScreenHeight($dbh, $_SESSION[['db_info']['t_main'], -1, $_SESSION['item_arr'], $_SESSION['p_start'], $_SESSION['PR'], $_SESSION['mess'], 0, "inactive"); break;
		case "list_plus":		ChangeMTScreenHeight($dbh, $_SESSION[['db_info']['t_main'], 1, $_SESSION['item_arr'], $_SESSION['p_start'], $_SESSION['PR'], $_SESSION['mess'], 0, "inactive"); break;
		case "list_height_b":	ChangeMTScreenHeight($dbh, $_SESSION[['db_info']['t_main'], $_POST['list_height_v'], $_SESSION['item_arr'], $_SESSION['p_start'], $_SESSION['PR'], $_SESSION['mess'], 0, "inactive"); break;
		case "del_all":			$_SESSION['all_item_del_question'] = true; break;
		case "yes_del_all":		DeleteAllMarked($dbh, $_SESSION[['db_info']['t_main'], $t_attach, $_SESSION[['PR']['con'], $_SESSION['URL_p'], $_SESSION['p_count'], $_SESSION['item_arr'], $_SESSION['p_code']); break;
		case "no_del_all":		break;
		case "item_beg":		MTNavigation($dbh, "beg", $_SESSION[['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], true, $_SESSION['p_code'], 0, "inactive"); break;
		case "item_pg_up":		MTNavigation($dbh, "pgup", $_SESSION[['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], true, $_SESSION['p_code'], 0, "inactive"); break;
		case "item_ln_up":		MTNavigation($dbh, "lnup", $_SESSION[['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], true, $_SESSION['p_code'], 0, "inactive"); break;
		case "item_ln_dn":		MTNavigation($dbh, "lndn", $_SESSION[['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], true, $_SESSION['p_code'], 0, "inactive"); break;
		case "item_pg_dn":		MTNavigation($dbh, "pgdn", $_SESSION[['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], true, $_SESSION['p_code'], 0, "inactive"); break;
		case "item_end":		MTNavigation($dbh, "end", $_SESSION[['db_info']['t_main'], $_SESSION['p_count'], $_SESSION['p_start'], $_SESSION['item_arr'], $_SESSION['PR'], true, $_SESSION['p_code'], 0, "inactive"); break;
		case "del_item":		$_SESSION['item_del_question'] = true; $_SESSION['p_code'] = (integer)$arr_key[1]; break;
		case "rest_item":		RestorePub($dbh, $_SESSION[['db_info']['t_main'], $arr_key[1], $_SESSION['p_code'], $_SESSION['p_start'], $_SESSION['PR'], $_SESSION['item_arr'], $_SESSION['p_count']); break;
		default:				$sw_break = false;
	}
	if ($sw_break) break;
}
$_SESSION['ext_call'] = "";
?>
<div align="center"><h2><b>List of items marked to deletion</b></h2></div><hr align="left" size="2" noshade="noshade" color="#000000" >
<form action="ListDelete.php" method="post">
	<?php if ($_SESSION['all_item_del_question'] == 1) {QuestionForm("modal_form", "Do You sure You want delete all marked publication permanently?", array("yes_del_all", "no_del_all"), array("Yes", "No")); $_SESSION['block']['item_exit'] = true;}?>
	<table>
		<tr>
			<td><button name="db_exit" type="submit" title="exit" class="list_form_exit" value="*"><?php echo ImgV("Close", 16, 16);?></button></td>
			<td><font class="invisible_button">X</font></td>
			<td><?php echo TableAdjustment("list_minus", "list_plus", "w_b", "invisible_button");?></td>
			<td>
				<input name="list_height_v" size="6" type="text" title="<?php echo Title(15);?>" style="text-align:right" value="<?php echo (string)$_SESSION['conf']['portion_item'];?>">
				<input name="list_height_b" type="submit" title="<?php echo Title(32);?>" value="...">
			</td>
			<?php echo "<td><font color='#FF0000'>".((count($_SESSION['mess']) == 0) ? "" : $_SESSION['mess'][0]['text'])."</font></td>";?>
			<td><font class="invisible_button">X</font></td>
			<td><input name="del_all" class="delete_all" type="submit" value="Delete all marked publication permanently"></td>
		</tr>
	</table>
	<table>
		<tr>
			<td><b>Rows:</b></td>
			<td><font class="invisible_button">X</font></td>
			<td>total <b><?php echo (string)$_SESSION['p_count']['total'];?></b></td>
			<td><font class="invisible_button">X</font></td>
			<td>active <b><?php echo (string)$_SESSION['p_count']['active'];?></b></td>
			<td><font class="invisible_button">X</font></td>
			<td>inactive <b><?php echo (string)$_SESSION['p_count']['inactive'];?></b></td>
		</tr>
	</table>
	<table frame="border" width="100%">
		<tr valign="top">
			<td>
				<table>
					<tr><td><button name="item_beg" type="submit" class="i_h" value="*" title="<?php echo Title(35);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("LineFirst", 16, 16);?></button></td></tr>
					<tr><td><button name="item_pg_up" type="submit" class="i_h" value="*" title="<?php echo Title(36);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("PageUp", 16, 16);?></button></td></tr>
					<tr><td><button name="item_ln_up" type="submit" class="i_h" value="*" title="<?php echo Title(37);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("LineUp", 16, 16);?></button></td></tr>
					<tr><td><button name="item_ln_dn" type="submit" class="i_h" value="*" title="<?php echo Title(38);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("LineDown", 16, 16);?></button></td></tr>
					<tr><td><button name="item_pg_dn" type="submit" class="i_h" value="*" title="<?php echo Title(39);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("PageDown", 16, 16);?></button></td></tr>
					<tr><td><button name="item_end" type="submit" class="i_h" value="*" title="<?php echo Title(40);?>" <?php echo BSize(32, 20)." ".SetExPar(SetBlock(), "").">".ImgV("LineEnd", 16, 16);?></button></td></tr>
				</table>
			</td>			
			<td>
				<table>			
					<tr valign="top">
						<th width="1%" class="header_button">X</th>
						<th width="1%" class="header_button">X</th>
						<?php foreach ($_SESSION['PR']['con'] as $k => $v) if ($v['screen_order'] > 0 && strpos($v['using'], "delmark") !== false) echo "<th width='".$v['t_prc']."' class='header_text' align='center'><b>".$v['name']."</b></th>";?>
					</tr>
					<?php ViewDelMTPortion($_SESSION['item_arr'], $_SESSION['PR']['con'], $_SESSION['p_code'], $_SESSION['block'], $_SESSION['item_del_question'], $_SESSION['field_align']);?>
				</table>
			</td>
		</tr>
	</table>
</form>
