{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* @filesource frmInner.tpl *}
{* Purpose: smarty template - inner frame for workarea *}
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$pageCharset}" />
	<meta http-equiv="Content-language" content="en" />
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="generator" content="testlink" />
	<meta name="author" content="Martin Havlat" />
	<meta name="copyright" content="GNU" />
	<meta name="robots" content="NOFOLLOW" />
	<base href="{$basehref}" />
	<title>TestLink Inner Frame</title>
	<style media="all" type="text/css">@import "{$css}";</style>
	<script type="text/javascript">
		
		initialization();
		function initialization(){
			var xhr = new XMLHttpRequest;
			xhr.open('post', 'lib/testcases/archiveData.php');
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.send(JSON.stringify("testplan"));
			xhr.onreadystatechange = function() {
				if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status ==304)) { //响应完成并且响应码为200或304
					var testplanlist=xhr.responseText;
					
					
					localStorage.setItem('testplanlist', testplanlist);
					
				}
			};
		}
	</script>
</head>

<frameset cols="{$treewidth|default:"30%"},*" border="5" frameborder="10" framespacing="1">
	<frame src="{$treeframe}" name="treeframe" scrolling="auto" />
	<frame src="{$workframe}" name="workframe" scrolling="auto" />
</frameset>

</html>
