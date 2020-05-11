{*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_tree_control.tpl,v 1.1.2.3 2010/11/22 09:46:26 asimon83 Exp $
 *
 * Shows some buttons which perform actions on the displayed tree.
 * Is included from filter panel template.
 *
 * @author Andreas Simon
 * @internal revision
 *}

{lang_get var=labels s='expand_tree,collapse_tree,copy,copy_test_plan,select,relation_test_plan,relation,btn_show_exec_history'}
{$aStyle="padding: 3px 15px;font-size:16px"}


<div class="x-panel-body exec_additional_info" style="padding:3px; padding-left: 9px;border:1px solid #99BBE8;">

<form action="lib/testcases/archiveData.php" method="post">
	<table border="0" >
		<tr>
			
			<td>{$labels.relation_test_plan}</td>
			
			<td>
				
				<script type="text/javascript">
					
					
					
					function relationtestplan(){
						
						var oS1=document.getElementsByName('relation');
						
						
						var testplanlist = JSON.parse(localStorage.getItem('testplanlist'));
						
						var length = testplanlist.length;
						html = new Array(length);
						var i=0;
						for(var p in testplanlist){
							html[i] = ['<option value="'+p+'">'+testplanlist[p]+'</option>'].join("");
							i++;
						}
						oS1[0].innerHTML+=html;
					}
					
					function addLoadEvent(func){ 
						var oldonload=window.onload; 
						if(typeof window.onload!='function'){ 
							window.onload=func; 
						} else{ 
							window.onload=function(){ 
								oldonload(); 
								func(); 
							} 
						} 
					} 
					addLoadEvent(relationtestplan);
				</script>
				<select name="relation" id="relation">
					<option value="">{$labels.select}</option>
				</select>
				<input type="hidden" name="relationcase" value="Ture" />
				<td colspan="2"><input type="submit" value="{$labels.relation}"></td>
				
			</td>
		</tr>
		<tr>
		</tr>
	</table>
</form>
<form action="lib/testcases/archiveData.php" method="post">
	<table border="0" >
		<tr>
			
			<td>{$labels.copy_test_plan}</td>
			
			<td>
				
				<script type="text/javascript">
					
					function copytestplan(){
						
						var oS1=document.getElementsByName('copy');
						
						
						var testplanlist = JSON.parse(localStorage.getItem('testplanlist'));
						
						var length = testplanlist.length;
						html = new Array(length);
						var i=0;
						for(var p in testplanlist){
							html[i] = ['<option value="'+p+'">'+testplanlist[p]+'</option>'].join("");
							i++;
						}
						oS1[0].innerHTML+=html;
					}
					
					function addLoadEvent(func){ 
						var oldonload=window.onload; 
						if(typeof window.onload!='function'){ 
							window.onload=func; 
						} else{ 
							window.onload=function(){ 
								oldonload(); 
								func(); 
							} 
						} 
					} 
					addLoadEvent(copytestplan);
				</script>
				<select name="copy" id="copy">
					<option value="">{$labels.select}</option>
				</select>
				<input type="hidden" name="copycase" value="Ture" />
				
				<td colspan="2"><input type="submit" value="{$labels.copy}"></td>
			</td>
		</tr>
		<tr>
		</tr>
	</table>
</form>

</div>
<div class="x-panel-body exec_additional_info" style="padding:3px; padding-left: 9px;border:1px solid #99BBE8;">
<script type="text/javascript">
					
	function queryhistory(){
		
		var cookie = document.cookie;

		var posName = cookie.indexOf("; currentcaseid=");


		var valueStart = cookie.indexOf("=", posName)+1;
		var valueEnd = cookie.indexOf(";", posName+1);
		if (valueEnd == -1) valueEnd = cookie.length;

		var value = cookie.substring(valueStart, valueEnd);
		var currentcaseid= unescape(value);
		javascript:openExecHistoryWindow(currentcaseid,1);

	}
	
	
</script>
<span>
    <input type="button" onclick="queryhistory();"value="{$labels.btn_show_exec_history}" />
</span>
</div>