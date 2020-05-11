<?php


require_once('../config.inc.php');
require_once("for_project_mag.php");
testlinkInitPage($db,('initProject' == 'initProject'));

$args = init_args($db);
$gui = initializeGui($db,$args);


$param_arr = convertUrlQuery($_SERVER["QUERY_STRING"]);
$proj_id_selected = $param_arr['proj_id_selected'];



$smarty = new TLSmarty();
$smarty->assign('gui',$gui);

$permit = Get_Permit_From_Gui($gui);
$smarty->assign('permit',$permit);
$smarty->assign('proj_id_selected',$proj_id_selected);

$smarty->display('project_management/modify_project.html');


