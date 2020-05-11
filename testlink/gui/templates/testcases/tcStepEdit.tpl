{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource tcStepEdit.tpl
Purpose: create/edit test case step
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{$module='lib/testcases/'}
{$tcase_id=$gui->tcase_id}
{$tcversion_id=$gui->tcversion_id}

{* Used on several operations to implement goback *}
{$showMode=$gui->show_mode} 

{$tcViewAction="lib/testcases/archiveData.php?tcase_id=$tcase_id&show_mode=$showMode"}
{$goBackAction="$basehref$tcViewAction"}
{$goBackActionURLencoded=$goBackAction|escape:'url'}
{$url_args="tcEdit.php?doAction=editStep&testcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{$url_args="$url_args&goback_url=$goBackActionURLencoded&step_id="}
{$hrefEditStep="$basehref$module$url_args"}

{lang_get var="labels"
          s="warning_step_number_already_exists,warning,warning_step_number,btn_save_and_exit,btn_save_and_insert,
             expected_results,step_actions,step_number_verbose,btn_cancel,btn_create_step,ghost,
             show_ghost_string,display_author_updater,
             btn_create,btn_cp,btn_copy_step,btn_save,cancel,warning_unsaved,step_number,execution_type_short_descr,
             title_created,version,by,summary,preconditions,title_last_mod,step_actions,step_expected_results,step_notes"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript" src="gui/javascript/ext_extensions.js" language="javascript"></script>
<script type="text/javascript">
var warning_step_number = "{$labels.warning_step_number|escape:'javascript'}";
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_step_number_already_exists = "{$labels.warning_step_number_already_exists|escape:'javascript'}";

function validateForm(the_form,step_set,step_number_on_edit)
{
  var value = '';
  var status_ok = true;
  var feedback = '';
  var value_found_on_set=false;
  var value_step_mistmatch=false;
  value = parseInt(the_form.step_number.value);

  if( isNaN(value) || value <= 0)
  {
    alert_message(alert_box_title,warning_step_number);
    selectField(the_form,'step_number');
    return false;
  }

  if( step_set.length > 0 )
  {
    value_found_on_set = (step_set.indexOf(value) >= 0);
    value_step_mistmatch = (value != step_number_on_edit);
    if(value_found_on_set && value_step_mistmatch)
    {
      feedback = warning_step_number_already_exists.replace('%s',value);
      alert_message(alert_box_title,feedback);
      selectField(the_form,'step_number');
      return false;
    }
  }
  return Ext.ux.requireSessionAndSubmit(the_form);
}
</script>

{if $tlCfg->gui->checkNotSaved}
<script type="text/javascript">
var unload_msg = "{$labels.warning_unsaved|escape:'javascript'}";
var tc_editor = "{$gui->editorType}";
</script>
<script src="gui/javascript/checkmodified.js" type="text/javascript"></script>

{/if}
</head>

{if $gui->action == 'createStep' || $gui->action == 'doCreateStep'}
  {$scrollPosition='new_step'}
{else}
  {$stepToScrollTo=$gui->step_number}
  {$scrollPosition="step_row_$stepToScrollTo"}
{/if}

<body onLoad="scrollToShowMe('{$scrollPosition}')">
<h1 class="title">{$gui->main_descr}</h1> 

	<div class="workBack" style="width:98.6%;">

		{if $gui->user_feedback != ''}
		  <div>
			<p class="info">{$gui->user_feedback}</p>
		  </div>
		{/if}

		{if $gui->has_been_executed}
			{lang_get s='warning_editing_executed_step' var="warning_edit_msg"}
			<div class="messages" align="center">{$warning_edit_msg}</div>
		{/if}

		<form method="post" action="{$basehref}lib/testcases/tcEdit.php" name="tcStepEdit"
			  onSubmit="return validateForm(this,'{$gui->step_set}',{$gui->step_number});">

		  <input type="hidden" name="testcase_id" value="{$gui->tcase_id}" />
		  <input type="hidden" name="tcversion_id" value="{$gui->tcversion_id}" />
		  <input type="hidden" name="doAction" value="" />
		  <input type="hidden" name="show_mode" value="{$gui->show_mode}" />
		  <input type="hidden" name="step_id" value="{$gui->step_id}" />
		  <input type="hidden" name="step_number" value="{$gui->step_number}" />
		  <input type="hidden" name="goback_url" value="{$goBackAction}" />


			{include file="testcases/inc_tcbody.tpl" 
					 inc_tcbody_close_table=true
					 inc_tcbody_testcase=$gui->testcase
					 inc_tcbody_show_title="yes"
					 inc_tcbody_tableColspan=2
					 inc_tcbody_labels=$labels
					 inc_tcbody_author_userinfo=$gui->authorObj
					 inc_tcbody_updater_userinfo=$gui->updaterObj
						   inc_tcbody_editor_type=$gui->editorType
					 inc_tcbody_cf=null}



		  {* when save or cancel is pressed do not show modification warning *}
		  <div class="groupBtn">
			<input id="do_update_step" type="submit" name="do_update_step" 
				   onclick="show_modified_warning=false; doAction.value='{$gui->operation}'" 
				   value="{$labels.btn_save}" />

			<input id="do_update_step_and_exit" type="submit" name="do_update_step_and_exit" 
				   onclick="show_modified_warning=false; doAction.value='{$gui->operation}AndExit'" 
				   value="{$labels.btn_save_and_exit}" />

			{if $gui->operation == 'doUpdateStep'}
			  <input id="do_create_step" type="submit" name="do_create_step" 
					 onclick="doAction.value='createStep'" value="{$labels.btn_create_step}" />

			  <input id="do_copy_step" type="submit" name="do_copy_step" 
					 onclick="doAction.value='doCopyStep'" value="{$labels.btn_copy_step}" />
			{/if}

			<input type="button" name="cancel" value="{$labels.btn_cancel}"
				   {if $gui->goback_url != ''}  onclick="show_modified_warning=false; location='{$gui->goback_url}';"
				   {else}  onclick="show_modified_warning=false; javascript:history.back();" {/if} />
		  </div>  

			<table class="simple">
				{if $gui->steps_results_layout == "horizontal"}
					<tr>
						
						<th width="5%">{$labels.step_number}</th>
						{* Julian: added width to show columns step details and expected
						* results at approximately same size (step details get 45%
						* expected results get the rest)
						*}
						<th >{$labels.step_actions}</th>
						
					</tr>
			  
					{* this means we have steps to display *}
					{if $gui->tcaseSteps != ''}
						{$rowCount=$gui->tcaseSteps|@count} 
						{$row=0}
				
						{foreach from=$gui->tcaseSteps item=step_info}
							
							<!-- 编辑框 --> 
							{if $step_info.step_number == $gui->step_number }
								{if $gui->action == 'createStep' || $gui->action == 'doCreateStep'}
								<!-- 避免在创建步骤是两个框都出现 -->
								{else}
									
									<!-- 标题 -->
									<tr >
										{if $session['testprojectOptions']->automationEnabled}
										
											<td width="5%">
											 <select name="automation_type" onchange="content_modified = true">
												{html_options options=$gui->automation_type selected=$gui->step_automation_type}
											</select>
											</td>
											<td ><input type="text" id="spawn_id" name="spawn_id" align="left"  style="width:330px;height: 12px;background: #74BA74;border-radius:10px;text-align: left;color: #gfg" value="{$step_info.spawn_id}"></td>
										{/if}
									</tr>
									<!-- 详情 -->
									<tr id="step_row_{$step_info.step_number}" >
									 
										<td width="5%" style="word-break:break-all;font-weight:bold;background:#AFA;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;{$gui->step_number}&nbsp;{$labels.step_actions}</td>
										<td>{$steps}</td>
										
									</tr>
									<!-- 注释说明 -->
									
									<tr>
										<td width="5%" style="font-weight:bold;background:#CGA;text-align:left;">{$labels.step_expected_results}</td>
									
										<td >{$expected_results}</td>
										
									</tr>
									
									<tr >
										<td width="5%" style="font-weight:bold;background:#CCA;text-align:left;">{$labels.step_notes}</td>
										
										<td>{$notes}
											<div class="groupBtn">
											<input id="do_update_step" type="submit" name="do_update_step" 
												   onclick="show_modified_warning=false; doAction.value='{$gui->operation}'" 
												   value="{$labels.btn_save}" />

											<input type="submit" id="do_update_step_and_insert" name="do_update_step_and_insert" 
												   onclick="show_modified_warning=false; doAction.value='{$gui->operation}AndInsert'" 
												   value="{$labels.btn_save_and_insert}" />
											

											<input id="do_update_step_and_exit" type="submit" name="do_update_stepand_exit" 
												   onclick="show_modified_warning=false; doAction.value='{$gui->operation}AndExit'" 
												   value="{$labels.btn_save_and_exit}" />

											{if $gui->operation == 'doUpdateStep'}
											  <input id="do_copy_step" type="submit" name="do_copy_step" 
													 onclick="doAction.value='doCopyStep'" value="{$labels.btn_cp}" />
											{/if}

											<input type="button" name="cancel" value="{$labels.btn_cancel}"
												   {if $gui->goback_url != ''}  onclick="show_modified_warning=false; location='{$gui->goback_url}';"
												   {else}  onclick="show_modified_warning=false; javascript:history.back();" {/if} />
										  </div>  
								
										</td>
										
									</tr>
									
								{/if}
							
							{else}
								
								
								<!-- 步骤详情 -->
								
								<tr>
									{if $session['testprojectOptions']->automationEnabled}
									 
										<td width="5%" style="font-weight:bold;background:#AFF;text-align:left;" onclick="launchEditStep({$step_info.id})" >{$gui->automation_type[$step_info.automation_type]}</td>
									{/if}
									<td  style="font-weight:bold;background:#AFF;text-align:left;"><a href="{$hrefEditStep}{$step_info.id}">{$step_info.spawn_id}</a></td> 
								</tr>
								<tr>
									<td width="5%" style="font-weight:bold;background:#AFA;text-align:left;"><a href="{$hrefEditStep}{$step_info.id}">{$step_info.step_number}&nbsp;{$labels.step_actions}</a></td>
									<td style="font-weight:bold;background:#AFA;text-align:left;"><a href="{$hrefEditStep}{$step_info.id}">{$step_info.actions}</a></td>
								</tr>
								
								<!-- 预期结果 -->
								{if $step_info.expected_results!=''}
									<tr>
										<td width="5%" style="font-weight:bold;background:#CGA;text-align:left;">&nbsp;{$labels.step_expected_results}</td>
										<td style="font-weight:bold;background:#CGA;text-align:left;"><a href="{$hrefEditStep}{$step_info.id}">{$step_info.expected_results}</a></td>
										
									</tr>
								{/if}
								<!-- 注释说明 -->
								{if $step_info.notes!=''}
									<tr>
										<td width="5%" style="font-weight:bold;background:#CCA;text-align:left;">&nbsp;{$labels.step_notes}</td>
										<td style="font-weight:bold;background:#CCA;text-align:left;"><a href="{$hrefEditStep}{$step_info.id}">{$step_info.notes}</a></td>
										
										
									</tr>
								{/if}
							{/if}
							{$rCount=$row+$step_info.step_number}
							{if ($rCount < $rowCount) && ($rowCount>=1)}
								<tr width="100%">
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
					{/if}
				{else} 

					{* Vertical layout *}
					{foreach from=$gui->tcaseSteps item=step_info}
						<tr id="step_row_{$step_info.step_number}">
						<!-- 标题 -->
						<th style="font-weight:bold;background:#AFA;text-align:left;">{$args_labels.step_number} {$step_info.step_number}</th>
						<th>{$labels.step_actions}</th>
						{if $session['testprojectOptions']->automationEnabled}
							{if $step_info.step_number == $gui->step_number}
								<th width="200">{$labels.execution_type_short_descr}:
									<select name="exec_type" onchange="content_modified = true">
										{html_options options=$gui->automation_type selected=$gui->step_automation_type}
									</select>
								</th>
							{else}
								<th>{$labels.execution_type_short_descr}:
								{$gui->automation_type[$step_info.automation_type]}</th>
								
							{/if}
						{else}
							<th>&nbsp;</th>
						{/if} {* automation *}
						{if $edit_enabled}
						  <th>&nbsp;</th>
						{/if}
						</tr>
						
						<tr>
							<td style="font-weight:bold;background:#AFA;text-align:left;">&nbsp;</td>
							{if $step_info.step_number == $gui->step_number}
								<td colspan="2">{$steps}</td>
							{else}
								<td colspan="2"><a href="{$hrefEditStep}{$step_info.id}">{$step_info.actions}</a></td>
							{/if}
						</tr>
						<tr>
							<th style="background: transparent; border: none"></th>
							<th colspan="2">{$labels.expected_results}</th>
						</tr>
						<tr>
							<td>&nbsp;</td>
							{if $step_info.step_number == $gui->step_number}
								<td colspan="2">{$expected_results}</td>
							{else}
								<td colspan="2" style="padding: 0.5em 0.5em 2em 0.5em">
								<a href="{$hrefEditStep}{$step_info.id}">{$step_info.expected_results}</a></td>
							{/if}
						</tr>
					{/foreach}
				{/if}

				{if $gui->action == 'createStep' || $gui->action == 'doCreateStep'}
					{* We have forgotten to manage layout here *}
					{if $gui->steps_results_layout == "horizontal"}
						<tr id="new_step">
							<td width="5%">
								<select name="automation_type" onchange="content_modified = true">
									{html_options options=$gui->automation_type selected=$gui->step_automation_type}
								</select>
							</td>
							<td ><input type="text" id="spawn_id" name="spawn_id" align="left"  style="width:330px;height: 12px;background: #74BA74;border-radius:10px;text-align: left;color: #gfg" value=""></td>
						
						</tr>
						<tr id="new_step">
							
							<td width="5%" style="font-weight:bold;background:#AFA;text-align:left;" >{$step_info.step_number+1}&nbsp;{$labels.step_actions}</td>
							<td>{$steps}</td>
							
						</tr>
						
						<!-- 步骤详情 -->
						<tr>
							<td width="5%" style="font-weight:bold;background:#CGA;text-align:left;">{$labels.step_expected_results}</td>
							<td>{$expected_results}</td>
							<!-- <td>&nbsp;</td> -->
							<!-- <td>&nbsp;</td> -->
						</tr>
						<!-- 注释说明 -->
						<tr>
							<td width="5%" style="font-weight:bold;background:#CCA;text-align:left;">{$labels.step_notes}</td>
							<td>{$notes}</td>
							
						</tr>
					{else}
						<tr id="new_step">

							<th width="20">{$args_labels.step_number} {$gui->step_number}</th>
							<th>{$labels.step_actions}</th>
							{if $session['testprojectOptions']->automationEnabled}
								<th width="200">{$labels.execution_type_short_descr}:
									<select name="exec_type" onchange="content_modified = true">
										{html_options options=$gui->automation_type selected=$gui->step_automation_type}
									</select>
								</th>
							{/if}
							<tr>
								<td>&nbsp;</td>
								<td colspan="2">{$steps}</td>
							</tr>
							<tr>
								<th style="background: transparent; border: none"></th>
								<th colspan="2">{$labels.expected_results}</th>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td colspan="2" style="padding: 0.5em 0.5em 2em 0.5em"> {$expected_results}</td>
							</tr>
						</tr>
					{/if}
				{/if}
			</table>  
		  <p>
		  {* when save or cancel is pressed do not show modification warning *}
			<div class="groupBtn">
				<input id="do_update_step" type="submit" name="do_update_step" 
					   onclick="show_modified_warning=false; doAction.value='{$gui->operation}'" value="{$labels.btn_save}" />

				<input id="do_update_step_and_exit" type="submit" name="do_update_step_and_exit" 
					   onclick="show_modified_warning=false; doAction.value='{$gui->operation}AndExit'" 
					   value="{$labels.btn_save_and_exit}" />

				{if $gui->operation == 'doUpdateStep'}
				  <input id="do_create_step" type="submit" name="do_create_step" 
						 onclick="doAction.value='createStep'" value="{$labels.btn_create_step}" />

				  <input id="do_copy_step" type="submit" name="do_copy_step" 
						 onclick="doAction.value='doCopyStep'" value="{$labels.btn_copy_step}" />
				{/if}

				<input type="button" name="cancel" value="{$labels.btn_cancel}"
					   {if $gui->goback_url != ''}  onclick="show_modified_warning=false; location='{$gui->goback_url}';"
					   {else}  onclick="show_modified_warning=false; javascript:history.back();" {/if} />
			</div>  
		</form>

	</div>
</body>
</html>
