<?php

function downAction($projectname,$dataarry){
	
	$filename=$projectname." test case list testresult_".date("Ymd");

	$A = [];
	$TestSection= $dataarry[0][0];
	$s = 2;
	$e = 1;
	$B = [];
	$CaseSection= $dataarry[0][1];

	$s1 = 2;
	$e1 = 1;
	foreach ($dataarry  as $k=> $v){
		if($v[0] !=$TestSection){
			$A[]="A".$s.":A".$e."";
			$e++;
			$TestSection= $v[0];
			$s = $e;
		}else{
			$e++;
			if(count($dataarry)==($k+1)){
				$A[]="A".$s.":A".$e."";
			}
		}
		if($v[1] !=$CaseSection){
			$B[]="B".$s1.":B".$e1."";
			$e1++;
			$CaseSection = $v[1];
			$s1 = $e1;
		}else{
			$e1++;
			if(count($dataarry)==($k+1)){
				$B[]="B".$s1.":B".$e1."";
			}
		}
	}
	$title = ['Test TestSection','Test Case TestSection','Test Case ID','Test Case Name','surport','Script','Engineer','Priority','Result','Bug NO','Version','Comment','Test time','Execution type'];
    
	ob_end_clean();
    
	
	//创建PHPExcel实例
	$cellArr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	$cellNames = ['A', 'B', 'C', 'D', 'E', 'F','G'];
	$cellName = [];
	foreach ($cellNames as $key => $val) {
		foreach ($cellArr as $k => $v) {
			$cellName[] = $val.$v;
		}
	}
	$cellName = array_merge($cellArr,$cellName);
	/* @实例化 */
	$obpe = new PHPExcel();
	/* 设置宽度 */
	$obpe->getActiveSheet()->getColumnDimension()->setAutoSize(true);
	$obpe->getActiveSheet()->getColumnDimension('A')->setWidth(16);
	$obpe->getActiveSheet()->getColumnDimension('B')->setWidth(16);
	$obpe->getActiveSheet()->getColumnDimension('C')->setWidth(10);
	$obpe->getActiveSheet()->getColumnDimension('D')->setWidth(90);
	$obpe->getActiveSheet()->getColumnDimension('I')->setWidth(10);
	$obpe->getActiveSheet()->getColumnDimension('K')->setWidth(20);
	$obpe->getActiveSheet()->getColumnDimension('L')->setWidth(20);
	$obpe->getActiveSheet()->getColumnDimension('M')->setWidth(15);
	$obpe->getActiveSheet()->getColumnDimension('N')->setWidth(15);
	// 设置单元格高度
	// 所有单元格默认高度
	$obpe->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
	// 第一行的默认高度
	$obpe->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
	// 设置align
	$obpe->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$obpe->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	//设置SHEET
	$obpe->setactivesheetindex(0);
	$obpe->getActiveSheet()->setTitle($projectname.' functions test');
	$_row = 1;   //设置纵向单元格标识
	
	
	$obpe->getActiveSheet()->calculateWorksheetDimension();
	
	// 设置标题栏
	$obpe->getActiveSheet()->getStyle('A1:N1')->applyFromArray(
		array(
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
		)
	);
	// 设置边框
	$styleThinBlackBorderOutline = array(
        'borders' => array(
            'allborders' => array( //设置全部边框
                'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
            ),

        ),
    );
   
	
	// 设置font
	$obpe->getActiveSheet()->getstyle()->getFont()->setName('Candara');
	$obpe->getActiveSheet()->getstyle()->getFont()->setSize(11);
	
	foreach ($title as $k => $v) {
		$obpe->getactivesheet()->setCellValue($cellName[$k].$_row, $v);
	}
	$i = 1;
	

	foreach($dataarry AS $_v){
		$j = 0;
		foreach($_v AS $_cell){
			
			if($j==3) {
				//使用逗号或空格(包含" ")分隔短语
				$rowname = preg_split("/[\s]+/", $_cell,2);
				$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), $rowname[1]);
			} elseif($j==4) {
				
				if(strlen($_cell)>0) {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'yes');
				} else {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), ' ');
				}
			} elseif($j==5) {
				$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'yes');
			}  elseif($j==7) {
				$urg_imp=intval($_cell);
					
				if ($urg_imp>=6) 
				{            
				  $obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'High');
				} 
				else if($urg_imp<3) 
				{
				  $obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'LOW');
				}        
				else
				{
				  $obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'MEDIUM');
				}
				
			} elseif($j==8) {
				$in=$i+1;
				$index='I'.$in;
				if($_cell=='p') {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'Passed');
					// 设置填充颜色
					$obpe->getActiveSheet()->getstyle($index)->getFill()->setFillType(PHPExcel_style_Fill::FILL_SOLID);
					$obpe->getActiveSheet()->getstyle($index)->getFill()->getStartColor()->setARGB('FF238E23');
				} elseif ($_cell=='P') {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'Passed');
					// 设置填充颜色
					$obpe->getActiveSheet()->getstyle($index)->getFill()->setFillType(PHPExcel_style_Fill::FILL_SOLID);
					$obpe->getActiveSheet()->getstyle($index)->getFill()->getStartColor()->setARGB('FF238E23');
				} elseif ($_cell=='f') {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'Failed');
					// 设置填充颜色
					$obpe->getActiveSheet()->getstyle($index)->getFill()->setFillType(PHPExcel_style_Fill::FILL_SOLID);
					$obpe->getActiveSheet()->getstyle($index)->getFill()->getStartColor()->setARGB('FFFF1A1A');
				} elseif ($_cell=='F') {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'Failed');
					// 设置填充颜色
					$obpe->getActiveSheet()->getstyle($index)->getFill()->setFillType(PHPExcel_style_Fill::FILL_SOLID);
					$obpe->getActiveSheet()->getstyle($index)->getFill()->getStartColor()->setARGB('FFFF1A1A');
				} else {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'Not Run');
					// 设置填充颜色
					// $obpe->getActiveSheet()->getstyle($index)->getFill()->setFillType(PHPExcel_style_Fill::FILL_SOLID);
					// $obpe->getActiveSheet()->getstyle($index)->getFill()->getStartColor()->setARGB('FF23CCE6');
				}
			} elseif($j==9) {
				$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), ' ');
			} elseif($j==13) {
				if($_cell=='2') {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'Automated');
				} elseif (strlen($_cell)<1) {
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), ' ');
				} else {
					
					$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), 'Manual');
				}
			} else {
				
				$obpe->getActiveSheet()->setCellValue($cellName[$j] . ($i+$_row), $_cell);
			}
			$j++;
		}
		$i++;
	}
	//设置填充的样式和背景色
	$obpe->getActiveSheet()->getStyle( 'C2:C'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$obpe->getActiveSheet()->getStyle( 'C2:C'.$i)->getFill()->getStartColor()->setARGB('FF6060A1');
	// 设置边框
	$obpe->getActiveSheet()->getStyle('A1:N'.$i)->applyFromArray($styleThinBlackBorderOutline);
	# 设置自动换行
	$obpe->getActiveSheet()->getStyle('A1:N'.$i)->getAlignment()->setWrapText(TRUE); // a1 到a100 单元格，字符串自动换行
	
	foreach ($A as $aa){
		$obpe->getActiveSheet()->mergeCells($aa);
	}
	foreach ($B as $bb){
		$obpe->getActiveSheet()->mergeCells($bb);
	}
	//输出到浏览器

	$write = new PHPExcel_Writer_Excel2007($obpe);
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
	header("Content-Type:application/force-download");
	header("Content-Type:application/vnd.ms-execl");
	header("Content-Type:application/octet-stream");
	header("Content-Type:application/download");
	header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
	header("Content-Transfer-Encoding:binary");
	$write->save('php://output');
	
}
function getData($projectid,$tplan_id){
	
	
	$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

	mysqli_query($mysqli,"SET NAMES utf8");

	if ($mysqli->connect_error) {
	
		die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		
	}

	// $testplan_sql="SELECT li.TestSection,li.CaseSection,substring_index(li.CaseName,' ',1) AS CaseID,li.CaseName,re.Result as Surport,re.Result as Script,re.Engineer,li.Priority,re.Result,re.Result as BugNO,re.Version,re.Comment,re.testtime,re.execution_type from  
	// (SELECT fl1.id,fl.name as TestSection,sl.name as  CaseSection,tl.name as CaseName,(TPTCV.urgency * TCV.importance) as Priority from  nodes_hierarchy fl,nodes_hierarchy sl,
	// nodes_hierarchy tl ,nodes_hierarchy fl1,testplan_tcversions TPTCV ,tcversions TCV  where fl.node_type_id='2' and fl.parent_id in (SELECT id from  nodes_hierarchy where id='".$projectid."') 
	// and sl.node_type_id='2' and sl.parent_id=fl.id and tl.node_type_id='3' and tl.parent_id=sl.id and fl1.node_type_id='4' and 
	// fl1.parent_id=tl.id and TPTCV.tcversion_id=f1.id and TCV.id = TPTCV.tcversion_id) li LEFT JOIN  ( SELECT r.id,r.TestSection,r.CaseSection,r.CaseName,r.Engineer,r.Result,r.Version,r.Comment,
	// r.testtime,r.execution_type from  ( SELECT f1.id,f.name as TestSection,s.name as  CaseSection,t.name as CaseName,u.login as Engineer,
	// e.status as Result,b.name as Version,e.notes as Comment,e.execution_ts as testtime,e.execution_type from  nodes_hierarchy f,nodes_hierarchy s,nodes_hierarchy t 
	// ,nodes_hierarchy f1,executions e,users u,builds b where f.node_type_id='2' and f.parent_id in (SELECT id from  nodes_hierarchy where  id='".$projectid."') 
	// and s.node_type_id='2' and s.parent_id=f.id and t.node_type_id='3' and t.parent_id=s.id and f1.node_type_id='4' and f1.parent_id=t.id  and 
	// e.tcversion_id=f1.id and e.tester_id=u.id and e.build_id=b.id and e.testplan_id='".$tplan_id."' order by e.execution_ts DESC  limit 999999) r GROUP BY r.Version) re on li.id=re.id order by substring_index(li.CaseName,'.',1)+0 ASC;";

	$testplan_sql="SELECT li.TestSection,li.CaseSection,substring_index(li.CaseName,' ',1) AS CaseID,li.CaseName,re.Result as Surport,re.Result as Script,re.Engineer,li.Priority,re.Result,re.Result as BugNO,re.Version,re.Comment,re.testtime,re.execution_type from  
	(SELECT fl1.id,fl.name as TestSection,sl.name as  CaseSection,tl.name as CaseName,(TPTCV.urgency * TCV.importance) as Priority from  nodes_hierarchy fl,nodes_hierarchy sl,testplan_tcversions TPTCV ,tcversions TCV,
	nodes_hierarchy tl ,nodes_hierarchy fl1 where fl.node_type_id='2' and fl.parent_id in (SELECT id from  nodes_hierarchy where id='".$projectid."') 
	and sl.node_type_id='2' and sl.parent_id=fl.id and tl.node_type_id='3' and tl.parent_id=sl.id and fl1.node_type_id='4' and 
	fl1.parent_id=tl.id and TPTCV.tcversion_id=fl1.id and TCV.id = TPTCV.tcversion_id) li LEFT JOIN  ( SELECT r.id,r.TestSection,r.CaseSection,r.CaseName,r.Engineer,r.Result,r.Version,r.Comment,
	r.testtime,r.execution_type from  ( SELECT f1.id,f.name as TestSection,s.name as  CaseSection,t.name as CaseName,u.login as Engineer,
	e.status as Result,b.name as Version,e.notes as Comment,e.execution_ts as testtime,e.execution_type from  nodes_hierarchy f,nodes_hierarchy s,nodes_hierarchy t 
	,nodes_hierarchy f1,executions e,users u,builds b where f.node_type_id='2' and f.parent_id in (SELECT id from  nodes_hierarchy where  id='".$projectid."') 
	and s.node_type_id='2' and s.parent_id=f.id and t.node_type_id='3' and t.parent_id=s.id and f1.node_type_id='4' and f1.parent_id=t.id  and 
	e.tcversion_id=f1.id and e.tester_id=u.id and e.build_id=b.id and e.testplan_id='".$tplan_id."'   order by e.execution_ts DESC  limit 999999) r GROUP BY r.Version) re on li.id=re.id order by substring_index(li.CaseName,'.',1)+0,substring_index(substring_index(li.CaseName,'.',-2),'.',1)+0,substring_index(li.CaseName,'.',-1)+0 ASC;";

	
	$result =$mysqli->query($testplan_sql);
	$data=$result->fetch_all();
	$mysqli->close();
	
	return $data;
}

require_once('../../third_party/codeplex/PHPExcel.php');
require_once('../../third_party/codeplex/PHPExcel/IOFactory.php');



$tplan_id = $_GET['tplan_id'];


session_start(); 
$testprojectID=$_SESSION['testprojectID'];

@include_once('../../config_db.inc.php');
	if( !defined('DB_HOST') ) {
		define('DB_HOST','localhost' );
	}
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);



mysqli_query($mysqli,"SET NAMES utf8");
if ($mysqli->connect_error) {
	die('Connect Error (' . $mysqli->connect_errno . ') '
			. $mysqli->connect_error);
}


$sql="SELECT name from  nodes_hierarchy where id='".$testprojectID."';";

$result =$mysqli->query($sql);
$re=$result->fetch_all();
$mysqli->close();
$projectname=$re[0][0];


$data = getData($testprojectID,$tplan_id);
downAction($projectname,$data);

?>
