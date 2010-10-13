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
<input type="text" name="{{sugarvar key='name'}}"  class={{if empty($displayParams.class) }}"sqsEnabled"{{else}} "{{$displayParams.class}}" {{/if}} tabindex="{{$tabindex}}" id="{{sugarvar key='name'}}" size="{{$displayParams.size}}" value="{{sugarvar key='value'}}" title='{{$vardef.help}}' autocomplete="off" {{$displayParams.readOnly}} {{$displayParams.field}}>
<input type="hidden" {{if $displayParams.useIdSearch}}name="{{sugarvar memberName='vardef.id_name' key='name'}}"{{/if}} id="{{sugarvar memberName='vardef.id_name' key='name'}}" value="{{sugarvar memberName='vardef.id_name' key='value'}}">
{{if empty($displayParams.hideButtons) }}
<span class="id-ff multiple">
{{if empty($displayParams.clearOnly) }}
<button type="button" name="btn_{{sugarvar key='name'}}" tabindex="{{$tabindex}}" title="{$APP.LBL_SELECT_BUTTON_TITLE}" accessKey="{$APP.LBL_SELECT_BUTTON_KEY}" class="button{{if empty($displayParams.selectOnly) }} firstChild{{/if}}" value="{$APP.LBL_SELECT_BUTTON_LABEL}" onclick='open_popup("{{sugarvar key='module'}}", 600, 400, "", true, false, {{$displayParams.popupData}}, "single", true);'><img src="{sugar_getimagepath file="id-ff-select.png"}"></button>{{/if}}
{{if empty($displayParams.selectOnly) }}<button type="button" name="btn_clr_{{sugarvar key='name'}}" tabindex="{{$tabindex}}" title="{$APP.LBL_CLEAR_BUTTON_TITLE}" accessKey="{$APP.LBL_CLEAR_BUTTON_KEY}" class="button{{if empty($displayParams.clearOnly) }} lastChild{{/if}}" onclick="this.form.{{sugarvar key='name'}}.value = ''; this.form.{{sugarvar memberName='vardef.id_name' key='name'}}.value = '';" value="{$APP.LBL_CLEAR_BUTTON_LABEL}"><img src="{sugar_getimagepath file="id-ff-clear.png"}"></button>
{{/if}}
</span>
{{/if}}
{{if !empty($displayParams.allowNewValue) }}
<input type="hidden" name="{{sugarvar key='name'}}_allow_new_value" id="{{sugarvar key='name'}}_allow_new_value" value="true">
{{/if}}
