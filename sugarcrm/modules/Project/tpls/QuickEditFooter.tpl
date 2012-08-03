{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

*}<table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
    <tr>
        <td>
        {if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary" onclick="this.form.action.value='Save';this.form.return_action.value='ProjectTemplatesListView';if(check_form('{$view}'))return DCMenu.save(this.form.id, 'Project_subpanel_save_button');return false;" type="submit" name="Project_dcmenu_save_button" id="Project_dcmenu_save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">{/if}
        {{foreach from=$form.buttons key=val item=button}}
           {{sugar_button module="$module" id="$button" view="$view"}}
        {{/foreach}}
        <input title="{$APP.LBL_FULL_FORM_BUTTON_TITLE}" accessKey="{$APP.LBL_FULL_FORM_BUTTON_KEY}" class="button" accept=""  onclick="disableOnUnloadEditView(this.form); this.form.return_action.value='ProjectTemplatesDetailView'; this.form.action.value='ProjectTemplatesEditView'; this.form.return_module.value='Project';this.form.return_id.value=this.form.record.value;if(typeof(this.form.to_pdf)!='undefined') this.form.to_pdf.value='0';SUGAR.ajaxUI.submitForm(this.form,null,true);DCMenu.closeOverlay();"  type="button" name="Project_subpanel_full_form_button"  id="Project_subpanel_full_form_button"  value="{$APP.LBL_FULL_FORM_BUTTON_LABEL}">
        <input type="hidden" name="full_form" value="full_form">
        </td>
        <td align="right" nowrap>
            <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> {$APP.NTC_REQUIRED}
        </td>
    </tr>
</table>
<script>
    {literal}
    //lets overwrite dcmenu value that is prepoulated and passed into ajaxui to navigate.  This makes sure we go to
    //projectstemplate list view after the save has been processed.
    if(DCMenu){
        DCMenu.qe_refresh = 'SUGAR.ajaxUI.loadContent("index.php?module=Project&action=ProjectTemplatesListView&ignore='+new Date().getTime()+'");';
    }
    {/literal}
</script>