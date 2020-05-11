<?php


require_once('../config.inc.php');
require_once("for_project_mag.php");
testlinkInitPage($db,('initProject' == 'initProject'));

$args = init_args($db);
$gui = initializeGui($db,$args);



$permit = Get_Permit_From_Gui($gui);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('permit',$permit);
$smarty->display('project_management/manage_group.html');


