{*
TestLink Open Source Project - http://testlink.sourceforge.net/

Viewer for massive delete of test cases inside a test suite

@filesource containerDeleteTC.tpl
20110402 - franciscom - BUGID 4322: New Option to block delete of executed test cases
20100910 - franciscom - BUGID 3047: Deleting multiple TCs
*}
{lang_get var='labels'
          s='th_test_case,th_id,title_move_cp,title_move_cp_testcases,sorry_further,
             check_uncheck_all_checkboxes,btn_delete,th_linked_to_tplan,th_version,
             platform,th_executed,choose_target,copy_keywords,btn_move,warning,btn_cp,
             execution_history,design,btn_edit_tc'}

{lang_get s='select_at_least_one_testcase' var="check_msg"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

{literal}

<script type="text/javascript">
{/literal}
//BUGID 3943: Escape all messages (string)
var alert_box_title = "{$labels.warning|escape:'javascript'}";
{literal}
/*
  function: check_action_precondition

  args :

  returns:

*/
function check_action_precondition(container_id,action,msg)
{
	var containerSelect = document.getElementById('containerID');
	if(checkbox_count_checked(container_id) > 0 && containerSelect.value > 0)
	{
	     return true;
	}
	else
	{
	   alert_message(alert_box_title,msg);
	   return false;
	}
}
function set_testcase_value(id)
{
	
	var name = document.getElementById(id).innerHTML;
	
	console.log(name);
	document.getElementById('edit_id').value=id;
	document.getElementById('testcase_name').value=name.trim();
}

</script>
{/literal}
</head>

<body>
{lang_get s=$level var=level_translated}
<h1 class="title">{$level_translated}{$smarty.const.TITLE_SEP}{$gui->object_name|escape} </h1>
<div> 
	<p>请选择所需修改的用例</p>
</div>
<form style="display: inline;" id="versionControls" name="versionControls" method="post" action="{$basehref}lib/testcases/archiveData.php">	   
	
	<div> 
		<input type="text" name="testcase_name" id="testcase_name"  
		  size="200" required 
		  maxlength="400"
		   value=""
		<p />
		
	</div>
	<input type="hidden" name="edit_id" id="edit_id" value="" />
	<input type="hidden" name="btn_edit_tc" value="Ture" />
	<input type="submit" name="edit_tc" 
	  value="{$labels.btn_edit_tc}" />
	
 </form>

  

<div class="workBack">
<h1 class="title">修改测试用例</h1>

{if $gui->op_ok == false}
	{$gui->user_feedback}
{else}
	<form id="delete_testcases" name="delete_testcases" method="post"
	      action="{$basehref}lib/testcases/containerEdit.php?objectID={$gui->objectID}">
		<input type="hidden" name="do_delete_testcases"  id="do_delete_testcases"  value="1" />

		{if $gui->user_feedback != ''}
		  <div class="user_feedback">{$gui->user_feedback}</div>
		  <br />
		{/if}
		{if $gui->system_message != ''}
		  <div class="user_feedback">{$gui->system_message}</div>
		  <br />
		{/if}

			{* need to do JS checks*}
		{* used as memory for the check/uncheck all checkbox javascript logic *}
		<input type="hidden" name="add_value_memory"  id="add_value_memory"  value="0" />
			<div id="delete_checkboxes">
			<table class="simple">
			  <tr>
			  <th class="clickable_icon">
						 <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
							  onclick='cs_all_checkbox_in_div("delete_checkboxes","tcaseSet_","add_value_memory");'
						title="{$labels.check_uncheck_all_checkboxes}" />
					</th>
			  <th>{$labels.th_test_case}</th>
			  </tr>
		
			{foreach from=$gui->testCaseSet key=rowid item=tcinfo}
				<tr>
					<td style="display:none" id={$tcinfo.id}> 
					{$tcinfo.name}
					</td>
					<td>
					</td>
					<td>
		
						<img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/edit_icon.png" 
							 onclick='set_testcase_value("{$tcinfo.id}");'/>
							 {$tcinfo.external_id|escape}{$gsmarty_gui->title_separator_1}{$tcinfo.name|escape}
					</td>
					
				</tr>
			   
			{/foreach}
			</table>
			<br />
		</div>

	</form>
{/if}

</body>
</html>