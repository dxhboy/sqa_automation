{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_steps.tpl
            Shows the steps for a testcase in horizontal layout

@used-by inc_steps.tpl

@param $steps Array of the steps
@param $edit_enabled Steps links to edit page if true

@internal revisions
*}
  {if isset($add_exec_info) && $add_exec_info}
    {$inExec = 1}
  {else}
    {$inExec = 0}
  {/if}  
{lang_get var="labels" s="step_actions,step_type,step_tunnel,step_expected_results,step_notes"}
  <tr>
	
    <th width="5%" style="font-weight:bold;text-align:center;"><nobr>
    {if $edit_enabled && $steps != '' && !is_null($steps) && $args_frozen_version=="no"||$inExec}
      <img class="clickable" src="{$tlImages.reorder}" align="left"
           title="{$inc_steps_labels.show_hide_reorder}"
           onclick="showHideByClass('span','order_info');">
      <img class="clickable" src="{$tlImages.ghost_item}" align="left"
           title="{$inc_steps_labels.show_ghost_string}"
           onclick="showHideByClass('tr','ghost');">
    {/if}
    {$inc_steps_labels.step_number}
    </th>
	<th width="6%" style="font-weight:bold;text-align:center;">步骤</th>
	<!-- 标题栏 -->
    <th style="font-weight:bold;text-align:center;table-layout:fixed;word-break:break-all;">{$inc_steps_labels.step_actions} </th>
	
	<!-- 期望结果 -->
   
	{if !$inExec}
		<th  width="10%" style="font-weight:bold;text-align:center;">{$labels.step_type}</th>
	{/if}
    <th  width="8%" style="font-weight:bold;text-align:center;">{$labels.step_tunnel}</th>
	
	<th width="6%" >强制执行/忽略</th>
	
    {if $edit_enabled}
		<th>&nbsp;</th>
		<th>&nbsp;</th>
    {/if}

    {if $inExec}
      <th width="10%">{if $tlCfg->exec_cfg->steps_exec_notes_default == 'latest'}{$inc_steps_labels.latest_exec_notes}
          {else}{$inc_steps_labels.step_exec_notes}{/if}
          <img class="clickable" src="{$tlImages.clear_notes}" 
          onclick="javascript:clearTextAreaByClassName('step_note_textarea');" title="{$inc_steps_labels.clear_all_notes}"></th>

      <th width="3%">{$inc_steps_labels.step_exec_status}
       <img class="clickable" src="{$tlImages.reset}" 
          onclick="javascript:clearSelectByClassName('step_status');" title="{$inc_steps_labels.clear_all_status}"></th>
    {/if}    


  </tr>
  


  
  
  {$rowCount=$steps|@count} 
  {$row=0}

  {$att_ena = $inExec && $tlCfg->exec_cfg->steps_exec_attachments}

  {foreach from=$steps item=step_info}
	
	 <script type="text/javascript">
		
		function hide_or_show(step_num){
			var action_id = 'step_row_' + step_num;
			var exp_id = 'exp_step_row_' + step_num;
			
			var state = document.getElementById(step_num).firstElementChild.lastChild.value;
			if (state == "show") {
				document.getElementById(step_num).firstElementChild.lastChild.value="hide";
				document.getElementById(step_num).firstElementChild.lastChild.innerText = "show"; 
				document.getElementById(action_id).style.display= "none";
				document.getElementById(exp_id).style.display= "none";
			} else {
				document.getElementById(step_num).firstElementChild.lastChild.value="show";
				document.getElementById(step_num).firstElementChild.lastChild.innerText = "hide"; 
				document.getElementById(action_id).style.display= "";
				document.getElementById(exp_id).style.display= "";
			}
		}
		
	</script>
	
    <!-- 注释说明 -->
	{if $step_info.notes!=''||$inExec}
		<tr id="{$step_info.id}">
			<td align="center" style="vertical-align: middle">
			   {if $edit_enabled =="1" &&  $args_frozen_version=="no"}
				   <span class="order_info" >
					step&nbsp;{$step_info.step_number}</span>
			  {/if}
			  {if $edit_enabled && $args_frozen_version=="no"}
				<span class="order_info" style='display:none'>
				step&nbsp;<input type="text" class="step_number{$args_testcase.id}" name="step_set[{$step_info.id}]" id="step_set_{$step_info.id}"
				  value="{$step_info.step_number}"
				  size="{#STEP_NUMBER_SIZE#}"
				  maxlength="{#STEP_NUMBER_MAXLEN#}">
				{include file="error_icon.tpl" field="step_number"}</span>
			  
			  {/if}
			  {if $inExec}
				   <span class="order_info" >
					step&nbsp;{$step_info.id}</span>
			  {/if}
				<button  type="button" value="show" onclick="hide_or_show({$step_info.id})">hide/show</button>
			</td>
			<td style="font-weight:bold;background:#CCA;text-align:center;">{$labels.step_notes}</td>
			<td {if $edit_enabled && $args_frozen_version=="no"} style="font-weight:bold;background:#CCA;text-align:left;" onclick="launchEditStep({$step_info.id})" {/if}>{if $gui->stepDesignEditorType == 'none'}{$step_info.notes|nl2br}{else}{$step_info.notes}{/if}</td>

			{if !$inExec}
				<td {if $edit_enabled && $args_frozen_version=="no"} style="font-weight:bold;background:#CCA;text-align:left;" onclick="launchEditStep({$step_info.id})" {/if}>{$gui->automation_type[$step_info.automation_type]}</td>
			{/if}
			<td {if $edit_enabled && $args_frozen_version=="no"} style="font-weight:bold;background:#CCA;text-align:left;" onclick="launchEditStep({$step_info.id})" {/if}>{$step_info.spawn_id}</td>
			<td style="font-weight:bold;background:#CCA;text-align:left;">
				<select id="small_step_{$step_info.id}" name="{$step_info.id}" onchange="small_step_igofen(id,name)" >
					{if $step_info.ignore == 1}
						<option value="">---</option>
						<option value="{$step_info.enforce}">enforce</option>
						<option value="{$step_info.ignore}" selected="selected">ignore</option>
					{elseif $step_info.enforce == 1}
						<option value="">---</option>
						<option value="{$step_info.enforce}" selected="selected">enforce</option>
						<option value="{$step_info.ignore}">ignore</option>
					{else}
						<option value="" selected="selected">---</option>
						<option value="{$step_info.enforce}">enforce</option>
						<option value="{$step_info.ignore}">ignore</option>
					{/if}
				</select>
				{literal}
				<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
				<script type="text/javascript">
					function small_step_igofen(id,name){
						var obj = document.getElementById(id); // choose id
						var index = obj.selectedIndex; // choose index
						var selected= obj.options[index].text
						var post_data = {
							id:name,
							selected:selected,
						}
						$.ajax({
							type: 'POST',
							url: './gui/templates/testcases/igoren.php',
							data: post_data,
							success: function(msg) {
								console.log(id,name)
								console.log("succ")
							}
						});
					}
				</script>
				{/literal}
			</td>
			
			<!-- 删除和添加操作 -->
			{if $edit_enabled && $args_frozen_version=="no"}
		
			<td class="clickable_icon" style="font-weight:bold;background:#CCA;text-align:left;">
			  <img style="border:none;cursor: pointer;"
				   title="{$inc_steps_labels.delete_step}"
				   alt="{$inc_steps_labels.delete_step}"
				   onclick="delete_confirmation({$step_info.id},'{$step_info.step_number|escape:'javascript'|escape}',
												 '{$del_msgbox_title}','{$warning_msg}');"
				   src="{$tlImages.delete}"/>
			</td>
			
			<td class="clickable_icon" style="font-weight:bold;background:#CCA;text-align:left;">
			  <img style="border:none;cursor: pointer;"  title="{$inc_steps_labels.insert_step}"    
				   alt="{$inc_steps_labels.insert_step}"
				   onclick="launchInsertStep({$step_info.id});"    src="{$tlImages.insert_step}"/>
			</td>
		
			{/if}
			{if $inExec}
				<td class="exec_tcstep_note">
				<textarea class="step_note_textarea" name="step_notes[{$step_info.id}]" id="step_notes_{$step_info.id}" 
						  cols="40" rows="5">{$step_info.execution_notes|escape}</textarea>
				</td>

				<td>
				<select class="step_status" name="step_status[{$step_info.id}]" id="step_status_{$step_info.id}">
				  {html_options options=$gui->execStepStatusValues selected=$step_info.execution_status}

				</select> <br>

				{if $gui->tlCanCreateIssue}
				  {include file="execute/add_issue_on_step.inc.tpl" 
						   args_labels=$labels
						   args_step_id=$step_info.id}
				{/if}
				</td>
			
			{/if}
		</tr>
	{/if}
	<!-- 这里是用例步骤开始 -->
	<tr id="step_row_{$step_info.id}" style='display:none'>	
		
		<td>&nbsp;</td>
		<td style="font-weight:bold;background:#AFA;text-align:center;">
		  {$labels.step_actions}
		</td> 
		
		{if $step_info.actions!=''}
			<td colspan="4" style="font-weight:bold;background:#AFA;text-align:left;table-layout:fixed;word-break:break-all;" {if $edit_enabled && $args_frozen_version=="no"}  onclick="launchEditStep({$step_info.id})" {/if}>{if $gui->stepDesignEditorType == 'none'}{$step_info.actions|nl2br}{else}{$step_info.actions}{/if} </td>
		{else}
			<td style="font-weight:bold;background:#AFA;text-align:left;" onclick="launchEditStep({$step_info.id})" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		{/if}
		
		<!-- <td {if $edit_enabled && $args_frozen_version=="no"} style="font-weight:bold;background:#AFA;text-align:left;" onclick="launchEditStep({$step_info.id})" {/if}>{$gui->automation_type[$step_info.automation_type]}</td> -->
		<!-- <td {if $edit_enabled && $args_frozen_version=="no"} style="font-weight:bold;background:#AFA;text-align:left;" onclick="launchEditStep({$step_info.id})" {/if}>{$step_info.spawn_id}</td> -->
		<td style="font-weight:bold;background:#AFA;text-align:left;">&nbsp;</td>
		<td style="font-weight:bold;background:#AFA;text-align:left;">&nbsp;</td>
		
		
	</tr>	
    
	<!-- 期望值 -->
	{if $step_info.expected_results!=''}
		<tr id="exp_step_row_{$step_info.id}" style='display:none'>
			<td>&nbsp;</td>
			<td style="font-weight:bold;background:#AGA;text-align:center;">{$labels.step_expected_results}</td>
			<td {if $edit_enabled && $args_frozen_version=="no"} colspan="5" style="font-weight:bold;background:#AGA;text-align:left;table-layout:fixed;word-break:break-all;" onclick="launchEditStep({$step_info.id})" {/if}>{if $gui->stepDesignEditorType == 'none'}{$step_info.expected_results|nl2br}{else}{$step_info.expected_results}{/if}</td>
			<td style="font-weight:bold;background:#AGA;text-align:left;">&nbsp;</td>
			<td style="font-weight:bold;background:#AGA;text-align:left;">&nbsp;</td>
			<td style="font-weight:bold;background:#AGA;text-align:left;">&nbsp;</td>
			<td style="font-weight:bold;background:#AGA;text-align:left;">&nbsp;</td>
	   </tr>
   {/if}
	
	{if $inExec && $gui->tlCanCreateIssue} 
		<tr>
			<td>
			{include file="execute/issue_inputs_on_step.inc.tpl"
				   args_labels=$labels
				   args_step_id=$step_info.id}
			</td>
		</tr> 
	{/if}

 
	{if $gui->allowStepAttachments && $att_ena}
		<tr>
		  <td colspan=8>
		  {include file="attachments_simple.inc.tpl" attach_id=$step_info.id}
		  </td>
		</tr> 
	{/if} 
	{if $ghost_control}
		<tr class='ghost' style='display:none'><td></td><td>{$step_info.ghost_action}</td><td>{$step_info.ghost_result}</td></tr>    
	{/if}

    {$rCount=$row+$step_info.step_number}
    {if ($rCount < $rowCount) && ($rowCount>=1)}
      <tr>
        {if $session['testprojectOptions']->automationEnabled}
        <td colspan=6>
        {else}
        <td colspan=5>
        {/if}
        <hr align="center" width="100%" color="grey" size="1">
        </td>
      </tr>
    {/if}

  {/foreach}
