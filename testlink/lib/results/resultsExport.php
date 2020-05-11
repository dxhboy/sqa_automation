<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource resultsGeneral.php
 * @author	Martin Havlat <havlat at users.sourceforge.net>
 * 
 * Show Test Results over all Builds.
 * 
 */
require('../../config.inc.php');
require_once('common.php');
require_once('displayMgr.php');
require_once('../../third_party/codeplex/PHPExcel/Classes/PHPExcel.php');
// require_once('../../third_party/codeplex/PHPExcel.php');
require_once('../../third_party/codeplex/PHPExcel/IOFactory.php');

$dir = dirname(__FILE__); 

$timerOn = microtime(true);
$tplCfg = templateConfiguration();

$args = init_args($db);

$tplan_mgr = new testplan($db);
$gui = initializeGui($db,$args,$tplan_mgr);
$mailCfg = buildMailCfg($gui);
$metricsMgr = new tlTestPlanMetrics($db);
$dummy = $metricsMgr->getStatusTotalsByTopLevelTestSuiteForRender($args->tplan_id);

if(is_null($dummy))
{
	// no test cases -> no report
	$gui->do_report['status_ok'] = 0;
	$gui->do_report['msg'] = lang_get('report_tspec_has_no_tsuites');
	tLog('Overall Metrics page: no test cases defined');
}
else
{
	 // do report
	$gui->statistics->testsuites = $dummy->info;
	$gui->do_report['status_ok'] = 1;
	$gui->do_report['msg'] = '';

	$items2loop = array('testsuites','keywords');
	$keywordsMetrics = $metricsMgr->getStatusTotalsByKeywordForRender($args->tplan_id);
	$gui->statistics->keywords = !is_null($keywordsMetrics) ? $keywordsMetrics->info : null; 
                              
	if( $gui->showPlatforms )
	{
		$items2loop[] = 'platform';
		$platformMetrics = $metricsMgr->getStatusTotalsByPlatformForRender($args->tplan_id);
		$gui->statistics->platform = !is_null($platformMetrics) ? $platformMetrics->info : null; 
	}

	if($gui->testprojectOptions->testPriorityEnabled)
	{
		$items2loop[] = 'priorities';
		$filters = null;
		$opt = array('getOnlyAssigned' => false);
		$priorityMetrics = $metricsMgr->getStatusTotalsByPriorityForRender($args->tplan_id,$filters,$opt);
		$gui->statistics->priorities = !is_null($priorityMetrics) ? $priorityMetrics->info : null; 
	}

	foreach($items2loop as $item)
	{
    if( !is_null($gui->statistics->$item) )
    {
      $gui->columnsDefinition->$item = array();
      
     	// Get labels
     	$dummy = current($gui->statistics->$item);
     	if(isset($dummy['details']))
      {  
        foreach($dummy['details'] as $status_verbose => $value)
       	{
          $dummy['details'][$status_verbose]['qty'] = lang_get($tlCfg->results['status_label'][$status_verbose]);
          $dummy['details'][$status_verbose]['percentage'] = "[%]";
        }
        $gui->columnsDefinition->$item = $dummy['details'];
      }
    }
  } 

 	/* BUILDS REPORT */
	$colDefinition = null;
	$results = null;
	if($gui->do_report['status_ok'])
	{
		$gui->statistics->overallBuildStatus = $metricsMgr->getOverallBuildStatusForRender($args->tplan_id);
		$gui->displayBuildMetrics = !is_null($gui->statistics->overallBuildStatus);
	}  
	
  /* MILESTONE & PRIORITY REPORT */
	$milestonesList = $tplan_mgr->get_milestones($args->tplan_id);
	if (!empty($milestonesList))
	{
		$gui->statistics->milestones = $metricsMgr->getMilestonesMetrics($args->tplan_id,$milestonesList);
  }
} 


 // 每个版本的测试结果进度数据 testsuites
 $editiontitle = ["Edition","Total Test Cases","Not Run","Not Run Percentage","Passed","Passed Percentage","Failed","Failed Percentage","Blocked","Blocked Percentage","Percentage Completed"];
 
 $Edition = array();
foreach($gui->statistics->overallBuildStatus->info as $key1 => $value1){
	
	$details=$value1['details'];
	array_push($Edition,array($value1['build_name'],$value1['total_assigned'],$details['not_run']['qty'],$details['not_run']['percentage'].'%',$details['passed']['qty'],$details['passed']['percentage'].'%',$details['failed']['qty'],$details['failed']['percentage'].'%',$details['blocked']['qty'],$details['blocked']['percentage'].'%',$value1['percentage_completed'].'%'));
	
	
 }

 // 优先级数据
  $Priorities = array(); 
  $prioritiestitle = ["Priorities","Total Test Cases","Not Run","Not Run Percentage","Passed","Passed Percentage","Failed","Failed Percentage","Blocked","Blocked Percentage","Percentage Completed"];
 
 foreach($gui->statistics->priorities as $key1 => $value1){
	
	$details=$value1['details'];
	if($value1['name']=="高") {
		$value1['name']='High';
	} elseif ($value1['name']=="中") {
		$value1['name']='Middle';
	} elseif ($value1['name']=="低") {
		$value1['name']='Low';
	}
	array_push($Priorities,array($value1['name'],$value1['total_tc'],$details['not_run']['qty'],$details['not_run']['percentage'].'%',$details['passed']['qty'],$details['passed']['percentage'].'%',$details['failed']['qty'],$details['failed']['percentage'].'%',$details['blocked']['qty'],$details['blocked']['percentage'].'%',$value1['percentage_completed'].'%'));
	
 }


// 上级测试用例集的测试结果数据
$Testsuite= array(); 
$Testsuitetitle=["Test Suite","Total Test Cases","Not Run","Not Run Percentage","Passed","Passed Percentage","Failed","Failed Percentage","Blocked","Blocked Percentage","Percentage Completed"];
 
 foreach($gui->statistics->testsuites as $key1 => $value1){
	$details=$value1['details'];
	array_push($Testsuite,array($value1['name'],$value1['total_tc'],$details['not_run']['qty'],$details['not_run']['percentage'].'%',$details['passed']['qty'],$details['passed']['percentage'].'%',$details['failed']['qty'],$details['failed']['percentage'].'%',$details['blocked']['qty'],$details['blocked']['percentage'].'%',$value1['percentage_completed'].'%'));
	
 }
   
ob_end_clean();


//创建PHPExcel实例
$cellArr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD');
$cellNames = ['A', 'B', 'C', 'D', 'E', 'F','G'];
$cellName = [];
foreach ($cellNames as $key => $val) {
	foreach ($cellArr as $k => $v) {
		$cellName[] = $val.$v;
	}
}


$cellName = array_merge($cellArr,$cellName);
/* @实例化 */
$objPHPExcel = new PHPExcel();
$objWorksheet = $objPHPExcel->getActiveSheet();
/* 设置宽度 */
$objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);

// 设置单元格高度
// 所有单元格默认高度
$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
// 第一行的默认高度
$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
// 设置align
$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$sheettitle=$gui->tproject_name." Test Summary";
$sheettitle=str_replace(" ","_",$sheettitle);
//设置SHEET
$objPHPExcel->setactivesheetindex(0);
$objPHPExcel->getActiveSheet()->setTitle($sheettitle);

// 设置标题
$styletitle=array(
	'font'    => array(
		'bold'      => true,
		'size'      => 12,
		'name'      => 'Candara'
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	),
	'borders' => array(
		'top'     => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN
		)
	),
	'fill' => array(
		'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
		'rotation'   => 90,
		'startcolor' => array(
			'argb' => 'FFA0A0A0'
		),
		'endcolor'   => array(
			'argb' => 'FFFFFFFF'
		)
	)
);
  
// 设置表格标题
$styleTabletitle = array(
	'font'    => array(
		'bold'      => true,
		'size'      => 12,
		'name'      => 'Candara'
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	),
	'borders' => array(
		'top'     => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN
		)
	)
);  

$objPHPExcel->getActiveSheet()->calculateWorksheetDimension();
$objPHPExcel->getActiveSheet()->setCellValue('A1','Progress of test results for each version');
$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($styleTabletitle);
$objPHPExcel->getActiveSheet()->mergeCells('A1:K1');

//设置纵向单元格标识
$_row = 2; 
// 设置标题栏
$objPHPExcel->getActiveSheet()->getStyle('A2:K2')->applyFromArray($styletitle);

// 设置边框
$styleThinBlackBorderOutline = array(
	'borders' => array(
		'allborders' => array( //设置全部边框
			'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
		),

	),
);


// 设置font
$objPHPExcel->getActiveSheet()->getstyle()->getFont()->setName('Candara');
$objPHPExcel->getActiveSheet()->getstyle()->getFont()->setSize(11);

foreach ($editiontitle as $k => $v) {
	$objPHPExcel->getactivesheet()->setCellValue($cellName[$k].$_row, $v);
	
}

foreach($Edition AS $_v){
	$j = 0;
	$_row++;
	foreach($_v AS $_cell){
		$objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . $_row, $_cell);
		$j++;
	}
}
// 设置边框
$objPHPExcel->getActiveSheet()->getStyle('A1:K'.$_row)->applyFromArray($styleThinBlackBorderOutline);

$_row=$_row+2;
$objPHPExcel->getActiveSheet()->setCellValue('A'.$_row,'Display test results according to the priority of test cases');
$objPHPExcel->getActiveSheet()->getStyle('A'.$_row.':K'.$_row)->applyFromArray($styleTabletitle);
$objPHPExcel->getActiveSheet()->mergeCells('A'.$_row.':K'.$_row);

$_row=$_row+1;
$_row1=$_row;
// 设置标题栏
$objPHPExcel->getActiveSheet()->getStyle('A'.$_row.':K'.$_row)->applyFromArray($styletitle);

foreach ($prioritiestitle as $k => $v) {
	$objPHPExcel->getactivesheet()->setCellValue($cellName[$k].$_row, $v);
}

foreach($Priorities AS $_v){
	$j = 0;
	$_row++;
	foreach($_v AS $_cell){
		$objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . $_row, $_cell);
		$j++;
	}
}
// 设置边框
$objPHPExcel->getActiveSheet()->getStyle('A'.$_row1.':K'.$_row)->applyFromArray($styleThinBlackBorderOutline);


$_row=$_row+2;
$objPHPExcel->getActiveSheet()->setCellValue('A'.$_row,'Test results of superior test case sets');
$objPHPExcel->getActiveSheet()->getStyle('A'.$_row.':K'.$_row)->applyFromArray($styleTabletitle);
$objPHPExcel->getActiveSheet()->mergeCells('A'.$_row.':K'.$_row);

$_row=$_row+1;
$_row1=$_row;
// 设置标题栏
$objPHPExcel->getActiveSheet()->getStyle('A'.$_row.':K'.$_row)->applyFromArray($styletitle);

foreach ($Testsuitetitle as $k => $v) {
	$objPHPExcel->getactivesheet()->setCellValue($cellName[$k].$_row, $v);
}

foreach($Testsuite AS $_v){
	$j = 0;
	$_row++;
	foreach($_v AS $_cell){
		$objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . $_row, $_cell);
		$j++;
	}
}


$objPHPExcel->getactivesheet()->setCellValue('AA1', 'Not Run');
$objPHPExcel->getactivesheet()->setCellValue('AA3', 'Passed');
$objPHPExcel->getactivesheet()->setCellValue('AA2', 'Failed');
$objPHPExcel->getactivesheet()->setCellValue('AA4', 'Blocked');
$_row2=$_row1+1;
$objPHPExcel->getactivesheet()->setCellValue('AB1', '=SUM(C'.$_row2.':C'.$_row.')');
$objPHPExcel->getactivesheet()->setCellValue('AB3', '=SUM(E'.$_row2.':E'.$_row.')');
$objPHPExcel->getactivesheet()->setCellValue('AB2', '=SUM(G'.$_row2.':G'.$_row.')');
$objPHPExcel->getactivesheet()->setCellValue('AB4', '=SUM(I'.$_row2.':I'.$_row.')');


//设置A3单元格为文本
// $objPHPExcel->getActiveSheet()->getStyle('AA1:AA4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
// $objPHPExcel->getActiveSheet()->getStyle('AB1:AB4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
// 设置边框
$objPHPExcel->getActiveSheet()->getStyle('A'.$_row1.':K'.$_row)->applyFromArray($styleThinBlackBorderOutline);

//  设置自动换行
$objPHPExcel->getActiveSheet()->getStyle('A1:K'.$_row)->getAlignment()->setWrapText(TRUE); // a1 到a100 单元格，字符串自动换行

//  Set the Labels for each data series we want to plot
//      Datatype
//      Cell reference for data
//      Format Code
//      Number of datapoints in series
//      Data values
//      Data Marker
$_row3=$_row1-1;
$dataseriesLabels1 = array(
	new PHPExcel_Chart_DataSeriesValues('String', $sheettitle.'!$A$'.$_row3, NULL, 1),   //  2011
);
//  Set the X-Axis Labels
//      Datatype
//      Cell reference for data
//      Format Code
//      Number of datapoints in series
//      Data values
//      Data Marker
$xAxisTickValues1 = array(
	new PHPExcel_Chart_DataSeriesValues('String', $sheettitle.'!$AA$1:$AA$4', NULL, 4),  //  Q1 to Q4
	
);
//  Set the Data values for each data series we want to plot
//      Datatype
//      Cell reference for data
//      Format Code
//      Number of datapoints in series
//      Data values
//      Data Marker
$dataSeriesValues1 = array(
	
	new PHPExcel_Chart_DataSeriesValues('Number', $sheettitle.'!$AB$1:$AB$4', NULL, 4),
	
);

//  Build the dataseries
$series1 = new PHPExcel_Chart_DataSeries(
	PHPExcel_Chart_DataSeries::TYPE_PIECHART,               // plotType
	PHPExcel_Chart_DataSeries::GROUPING_STANDARD,           // plotGrouping
	range(0, count($dataSeriesValues1)-1),                  // plotOrder
	$dataseriesLabels1,                                     // plotLabel
	$xAxisTickValues1,                                      // plotCategory
	$dataSeriesValues1                                      // plotValues
);

//  Set up a layout object for the Pie chart
$layout1 = new PHPExcel_Chart_Layout();
$layout1->setShowVal(TRUE);
$layout1->setShowPercent(TRUE);

//  Set the series in the plot area
$plotarea1 = new PHPExcel_Chart_PlotArea($layout1, array($series1));
//  Set the chart legend
$legend1 = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);

$title1 = new PHPExcel_Chart_Title('Test Suite test summary');

//  Create the chart
$chart1 = new PHPExcel_Chart(
	'chart1',       // name
	$title1,        // title
	$legend1,       // legend
	$plotarea1,     // plotArea
	true,           // plotVisibleOnly
	0,              // displayBlanksAs
	NULL,           // xAxisLabel
	NULL            // yAxisLabel       - Pie charts don't have a Y-Axis
);

//  Set the position where the chart should appear in the worksheet
$chart1->setTopLeftPosition('M3');
$chart1->setBottomRightPosition('W17');

//  Add the chart to the worksheet
$objWorksheet->addChart($chart1);

$excel = 'Excel2007';  
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,$excel);  
$objWriter->setIncludeCharts(true); //图表必须  

$xlsxtitle=$gui->tproject_name.'-'.$gui->tplan_name.'_'.date("Ymd").'.xlsx';
 
browser_export($excel,$xlsxtitle); //浏览器输出  
SaveViaTempFile($objWriter);  


/*
  function: init_args 
  args: none
  returns: array 
*/
function init_args(&$dbHandler)
{
  $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,64),
                   "tproject_id" => array(tlInputParameter::INT_N), 
	                 "tplan_id" => array(tlInputParameter::INT_N),
                   "format" => array(tlInputParameter::INT_N),
                   "sendByMail" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
  if( !is_null($args->apikey) )
  {
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;
    
    if(strlen($args->apikey) == 32)
    {
      $cerbero->args->getAccessAttr = true;
      $cerbero->method = 'checkRights';
      $cerbero->redirect_target = "../../login.php?note=logout";
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
    }
    else
    {
      $args->addOpAccess = false;
      $cerbero->method = null;
      setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
    }  
  }
  else
  {
    testlinkInitPage($dbHandler,true,false,"checkRights");  
	  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }

  if($args->tproject_id <= 0)
  {
  	$msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test Project ID ({$args->tproject_id})";
  	throw new Exception($msg);
  }

  if (is_null($args->format))
	{
		tlog("Parameter 'format' is not defined", 'ERROR');
		exit();
	}
	
	$args->user = $_SESSION['currentUser'];

  $args->format = $args->sendByMail ? FORMAT_MAIL_HTML : $args->format;

  return $args;
}


/**
 * 
 *
 */
function buildMailCfg(&$guiObj)
{
	$labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
	$cfg = new stdClass();
	$cfg->cc = ''; 
	$cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name . 
	                ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
	                 
	return $cfg;
}


function initializeGui(&$dbHandler,$argsObj,&$tplanMgr)
{
  $gui = new stdClass();
  $gui->title = lang_get('title_gen_test_rep');
  $gui->do_report = array();
  $gui->showPlatforms=true;
  $gui->columnsDefinition = new stdClass();
  $gui->columnsDefinition->keywords = null;
  $gui->columnsDefinition->testers = null;
  $gui->columnsDefinition->platform = null;
  $gui->statistics = new stdClass();
  $gui->statistics->keywords = null;
  $gui->statistics->testers = null;
  $gui->statistics->milestones = null;
  $gui->statistics->overalBuildStatus = null;
  $gui->elapsed_time = 0; 
  $gui->displayBuildMetrics = false;
  $gui->buildMetricsFeedback = lang_get('buildMetricsFeedback');

  $mgr = new testproject($dbHandler);
  $dummy = $mgr->get_by_id($argsObj->tproject_id);
  $gui->testprojectOptions = new stdClass();
  $gui->testprojectOptions->testPriorityEnabled = $dummy['opt']->testPriorityEnabled;
  $gui->tproject_name = $dummy['name'];

  $info = $tplanMgr->get_by_id($argsObj->tplan_id);
  $gui->tplan_name = $info['name'];
  $gui->tplan_id = intval($argsObj->tplan_id);

  $gui->platformSet = $tplanMgr->getPlatforms($argsObj->tplan_id,array('outputFormat' => 'map'));
  if( is_null($gui->platformSet) )
  {
  	$gui->platformSet = array('');
  	$gui->showPlatforms = false;
  }

  $gui->basehref = $_SESSION['basehref'];
  $gui->actionSendMail = $gui->basehref . 
                         "lib/results/resultsGeneral.php?format=" . 
                         FORMAT_MAIL_HTML . "&tplan_id={$gui->tplan_id}"; 

  $gui->mailFeedBack = new stdClass();
  $gui->mailFeedBack->msg = '';
  return $gui;
}

/**
 *
 */
function checkRights(&$db,&$user,$context = null)
{
  if(is_null($context))
  {
    $context = new stdClass();
    $context->tproject_id = $context->tplan_id = null;
    $context->getAccessAttr = false; 
  }

  $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
  return $check;
}
function browser_export($type, $filename){  
	if($type == "Excel5"){  
		header('Content-Type: application/vnd.ms-excel'); //excel2003  
	}else{  
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //excel2007  
	}  
	header('Content-Disposition: attachment;filename="'.$filename.'"');  
	header('Cache-Control: max-age=0');  
}  

/*解决Excel2007不能导出*/  
function SaveViaTempFile($objWriter){  
	$filePath = dirname(__FILE__) . rand(0, getrandmax()) . rand(0, getrandmax()) . ".tmp";  
	$objWriter->save($filePath);  
	readfile($filePath);  
	unlink($filePath);  
}
?>