{capture name=form_field_id assign=form_field_id}{{sugarvar key='name'}}{/capture}
{capture name=inline_module assign=inline_module}{{sugarvar key='inline_module'}}{/capture}
{capture name=inline_link_table assign=inline_link_table}{{sugarvar key='inline_link_table'}}{/capture}
{capture name=inline_parent_link_field assign=inline_parent_link_field}{{sugarvar key='inline_parent_link_field'}}{/capture}
{capture name=inline_child_link_field assign=inline_child_link_field}{{sugarvar key='inline_child_link_field'}}{/capture}
{ibm_inline_one_to_many 
parent_id=$smarty.request.record 
link_table=$smarty.capture.inline_link_table 
parent_link_field=$smarty.capture.inline_parent_link_field 
child_link_field=$smarty.capture.inline_child_link_field
form_field_id=$smarty.capture.form_field_id
child_module=$smarty.capture.inline_module
}

{* special case for Users *}
{if $inline_module == "Users"}
	{assign var=module value="Employees"}
{else}
	{assign var=module value=$inline_module}
{/if}

{foreach from=$inline_one_to_many_populate item=record}
	<a href="index.php?module={$module}&action=DetailView&record={$record.id}">
	{$record.name}</a><br />
{/foreach}
