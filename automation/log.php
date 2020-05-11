<!--<br><br>Code highlighting produced by Actipro CodeHighlighter (freeware)<br>http://www.CodeHighlighter.com/<br><br>-->
<?php

 //----------------you should save this file as m.php----------------

  session_start(); 
  if (empty($page)) {$page=1;}
  if (isset($_GET['page'])==TRUE) {$page=$_GET['page']; }
  if (isset($_GET['data'])==TRUE) {
	$data=$_GET['data'];	
	} 
  if (isset($_GET['file'])==TRUE) {
	$file=$_GET['file']; 

  } 
  if (isset($_GET['title'])==TRUE) {
	$title=$_GET['title']; 
	$titlename = strtr($title, ' ', '-%'); // zesz
	
  } 
$filename="./log/".$data."/".$file;

$title = strtr($title, '-%', ' '); // zesz
echo '<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
<title>Read Result</title> 
<style type="text/css"> 

    body{
    }
    table .table-striped{
    }
    table th{
        text-align: left;
        height: 30px;
        background: #deeeee;
        padding: 5px;
        margin: 0;
        border: 0px;
    }
    table td{
        text-align: left;
        height:30px;
        margin: 0;
        padding: 5px;
        border:0px
    }
    table tr:hover{
        background: #eeeeee;
    }
    .span6{
        /*width:500px;*/
        float:inherit;
        margin:10px;
    }
    #pagiDiv span{
        background:#1e90ff;
        border-radius: .2em;
        padding:5px;
    }
	.colorGreen{
		color: green;
	}
	.colorRed{
		color: red;
	}
	.colorOrange{
		color: orange;
	}
	.colorYellow{
		color: yellow;
	}
	.colorBlue{
		color: blue;
	}
	.colorIndigo{
		color: indigo;
	}
	.colorBlack{
		color: black;
	}

.STYLE1 {font-size: 16px;text-align: center;display:block;} 
.STYLE2 {font-size: 16px;text-align: right;display:block;} 

</style> 
</head> 
<body> 
<table align="center" width="80%" bgcolor="#CCCCCC"> 
<h1 align="center">'.$title.'自动化测试结果</h1>
<tr> 

<td width="20%"> ';
 
if($page){
	if (file_exists($filename)) {
		$colorGreen='<span class="colorGreen">';
		$colorRed='<span class="colorRed">';
		$colorBlue='<span class="colorBlue">';
		$colorIndigo='<span class="colorIndigo">';
		$colorYellow='<span class="colorYellow">';
		$colorBlack='<span class="colorBlack">';
		// $counter=file_get_contents($filename); //-------read the file into a string.-------
		$cbody = file($filename); 
		
		for($i=0;$i<count($cbody);$i++){ //count函数就是获取数组的长度的，长度为3 因为一行被识别为一个数组 有三行
			if(strpos($cbody[$i],'Result:    PASSED') !==false) {
				echo $colorGreen.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'Success') !==false) { 
				echo $colorGreen.$cbody[$i].'</span></br>';
			}  elseif (strpos($cbody[$i],'Error') !==false) { 
				if (strpos($cbody[$i],'Errors: 0') !==false) {
					echo $colorBlack.$cbody[$i].'</span></br>';
				} else {
					echo $colorRed.$cbody[$i].'</span></br>';
				}
				
			} elseif (strpos($cbody[$i],'Result:    ABORTED') !==false) { 
				echo $colorRed.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'FAIL') !==false) { 
				echo $colorRed.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'Fail') !==false) { 
				echo $colorRed.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'fail') !==false) { 
				echo $colorRed.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'=== show') !==false) { 
				echo $colorBlue.$cbody[$i].'</span></br>';
				
			} elseif (strpos($cbody[$i],'=== clear') !==false) { 
				echo $colorBlue.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'SendAndExpect') !==false) { 
				echo $colorBlue.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'Info') !==false) { 
				echo $colorIndigo.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'run') !==false) { 
				echo $colorBlue.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'filter') !==false) { 
				echo $colorBlue.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'mirror') !==false) { 
				echo $colorBlue.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'green') !==false) { 
				echo $colorGreen.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'blue') !==false) { 
				echo $colorBlue.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'red') !==false) { 
				echo $colorRed.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'indigo') !==false) { 
				echo $colorIndigo.$cbody[$i].'</span></br>';
				
			} elseif (strpos($cbody[$i],'PASS') !==false) { 
				echo $colorGreen.$cbody[$i].'</span></br>';
			} elseif (strpos($cbody[$i],'Warnings') !==false) { 
				
				echo $colorYellow.$cbody[$i].'</span></br>';
			} else {
				echo $colorBlack.$cbody[$i].'</span></br>';
			}
		}

	} else {
		echo "The log does not exist please check!! <br>";
	}	 
} 
echo '
</td> 

</tr> 
</table> 
  
<table align="center" width="80%" bgcolor="#cccccc"> 
<tr> ';



echo '<td  width="10%" align="right" valign="middle"><button><a href = "download.php?f='.$filename.'">下载日志</button></td>
</tr> 
</table> 
</body> 
</html>';

?>