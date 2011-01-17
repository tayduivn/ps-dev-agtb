<!--
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: EditView.html 38464 2008-07-31 19:38:57Z Ajay Gupta $
 ********************************************************************************/
-->

{$ROLLOVER}
<script type="text/javascript" src="{sugar_getjspath file='modules/Emails/javascript/vars.js'}"></script>
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_emails.js'}"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Users/PasswordRequirementBox.css'}">
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/sugar_grp_overlib.js'}'></script>
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_yui_widgets.js'}"></script>
<script type='text/javascript' src='{sugar_getjspath file='include/SubPanel/SubPanelTiles.js'}'></script>
<script type='text/javascript'>
var ERR_RULES_NOT_MET = '{$MOD.ERR_RULES_NOT_MET}';
var ERR_ENTER_OLD_PASSWORD = '{$MOD.ERR_ENTER_OLD_PASSWORD}';
var ERR_ENTER_NEW_PASSWORD = '{$MOD.ERR_ENTER_NEW_PASSWORD}';
var ERR_ENTER_CONFIRMATION_PASSWORD = '{$MOD.ERR_ENTER_CONFIRMATION_PASSWORD}';
var ERR_REENTER_PASSWORDS = '{$MOD.ERR_REENTER_PASSWORDS}';
</script>
<script type='text/javascript' src='{sugar_getjspath file='modules/Users/PasswordRequirementBox.js'}'></script>
{$ERROR_STRING}
<!-- This is here for the external API forms -->
<form name="DetailView" id="DetailView" method="POST" action="index.php">
	<input type="hidden" name="record" id="record" value="{$ID}">
	<input type="hidden" name="module" value="Users">
	<input type="hidden" name="return_module" value="Users">
	<input type="hidden" name="return_id" value="{$ID}">
	<input type="hidden" name="return_action" value="EditView">
</form>

<form name="EditView" enctype="multipart/form-data" id="EditView" method="POST" action="index.php">
	<input type="hidden" name="display_tabs_def">
	<input type="hidden" name="hide_tabs_def">
	<input type="hidden" name="remove_tabs_def">
	<input type="hidden" name="module" value="Users">
	<input type="hidden" name="record" id="record" value="{$ID}">
	<input type="hidden" name="action">
	<input type="hidden" name="page" value="EditView">
	<input type="hidden" name="return_module" value="{$RETURN_MODULE}">
	<input type="hidden" name="return_id" value="{$RETURN_ID}">
	<input type="hidden" name="return_action" value="{$RETURN_ACTION}">
	<input type="hidden" name="password_change" id="password_change" value="false">
    <input type="hidden" name="required_password" id="required_password" value='{$REQUIRED_PASSWORD}' >
	<input type="hidden" name="user_name" value="{$USER_NAME}">
	<input type="hidden" name="type" value="{$REDIRECT_EMAILS_TYPE}">
	<input type="hidden" id="is_group" name="is_group" value='{$IS_GROUP}' {$IS_GROUP_DISABLED}>
	<input type="hidden" id='portal_only' name='portal_only' value='{$IS_PORTALONLY}' {$IS_PORTAL_ONLY_DISABLED}>
	<input type="hidden" name="is_admin" id="is_admin" value='{$IS_FOCUS_ADMIN}' {$IS_ADMIN_DISABLED} >
	<input type="hidden" name="is_current_admin" id="is_current_admin" value='{$IS_ADMIN}' >
	<input type="hidden" name="edit_self" id="edit_self" value='{$EDIT_SELF}' >
	<input type="hidden" name="required_email_address" id="required_email_address" value='{$REQUIRED_EMAIL_ADDRESS}' >
<!-- //BEGIN SUGARCRM flav=sales ONLY -->
	{$ut_hidden}
<!-- //END SUGARCRM flav!=sales ONLY -->
	<div id="popup_window"></div>
						
<script type="text/javascript">
var EditView_tabs = new YAHOO.widget.TabView("EditView_tabs");

{literal}
//Override so we do not force submit
SUGAR.EmailAddressWidget.prototype.forceSubmit = function() { }

EditView_tabs.on('contentReady', function(e){
{/literal}
{if $ID}
{literal}
    EditView_tabs.addTab( new YAHOO.widget.Tab({
        label: '{/literal}{$MOD.LBL_EAPM_SUBPANEL_TITLE}{literal}',
        dataSrc: 'index.php?sugar_body_only=1&module=Users&subpanel=eapm&action=SubPanelViewer&inline=1&record={/literal}{$ID}{literal}&layout_def_key=UserEAPM&inline=1&ajaxSubpanel=true',
        content: '<div style="text-align:center; width: 100%">{/literal}{sugar_image name="loading"}{literal}</div>',
        cacheData: true
    }));
    EditView_tabs.getTab(4).getElementsByTagName('a')[0].id = 'tab5';
{/literal}
{/if}
//BEGIN SUGARCRM flav!=com && flav!=sales ONLY
{if $EDIT_SELF}
{literal}
    EditView_tabs.addTab( new YAHOO.widget.Tab({
        label: '{/literal}{$MOD.LBL_DOWNLOADS}{literal}',
        dataSrc: 'index.php?to_pdf=1&module=Home&action=pluginList',
        content: '<div style="text-align:center; width: 100%">{/literal}{sugar_image name="loading"}{literal}</div>',
        cacheData: true
    }));
    EditView_tabs.getTab(5).getElementsByTagName('a')[0].id = 'tab6';
{/literal}
{/if}
//END SUGARCRM flav!=com && flav!=sales ONLY
});
</script> 

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
    <tr>
        <td>
            <input	id="Save" title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" 
                    class="button primary" onclick="if (!set_password(form,newrules('{$PWDSETTINGS.minpwdlength}','{$PWDSETTINGS.maxpwdlength}','{$REGEX}'))) return false; if (!Admin_check()) return false; this.form.action.value='Save'; {$CHOOSER_SCRIPT} {$REASSIGN_JS} if(verify_data(EditView)) this.form.submit();"
                    type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" >
            <input	title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" 
                    class="button" onclick="this.form.action.value='{$RETURN_ACTION}'; this.form.module.value='{$RETURN_MODULE}'; this.form.record.value='{$RETURN_ID}'; this.form.submit()" 
                    type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
            {$BUTTONS}
        </td>
        <td align="right" nowrap>
            <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> {$APP.NTC_REQUIRED}
        </td>
    </tr>
</table>

<div id="EditView_tabs" class="yui-navset">
    <ul class="yui-nav">
        <li class="selected"><a id="tab1" href="#tab1"><em>{$MOD.LBL_USER_INFORMATION}</em></a></li>
        <li {if $CHANGE_PWD == 0}style='display:none'{/if}><a id="tab2" href="#tab2"><em>{$MOD.LBL_CHANGE_PASSWORD_TITLE}</em></a></li>
        {if $SHOW_THEMES}
        <li><a id="tab3" href="#tab3"><em>{$MOD.LBL_THEME}</em></a></li>
        {/if}
        <li><a id="tab4" href="#tab4" style='display:{$HIDE_FOR_GROUP_AND_PORTAL};'><em>{$MOD.LBL_ADVANCED}</em></a></li>
    </ul>            
    <div class="yui-content">
        <div>
            <div id="basic">
            <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
                            <tr>
                                <th align="left" scope="row" colspan="4"><h4><slot>{$MOD.LBL_USER_INFORMATION}</slot></h4></th>
                            </tr>
                            <tr>
                                <td width="17%" scope="row"><slot>{$MOD.LBL_USER_NAME}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></slot></td>
                                <td width="33%" ><slot><input name='sugar_user_name' type="text" {$USER_NAME_DISABLED} tabindex='1' size='15' maxlength='25' value='{$USER_NAME}' /></slot></td>
                                <td width="17%" id='name_lbl' style='display:{$HIDE_FOR_NORMAL_AND_ADMIN}'  scope="row"><slot>{$MOD.LBL_LIST_NAME}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></slot></td>
                                <td width="33%" id='name_field' style='display:{$HIDE_FOR_NORMAL_AND_ADMIN}'><slot><input id='unique_name' name='unique_name' {$LAST_NAME_DISABLED} tabindex='2' size='25' maxlength='25' type="text" value="{$LAST_NAME}" onblur="document.getElementById('last_name').value=this.value"></slot></td>
                                <td width="17%" id='first_name_lbl' scope="row"  style='display:{$HIDE_FOR_GROUP_AND_PORTAL}'><slot>{$MOD.LBL_FIRST_NAME}:</slot></td>
                                <td width="33%" id='first_name_field'  style='display:{$HIDE_FOR_GROUP_AND_PORTAL}'><slot><input id='first_name' name='first_name' {$FIRST_NAME_DISABLED} tabindex='1' size='25' maxlength='25' type="text" value="{$FIRST_NAME}"></slot></td>
                            </tr>
                            <tr>
                                {$USER_STATUS_OPTIONS}	
                                <td width="17%" scope="row" style='display:{$HIDE_STATIC_USERTYPE}'><slot>{$MOD.LBL_USER_TYPE}:</slot></td>
                                <td scope="row" style='display:{$HIDE_STATIC_USERTYPE}'><slot>{$USER_TYPE_LABEL}&nbsp;{sugar_help text=$USER_TYPE_DESC WIDTH=250}</slot></td>
                                <td id='last_name_lbl' scope="row"  style='display:{$HIDE_FOR_GROUP_AND_PORTAL}'><slot>{$MOD.LBL_LAST_NAME}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></slot></td>
                                <td id='last_name_field'  style='display:{$HIDE_FOR_GROUP_AND_PORTAL}'><slot><input id='last_name' name='last_name' type="text" {$LAST_NAME_DISABLED} tabindex='1' size='25' maxlength='25' value="{$LAST_NAME}"></slot></td>
                            </tr>
                            {* //BEGIN SUGARCRM flav=sales ONLY *}
                            {if not $NON_ADMIN_USER_ADMIN_RIGHTS}
                            {* //END SUGARCRM flav=sales ONLY *}                            
                            <tr style='display:{$HIDE_CHANGE_USERTYPE}'>
                                <td width="17%" scope="row"><slot>{$MOD.LBL_USER_TYPE}:</slot></td>
                                <td colspan='3'>
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                    {if $USER_ADMIN}
                                        <td width="20%"><select id="UserType" name="UserType" onchange="user_status_display(this);" value='' disabled>
                                            <option value="RegularUser">{$MOD.LBL_REGULAR_USER}</option>
                                        </select></td>
                                    {elseif $ADMIN_EDIT_SELF}
                                        <td width="20%"><select id="UserType" name="UserType" onchange="user_status_display(this);" value='' disabled>
                                            <option value="Administrator">{$MOD.LBL_ADMIN_USER}</option>
                                        </select></td>
                                    {else}
                                        <td width="20%"><select id="UserType" name="UserType" onchange="user_status_display(this);" value='' tabindex='1' >
                                            <option value="RegularUser">{$MOD.LBL_REGULAR_USER}</option>
                                            {* //BEGIN SUGARCRM flav=sales ONLY *}
                                            <option value="UserAdministrator" {if $IS_USER_ADMIN} SELECTED {/if}>{$MOD.LBL_USER_ADMINISTRATOR}</option>
                                            {* //END SUGARCRM flav=sales ONLY *}
                                            <option value="Administrator" {if $IS_FOCUS_ADMIN} SELECTED {/if}>{$MOD.LBL_ADMIN_USER}</option>
                                        </select></td>
                                    {/if}
                                        <td colspan='4'>
                                            <div id='UserTypeDesc'>{$USER_TYPE_DESC}</div>
                                        </td></tr>
                                    </table>
                                </td>
                            </tr>
                            {* //BEGIN SUGARCRM flav=sales ONLY *}
                            {/if}
                            {* //END SUGARCRM flav=sales ONLY *}
                            {* //BEGIN SUGARCRM flav!=com ONLY *}
                            {if !$IS_GROUP && !$IS_PORTALONLY}
                            <tr>
                               <td width="17%" scope="row">
                               {$APP.LBL_PICTURE_FILE}:
                               </td>
                               <td width="33%">
                               {$PICTURE_FILE_CODE}
                               </td>
                               <td width="17%">&nbsp;</td>
                               <td width="33%"></td>
                            </tr>
                            {/if}
                            {* //END SUGARCRM flav!=com ONLY *}
                        </table>

            </div>
            <div id="information" style='display:{$HIDE_FOR_GROUP_AND_PORTAL}'>
            <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
                            <tr>
                                <th align="left" scope="row" colspan="4"><h4><slot>{$MOD.LBL_EMPLOYEE_INFORMATION}</slot></h4></th>
                            </tr>
                            <tr>
                                <td width="17%" scope="row"><slot>{$MOD.LBL_EMPLOYEE_STATUS}:</slot></td>
                                <td width="33%" ><slot>{$EMPLOYEE_STATUS_OPTIONS}</slot></td>
                                <td scope="row"><slot>&nbsp;</slot></td>
                                <td  ><slot>&nbsp;</slot></td>
                            </tr>
                            <tr>
                                <td width="17%" scope="row"><slot>{$MOD.LBL_TITLE}:</slot></td>
                                <td width="33%" ><slot><input name='title' type="text" tabindex='5' size='15' maxlength='25' value='{$TITLE}' {$IS_ADMIN_DISABLED}></slot></td>
                                <td width="17%" scope="row"><slot>{$MOD.LBL_OFFICE_PHONE}:</slot></td>
                                <td width="33%" ><slot><input name='phone_work' type="text" tabindex='6' size='20' maxlength='25' value='{$PHONE_WORK}'></slot></td>
                            </tr>
                            <tr>
                                <td scope="row"><slot>{$MOD.LBL_DEPARTMENT}:</slot></td>
                                <td  ><slot><input name='department' type="text" tabindex='5' size='25' maxlength='100' value='{$DEPARTMENT}' {$IS_ADMIN_DISABLED}></slot></td>
                                <td scope="row"><slot>{$MOD.LBL_MOBILE_PHONE}:</slot></td>
                                <td  ><slot><input name='phone_mobile' type="text" tabindex='6' size='20' maxlength='25' value='{$PHONE_MOBILE}'></slot></td>
                            </tr>
                            <tr>
                                <td scope="row"><slot>{$MOD.LBL_REPORTS_TO}:</slot></td>
                                <td ><slot><input type="text" class="sqsEnabled" tabindex='5' name="reports_to_name" id="reports_to_name" value="{$REPORTS_TO_NAME}" autocomplete="off" {$IS_ADMIN_DISABLED}/>
                                    <input type="hidden" name="reports_to_id" id="reports_to_id" value="{$REPORTS_TO_ID}" tabindex="240"/>
                                    {$REPORTS_TO_CHANGE_BUTTON}</slot></td>
                                <td scope="row"><slot>{$MOD.LBL_OTHER_PHONE}:</slot></td>
                                <td  ><slot><input name='phone_other' type="text" tabindex='6' size='20' maxlength='25' value='{$PHONE_OTHER}'></slot></td>
                            </tr>
                            <tr>
                                <td scope="row"><slot>&nbsp;</slot></td>
                                <td  ><slot>&nbsp;</slot></td>
                                <td scope="row"><slot>{$MOD.LBL_FAX}:</slot></td>
                                <td  ><slot><input name='phone_fax' type="text" tabindex='6' size='20' maxlength='25' value='{$PHONE_FAX}'></slot></td>
                            </tr>
                            <tr>
                                <td scope="row"><slot>&nbsp;</slot></td>
                                <td  ><slot>&nbsp;</slot></td>
                                <td scope="row"><slot>{$MOD.LBL_HOME_PHONE}:</slot></td>
                                <td  ><slot><input name='phone_home' type="text" tabindex='6' size='20' maxlength='25' value='{$PHONE_HOME}'></slot></td>
                            </tr>
                            <tr>
                                <td scope="row"><slot>{$MOD.LBL_MESSENGER_TYPE}:</slot></td>
                                <td  ><slot>{$MESSENGER_TYPE_OPTIONS}</slot></td>
                                <td scope="row"><slot>{$MOD.LBL_MESSENGER_ID}:</slot></td>
                                <td  ><slot><input name='messenger_id' type="text" tabindex='6' size='35' maxlength='100' value='{$MESSENGER_ID}'></slot></td>
                            </tr>
                            {*<tr>
                                <th align="left" scope="row" colspan="4"><h4><slot>{$MOD.LBL_ADDRESS_INFORMATION}</slot></h4></th>
                            </tr>*}
                            <tr>
                                <td width="17%" scope="row"><slot>{$MOD.LBL_PRIMARY_ADDRESS}:</slot></td>
                                <td width="33%" ><slot><textarea name='address_street' rows="2" tabindex='8' cols="30">{$ADDRESS_STREET}</textarea></slot></td>
                                <td width="17%" scope="row"><slot>{$MOD.LBL_CITY}:</slot></td>
                                <td width="33%" ><slot><input name='address_city' tabindex='8' size='15' maxlength='100' value='{$ADDRESS_CITY}'></slot></td>
                            </tr>
                            <tr>
                                <td scope="row"><slot>{$MOD.LBL_STATE}:</slot></td>
                                <td  ><slot><input name='address_state' tabindex='9' size='15' maxlength='100' value='{$ADDRESS_STATE}'></slot></td>
                                <td scope="row"><slot>{$MOD.LBL_POSTAL_CODE}:</slot></td>
                                <td  ><slot><input name='address_postalcode' tabindex='9' size='10' maxlength='20' value='{$ADDRESS_POSTALCODE}'></slot></td>
                            </tr>
                            <tr>
                                <td scope="row"><slot>{$MOD.LBL_COUNTRY}:</slot></td>
                                <td  ><slot><input name='address_country' tabindex='10' size='10' maxlength='20' value='{$ADDRESS_COUNTRY}'></slot></td>
                            </tr>
                            <tr>
                                <td valign="top" scope="row"><slot>{$MOD.LBL_NOTES}:</slot></td>
                                <td colspan="4"><slot><textarea name='description' tabindex='11' cols='100%' rows="4">{$DESCRIPTION}</textarea></slot></td>
                            </tr>
                        </table>
            </div>
            <div id="email_options">
            <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
                            <tr>
                                <th align="left" scope="row" colspan="4">
                                    <h4>{$MOD.LBL_MAIL_OPTIONS_TITLE}</h4>
                                </th>
                            </tr>
                            <tr>
                                <td scope="row" width="17%">
                                {$MOD.LBL_EMAIL}:  {if $REQUIRED_EMAIL_ADDRESS}<span class="required" id="mandatory_email">{$APP.LBL_REQUIRED_SYMBOL}</span> {/if}
                                </td>
                                <td>
                                    {$NEW_EMAIL}
                                </td>
                            </tr>
                            <!--//BEGIN SUGARCRM flav!=sales ONLY -->
                            <tr id="email_options_link_type" style='display:{$HIDE_FOR_GROUP_AND_PORTAL}'>
                                <td scope="row" width="17%">
                                    {$MOD.LBL_EMAIL_LINK_TYPE}:&nbsp;{sugar_help text=$MOD.LBL_EMAIL_LINK_TYPE_HELP WIDTH=450}
                                </td>
                                <td>
                                    <select id="email_link_type" name="email_link_type" tabindex='410'>
                                    {$EMAIL_LINK_TYPE}
                                    </select>
                                </td>
                            </tr>
                            <!--//END SUGARCRM flav!=sales ONLY -->
                            {if !$HIDE_IF_CAN_USE_DEFAULT_OUTBOUND}
                            <tr id="mail_smtpserver_tr">
                                <td width="20%" scope="row"><span id="mail_smtpserver_label">{$MOD.LBL_EMAIL_PROVIDER}</span></td>
                                <td width="30%" ><slot>{$mail_smtpdisplay}<input id='mail_smtpserver' name='mail_smtpserver' type="hidden" value='{$mail_smtpserver}' /></slot></td>
                                <td>&nbsp;</td>
                                <td >&nbsp;</td>
                            </tr>
                             {if !empty($mail_smtpauth_req) }
                            
                            <tr id="mail_smtpuser_tr">
                                <td width="20%" scope="row" nowrap="nowrap"><span id="mail_smtpuser_label">{$MOD.LBL_MAIL_SMTPUSER}</span></td>
                                <td width="30%" ><slot><input type="text" id="mail_smtpuser" name="mail_smtpuser" size="25" maxlength="64" value="{$mail_smtpuser}" tabindex='1' ></slot></td>
                                <td>&nbsp;</td>
                                <td >&nbsp;</td>
                            </tr>
                            <tr id="mail_smtppass_tr">
                                <td width="20%" scope="row" nowrap="nowrap"><span id="mail_smtppass_label">{$MOD.LBL_MAIL_SMTPPASS}</span></td>
                                <td width="30%" ><slot><input type="password" id="mail_smtppass" name="mail_smtppass" size="25" maxlength="64" value="{$mail_smtppass}" tabindex='1'></slot></td>
                                <td>&nbsp;</td>
                                <td >&nbsp;</td>
                            </tr>
                            {/if}
                         
                            <tr id="test_outbound_settings_tr">
                                <td width="17%" scope="row"><input type="button" class="button" value="{$APP.LBL_EMAIL_TEST_OUTBOUND_SETTINGS}" onclick="startOutBoundEmailSettingsTest();"></td>
                                <td width="33%" >&nbsp;</td>
                                <td width="17%">&nbsp;</td>
                                <td width="33%" >&nbsp;</td>
                            </tr>
                            {/if}
                        </table>
            </div>
        </div>
        <div>
            {if ($CHANGE_PWD) == '1'} 
            <div id="generate_password">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
                <tr>
                    <td width='40%'>
                        <table width='100%' cellspacing='0' cellpadding='0' border='0' >
                            <tr>
                                <th align="left" scope="row" colspan="4">
                                    <h4>{$MOD.LBL_CHANGE_PASSWORD_TITLE}</h4><br>
                                    {$ERROR_PASSWORD}
                                </th>
                            </tr>
                        </table>
                            <!-- hide field if user is admin -->
                            <div id='generate_password_old_password' {if ($IS_ADMIN)} style='display:none' {/if}>
                                 <table width='100%' cellspacing='0' cellpadding='0' border='0' >
                                    <tr>
                                        <td width='35%' scope="row">
                                            {$MOD.LBL_OLD_PASSWORD}
                                        </td>
                                        <td >
                                            <input name='old_password' id='old_password' type='password' tabindex='2' onkeyup="password_confirmation();" >
                                        </td>
                                        <td width='40%'>
                                        </td>
                                    </tr>
                                 </table>
                            </div>
                        <table width='100%' cellspacing='0' cellpadding='0' border='0' >
                            <tr>
                                <td width='35%' scope="row" snowrap>
                                    {$MOD.LBL_NEW_PASSWORD}
                                    <span class="required" id="mandatory_pwd">{if ($REQUIRED_PASSWORD)}{$APP.LBL_REQUIRED_SYMBOL}{/if}</span>
                                </td>
                                <td class='dataField'>
                                    
                                    <input name='new_password' id= "new_password" type='password' tabindex='2' onkeyup="password_confirmation();newrules('{$PWDSETTINGS.minpwdlength}','{$PWDSETTINGS.maxpwdlength}','{$REGEX}');" />
                                </td>
                                <td width='40%'>
                                </td>
                            </tr>
                            <tr>
                                <td scope="row" width='35%'>
                                    {$MOD.LBL_CONFIRM_PASSWORD}
                                </td>
                                <td class='dataField'>
                                    <input name='confirm_new_password' id='confirm_pwd' style ='' type='password' tabindex='2' onkeyup="password_confirmation();"  >
                                </td>
                                <td width='40%'>
                                <div id="comfirm_pwd_match" class="error" style="display: none;">{$MOD.ERR_PASSWORD_MISMATCH}</div>
                                     {*<span id="ext-gen63" class="x-panel-header-text">
                                        Requirements
                                        <span id="Filter.1_help" onmouseout="return nd();" onmouseover="return overlib(help(), FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass' );">
                                            <img src="themes/default/images/help.gif"/>
                                        </span>
                                    </span>*}
                                </td>
                            </tr>
                            <tr>
                                <td class='dataLabel'></td>
                                <td class='dataField'></td>
                            </td>                    
                        </table>
            
                        <table width='17%' cellspacing='0' cellpadding='1' border='0'>
                            <tr>
                                <td width='50%'>
                                    <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey='{$APP.LBL_SAVE_BUTTON_KEY}' class='button' id='save_new_pwd_button' LANGUAGE=javascript onclick='if (set_password(this.form)) window.close(); else return false;' type='submit' name='button' style='display:none;' value='{$APP.LBL_SAVE_BUTTON_LABEL}'>
                                </td>
                                <td width='50%'>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width='60%' style="vertical-align:middle;">
                        <!--//BEGIN SUGARCRM flav=pro || flav=sales ONLY -->
                        {if !$IS_PORTALONLY}
                            {sugar_password_requirements_box width='300px' class='x-sqs-list' style='background-color:white; padding:5px !important;'}
                        {/if}
                        <!--//END SUGARCRM flav=pro || flav=sales ONLY -->
                    </td>
                </tr>
            </table>
            </div>
            {else}
            <div id="generate_password">
                <input name='old_password' id='old_password' type='hidden'>
                <input name='new_password' id= "new_password" type='hidden'>
                <input name='confirm_new_password' id='confirm_pwd' type='hidden'>
            </div>
            {/if}
    </div>
    {if $SHOW_THEMES}
    <div>
        <div id="themepicker" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        <table class="edit view" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td scope="row" colspan="4"><h4>{$MOD.LBL_THEME}</h4></td>
                </tr>
                <tr>
                    <td width="17%">
                        <select name="user_theme" tabindex='366' size="20" id="user_theme_picker" style="width: 100%">
                            {$THEMES}
                        </select>
                    </td>
                    <td width="33%">
                        <img id="themePreview" src="{sugar_getimagepath file='themePreview.png'}" border="1" />
                    </td>
                    <td width="17%">&nbsp;</td>
                    <td width="33%">&nbsp;</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
    {/if}
    <div>
        <div id="settings" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">

                        <tr>
                            <th width="100%" align="left" scope="row" colspan="4"><h4><slot>{$MOD.LBL_USER_SETTINGS}</slot></h4></th>
                        </tr>
                        <tr>
                            <td scope="row"  valign="top"><slot>{$MOD.LBL_EXPORT_DELIMITER}:</slot>&nbsp;{sugar_help text=$MOD.LBL_EXPORT_DELIMITER_DESC }</td>
                            <td ><slot><input type="text" tabindex='12' name="export_delimiter" value="{$EXPORT_DELIMITER}" size="5"></slot></td>
                            <td scope="row" width="17%">
                            {* //BEGIN SUGARCRM flav!=sales ONLY*}
                            <slot>{$MOD.LBL_RECEIVE_NOTIFICATIONS}:</slot>&nbsp;{sugar_help text=$MOD.LBL_RECEIVE_NOTIFICATIONS_TEXT}
                            {* //END SUGARCRM flav!=sales ONLY*}
                            </td>
                            <td width="33%">
                            {* //BEGIN SUGARCRM flav!=sales ONLY*}
                            <slot><input name='receive_notifications' class="checkbox" tabindex='12' type="checkbox" value="12" {$RECEIVE_NOTIFICATIONS}></slot>
                            {* //END SUGARCRM flav!=sales ONLY*}
                            </td>                
                        </tr>
        
                        <tr>
                            <td scope="row" valign="top"><slot>{$MOD.LBL_EXPORT_CHARSET}:</slot>&nbsp;{sugar_help text=$MOD.LBL_EXPORT_CHARSET_DESC }</td>
                            <td ><slot><select tabindex='12' name="default_export_charset">{$EXPORT_CHARSET}</select></slot></td>
                            <td scope="row" valign="top">
                            {* //BEGIN SUGARCRM flav!=sales ONLY*}
                            <slot>{$MOD.LBL_REMINDER}:</slot>&nbsp;{sugar_help text=$MOD.LBL_REMINDER_TEXT }
                            {* //END SUGARCRM flav!=sales ONLY*}
                            </td>
                            <td valign="top"  nowrap>
                                {* //BEGIN SUGARCRM flav!=sales ONLY*}
                                <slot>
                                <input tabindex='12' name='mailmerge_on' type='hidden' value='0'>
                                <input name='should_remind' size='2' maxlength='2' tabindex='12' onclick='toggleDisplay("should_remind_list");' type="checkbox" class="checkbox" value='1' {$REMINDER_CHECKED}>
                                <div id='should_remind_list' style='display:{$REMINDER_TIME_DISPLAY}'>
                                    <select tabindex='12' name='reminder_time'  >{$REMINDER_TIME_OPTIONS}</select></div></slot>
                               {* //END SUGARCRM flav!=sales ONLY*}
                            </td>
                        </tr>
                        <tr>
                            <td scope="row" valign="top"><slot>{$MOD.LBL_USE_REAL_NAMES}:</slot>&nbsp;{sugar_help text=$MOD.LBL_USE_REAL_NAMES_DESC }</td>
                            <td ><slot><input tabindex='12' type="checkbox" name="use_real_names" {$USE_REAL_NAMES}></slot></td>
                            <td scope="row" valign="top">
                            {* //BEGIN SUGARCRM flav!=sales ONLY*}
                            <slot>{$MOD.LBL_MAILMERGE}:</slot>&nbsp;{sugar_help text=$MOD.LBL_MAILMERGE_TEXT }
                            {* //END SUGARCRM flav!=sales ONLY*}
                            </td>
                            <td valign="top"  nowrap>
                            {* //BEGIN SUGARCRM flav!=sales ONLY*}
                            <slot><input tabindex='12' name='mailmerge_on' class="checkbox" type="checkbox" {$MAILMERGE_ON}></slot>
                            {* //END SUGARCRM flav!=sales ONLY*}
                            </td>
                        </tr>
                        <!--//BEGIN SUGARCRM flav!=dce ONLY -->
                        <!--//BEGIN SUGARCRM flav=pro ONLY -->
                        <!-- BEGIN: pro_oc -->
                        <tr>
                            <!--//BEGIN SUGARCRM flav=ent ONLY -->
                            <td  scope="row" valign="top"><slot>{$APP.LBL_OC_STATUS}:</slot>&nbsp;{sugar_help text=$APP.LBL_OC_STATUS_TEXT }</td>
                            <td ><slot><select tabindex='12' name="oc_status" {$IS_ADMIN_DISABLED}>{$OC_STATUS}</select></slot></td>
                            <!--//END SUGARCRM flav=ent ONLY -->
                            <td scope="row" valign="top"><slot>{$MOD.LBL_OWN_OPPS}:</slot>&nbsp;{sugar_help text=$MOD.LBL_OWN_OPPS_DESC }</td>
                            <td ><slot><input tabindex='12' type="checkbox" name="no_opps" {$NO_OPPS}></slot></td>
                        </tr>			
                        <!-- END: pro_oc -->
                        <!--//END SUGARCRM flav=pro ONLY -->
                        <!--//END SUGARCRM flav!=dce ONLY -->
                        <!--//BEGIN SUGARCRM flav=pro ONLY -->
                        <!-- BEGIN: pro -->
                        <tr>
                            {if !empty($SHOW_TEAM_SELECTION)}
                            <td width="20%" scope="row"><slot>{$MOD.LBL_DEFAULT_TEAM}:</slot>&nbsp;{sugar_help text=$MOD.LBL_DEFAULT_TEAM_TEXT }</td>
                            <td ><slot>{$DEFAULT_TEAM_OPTIONS}</slot></td>
                            {/if}
                            <td scope="row"></td>
                            <td></td>
                        </tr>
                        <!-- END: pro -->
                        <!--//END SUGARCRM flav=pro ONLY -->
                        <!--{if !empty($EXTERNAL_AUTH_CLASS) && !empty($IS_ADMIN)}-->
                            <tr>
                                {capture name=SMARTY_LBL_EXTERNAL_AUTH_ONLY}&nbsp;{$MOD.LBL_EXTERNAL_AUTH_ONLY} {$EXTERNAL_AUTH_CLASS_1}{/capture}
                                <td scope="row" nowrap><slot>{$EXTERNAL_AUTH_CLASS} {$MOD.LBL_ONLY}:</slot>&nbsp;{sugar_help text=$smarty.capture.SMARTY_LBL_EXTERNAL_AUTH_ONLY}</td>
                                <td ><input type='hidden' value='0' name='external_auth_only'><input type='checkbox' value='1' name='external_auth_only' {$EXTERNAL_AUTH_ONLY_CHECKED}></td>
                                <td ></td>
                                <td ></td>
                            </tr>
                        <!--{/if}-->
                    </table>
        </div>
        {* //BEGIN SUGARCRM flav!=sales ONLY*}
        <div id="layout">
        <table class="edit view" border="0" cellpadding="0" cellspacing="1" width="100%">
            <tbody>
                <tr>
                    <th align="left" scope="row" colspan="4"><h4>{$MOD.LBL_LAYOUT_OPTIONS}</h4></th>
                </tr>
							<tr id="use_group_tabs_row" style="display: {$DISPLAY_GROUP_TAB};">	
                                <td scope="row"><span>{$MOD.LBL_USE_GROUP_TABS}:</span>&nbsp;{sugar_help text=$MOD.LBL_NAVIGATION_PARADIGM_DESCRIPTION }</td>
                                <td colspan="3"><input name="use_group_tabs" type="hidden" value="m"><input id="use_group_tabs" type="checkbox" name="use_group_tabs" {$USE_GROUP_TABS} tabindex='12' value="gm"></td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td scope="row" align="left" style="padding-bottom: 2em;">{$TAB_CHOOSER}</td>
                                            <td width="90%" valign="top"><BR>&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td width="17%" scope="row"><span scope="row">{$MOD.LBL_MAX_TAB}:</span>&nbsp;{sugar_help text=$MOD.LBL_MAX_TAB_DESCRIPTION }</td>
                                <td width="83%" colspan="3">
                                    <select name="user_max_tabs" tabindex='12'>
                                    {html_options values=$MAX_TAB_OPTIONS output=$MAX_TAB_OPTIONS selected=$MAX_TAB} 
                                    </select>
                                </td>
							</tr>
							<tr>	
                                <td scope="row"><span>{$MOD.LBL_SUBPANEL_TABS}:</span>&nbsp;{sugar_help text=$MOD.LBL_SUBPANEL_TABS_DESCRIPTION }</td>
                                <td colspan="3"><input type="checkbox" name="user_subpanel_tabs" {$SUBPANEL_TABS} tabindex='13'></td>
                            </tr>
                        </table>
        </div>
        {* //END SUGARCRM flav!=sales ONLY*}
        <div id="locale" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
                        <tr>
                            <th width="100%" align="left" scope="row" colspan="4">
                                <h4><slot>{$MOD.LBL_USER_LOCALE}</slot></h4></th>
                        </tr>
                        <tr>
                            <td width="17%" scope="row"><slot>{$MOD.LBL_DATE_FORMAT}:</slot>&nbsp;{sugar_help text=$MOD.LBL_DATE_FORMAT_TEXT }</td>
                            <td width="33%"><slot><select tabindex='14' name='dateformat'>{$DATEOPTIONS}</select></slot></td>
                            <!-- END: prompttz -->
                            <!--//BEGIN SUGARCRM flav!=dce ONLY -->
                            <!-- BEGIN: currency -->
                            <td width="17%" scope="row"><slot>{$MOD.LBL_CURRENCY}:</slot>&nbsp;{sugar_help text=$MOD.LBL_CURRENCY_TEXT }</td>
                                <td ><slot>
                                    <select tabindex='14' id='currency_select' name='currency' onchange='setSymbolValue(this.options[this.selectedIndex].value);setSigDigits();'>{$CURRENCY}</select>
                                    <input type="hidden" id="symbol" value="">
                                </slot></td>
                            <!-- END: currency -->
                            <!--//END SUGARCRM flav!=dce ONLY -->
                        </tr>
                        <tr>
                            <td scope="row"><slot>{$MOD.LBL_TIME_FORMAT}:</slot>&nbsp;{sugar_help text=$MOD.LBL_TIME_FORMAT_TEXT }</td>
                            <td ><slot><select tabindex='14' name='timeformat'>{$TIMEOPTIONS}</select></slot></td>
                            <!--//BEGIN SUGARCRM flav!=dce ONLY -->
                            <!-- BEGIN: currency -->
                            <td width="17%" scope="row"><slot>
                                {$MOD.LBL_CURRENCY_SIG_DIGITS}:
                            </slot></td>
                            <td ><slot>
                                <select id='sigDigits' onchange='setSigDigits(this.value);' name='default_currency_significant_digits'>{$sigDigits}</select>
                            </slot></td>
                            <!-- END: currency -->
                            <!--//END SUGARCRM flav!=dce ONLY -->
                        </tr>
                        <tr>
                            <td scope="row"><slot>{$MOD.LBL_TIMEZONE}:</slot>&nbsp;{sugar_help text=$MOD.LBL_TIMEZONE_TEXT }</td>
                            <td ><slot><select tabindex='14' name='timezone'>{$TIMEZONEOPTIONS}</select></slot></td>
                            <!--//BEGIN SUGARCRM flav!=dce ONLY -->
                            <!-- BEGIN: currency -->
                            <td width="17%" scope="row"><slot>
                                <i>{$MOD.LBL_LOCALE_EXAMPLE_NAME_FORMAT}</i>:
                            </slot></td>
                            <td ><slot>
                                <input type="text" disabled id="sigDigitsExample" name="sigDigitsExample">
                            </slot></td>
                            <!-- END: currency -->
                            <!--//END SUGARCRM flav!=dce ONLY -->
                        </tr>
                        <tr>
                        <!--  //BEGIN SUGARCRM flav!=sales ONLY -->
                        {if ($IS_ADMIN)} 
                            <td scope="row"><slot>{$MOD.LBL_PROMPT_TIMEZONE}:</slot>&nbsp;{sugar_help text=$MOD.LBL_PROMPT_TIMEZONE_TEXT }</td>
                            <td ><slot><input type="checkbox" tabindex='14'class="checkbox" name="ut" value="0" {$PROMPTTZ}></slot></td>
                        {else}
                        <!--  //END SUGARCRM flav!=sales ONLY -->
                            <td scope="row"><slot></td>
                            <td ><slot></slot></td>
                        <!--  //BEGIN SUGARCRM flav!=sales ONLY -->
                        {/if}
                        <!--  //END SUGARCRM flav!=sales ONLY -->
                            <td width="17%" scope="row"><slot>{$MOD.LBL_NUMBER_GROUPING_SEP}:</slot>&nbsp;{sugar_help text=$MOD.LBL_NUMBER_GROUPING_SEP_TEXT }</td>
                            <td ><slot>
                                <input tabindex='14' name='num_grp_sep' id='default_number_grouping_seperator'
                                    type='text' maxlength='1' size='1' value='{$NUM_GRP_SEP}' 
                                    onkeydown='setSigDigits();' onkeyup='setSigDigits();'>
                            </slot></td></tr>
                        {capture name=SMARTY_LOCALE_NAME_FORMAT_DESC}&nbsp;{$MOD.LBL_LOCALE_NAME_FORMAT_DESC}<br />{$MOD.LBL_LOCALE_NAME_FORMAT_DESC_2}{/capture}
                        <tr>
                            <td  scope="row" valign="top">{$MOD.LBL_LOCALE_DEFAULT_NAME_FORMAT}:&nbsp;{sugar_help text=$smarty.capture.SMARTY_LOCALE_NAME_FORMAT_DESC }</td>
                            <td   valign="top">
                                <input onkeyup="setPreview();" onkeydown="setPreview();" id="default_locale_name_format" type="text" tabindex='14' name="default_locale_name_format" value="{$default_locale_name_format}">
                            </td>
                             <td width="17%" scope="row"><slot>{$MOD.LBL_DECIMAL_SEP}:</slot>&nbsp;{sugar_help text=$MOD.LBL_DECIMAL_SEP_TEXT }</td>
                            <td ><slot>
                                <input tabindex='14' name='dec_sep' id='default_decimal_seperator' 
                                    type='text' maxlength='1' size='1' value='{$DEC_SEP}'
                                    onkeydown='setSigDigits();' onkeyup='setSigDigits();'>
                            </slot></td>
                        </tr>
                        <tr>
                            <td  scope="row" valign="top"><i>{$MOD.LBL_LOCALE_EXAMPLE_NAME_FORMAT}:</i> </td>
                            <td   valign="top"><input tabindex='14' name="no_value" id="nameTarget" value="" style="border: none;" disabled size="50"></td>        
                        </tr>
                    </table>
        </div>
        
        <!--//BEGIN SUGARCRM flav=pro ONLY -->
        <div id="pdf_settings" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        {if $PDF_CLASS == "TCPDF"}
        <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
                        <tr>
                            <th width="100%" align="left"  colspan="4">
                                <h4 ><slot>{$MOD.LBL_PDF_SETTINGS}</slot></h4></th>
                        </tr>
                        <tr>
                            <td width="17%" scope="row"><slot>{$MOD.LBL_PDF_FONT_NAME_MAIN}:</slot>&nbsp;{sugar_help text=$MOD.LBL_PDF_FONT_NAME_MAIN_TEXT}</td>
                            <td width="33%"><slot><select name='sugarpdf_pdf_font_name_main' tabindex='16'>{$PDF_FONT_NAME_MAIN}</select></slot></td>
                            <td colspan="2"><slot>&nbsp;</slot></td>
                        </tr>
                        <tr>
                            <td width="17%" scope="row"><slot>{$MOD.LBL_PDF_FONT_SIZE_MAIN}:</slot></td>
                            <td width="33%"><slot><input type="text" name="sugarpdf_pdf_font_size_main" value="{$PDF_FONT_SIZE_MAIN}" size="5" maxlength="5" tabindex='16'/></slot></td>
                            <td colspan="2"><slot>{$MOD.LBL_PDF_FONT_SIZE_MAIN_TEXT}&nbsp;</slot></td>
                        </tr>
                        <tr>
                            <td width="17%" scope="row"><slot>{$MOD.LBL_PDF_FONT_NAME_DATA}:</slot>&nbsp;{sugar_help text=$MOD.LBL_PDF_FONT_NAME_DATA_TEXT}</td>
                            <td width="33%"><slot><select name='sugarpdf_pdf_font_name_data' tabindex='16'>{$PDF_FONT_NAME_DATA}</select></slot></td>
                            <td colspan="2"><slot>&nbsp;</slot></td>
                        </tr>
                        <tr>
                            <td width="17%" scope="row"><slot>{$MOD.LBL_PDF_FONT_SIZE_DATA}:</slot></td>
                            <td width="33%"><slot><input type="text" name="sugarpdf_pdf_font_size_data" value="{$PDF_FONT_SIZE_DATA}" size="5" maxlength="5" tabindex='16'/></slot></td>
                            <td colspan="2"><slot>{$MOD.LBL_PDF_FONT_SIZE_DATA_TEXT}&nbsp;</slot></td>
                        </tr>              
                    </table>
        {/if}
        </div>
        <!--//END SUGARCRM flav=pro ONLY -->
        <!--//BEGIN SUGARCRM flav!=sales ONLY -->
        <!--//BEGIN SUGARCRM flav!=dce ONLY -->
        <div id="calendar_options" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
            <tr>
                <th align="left" scope="row" colspan="4"><h4>{$MOD.LBL_CALENDAR_OPTIONS}</h4></th>
            </tr>
                        <tr>
                            <td width="17%" scope="row"><slot>{$MOD.LBL_PUBLISH_KEY}:</slot>&nbsp;{sugar_help text=$MOD.LBL_CHOOSE_A_KEY}</td>
                            <td width="20%" ><slot><input name='calendar_publish_key' tabindex='17' size='25' maxlength='25' type="text" value="{$CALENDAR_PUBLISH_KEY}"></slot></td>
                            <td width="63%" ><slot>&nbsp;</slot></td>
                        </tr>
                    </table>
        </div>
        <!--//END SUGARCRM flav!=dce ONLY -->
        <!--//END SUGARCRM flav!=sales ONLY -->
    </div>
</div>














<div class="actionContainer">
	<input	id="Save" title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary" onclick="if (!set_password(form,newrules('{$PWDSETTINGS.minpwdlength}','{$PWDSETTINGS.maxpwdlength}','{$REGEX}'))) return false;Admin_check(); this.form.action.value='Save'; {$CHOOSER_SCRIPT} {$REASSIGN_JS} if(verify_data(EditView)) this.form.submit();" 
			type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" />
	<input	title="{$APP.LBL_CANCEL_BUTTON_TITLE}" class="button" onclick="this.form.action.value='{$RETURN_ACTION}'; this.form.module.value='{$RETURN_MODULE}'; this.form.record.value='{$RETURN_ID}'; this.form.submit();" 
			type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" />
    {$BUTTONS}
</div>
<script type="text/javascript">
<!--
var mail_smtpport = '{$MAIL_SMTPPORT}';
var mail_smtpssl = '{$MAIL_SMTPSSL}';
{literal}
EmailMan = {};

function Admin_check(){
	if (('{/literal}{$IS_FOCUS_ADMIN}{literal}') && document.getElementById('is_admin').value=='0'){
		r=confirm('{/literal}{$MOD.LBL_CONFIRM_REGULAR_USER}{literal}');
		return r;
		}
	else
		return true;
}

function user_status_display(field){
		switch (field.value){
		case 'Administrator':
		document.getElementById('UserTypeDesc').innerHTML="{/literal}{$MOD.LBL_ADMIN_DESC}{literal}";
		document.getElementById('is_admin').value='1';
		break;
		case 'RegularUser':
			document.getElementById('is_admin').value='0';
			document.getElementById('UserTypeDesc').innerHTML="{/literal}{$MOD.LBL_REGULAR_DESC}{literal}";
		break;
		case 'UserAdministrator':
			document.getElementById('is_admin').value='0';
			document.getElementById('UserTypeDesc').innerHTML="{/literal}{$MOD.LBL_USER_ADMIN_DESC}{literal}";
		break;
	}
}


function startOutBoundEmailSettingsTest()
{
    var loader = new YAHOO.util.YUILoader({
    require : ["element","sugarwidgets"],
    loadOptional: true,
    //BEGIN SUGARCRM flav=int ONLY
	filter: 'debug',
	//END SUGARCRM flav=int ONLY
    skin: { base: 'blank', defaultSkin: '' },
    onSuccess: testOutboundSettings,
    allowRollup: true,
    base: "include/javascript/yui/build/"
    });
    loader.addModule({
        name :"sugarwidgets",
        type : "js",
        fullpath: "include/javascript/sugarwidgets/SugarYUIWidgets.js",
        varName: "YAHOO.SUGAR",
        requires: ["datatable", "dragdrop", "treeview", "tabview"]
    });
    loader.insert();
     
}

function testOutboundSettings() 
{ 
	var errorMessage = '';
	var isError = false;
	var fromAddress = document.getElementById("outboundtest_from_address").value;
    var errorMessage = '';
    var isError = false;
    var smtpServer = document.getElementById('mail_smtpserver').value;

    var mailsmtpauthreq = document.getElementById('mail_smtpauth_req');
    if(trim(smtpServer) == '' || trim(mail_smtpport) == '') 
    {
        isError = true;
        errorMessage += "{/literal}{$MOD.LBL_MISSING_DEFAULT_OUTBOUND_SMTP_SETTINGS}{literal}" + "<br/>";
        overlay("{/literal}{$APP.ERR_MISSING_REQUIRED_FIELDS}{literal}", errorMessage, 'alert');
        return false;
    }
    
   
    if(document.getElementById('mail_smtpuser') && trim(document.getElementById('mail_smtpuser').value) == '') 
    {
        isError = true;
        errorMessage += "{/literal}{$APP.LBL_EMAIL_ACCOUNTS_SMTPUSER}{literal}" + "<br/>";
    }

    
    if(document.getElementById('mail_smtppass') && trim(document.getElementById('mail_smtppass').value) == '') 
    {
        isError = true;
        errorMessage += "{/literal}{$APP.LBL_EMAIL_ACCOUNTS_SMTPPASS}{literal}" + "<br/>";
    }
    if(isError) {
        overlay("{/literal}{$APP.ERR_MISSING_REQUIRED_FIELDS}{literal}", errorMessage, 'alert');
        return false;    
    } 
	
    testOutboundSettingsDialog();
}

function sendTestEmail()
{
    var fromAddress = document.getElementById("outboundtest_from_address").value;
    
    if (trim(fromAddress) == "") 
    {
        overlay("{/literal}{$APP.ERR_MISSING_REQUIRED_FIELDS}{literal}", "{{/literal}$APP.LBL_EMAIL_SETTINGS_FROM_TO_EMAIL_ADDR}{literal}", 'alert');
        return;
    }
    else if (!isValidEmail(fromAddress)) {
        overlay("{/literal}{$APP.ERR_INVALID_REQUIRED_FIELDS}{literal}", "{/literal}{$APP.LBL_EMAIL_SETTINGS_FROM_TO_EMAIL_ADDR}{literal}", 'alert');
        return;
    }
    
    //Hide the email address window and show a message notifying the user that the test email is being sent.
    EmailMan.testOutboundDialog.hide();
    overlay("{/literal}{$APP.LBL_EMAIL_PERFORMING_TASK}{literal}", "{/literal}{$APP.LBL_EMAIL_ONE_MOMENT}{literal}", 'alert');
    
    var callbackOutboundTest = {
    	success	: function(o) {
    		hideOverlay();
    		overlay("{/literal}{$APP.LBL_EMAIL_TEST_OUTBOUND_SETTINGS}{literal}", "{/literal}{$APP.LBL_EMAIL_TEST_NOTIFICATION_SENT}{literal}", 'alert');
    	}
    };    
    var smtpServer = document.getElementById('mail_smtpserver').value;
    
    if(document.getElementById('mail_smtpuser') && document.getElementById('mail_smtppass')){
    var postDataString = 'mail_sendtype=SMTP&mail_smtpserver=' + smtpServer + "&mail_smtpport=" + mail_smtpport + "&mail_smtpssl=" + mail_smtpssl + "&mail_smtpauth_req=true&mail_smtpuser=" + trim(document.getElementById('mail_smtpuser').value) + "&mail_smtppass=" + trim(document.getElementById('mail_smtppass').value) + "&outboundtest_from_address=" + fromAddress;
    }
    else{
	var postDataString = 'mail_sendtype=SMTP&mail_smtpserver=' + smtpServer + "&mail_smtpport=" + mail_smtpport + "&mail_smtpssl=" + mail_smtpssl + "&outboundtest_from_address=" + fromAddress;
    }
	YAHOO.util.Connect.asyncRequest("POST", "index.php?action=testOutboundEmail&module=EmailMan&to_pdf=true&sugar_body_only=true", callbackOutboundTest, postDataString);
}
function testOutboundSettingsDialog() {
        // lazy load dialogue
        if(!EmailMan.testOutboundDialog) {
        	EmailMan.testOutboundDialog = new YAHOO.widget.Dialog("testOutboundDialog", {
                modal:true,
				visible:true,
            	fixedcenter:true,
            	constraintoviewport: true,
                width	: 600,
                shadow	: false
            });
            EmailMan.testOutboundDialog.setHeader("{/literal}{$APP.LBL_EMAIL_TEST_OUTBOUND_SETTINGS}{literal}");
            YAHOO.util.Dom.removeClass("testOutboundDialog", "yui-hidden");
        } // end lazy load
        
        EmailMan.testOutboundDialog.render();
        EmailMan.testOutboundDialog.show();
} // fn

function overlay(reqtitle, body, type) {
    var config = { };
    config.type = type;
    config.title = reqtitle;
    config.msg = body;
    YAHOO.SUGAR.MessageBox.show(config);
}

function hideOverlay() {
	YAHOO.SUGAR.MessageBox.hide();
}

YAHOO.util.Event.onContentReady('user_theme_picker',function()
{
    document.getElementById('user_theme_picker').onchange = function()
    {
        document.getElementById('themePreview').src =
            "index.php?entryPoint=getImage&themeName=" + document.getElementById('user_theme_picker').value + "&imageName=themePreview.png";
        if (typeof themeGroupList[document.getElementById('user_theme_picker').value] != 'undefined' &&
            themeGroupList[document.getElementById('user_theme_picker').value] ) {
            document.getElementById('use_group_tabs_row').style.display = '';
        } else {
            document.getElementById('use_group_tabs_row').style.display = 'none';
        }
    }
});
{/literal}
<!--//BEGIN SUGARCRM flav!=sales ONLY -->
{if !$HIDE_IF_CAN_USE_DEFAULT_OUTBOUND}
{literal}
document.getElementById('email_link_type').onchange = function()
{
    document.getElementById('mail_smtpserver_tr').style.display = (this.value == 'mailto' ? 'none' : '' );
    document.getElementById('mail_smtpuser_tr').style.display = (this.value == 'mailto' ? 'none' : '' );
    document.getElementById('mail_smtppass_tr').style.display = (this.value == 'mailto' ? 'none' : '' );
    document.getElementById('test_outbound_settings_tr').style.display = (this.value == 'mailto' ? 'none' : '' );
}
document.getElementById('email_link_type').onchange();
{/literal}
{/if}
<!--//END SUGARCRM flav!=sales ONLY -->
-->
</script>
{$JAVASCRIPT}
<!--//BEGIN SUGARCRM flav!=sales ONLY -->
{literal}
<script type="text/javascript" language="Javascript">
{/literal}
{$getNameJs}
{$getNumberJs}
{$confirmReassignJs}
{$currencySymbolJs}
themeGroupList = {$themeGroupListJSON};

setSymbolValue(document.getElementById('currency_select').options[document.getElementById('currency_select').selectedIndex].value);
setSigDigits();

</script>

</form>

<!--//END SUGARCRM flav!=sales ONLY -->
<div id="testOutboundDialog" class="yui-hidden">
    <div id="testOutbound">
        <form>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
			<tr>
				<td scope="row">
					{$APP.LBL_EMAIL_SETTINGS_FROM_TO_EMAIL_ADDR} 
					<span class="required">
						{$APP.LBL_REQUIRED_SYMBOL}
					</span>
				</td>
				<td >
					<input type="text" id="outboundtest_from_address" name="outboundtest_from_address" size="35" maxlength="64" value="{$TEST_EMAIL_ADDRESS}">
				</td>
			</tr>
			<tr>
				<td scope="row" colspan="2">
					<input type="button" class="button" value="   {$APP.LBL_EMAIL_SEND}   " onclick="javascript:sendTestEmail();">&nbsp;
					<input type="button" class="button" value="   {$APP.LBL_CANCEL_BUTTON_LABEL}   " onclick="javascript:EmailMan.testOutboundDialog.hide();">&nbsp;
				</td>
			</tr>
		</table>
		</form>
	</div>
</div>