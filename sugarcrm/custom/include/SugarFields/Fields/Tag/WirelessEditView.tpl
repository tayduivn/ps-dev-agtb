{capture name=val assign=val}{sugarvar key='value' string=true}{/capture}
{capture name=default assign=default}{sugarvar key='default' string=true}{/capture}
<input
type="hidden"
id="{$vardef.name}_multiselect"
name="{$vardef.name}_multiselect"
value="true">
{multienum_to_array string=$val default=$default assign="values"}
<select
id="{$vardef.name}"
name="{$vardef.name}[]"
multiple="true"
style="width:150"
tabindex="{$tabindex}">
{html_options options=$all_tags_arr selected=$selected}
</select>
