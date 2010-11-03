{*

/**
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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

 */
 
*}
<p id="moduletitle">
{$MODULE_TITLE}
</p>

<form action="index.php" 
    method="post" 
    name="MassUpdate" 
    id="MassUpdate" >

<input name="module" value="LeadAccounts" type="hidden">
<input name="action" value="ConvertLeadSave" type="hidden">
<input name="return_module" value="LeadAccounts" type="hidden">
<input name="return_action" value="ConvertLead" type="hidden">
<input name="record" value="{$fields.id.value}" type="hidden">
<input name='lvso' value='{$smarty.request.lvso}' type='hidden' />
<input name='LeadContacts2_LEADCONTACT_ORDER_BY' value='{$smarty.request.LeadContacts2_LEADCONTACT_ORDER_BY}' type='hidden' />

<table class="tabForm" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
    {if $fields.account_name.value != ''}
    <tr>
        <td class="dataLabel" nowrap="nowrap" width="12%">
            {$MOD.LBL_ACCOUNT_NAME}
        </td>
        <td class="dataField" nowrap="nowrap" width="37%">
            <a href="index.php?module=Accounts&action=DetailView&record={$fields.account_id.value}">{$fields.account_name.value}</a>
        </td>
        <td class="dataLabel" nowrap="nowrap" width="12%">&nbsp;</td>
        <td class="dataField" nowrap="nowrap" width="37%">&nbsp;</td>
    </tr>
    {else}
    <tr>
        <td border="0" align="left" valign="top">
            <h4 class="dataLabel">
                <input type="radio" name="create_account" id="select_account" value="no" 
                    onclick='toggleDisplay("existingaccount");toggleDisplay("newaccount"); checkAccountRadio(this.id, this.checked);' />
                {$MOD.LNK_SELECT_ACCOUNT}
            </h4>
        </td>
    </tr>
    <tr id="existingaccount" style="display: none;">
        <td align="left" valign="top">
            <table cellpadding="0" border="0" cellspacing="0" width="100%">
            <tbody>
            <tr>
                <td class="dataLabel" nowrap="nowrap">{$MOD.LBL_ACCOUNT_NAME}&nbsp;<span class="required">*</span></td>
                <td colspan="3" class="dataLabel" nowrap="nowrap">
                    <input type="text" name="account_name" class="sqsEnabled" tabindex="l" id="account_name" size="" value="" title="" autocomplete="off" />
                    <input type="hidden" name="account_id" id="account_id" value="" />
                    <input type="button" name="btn_account_name" tabindex="l" title="{$APP.LBL_SELECT_BUTTON_TITLE}" accessKey="{$APP.LBL_SELECT_BUTTON_KEY}" class="button" value="{$APP.LBL_SELECT_BUTTON_LABEL}" 
                        {literal}onclick='open_popup("Accounts", 600, 400, "", true, false, {"call_back_function":"set_return","form_name":"MassUpdate","field_to_name_array":{"id":"account_id","name":"account_name"}}, "single", true);'{/literal} />
                    <input type="button" name="btn_clr_account_name" tabindex="l" title="{$APP.LBL_CLEAR_BUTTON_TITLE}" accessKey="{$APP.LBL_CLEAR_BUTTON_KEY}" class="button" onclick="this.form.account_name.value = ''; this.form.account_id.value = '';" value="{$APP.LBL_CLEAR_BUTTON_LABEL}" />
                </td>
            </tr>
            </tbody>
            </table>
        </td>
    <tr>
        <td border="0" align="left" valign="top">
            <h4 class="dataLabel">
                <input type="radio" name="create_account" id="create_account" checked="checked" value="yes" onclick='toggleDisplay("existingaccount");toggleDisplay("newaccount"); checkAccountRadio(this.id, this.checked);' />
                {$MOD.LNK_NEW_ACCOUNT}
            </h4>
        </td>
    </tr>
	<tr id="newaccount">
        <td align="left" valign="top">
            <table cellpadding="0" border="0" cellspacing="0" width="100%">
            <tbody>
            <tr>
                <td colspan="4" class="dataLabel" nowrap="nowrap">{$MOD.LBL_ACCOUNT_NAME}&nbsp;<span class="required">*</span></td>
            </tr>
            <tr>
                <td colspan="4" class="dataField" nowrap="nowrap">
                    <input name="Accountsname" value="{$fields.name.value}" type="text">
                </td>
            </tr>
            <tr>
                <td colspan="4" class="dataLabel" nowrap="nowrap">{$MOD.LBL_BILLING_ADDRESS_STREET}</td>
            </tr>
            <tr>
                <td colspan="4" class="dataField" nowrap="nowrap">
                    <textarea cols="80" rows="2" name="Accountsbilling_address_street">{$fields.billing_address_street.value}</textarea>
                </td>
            </tr>
            <tr>
                <td class="dataLabel">{$MOD.LBL_CITY}</td>
                <td class="dataLabel">{$MOD.LBL_STATE}</td>
                <td class="dataLabel">{$MOD.LBL_POSTAL_CODE}</td>
                <td class="dataLabel">{$MOD.LBL_COUNTRY}</td>
            </tr>
            <tr>
                <td class="dataField"><input name="Accountsbilling_address_city" maxlength="100" value="{$fields.billing_address_city.value}"></td>
                <td class="dataField"><input name="Accountsbilling_address_state" maxlength="100" value="{$fields.billing_address_state.value}"></td>
                <td class="dataField"><input name="Accountsbilling_address_postalcode" maxlength="100" value="{$fields.billing_address_postalcode.value}"></td>
                <td class="dataField">
                    {html_options name="Accountsbilling_address_country" options=$fields.billing_address_country.options selected=$fields.billing_address_country.value}
                </td>
            </tr>
            <tr>
                <td colspan="4" class="dataLabel" nowrap="nowrap">{$MOD.LBL_SHIPPING_ADDRESS_STREET}</td>
            </tr>
            <tr>
                <td colspan="4" class="dataField" nowrap="nowrap">
                    <textarea cols="80" rows="2" name="Accountsshipping_address_street">{$fields.shipping_address_street.value}</textarea>
                </td>
            </tr>
            <tr>
                <td class="dataLabel">{$MOD.LBL_CITY}</td>
                <td class="dataLabel">{$MOD.LBL_STATE}</td>
                <td class="dataLabel">{$MOD.LBL_POSTAL_CODE}</td>
                <td class="dataLabel">{$MOD.LBL_COUNTRY}</td>
            </tr>
            <tr>
                <td class="dataField"><input name="Accountsshipping_address_city" maxlength="100" value="{$fields.shipping_address_city.value}"></td>
                <td class="dataField"><input name="Accountsshipping_address_state" maxlength="100" value="{$fields.shipping_address_state.value}"></td>
                <td class="dataField"><input name="Accountsshipping_address_postalcode" maxlength="100" value="{$fields.shipping_address_postalcode.value}"></td>
                <td class="dataField">
                    {html_options name="Accountshipping_address_country" options=$fields.shipping_address_country.options selected=$fields.shipping_address_country.value}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" nowrap="nowrap">{$MOD.LBL_OFFICE_PHONE}</td>
                <td class="dataLabel" nowrap="nowrap">{$MOD.LBL_FAX_PHONE}</td>
                <td class="dataLabel" nowrap="nowrap">{$MOD.LBL_OTHER_PHONE}</td>
                <td class="dataLabel" nowrap="nowrap">{$MOD.LBL_LEAD_SOURCE}</td>
            </tr>
            <tr>
                <td class="dataField" nowrap="nowrap"><input name="Accountsphone_office" value="{$fields.phone_office.value}" type="text"></td>
                <td class="dataField" nowrap="nowrap"><input name="Accountsphone_fax" value="{$fields.phone_fax.value}" type="text"></td>
                <td class="dataField" nowrap="nowrap"><input name="Accountsphone_alternate" value="{$fields.phone_alternate.value}" type="text"></td>
                <td class="dataField" nowrap="nowrap">
                    {html_options name="Accountlead_source" options=$fields.lead_source.options selected=$fields.lead_source.value}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" nowrap="nowrap">{$MOD.LBL_WEBSITE}</td>
                <td class="dataLabel" nowrap="nowrap">{$MOD.Type__c}&nbsp;<span class="required">*</span></td>
            </tr>
            <tr>
                <td class="dataField" nowrap="nowrap"><input name="Accountswebsite" value="{$fields.website.value}" type="text"></td>
                <td class="dataField" nowrap="nowrap">
                    {html_options name="Accountsaccount_type" options=$fields.account_type.options selected=$fields.account_type.value}
                </td>
            </tr>
            <tr>
                <td colspan="4" class="dataLabel" nowrap="nowrap">{$MOD.LBL_DESCRIPTION}</td>
            </tr>
            <tr>
                <td colspan="4" class="dataField" nowrap="nowrap">
                    <textarea cols="80" rows="4" name="Accountsdescription">{$fields.description.value}</textarea>
                </td>
            </tr>		
            </tbody>
            </table>
            <script type="text/javascript">
            <!--
            num_grp_sep = ',';
            dec_sep = '.';
            addToValidate('MassUpdate', 'Accountsname', 'varchar', true,'{$MOD.LBL_ACCOUNT_NAME}' );
            addToValidate('MassUpdate', 'Accountsaccount_type', 'enum', true,'{$MOD.Type__c}' );
            -->
            </script>
        </td>
    </tr>
    <tr>
        <td border="0" align="left" valign="top">
            <h4 class="dataLabel">
                <input name="newaccountnote" onclick='toggleDisplay("accountnote");' type="checkbox">&nbsp;
                {$APP.LBL_CREATE_NOTE}
            </h4>
        </td>
    </tr>
    <tr id="accountnote" style="display: none;">
        <td align="left" valign="top">
            <table border="0" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_NOTES.LBL_NOTE_SUBJECT}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="AccountNotesname" size="80" maxlength="255" value="" type="text"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_NOTES.LBL_NOTE}</td>
                <td class="dataField" width="37%"><textarea name="AccountNotesdescription" cols="80" rows="4"></textarea></td>
            </tr>
            </tbody>
            </table>
            <script type="text/javascript">
            <!--
            num_grp_sep = ',';
            dec_sep = '.';
            addToValidate('MassUpdate', 'AccountNotesname', 'name', true,'{$MOD_NOTES.LBL_NOTE_SUBJECT}' );
            -->
            </script>
        </td>
    </tr>
    {/if}
    </tbody>
    </table>
    </td>
</tr>
</tbody>
</table>

{$LEAD_CONTACTS_LISTVIEW}

<table class="tabForm" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
    <tr>
        <td class="dataLabel">
            <h4 class="dataLabel">
                <input class="checkbox" name="newopportunity" type="checkbox"
                    onclick='toggleDisplay("newoppdiv");' >
                &nbsp;{$MOD.LNK_NEW_OPPORTUNITY}
            </h4>
        </td>
    </tr>
    <tr id="newoppdiv" style="display: none;">
        <td align="left" valign="top">
            <table border="0" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_OPPORTUNITY_NAME}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    <input name="Opportunitiesname" size="80" maxlength="255" value="" type="text">
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD.LBL_RELATE_TO_CONTACT}</td>
                <td class="dataField" width="37%">
                    {html_options id="Opportunitiescontact_id" name="Opportunitiescontact_id" options=$leadcontacts selected="account" onchange="checkContact(this.options[this.selectedIndex].value)"}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD.LBL_TYPE}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    {html_options id="Opportunitiesopportunity_type" name="Opportunitiesopportunity_type" options=$opp_fields.opportunity_type.options}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_DATE_CLOSED}&nbsp;<span class="required">*</span>&nbsp;<span class="dateFormat">{$USER_DATEFORMAT}</span></td>
                <td class="dataField" width="37%">
                    <input name="Opportunitiesdate_closed" onblur="parseDate(this, '{$CAL_DATEFORMAT}');" size="12" maxlength="10" id="Opportunitiesjscal_field" value="{$Opportunitiesdate_closed}" type="text">&nbsp;
                    <img src="themes/default/images/jscalendar.gif" alt="Enter Date" id="Opportunitiesjscal_trigger" align="absmiddle">
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_SALES_STAGE}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    {html_options id="Opportunitiessales_stage" name="Opportunitiessales_stage" options=$opp_fields.sales_stage.options}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_PROBABILITY}</td>
                <td class="dataField">
                    <input title="" value="" id="Opportunitiesprobability" name="Opportunitiesprobability" type="text">	
                    {literal}
                    <script type="text/javascript">
                    <!--
                    prob_array = {/literal}{$PROB_ARRAY}{literal};
                    document.getElementById('Opportunitiessales_stage').onchange = function() {
                        if(typeof(document.getElementById('Opportunitiessales_stage').value) != "undefined" && prob_array[document.getElementById('Opportunitiessales_stage').value]) {
                            document.getElementById('Opportunitiesprobability').value = prob_array[document.getElementById('Opportunitiessales_stage').value];
                        } 
                    };
                    document.getElementById('Opportunitiessales_stage').onchange();
                    -->
                    </script>
                    {/literal}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_AMOUNT}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Opportunitiesamount" value="" type="text"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.Term__c}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    {html_options id="OpportunitiesTerm_c" name="OpportunitiesTerm_c" options=$opp_fields.Term_c.options}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.Revenue_Type__c}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    {html_options id="OpportunitiesRevenue_Type_c" name="OpportunitiesRevenue_Type_c" options=$opp_fields.Revenue_Type_c.options}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_CURRENT_SOLUTION}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Opportunitiescurrent_solution" value="{$Opportunitiescurrent_solution}" type="text"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_COMPETITOR_1}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Opportunitiescompetitor_1" value="{$Opportunitiescompetitor_1}" type="text"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_USERS_1}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Opportunitiesusers" type="text" value="{$Opportunitiesusers}" /></td>
            </tr
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_DESCRIPTION}</td>
                <td class="dataField" width="37%">
                    <textarea name="Opportunitiesdescription" rows="5" cols="50">{$Opportunitiesdescription}</textarea>
                </td>
            </tr>
            </tbody>
            </table>
            {literal}
            <script type="text/javascript">
            <!--
            Calendar.setup ({
                inputField : "Opportunitiesjscal_field", ifFormat : "{/literal}{$CAL_DATEFORMAT}{literal}", showsTime : false, button : "Opportunitiesjscal_trigger", singleClick : true, step : 1
            });
            -->
            </script>
            {/literal}
            <script type="text/javascript">
            <!--
            num_grp_sep = ',';
            dec_sep = '.';
            addToValidate('MassUpdate', 'Opportunitiesname', 'name', true,'{$MOD_OPPORTUNITIES.LBL_OPPORTUNITY_NAME}' );
            addToValidate('MassUpdate', 'Opportunitiesdate_closed', 'date', true,'{$MOD_OPPORTUNITIES.LBL_DATE_CLOSED}' );
            addToValidate('MassUpdate', 'Opportunitiesamount', 'currency', true,'{$MOD_OPPORTUNITIES.LBL_AMOUNT}' );
            addToValidate('MassUpdate', 'Opportunitiesopportunity_type', 'enum', true,'{$MOD_OPPORTUNITIES.LBL_TYPE}' );
            addToValidate('MassUpdate', 'Opportunitiessales_stage', 'enum', true,'{$MOD_OPPORTUNITIES.LBL_SALES_STAGE}' );
            addToValidate('MassUpdate', 'Opportunitiesadditional_training_credits_c', 'varchar', true,'{$MOD_OPPORTUNITIES.Learning_Credits__c}' );
            addToValidate('MassUpdate', 'OpportunitiesTerm_c', 'enum', true,'{$MOD_OPPORTUNITIES.Term__c}' );
            addToValidate('MassUpdate', 'OpportunitiesRevenue_Type_c', 'enum', true,'{$MOD_OPPORTUNITIES.Revenue_Type__c}' );
            addToValidate('MassUpdate', 'Opportunitiescurrent_solution', 'varchar', true,'{$MOD_OPPORTUNITIES.LBL_CURRENT_SOLUTION}' );
            addToValidate('MassUpdate', 'Opportunitiescompetitor_1', 'varchar', true,'{$MOD_OPPORTUNITIES.LBL_COMPETITOR_1}' );
            -->
            </script>
            <h4 class="dataLabel">
                <input name="newoppnote" onclick='toggleDisplay("oppnote");' type="checkbox">&nbsp;
                {$APP.LBL_CREATE_NOTE}
            </h4>
            <table border="0" cellpadding="0" cellspacing="0" id="oppnote" style="display: none;">
            <tbody>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_NOTES.LBL_NOTE_SUBJECT}<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="OpportunityNotename" size="85" maxlength="255" value="" type="text"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_NOTES.LBL_NOTE}</td>
                <td class="dataField" width="37%"><textarea name="OpportunityNotedescription" cols="85" rows="4"></textarea></td>
            </tr>
            </tbody>
            </table>
            <script type="text/javascript">
            <!--
            num_grp_sep = ',';
            dec_sep = '.';
            addToValidate('MassUpdate', 'OpportunityNotesname', 'name', true,'{$MOD_NOTES.LBL_NOTE_SUBJECT}' );
            -->
            </script>
        </td>
    </tr>
    </tbody>
    </table>
    </td>
</tbody>
</table>

<table class="tabForm" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
    <tr>
        <td class="dataLabel">
            <h4 class="dataLabel">
                <input class="checkbox" name="newmeeting" onclick='toggleDisplay("newmeetingdiv");' type="checkbox" />&nbsp;
                {$MOD.LNK_NEW_APPOINTMENT}
            </h4>
        </td>
    </tr>
    <tr id="newmeetingdiv" style="display: none;">
        <td align="left" valign="top">
            <input name="Appointmentsdirection" value="Outbound" type="hidden">
            <input name="Appointmentsstatus" value="Planned" type="hidden">
            <input name="Appointmentsduration_hours" value="1" type="hidden">
            <input name="Appointmentsduration_minutes" value="0" type="hidden">
            <table border="0" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td class="dataLabel" width="12%">&nbsp;</td>
                <td class="dataField" width="37%">
                    <input name="Appointmentsappointment_type" value="Call" class="radio" checked="checked" type="radio" /> {$MOD_CALLS.LNK_NEW_CALL}&nbsp;
                    <input name="Appointmentsappointment_type" value="Meeting" class="radio" type="radio" /> {$MOD_CALLS.LNK_NEW_MEETING}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD.LBL_ADD_AS_INVITEE}</td>
                <td class="dataField" width="37%">
                    {html_options id="Appointmentsparent_id" name="Appointmentsparent_id" options=$leadcontacts selected="account" onchange="checkContact(this.options[this.selectedIndex].value)"}
                </td>
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_CALLS.LBL_SUBJECT}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Appointmentsname" maxlength="255" type="text" /></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_CALLS.LBL_DATE}&nbsp;<span class="required">*</span>&nbsp;<span class="dateFormat">{$USER_DATEFORMAT}</span></td>
                <td class="dataField" width="37%"><input onblur="parseDate(this, '{$CAL_DATEFORMAT}');" name="Appointmentsdate_start" size="12" id="Appointmentsjscal_field" maxlength="10" value="{$USER_DATEDEFAULT}" type="text"> <img src="themes/default/images/jscalendar.gif" alt="Enter Date" id="Appointmentsjscal_trigger" align="absmiddle"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_CALLS.LBL_TIME}&nbsp;<span class="required">*</span>&nbsp;<span class="dateFormat">{$USER_TIMEFORMAT}</span></td>
                <td class="dataField" width="37%">
                    <input name="Appointmentstime_start" size="12" maxlength="5" value="{$USER_TIMEDEFAULT}" type="text">
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_CALLS.LBL_DESCRIPTION}</td>
                <td class="dataField" width="37%">
                    <textarea name="Appointmentsdescription" cols="50" rows="5"></textarea>
                </td>
            </tr>
            </tbody>
            </table>
            {literal}
            <script type="text/javascript">
            <!--
            Calendar.setup ({
                inputField : "Appointmentsjscal_field", daFormat : "{/literal}{$CAL_DATEFORMAT}{literal}", ifFormat : "{/literal}{$CAL_DATEFORMAT}{literal}", showsTime : false, button : "Appointmentsjscal_trigger", singleClick : true, step : 1
            });
            -->
            </script>
            {/literal}
            <script type="text/javascript">
            <!--
            num_grp_sep = ',';
            dec_sep = '.';
            addToValidate('MassUpdate', 'Appointmentsname', 'name', true,'Subject' );
            addToValidate('MassUpdate', 'Appointmentsdate_start', 'datetime', true,'Start Date' );
            -->
            </script>
        </td>
    </tr>
	</tbody>
    </table>
	</td>
</tr>
</tbody>
</table>


<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td align="left"><input title="Save [Alt+S]" accesskey="S" class="button" name="button" value="Save" type="submit"></td>
</tr>
</tbody>
</table>

</form>

<script type="text/javascript" src="modules/LeadAccounts/js/ConvertLead.js"></script>
{$QSJAVASCRIPT}
