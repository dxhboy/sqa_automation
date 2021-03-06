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

{lang_get var=labels s='expand_tree, collapse_tree,btn_edit_tc,btn_edit,export_case_list'}
{$url_args="tcEdit.php?doAction=editStep&testcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{$aStyle="padding: 3px 15px;font-size:16px"}
<div class="x-panel-body exec_additional_info" style="padding:3px; padding-left: 9px;border:1px solid #99BBE8;">

<input type="button"
       value="{$labels.expand_tree}" 
       id="expand_tree" 
       name="expand_tree"
       onclick="tree.expandAll();"
       style="font-size: 90%;" />
	   
<input type="button"
       value="{$labels.collapse_tree}"
       id="collapse_tree"
       name="collapse_tree"
       onclick="tree.collapseAll();"
       style="font-size: 90%;" />
	   
<!-- <span> -->
      <!-- <a href="lib/functions/exportcaselist.php" target="export_case_list">{$labels.export_case_list}</a> -->
<!-- </span> -->

</div>


