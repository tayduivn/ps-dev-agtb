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
{literal}
<script type="text/javascript">

/**
  *  Start Bug#50590 
  *  mod_array global array that contains required modules
  *  addDropdownElements fills lead_conv_ac_op_sel with all required modules except Contacts 
  *  as this module is in the list by default
  */

 var mod_array = new Array; 
 function addDropdownElements(){
   var i;
   for(i=0; i<=mod_array.length-1; i++){
     if(mod_array[i] != 'Contacts'){
       var dropdown = document.getElementById('lead_conv_ac_op_sel');
       var opt = document.createElement("option");
       opt.text = SUGAR.language.get('app_list_strings', "moduleListSingular")[mod_array[i]];
       opt.value = mod_array[i];
       opt.label = opt.text;
       dropdown.options.add(opt);
     }
   }
 }
 /**
  *   End Bug#50590
  */    


function addRemoveDropdownElement(module) {
    var accountText = document.getElementById('account_name');
    var checkbox = document.getElementById('new'+module);
    var dropdown = document.getElementById('lead_conv_ac_op_sel');
    if (!checkbox || !dropdown) {
        return;
    }
    var found = false;
    var i;
    for (i=dropdown.options.length-1; i>=0; i--) {
        if (dropdown.options[i].value == module) {
            found = true;
            if (!checkbox.checked) {
                // if this is Accounts and the text of account name is not empty, do not remove
                if (module != 'Accounts' || !accountText || accountText.value == '') {
                    dropdown.remove(i);
                }
            }
            break;
        }
    }
    if (!found && checkbox.checked) {
        var opt = document.createElement("option");
        opt.text = SUGAR.language.get('app_list_strings', "moduleListSingular")[module];
        opt.value = module;
        opt.label = opt.text;
        dropdown.options.add(opt);
    }
}
</script>
{/literal}

<form action="index.php" method="POST" name="{$form_name}" id="{$form_id}" enctype="multipart/form-data">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="buttons">
<input type="hidden" name="module" value="{$module}">
<input type="hidden" name="record" value="{$record_id}">
<input type="hidden" name="isDuplicate" value="false">
<input type="hidden" name="action" value="ConvertLead">
<input type="hidden" name="handle" value="save">
<input type="hidden" name="return_module" value="{$smarty.request.return_module}">
<input type="hidden" name="return_action" value="{$smarty.request.return_action}">
<input type="hidden" name="return_id" value="{$smarty.request.return_id}">
<input type="hidden" name="module_tab"> 
<input type="hidden" name="contact_role">
{if !empty($smarty.request.return_module) || !empty($smarty.request.relate_to)}
<input type="hidden" name="relate_to" value="{if $smarty.request.return_relationship}{$smarty.request.return_relationship}{elseif $smarty.request.relate_to && empty($smarty.request.from_dcmenu)}{$smarty.request.relate_to}{elseif empty($isDCForm) && empty($smarty.request.from_dcmenu)}{$smarty.request.return_module}{/if}">
<input type="hidden" name="relate_id" value="{$smarty.request.return_id}">
{/if}
<input type="hidden" name="offset" value="{$offset}">
{if $bean->aclAccess("save")}
    <input title='{sugar_translate label="LBL_SAVE_BUTTON_LABEL"}' id='SAVE_HEADER'accessKey="{sugar_translate label='LBL_SAVE_BUTTON_KEY}" class="button primary"
        onclick="return check_form('{$form_name}');"
        type="submit" name="button" value="{sugar_translate label='LBL_SAVE_BUTTON_LABEL'}">
{/if}

{if !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && !empty($record_id))}
    <input title="{sugar_translate label='LBL_CANCEL_BUTTON'}" id="CANCEL_HEADER" accessKey="{sugar_translate label='LBL_CANCEL_BUTTON_KEY'}" class="button"
        onclick="this.form.action.value='DetailView'; this.form.module.value='{$smarty.request.return_module}'; this.form.record.value='{$smarty.request.return_id}';" 
        type="submit" name="button" value="{sugar_translate label='LBL_CANCEL_BUTTON_LABEL'}">
{elseif !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && !empty($smarty.request.return_id))}';
    <input title="{sugar_translate label='LBL_CANCEL_BUTTON_TITLE'}" id="CANCEL_HEADER" accessKey="{sugar_translate label='LBL_CANCEL_BUTTON_KEY'}" class="button"
        onclick="this.form.action.value='DetailView'; this.form.module.value='{$smarty.request.return_module}'; this.form.record.value='{$smarty.request.return_id}';" 
        type="submit" name="button" value="{sugar_translate label='LBL_CANCEL_BUTTON_LABEL'}">
{else}
    <input title="{sugar_translate label='LBL_CANCEL_BUTTON_TITLE'}" id="CANCEL_HEADER" accessKey="{sugar_translate label='LBL_CANCEL_BUTTON_KEY'}" class="button"
        onclick="this.form.action.value='DetailView'; this.form.module.value='Leads'; this.form.record.value='{$smarty.request.record}';" 
        type="submit" name="button" value="{sugar_translate label='LBL_CANCEL_BUTTON_LABEL'}">
{/if}
</td>
</tr>
</table>
