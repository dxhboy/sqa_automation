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
		
		$counter=file_get_contents($filename); //-------read the file into a string.-------
		$length=strlen($counter); 
		$page_count=ceil($length/3000); 
		function msubstr($str,$start,$len){ 
		  $strlength=$start+$len; 
		  $tmpstr="";
		  $tmpstr2="";
		  $colorGreen='<br><span class="colorGreen">';
		  $colorRed='<br><span class="colorRed">';
		  $colorBlue='<br><span class="colorBlue">';
		  $colorIndigo='<br><span class="colorIndigo">';
		  $colorYellow='<br><span class="colorYellow">';
		  for($i=0;$i<$strlength;$i++) { 
			  if(ord(substr($str,$i,1))==0x0a) { 
					// file_put_contents("aa.txt", $tmpstr2."\n", FILE_APPEND);
					// file_put_contents("aa.txt", "##############\n", FILE_APPEND);
					if( strpos($tmpstr2,'Result:    PASSED') !==false) {
						$tmpstr.=$colorGreen.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'Success') !==false) { 
						$tmpstr.=$colorGreen.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'Error') !==false) { 
						if (strpos($tmpstr2,'Errors: 0') !==false) {
							$tmpstr.=$tmpstr2.'<br />';
						} else {
							$tmpstr.=$colorRed.$tmpstr2.'</span><br />';
						}
					} elseif (strpos($tmpstr2,'Result:    ABORTED') !==false) { 
						$tmpstr.=$colorRed.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'FAIL') !==false) { 
						$tmpstr.=$colorRed.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'Fail') !==false) { 
						$tmpstr.=$colorRed.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'fail') !==false) { 
						$tmpstr.=$colorRed.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'=== show') !==false) { 
						$tmpstr.=$colorBlue.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'=== clear') !==false) { 
						$tmpstr.=$colorBlue.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'run') !==false) { 
						$tmpstr.=$colorBlue.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'filter') !==false) { 
						$tmpstr.=$colorBlue.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'mirror') !==false) { 
						$tmpstr.=$colorBlue.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'green') !==false) { 
						$tmpstr.=$colorGreen.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'blue') !==false) { 
						$tmpstr.=$colorBlue.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'red') !==false) { 
						$tmpstr.=$colorRed.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'indigo') !==false) { 
						$tmpstr.=$colorIndigo.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'PASS') !==false) { 
						$tmpstr.=$colorGreen.$tmpstr2.'</span><br />';
					} elseif (strpos($tmpstr2,'Warnings') !==false) { 
						$tmpstr.=$colorYellow.$tmpstr2.'</span><br />';
					} else{ 
						$tmpstr.=$tmpstr2.'<br />';
					}

					$tmpstr2="";
				}
				if(ord(substr($str,$i,1))>0xa0) { 
					$tmpstr2.=substr($str,$i,2); 
					$i++; 
				} else{ 
					$tmpstr2.= substr($str,$i,1); } 
				} 
			return $tmpstr; 
		} 
		//--------------------------截取中文字符串-------------------------- 
		$c=msubstr($counter,0,($page-1)*3000); 
		$c1=msubstr($counter,0,$page*3000); 

		echo substr($c1,strlen($c),strlen($c1)-strlen($c));
	} else {
		echo "The log does not exist please check!! <br>";
	}	 
} 
echo '
</td> 

</tr> 
</table> 
  
<table align="center" width="80%" bgcolor="#cccccc"> 
<tr> 

<td  width="50%" align="center" valign="middle"><span class="STYLE2"> ';
 echo $page.'/'; echo $page_count.'页 </span></td> 
<td width="40%" height="28" align="center" valign="middle">
<span class="STYLE1">';

echo "<a href=log.php?page=1&data=".$data."&file=".$file."&title=".$titlename.">首页</a> "; 
if($page!=1){ 
  echo "<a href=log.php?page=".($page-1)."&data=".$data."&file=".$file."&title=".$titlename.">上一页</a> "; 
} 
if($page<$page_count){ 
  echo "<a href=log.php?page=".($page+1)."&data=".$data."&file=".$file."&title=".$titlename.">下一页</a> "; 
}
echo "<a href=log.php?page=".$page_count."&data=".$data."&file=".$file."&title=".$titlename.">尾页</a>"; 

echo '</span> </td> 
<td  width="10%" align="right" valign="middle"><button><a href = "download.php?f='.$filename.'">下载日志</button></td>
</tr> 
</table> 
</body> 
</html>';

?>