<?php  header("Content-Type: text/html; charset=utf-8");?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<head>
	<style>.button_save {background-color:#00CC99;}</style>
	<style>.cell_invisible {color:#FFFFFF; background-color:#FFFFFF; border:none;}</style>
	<style>.exit_button {background-color:#00FFCC;}</style>
	<style>.i_h {color:#FFFFFF; border:none; background-color:#FFFFFF;}</style>
	<style>.auto_increment_button {background-color:#33FFFF;}</style>
	<style>.odd_row {background-color:#CCFFFF;}</style>
	<style>.hidden_button {visibility:hidden;}</style>
</head>
<SCRIPT language=JavaScript>
function user_lang_on() {document.table_form.user_lang_s.value = '*'; table_form.submit();}
function sel_use_type_on(obj) {document.table_form.use_type_s.value = obj.name; table_form.submit();}
function sel_second_catalog_on() {document.table_form.second_catalog_s.value = '*'; table_form.submit();}
</SCRIPT>

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Administrator/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/HTML.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Languages/LanguageUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LocalLanguages/LocalUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/DataBases/DataBaseUpdate.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Configuration/ConfigUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/SpecialTextsUtils.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Fields/FieldUtilities.php");
require_once("TableUtilities.php");
require_once("TableUpdate.php");
require_once("TableTest.php");

session_start();
$Mes = array();
$dbh_sys = GetOnlyDB("db_manager");
if (!$dbh_sys) ExitSession(Title(252)."|FF0000");
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
if (count($_POST) == 0) $t_flags = TestTables(true);
foreach ($_POST as $str_key => $str_v)
{
	$k = explode("-", $str_key);
	$s_k = (count($k) == 1) ? $str_key : $k[0];
	$sw_break = true;
	switch ($s_k)
	{
        case "idle_button"      : break;
		case "user_lang_s"		: if (SetUserLangTable($dbh_sys, $sw_break)) $t_flags = TestTables($_SESSION['user_working_mode'] == 0); break;
		case "use_type_s"		: if (AfterUseTypeChoice($sw_break, $Mes)) $t_flags = TestTables(false); break;
		case "second_catalog_s"	: if (AfterSecondCatalogChoice($sw_break)) $t_flags = TestTables(false); break;
		case "group_symbol"		: $_SESSION['table_definitions'][$k[1]]['illegals'] .= $_SESSION['char_group']; break;
		case "table_exit"		: if (ActionTableExit($Mes)) header("Location: ../Administrator/DataBaseActions.php"); break;
		case "table_OK"			: $t_flags = TestTables(false); if (SetPreliminaryTableErrors($t_flags)) RewriteDefinition($dbh, $Mes); break;
		case "max_level"		: ReduceMaxLevel($dbh, $k[1]); $_SESSION['table_definitions'][$k[1]]['tab_err'][] = "<font color='#0000FF'>".Title(407)."</font>"; break; // change
		case "auto_increment"	: AutoIncrementAll($dbh); $Mes[] = "<font color='#0000FF'><b>".Title(406)."</b></font>"; break;
		default					: $sw_break = false;
	}
	if ($sw_break) break;
}
if ($_SESSION['admin_mes'][0] > 0) echo "<div align='center'><font color='#FF0000'><h3><b>".$_SESSION['admin_mes'][1]."</b></h3></font></div>";
$dis = ($_SESSION['user_working_mode'] == 1) ? "" : " disabled";
?>
<form method="post" id="table_form" name="table_form">
    <button name="idle_button" type="submit" value="*" class="hidden_button"></button>
	<?php SelectTag("user_lang",  $_SESSION['user_langs'], $_SESSION['user_lang'][1], "user_lang_s", false, "", "user_lang_on"); foreach ($Mes as $z) echo "<br>".$z; ?>
	<div align="center"><font size="+2"><b><?php echo Title(307);?></b></font></div>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td><button name="table_exit" type="submit" title="<?php echo Title(8);?>" class="exit_button"><?php echo Title(8);?></button></td>
			<td><button name="ib_table" class="cell_invisible">X</button></td>
			<td><button name="table_OK" title="<?php echo Title(310);?>" type="submit" value="*" class="button_save"<?php echo $dis;?>><?php echo Title(30);?></button></td>
			<td><button name="ib_table" class="cell_invisible">X</button></td>
			<td><button name="auto_increment" title="<?php echo Title(326);?>" type="submit" value="*" class="auto_increment_button"<?php echo $dis;?>>++</button></td>
		</tr>
	</table>
	<hr align="left" size="1" noshade="noshade" color="#000000" >
	<table>
		<tr>
			<td width="7%"><b><?php echo Title(365);?></b></td>
			<td width="9%"><b><?php echo Title(312);?></b></td>
			<td width="9%"><b><?php echo Title(311);?></b></td>
			<td width="1%"></td>
			<td width="15%"><b><?php echo Title(313);?></b></td>
			<td width="8%"><b><?php echo Title(315);?></td>
			<td width="7%"><b><?php echo Title(314);?></b></td>
			<td width="13%"><b><?php echo Title(370);?></b></td>
			<td width="1%"></td>
			<td></td>
		</tr>
		<?php
		$odd_row = true;
		foreach ($_SESSION['table_definitions'] as $k => $v)
		{
			echo "<tr valign='top'".(($odd_row) ? " class='odd_row'" : "").">";
				echo "<td><input size='12' type='text' name='table-".$k."' value='".$k."' readonly></td>";
				echo "<td>";
                    echo SelectTag("use_type-".$k, $_SESSION['table_types'], $v['use_type'], "", true, "", "sel_use_type_on(this)", $_SESSION['user_working_mode'] == 0);
//                    echo SelectTag("use_type-".$k, $_SESSION['table_types'], $_SESSION['table_types'][$v['use_type']], "", true, "", "sel_use_type_on(this)", $_SESSION['user_working_mode'] == 0);
//					echo "<select name='use_type-".$k."'".$dis." onchange='sel_use_type_on();'>"; // SelectTag
//						foreach ($_SESSION['table_types'] as $use_type) echo OptionTag($_SESSION['table_types'][$v['use_type']], $use_type);
//					echo "</select>";
				echo "</td>";
				echo "<td><input size='12' type='text' name='table_title-".$k."'".$dis." title='".$v['table_title']."' value='".$v['table_title']."'></td>";
				if ($v['use_type'] == 3)
				{
					if ($v['second_catalog'] == "")
					{
						echo "<td><button name='group_symbol-".$k."' type='submit' title='".Title(303)."' class='i_h' value='*'".$dis.">".ImgV("GoToK", 10, 16)."</button></td>";
						echo "<td><input size='20' type='text' name='illegals-".$k."' value='".$v['illegals']."'".$dis."></td>";
						echo "<td></td><td></td><td></td>";
					}
					else
					{
						echo "<td></td><td></td>";
						echo "<td>";
                            echo SelectTag("second_catalog-".$k, $_SESSION['single_catalogs'], $v['second_catalog'], "second_catalog_s", false, "", "sel_second_catalog_on", $dis);
//							echo "<select name='second_catalog-".$k."'".$dis." onchange='sel_second_catalog_on();'>"; // SelectTag
//								foreach ($_SESSION['single_catalogs'] as $second_catalog) echo OptionTag($v['second_catalog'], $second_catalog);
//							echo "</select>";
						echo "</td>";
						echo "<td><input size='12' type='text' name='separators-".$k."' value='".$v['separators']."'".$dis."></td>";
						echo "<td>";
                            echo SelectTag("group_type-".$k, $_SESSION['group_types'], $_SESSION['group_types'][$v['group_type']], "", false, "", "", "", $dis);
//							echo "<select name='group_type-".$k."'".$dis.">"; // SelectTag
//								foreach ($_SESSION['group_types'] as $group_type) echo OptionTag($_SESSION['group_types'][$v['group_type']], $group_type);
//							echo "</select>";
						echo "</td>";
					}
				}
				else echo "<td></td><td></td><td></td><td></td><td></td>";
				echo "<td>"; if ($v['second_catalog'] != "") echo "<button name='max_level-".$k."' type='submit' title='".Title(325)."' class='i_h' value='*'".$dis.">".ImgV("LineUp", 10, 16)."</button>"; echo "</td>";
				echo "<td>".implode("; ", $v['tab_err'])."</td>";
				$odd_row = !$odd_row;
			echo "</tr>";
		}
		?>
	</table>
	<input type="hidden" name="use_type_s" id="use_type_s" value="">
</form>
