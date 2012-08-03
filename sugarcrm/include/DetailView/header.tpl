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
{{* Add the preForm code if it is defined (used for vcards) *}}
{{if $preForm}}
	{{$preForm}}
{{/if}}
<script type="text/javascript" src="include/EditView/Panels.js"></script>
<script language="javascript">
{literal}
SUGAR.util.doWhen(function(){
    return $("#contentTable").length == 0;
}, SUGAR.themes.actionMenu);
{/literal}
</script>


<table cellpadding="0" cellspacing="0" border="0" width="100%" id="">
<tr>
<td class="buttons" align="left" NOWRAP width="20%">
<div class="actionsContainer">
{{if !isset($form.buttons)}}
    {{sugar_button module="$module" id="EDIT" view="$view" form_id="formDetailView" appendTo="detail_header_buttons"}}
    {{sugar_button module="$module" id="DUPLICATE" view="EditView" form_id="formDetailView" appendTo="detail_header_buttons"}}
    {{sugar_button module="$module" id="DELETE" view="$view" form_id="formDetailView" appendTo="detail_header_buttons"}}
{{else}}
    {{counter assign="num_buttons" start=0 print=false}}
    {{foreach from=$form.buttons key=val item=button}}
        {{if !is_array($button) && in_array($button, $built_in_buttons)}}
        {{counter print=false}}
        {{sugar_button module="$module" id="$button" view="EditView" form_id="formDetailView" appendTo="detail_header_buttons"}}
        {{/if}}
    {{/foreach}}
    {{if count($form.buttons) > $num_buttons}}
        {{foreach from=$form.buttons key=val item=button}}
            {{if is_array($button) && $button.customCode}}
                {{sugar_button module="$module" id="$button" view="EditView" form_id="formDetailView" appendTo="detail_header_buttons"}}
            {{/if}}
        {{/foreach}}
    {{/if}}
    {{if empty($form.hideAudit) || !$form.hideAudit}}
        {{sugar_button module="$module" id="Audit" view="EditView" form_id="formDetailView" appendTo="detail_header_buttons"}}
    {{/if}}
{{/if}}

<form action="index.php" method="post" name="DetailView" id="formDetailView">
    <input type="hidden" name="module" value="{$module}">
    <input type="hidden" name="record" value="{$fields.id.value}">
    <input type="hidden" name="return_action">
    <input type="hidden" name="return_module">
    <input type="hidden" name="return_id">
    <input type="hidden" name="module_tab">
    <input type="hidden" name="isDuplicate" value="false">
    <input type="hidden" name="offset" value="{$offset}">
    <input type="hidden" name="action" value="EditView">
    <input type="hidden" name="sugar_body_only">
{{if isset($form.hidden)}}
{{foreach from=$form.hidden item=field}}
{{$field}}
{{/foreach}}
{{/if}}
</form>
{{sugar_action_menu id="detail_header_action_menu" buttons=$detail_header_buttons class="fancymenu" }}

</div>

</td>


<td align="right" width="80%">{$ADMIN_EDIT}
	{{if $panelCount == 0}}
	    {{* Render tag for VCR control if SHOW_VCR_CONTROL is true *}}
		{{if $SHOW_VCR_CONTROL}}
			{$PAGINATION}
		{{/if}}
		{{counter name="panelCount" print=false}}
	{{/if}}
</td>
{{* Add $form.links if they are defined *}}
{{if !empty($form) && isset($form.links)}}
	<td align="right" width="10%">&nbsp;</td>
	<td align="right" width="100%" NOWRAP>
	{{foreach from=$form.links item=link}}
	    {{$link}}&nbsp;
	{{/foreach}}
	</td>
{{/if}}
</tr>
</table>