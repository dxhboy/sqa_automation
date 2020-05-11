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

{lang_get var=labels s='expand_tree, collapse_tree'}


<div class="x-panel-body exec_additional_info" style="padding:3px; padding-left: 9px;border:1px solid #99BBE8;">

<form action="lib/testcases/archiveData.php" method="post">
	<table border="0" >
		<tr>
			
			<td>选择关联用例</td>
			
			<td>
				
				<script type="text/javascript">
					
					
					
					window.onload=function(testplanlist){
						
						var oS1=document.getElementsByName('input_casename');
						
						
						var testplanlist = JSON.parse(localStorage.getItem('testplanlist'));
						<!-- console.log(testplanlist); -->
						
						
						for(var p in testplanlist){
							
							oS1[0].innerHTML+='<option value="'+p+'">'+testplanlist[p]+'</option>';
						}
					   
						
					}
				</script>
				<select name="input_casename" id="input_casename">
					<option value="">--请选择--</option>
				</select>
				<input type="hidden" name="update_flag" value="Ture" />
				<td colspan="2"><input type="submit" value="确认"></td>
			</td>
		</tr>
		<tr>
		</tr>
	</table>
</form>

