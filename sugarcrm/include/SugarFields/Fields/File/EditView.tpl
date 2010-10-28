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
<script type="text/javascript" src='{{sugar_getjspath file="cache/include/externalAPI.cache.js"}}'></script>
<script type="text/javascript" src='{{sugar_getjspath file="include/SugarFields/Fields/File/SugarFieldFile.js"}}'></script>
{{capture name=idName assign=idName}}{{sugarvar key='name'}}{{/capture}}
{{if !empty($displayParams.idName)}}
    {{assign var=idName value=$displayParams.idName}}
{{/if}}

{{if !isset($vardef.noRemove) || !$vardef.noRemove}}
{if !empty({{sugarvar key='value' stringFormat=true}}) }
    {assign var=showRemove value=true}
{else}
    {assign var=showRemove value=false}
{/if}
{{else}}
    {assign var=showRemove value=false}
{{/if}}

<input type="hidden" name="deleteAttachment" value="0">
<span id="{{$idName}}_old" style="display:{if !$showRemove}none;{/if}">
{{if isset($vardef.allowEapm) && $vardef.allowEapm}}
  {if !isset($fields.{{$vardef.docType}}) || empty($fields.{{$vardef.docType}}) || $fields.{{$vardef.docType}} == 'Sugar' || empty($fields.{{$vardef.docUrl}}.value) }
{{/if}}
  <a href="index.php?entryPoint=download&id={$fields.{{$vardef.fileId}}.value}&type={$module}" class="tabDetailViewDFLink">{{sugarvar key='value'}}</a>
{{if isset($vardef.allowEapm) && $vardef.allowEapm}}
  {else}
  <a href="{$fields.{{$vardef.docUrl}}.value}" class="tabDetailViewDFLink" target="_blank">{{sugarvar key='value'}}</a>
  {/if}
{{/if}}
<input type='button' class='button' value='{$APP.LBL_REMOVE}' onclick='SUGAR.field.file.deleteAttachment("{{$idName}}",this);'>
</span>

<span id="{{$idName}}_new" style="display:{if $showRemove}none;{/if}">
{{if isset($vardef.allowEapm) && $vardef.allowEapm}}
<h4>Upload From Your Computer</h4>
{{/if}}
<input type="hidden" name="{{$idName}}_escaped">
<input id="{{$idName}}" name="{{$idName}}" 
type="file" title='{{$vardef.help}}' size="{{$displayParams.size|default:30}}" 
{{if !empty($vardef.len)}}
    maxlength='{{$vardef.len}}'
{{elseif !empty($displayParams.maxlength)}}
    maxlength="{{$displayParams.maxlength}}"
{{else}}
    maxlength="255"
{{/if}} 
value="{if !isset($vardef.allowEapm) || !$vardef.allowEapm || empty($fields[{{$vardef.docId}}].value)}{{sugarvar key='name'}}{/if}"
{{$displayParams.field}}>


{{if isset($vardef.allowEapm) && $vardef.allowEapm}}
<span id="{{$idName}}_externalApiSelector" style="display:inline;">
<br><h4>Search External Source</h4>
<input type="hidden" name="{{$vardef.docId}}" id="{{$vardef.docId}}" value="{$fields[{{$vardef.docId}}].value}">
<input type="hidden" name="{{$vardef.docUrl}}" id="{{$vardef.docUrl}}" value="{$fields[{{$vardef.docUrl}}].value}">
<input type="text" class="sqsEnabled" name="{{$idName}}_remoteName" id="{{$idName}}_remoteName" size="{{$displayParams.size|default:30}}" 
{{if !empty($vardef.len)}}
    maxlength='{{$vardef.len}}'
{{elseif !empty($displayParams.maxlength)}}
    maxlength="{{$displayParams.maxlength}}"
{{else}}
    maxlength="255"
{{/if}} autocomplete="off" value="{if !empty($fields[{{$vardef.docId}}].value)}{{sugarvar key='name'}}{/if}">
<input type="button" value="Select" onclick="DCMenu.loadView('LotusLive Documents','index.php?module=Documents&action=extdoc&type='+ this.form.{{$vardef.docType}}.value +'&form_id='+ this.form.id);">
</span>
<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function() {ldelim}
SUGAR.field.file.setupEapiShowHide("{{$idName}}","{{$vardef.docType}}","{$form_name}");
{rdelim});

console.log("I'm over here");

if ( typeof(sqs_objects) == 'undefined' ) {ldelim}
    sqs_objects = new Array;
{rdelim}

sqs_objects["{$form_name}_{{$idName}}_remoteName"] = {ldelim}
"form":"{$form_name}",
"method":"externalApi",
"api":"",
"modules":["EAPM"],
"field_list":["name", "id", "url"],
"populate_list":["{{$idName}}_remoteName", "{{$vardef.docId}}", "{{$vardef.docUrl}}"],
"required_list":["name"],
"conditions":[],
"no_match_text":"No Match"
{rdelim};

if(typeof QSProcessedFieldsArray != 'undefined') {ldelim}
	QSProcessedFieldsArray["{$form_name}_{{$idName}}_remoteName"] = false;
{rdelim}

enableQS(false);
</script>
{{/if}}
</span>