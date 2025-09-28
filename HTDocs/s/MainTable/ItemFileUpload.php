<?php
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/Common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LogProcessing/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/LoadToServer/Utilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Utilities/DataBases.php");
require_once("PubUtilities.php");
require_once("PubFilesUtils.php");
require_once("PubFormUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/CommonUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/MainTable/MTRequests.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Visits/VisitUtilities.php");
require_once($_SERVER['DOCUMENT_ROOT']."/s/Titles/TitleSelect.php");

session_start();
$Mes = array();
$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
$s = $_FILES['db_filename']['size'];
$m = TransSize(ini_get('upload_max_filesize'));
if ($s > $m) $_SESSION['mes']['m'] = array("time"=>"", "text"=>"Size of uploaded file exceeds the directive in php.ini (".(string)$m.")", "status"=>"error");
elseif ($_FILES['db_filename']['name'] != "" && $_FILES['db_filename']['error'] == 0) 
{
	$d_file = $_SESSION['URL_p']."/".(string)$_SESSION['p_code']."-".$_SESSION['load_file_number'].".".pathinfo($_FILES['db_filename']['name'], PATHINFO_EXTENSION);
	$f_num = FindFileInArray($_SESSION['p_files']['e'], $_FILES['db_filename']['name']);
	if ($f_num == "")
	{
		if ($_SESSION['file_update']) DeleteFilesDir($_SESSION['URL_p'], $_SESSION['p_code'], array(), $_SESSION['load_file_number']); 
		$_SESSION['mes']['m'][] = SingleFileUpload($_FILES['db_filename']['tmp_name'], $d_file, $_FILES['db_filename']['name']);
		if (!end($_SESSION['mes']['m'])['error'])
		{
			$_SESSION['p_files']['e'][$_SESSION['load_file_number']][$_SESSION['spec_fld'][2]['URL_file']] = $_FILES['db_filename']['name'];
			$_SESSION['p_files']['e'][$_SESSION['load_file_number']][$_SESSION['spec_fld'][2]['URL_link']link] = $d_file;
			if (!$_SESSION['file_update'])
			{
				foreach ($_SESSION['main_params']['const'] as $k => $v)
				{
					if ($v['own_table'] == 0 && substr($v['f_type'], 4) != "URL_")
					{
						if ($v['f_type'] == "integer") $_SESSION['p_files']['e'][$_SESSION['load_file_number']][$k] = 0;
						else $_SESSION['p_files']['e'][$_SESSION['load_file_number']][$k] = "";
					}
				}
/*		
				$_SESSION['p_files']['e'][$_SESSION['load_file_number']]['file_description'] = "";
				$_SESSION['p_files']['e'][$_SESSION['load_file_number']]['file_issue_year'] = 0;
				$_SESSION['p_files']['e'][$_SESSION['load_file_number']]['file_volume'] = "";
				$_SESSION['p_files']['e'][$_SESSION['load_file_number']]['file_number'] = "";
				$_SESSION['p_files']['e'][$_SESSION['load_file_number']]['file_page'] = "";
*/
			}
		}
	}
	else $_SESSION['mes']['m'] = array("time"=>"", "text"=>"Such file is already (number <b>".$f_num."</b>)", "status"=>"error");
}
header("Location: PubForm.php");
?>
