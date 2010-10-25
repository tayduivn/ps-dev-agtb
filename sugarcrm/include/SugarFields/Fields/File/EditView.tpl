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
{{capture name=idName assign=idName}}{{sugarvar key='name'}}{{/capture}}
{{if !empty($displayParams.idName)}}
    {{assign var=idName value=$displayParams.idName}}
{{/if}}

{{if isset($vardef.onlyOnce) && $vardef.onlyOnce}}
{if !empty({{sugarvar key='value' stringFormat=true}})}
{* Can we only upload once, and do we already have something *}
{{if isset($vardef.allowEapm) && $vardef.allowEapm}}
{if !isset($fields.{{$vardef.docType}}) || empty($fields.{{$vardef.docType}}) || $fields.{{$vardef.docType}} == 'Sugar' || empty($fields.{{$vardef.docUrl}}.value) }
{{/if}}
<a href="index.php?entryPoint=download&id={$fields.{{$vardef.fileId}}.value}&type={$module}" class="tabDetailViewDFLink">{{sugarvar key='value'}}</a>
{{if isset($vardef) && isset($vardef.allowEapm) && $vardef.allowEapm}}
{else}
<a href="{$fields.{{$vardef.docUrl}}.value}" class="tabDetailViewDFLink" target="_blank">{{sugarvar key='value'}}</a>
{/if}
{{/if}}
{else} {* Upload once, don't have anything *}
{{/if}}
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
value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}"
{{$displayParams.field}}>
{{if isset($vardef.allowEapm) && $vardef.allowEapm}}
<span id="{{$idName}}_externalApiSelector" style="display:inline;">
<br><h4>Search External Source</h4>
<input type="hidden" name="{{$vardef.docId}}">
<input type="text" class="sqsEnabled" name="remote_filename" size="{{$displayParams.size|default:30}}" 
{{if !empty($vardef.len)}}
    maxlength='{{$vardef.len}}'
{{elseif !empty($displayParams.maxlength)}}
    maxlength="{{$displayParams.maxlength}}"
{{else}}
    maxlength="255"
{{/if}} autocomplete="off">
<input type="button" value="Select" onclick="DCMenu.loadView('LotusLive Documents','index.php?module=Documents&action=extdoc&type=LotusLive&form_id='+ this.form.id);">
</span>
{{/if}}
{{if isset($vardef.onlyOnce) && $vardef.onlyOnce}}
{/if} {* End of upload once, don't have anything *}
{{/if}}