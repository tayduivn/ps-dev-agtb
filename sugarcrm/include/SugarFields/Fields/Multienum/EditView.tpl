{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
{{if empty($vardef.autocomplete)}}

<input type="hidden" id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}_multiselect"
name="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}_multiselect" value="true">
{multienum_to_array string={{sugarvar key='value' string=true}} default={{sugarvar key='default' string=true}} assign="values"}
<select id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"
name="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}[]"
multiple="true" size='{{$displayParams.size|default:6}}' style="width:150" title='{{$vardef.help}}' tabindex="{{$tabindex}}">
{html_options options={{sugarvar key='options' string=true}} selected=$values}
</select>

{{else}}

{assign var="field_options" value={{sugarvar key='options' string="true"}} }
{capture name="field_val"}{{sugarvar key='value'}}{/capture}
{assign var="field_val" value=$smarty.capture.field_val}
{capture name="ac_key"}{{sugarvar key='name'}}{/capture}
{assign var="ac_key" value=$smarty.capture.ac_key}


<input type="hidden"
    id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"
    name="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"
    value="{{sugarvar key='value'}}">

<input
    id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}-input"
    name="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}-input"
    size="60"
	value="{$field_val|multienum_to_ac:$field_options}"
    type="text" style="vertical-align: top;"> <img src="{sugar_getimagepath file="down_arrow.png"}" id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}-image">

{literal}
<script>

SUGAR.AutoComplete.{/literal}{$ac_key}{literal} = [];

{/literal}
{{if empty($vardef.autocomplete_ajax)}}
SUGAR.AutoComplete.{$ac_key}.ds = SUGAR.AutoComplete.getSourceFromOptions("{{$vardef.autocomplete_options}}");
{{else}}
{literal}
// Create a new YUI instance and populate it with the required modules.
YUI().use('datasource', 'datasource-jsonschema', function (Y) {
	// DataSource is available and ready for use.
	SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.ds = new Y.DataSource.Get({
		source: 'index.php?module=Accounts&action=ajaxautocomplete&to_pdf=1'
	});
	SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.ds.plug(Y.Plugin.DataSourceJSONSchema, {
		schema: {
			resultListLocator: "option_items",
			resultFields: ["text", "key"],
			matchKey: "text",
		}
	});
});
{/literal}
{{/if}}
{literal}

YUI().use("autocomplete", "autocomplete-filters", "autocomplete-highlighters", function (Y) {
	{/literal}
	
    SUGAR.AutoComplete.{$ac_key}.inputNode = Y.one('#{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}-input');
    SUGAR.AutoComplete.{$ac_key}.inputImage = Y.one('#{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}-image');
    SUGAR.AutoComplete.{$ac_key}.inputHidden = Y.one('#{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}');

{{if empty($vardef.autocomplete_ajax)}}
	SUGAR.AutoComplete.{$ac_key}.minQLen = 0;
	SUGAR.AutoComplete.{$ac_key}.queryDelay = 0;
	SUGAR.AutoComplete.{$ac_key}.numOptions = {$field_options|@count};
	if(SUGAR.AutoComplete.{$ac_key}.numOptions >= 300) {literal}{
		{/literal}
		SUGAR.AutoComplete.{$ac_key}.minQLen = 1;
		SUGAR.AutoComplete.{$ac_key}.queryDelay = 200;
		{literal}
	}
	{/literal}
	if(SUGAR.AutoComplete.{$ac_key}.numOptions >= 3000) {literal}{
		{/literal}
		SUGAR.AutoComplete.{$ac_key}.minQLen = 1;
		SUGAR.AutoComplete.{$ac_key}.queryDelay = 500;
		{literal}
	}
	{/literal}
{{else}}
	SUGAR.AutoComplete.{$ac_key}.minQLen = 1;
	SUGAR.AutoComplete.{$ac_key}.queryDelay = 500;
{{/if}}
	{literal}
	
    SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.plug(Y.Plugin.AutoComplete, {
        activateFirstItem: true,
        allowTrailingDelimiter: true,
		{/literal}
        minQueryLength: SUGAR.AutoComplete.{$ac_key}.minQLen,
        queryDelay: SUGAR.AutoComplete.{$ac_key}.queryDelay,
        queryDelimiter: ',',
        zIndex: 99999,

{{if !empty($vardef.autocomplete_ajax)}}
		requestTemplate: '&options={{$vardef.autocomplete_options}}&q={literal}{query}{/literal}',
{{/if}}
		{literal}
		source: SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.ds,
		
        resultTextLocator: 'text',
        resultHighlighter: 'phraseMatch',
        resultFilters: 'phraseMatch',
        // Chain together a startsWith filter followed by a custom result filter
        // that only displays tags that haven't already been selected.
        resultFilters: ['phraseMatch', function (query, results) {
            // Split the current input value into an array based on comma delimiters.
            var selected = SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.ac.get('value').split(/\s*,\s*/);

            // Pop the last item off the array, since it represents the current query
            // and we don't want to filter it out.
            selected.pop();

            // Convert the array into a hash for faster lookups.
            selected = Y.Array.hash(selected);

            // Filter out any results that are already selected, then return the
            // array of filtered results.
            return Y.Array.filter(results, function (result) {
               return !selected.hasOwnProperty(result.text);
            });
        }]
    });
	
	if({/literal}SUGAR.AutoComplete.{$ac_key}.minQLen{literal} == 0){
	    // expand the dropdown options upon focus
	    SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.on('focus', function () {
	        SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.ac.sendRequest('');
	    });
	}

    SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.updateHidden = function() {
		index_array = SUGAR.MultiEnumAutoComplete.getMultiSelectKeysFromValues("{/literal}{{$vardef.autocomplete_options}}{literal}", SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.get('value'));
		SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputHidden.set('value', index_array.join("^,^"));
    };

    SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.updateText = function() {
		index_array = SUGAR.MultiEnumAutoComplete.getMultiSelectValuesFromKeys("{/literal}{{$vardef.autocomplete_options}}{literal}", SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputHidden.get('value'));
		if(index_array.length < 1){
			SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.set('value', '');
		}
		else{
			SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.set('value', index_array.join(", ") + ", ");
		}
    };

	SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.on('blur', function () {
		SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.updateHidden();
		SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.updateText();
	});
	
    // when they click on the arrow image, toggle the visibility of the options
    SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputImage.on('click', function () {
		if({/literal}SUGAR.AutoComplete.{$ac_key}.minQLen{literal} == 0){
			SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.ac.sendRequest('');
			SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.ac.show();
		}
		else{
			SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.focus();
		}
    });
	
	if({/literal}SUGAR.AutoComplete.{$ac_key}.minQLen{literal} == 0){
	    // After a tag is selected, send an empty query to update the list of tags.
	    SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.ac.after('select', function () {
	      SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.ac.sendRequest('');
	      SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.ac.show();
		  SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.updateHidden();
		  SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.updateText();
	    });
	}
	else{
	    // After a tag is selected, send an empty query to update the list of tags.
	    SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.ac.after('select', function () {
		  SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.updateHidden();
		  SUGAR.AutoComplete.{/literal}{$ac_key}{literal}.inputNode.updateText();
	    });
	}
});
</script>

{/literal}

{{/if}}
