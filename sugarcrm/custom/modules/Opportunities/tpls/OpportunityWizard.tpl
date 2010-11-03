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
<script type="text/javascript" src="{sugar_getjspath file='include/SugarFields/Fields/Address/SugarFieldAddress.js'}"></script>

<p id="moduletitle">
{$MODULE_TITLE}
</p>

<form action="index.php" 
    method="post" 
    name="MassUpdate" 
    id="MassUpdate">

<input name="module" value="Opportunities" type="hidden">
<input name="action" value="OpportunityWizardSave" type="hidden">
<input name="return_module" value="Opportunities" type="hidden">
<input name="return_action" value="OpportunityWizard" type="hidden">
<input name="record" value="{$opp_fields.id.value}" type="hidden">
<input name="account_id" value="{$account_fields.id.value}" type="hidden">
<input name='lvso' value='{$smarty.request.lvso}' type='hidden' />
<input name='Contacts2_CONTACT_ORDER_BY' value='{$smarty.request.Contacts2_CONTACT_ORDER_BY}' type='hidden' />

<table class="tabForm" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
    <tr id="newoppdiv">
        <td align="left" valign="top">
            <table border="0" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_OPPORTUNITY_NAME}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    <input name="Opportunitiesname" id="Opportunitiesname" size="80" maxlength="255" value="{$opp_fields.name.value}" type="text">
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.Partner_Assigned_To_c}&nbsp;</td>
                <td class="dataField" width="37%">
                    {html_options id="Opportunitiespartner_assigned_to_c" name="Opportunitiespartner_assigned_to_c" options=$opp_fields.partner_assigned_to_c.options selected=$opp_fields.partner_assigned_to_c.value}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_TYPE}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    {html_options id="Opportunitiesopportunity_type" name="Opportunitiesopportunity_type" options=$opp_fields.opportunity_type.options selected=$opp_fields.opportunity_type.value}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_DATE_CLOSED}&nbsp;<span class="required">*</span>&nbsp;<span class="dateFormat">{$USER_DATEFORMAT}</span></td>
                <td class="dataField" width="37%">
                    <input name="Opportunitiesdate_closed" onblur="parseDate(this, '{$CAL_DATEFORMAT}');" size="12" maxlength="10" id="Opportunitiesjscal_field" value="{$opp_fields.date_closed.value}" type="text">&nbsp;
                    <img src="themes/default/images/jscalendar.gif" alt="Enter Date" id="Opportunitiesjscal_trigger" align="absmiddle">
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_SALES_STAGE}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    {html_options id="Opportunitiessales_stage" name="Opportunitiessales_stage" options=$opp_fields.sales_stage.options selected=$opp_fields.sales_stage.value}
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
                <td class="dataField" width="37%"><input name="Opportunitiesamount" id="Opportunitiesamount" value="{$opp_fields.amount.value}" type="text"></td>
            </tr>
 	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_ORDER_NUMBER}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Opportunitiesorder_number" id="Opportunitiesorder_number" value="{$opp_fields.order_number.value}" type="text"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.Term__c}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    {html_options id="OpportunitiesTerm_c" name="OpportunitiesTerm_c" options=$opp_fields.Term_c.options selected=$opp_fields.Term_c.value}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.Revenue_Type__c}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%">
                    {html_options id="OpportunitiesRevenue_Type_c" name="OpportunitiesRevenue_Type_c" options=$opp_fields.Revenue_Type_c.options selected=$opp_fields.Revenue_Type_c.value}
                </td>
            </tr>
	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.Renewal_Date_c}:&nbsp;<span class="required">*</span><span class="dateFormat">{$USER_DATEFORMAT}</span></td>
                <td class="dataField" width="37%">
          	<input name="Opportunitiesrenewal_date_c" onblur="parseDate(this, '{$CAL_DATEFORMAT}');" size="12" maxlength="10" id="Opportunitiesrenewal_date_c" value="{$opp_fields.renewal_date_c.value}" type="text">&nbsp;
          	<img src="themes/default/images/jscalendar.gif" alt="Enter Date" id="Opportunitiesrenewal_date_c_trigger" align="absmiddle">
		{literal}
		<script type="text/javascript">
			Calendar.setup ({
				inputField : "Opportunitiesrenewal_date_c",
				ifFormat : "{/literal}{$CAL_DATEFORMAT}{literal}",
				showsTime : false,
				button : "Opportunitiesrenewal_date_c_trigger",
				singleClick : true,
				dateStr : "",
				step : 1,
				weekNumbers:false
			});
		</script>
		{/literal}
		</td>
            </tr>	
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_CURRENT_SOLUTION}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Opportunitiescurrent_solution" id="Opportunitiescurrent_solution" value="{$opp_fields.current_solution.value}" type="text"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_COMPETITOR_1}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Opportunitiescompetitor_1" id="Opportunitiescompetitor_1" value="{$opp_fields.competitor_1.value}" type="text"></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_USERS_1}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%"><input name="Opportunitiesusers" id="Opportunitiesusers" type="text" value="{$opp_fields.users.value}" /></td>
            </tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_DESCRIPTION}</td>
                <td class="dataField" width="37%">
                    <textarea name="Opportunitiesdescription" id="Opportunitiesdescription" rows="5" cols="50">{$opp_fields.description.value}</textarea>
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
	    addToValidate('MassUpdate', 'Opportunitiesorder_number', 'varchar', true,'{$MOD_OPPORTUNITIES.LBL_ORDER_NUMBER}' );
            addToValidate('MassUpdate', 'Opportunitiesopportunity_type', 'enum', true,'{$MOD_OPPORTUNITIES.LBL_TYPE}' );
            addToValidate('MassUpdate', 'Opportunitiessales_stage', 'enum', true,'{$MOD_OPPORTUNITIES.LBL_SALES_STAGE}' );
            addToValidate('MassUpdate', 'Opportunitiesadditional_training_credits_c', 'varchar', true,'{$MOD_OPPORTUNITIES.Learning_Credits__c}' );
            addToValidate('MassUpdate', 'OpportunitiesTerm_c', 'enum', true,'{$MOD_OPPORTUNITIES.Term__c}' );
            addToValidate('MassUpdate', 'OpportunitiesRevenue_Type_c', 'enum', true,'{$MOD_OPPORTUNITIES.Revenue_Type__c}' );
            addToValidate('MassUpdate', 'Opportunitiescurrent_solution', 'varchar', true,'{$MOD_OPPORTUNITIES.LBL_CURRENT_SOLUTION}' );
            addToValidate('MassUpdate', 'Opportunitiescompetitor_1', 'varchar', true,'{$MOD_OPPORTUNITIES.LBL_COMPETITOR_1}' );
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
    <tr id="existingaccount">
        <td align="left" valign="top">
            <table cellpadding="0" border="0" cellspacing="0" width="100%">
            <tbody>
            <tr>
                <td colspan="4" class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_ACCOUNT_NAME}&nbsp;<span class="required">*</span></td>
            </tr>
            <tr>
                <td colspan="4" class="dataField" nowrap="nowrap">
                    <input name="Accountsname" id="Accountsname" value="{$account_fields.name.value}" type="text">
                </td>
            </tr>
            <tr>
                <td colspan="4" class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_BILLING_ADDRESS_STREET}&nbsp;<span class="required">*</span></td>
            </tr>
            <tr>
                <td colspan="4" class="dataField" nowrap="nowrap">
                    <textarea cols="80" rows="2" id="Accountsbilling_address_street" name="Accountsbilling_address_street">{$account_fields.billing_address_street.value}</textarea>
                </td>
            </tr>
            <tr>
                <td class="dataLabel">{$MOD_ACCOUNTS.LBL_CITY}&nbsp;<span class="required">*</span></td>
                <td class="dataLabel">{$MOD_ACCOUNTS.LBL_STATE}&nbsp;<span class="required">*</span></td>
                <td class="dataLabel">{$MOD_ACCOUNTS.LBL_POSTAL_CODE}&nbsp;<span class="required">*</span></td>
                <td class="dataLabel">{$MOD_ACCOUNTS.LBL_COUNTRY}&nbsp;<span class="required">*</span></td>
            </tr>
            <tr>
                <td class="dataField"><input name="Accountsbilling_address_city" id="Accountsbilling_address_city" maxlength="100" value="{$account_fields.billing_address_city.value}"></td>
                <td class="dataField"><input name="Accountsbilling_address_state" id="Accountsbilling_address_state" maxlength="100" value="{$account_fields.billing_address_state.value}"></td>
                <td class="dataField"><input name="Accountsbilling_address_postalcode" id="Accountsbilling_address_postalcode" maxlength="100" value="{$account_fields.billing_address_postalcode.value}"></td>
                <td class="dataField">
                    {html_options name="Accountsbilling_address_country" id="Accountsbilling_address_country" options=$account_fields.billing_address_country.options selected=$account_fields.billing_address_country.value}
                </td>
            </tr>
	    <tr>
		<td class="dataField"><input id="Accountsshipping_checkbox" name="Accountsshipping_checkbox" type="checkbox" onclick="syncFields('Accountsbilling', 'Accountsshipping');"; CHECKED>&nbsp;&nbsp;Copy from billing</td>
	    	<script type="text/javascript" language="javascript">
    			var fromKey = 'Accountsbilling';
    			var toKey = 'Accountsshipping';
    			var checkbox = toKey + "_checkbox";
    			var obj = new TestCheckboxReady(checkbox); 
		</script>
	    </tr>
            <tr>
                <td colspan="4" class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_SHIPPING_ADDRESS_STREET}</td>
            </tr>
            <tr>
                <td colspan="4" class="dataField" nowrap="nowrap">
                    <textarea cols="80" rows="2" name="Accountsshipping_address_street" id="Accountsshipping_address_street">{$account_fields.shipping_address_street.value}</textarea>
                </td>
            </tr>
            <tr>
                <td class="dataLabel">{$MOD_ACCOUNTS.LBL_CITY}</td>
                <td class="dataLabel">{$MOD_ACCOUNTS.LBL_STATE}</td>
                <td class="dataLabel">{$MOD_ACCOUNTS.LBL_POSTAL_CODE}</td>
                <td class="dataLabel">{$MOD_ACCOUNTS.LBL_COUNTRY}</td>
            </tr>
            <tr>
                <td class="dataField"><input name="Accountsshipping_address_city" id="Accountsshipping_address_city" maxlength="100" value="{$account_fields.shipping_address_city.value}"></td>
                <td class="dataField"><input name="Accountsshipping_address_state" id="Accountsshipping_address_state" maxlength="100" value="{$account_fields.shipping_address_state.value}"></td>
                <td class="dataField"><input name="Accountsshipping_address_postalcode" id="Accountsshipping_address_postalcode" maxlength="100" value="{$account_fields.shipping_address_postalcode.value}"></td>
                <td class="dataField">
                    {html_options name="Accountsshipping_address_country" id="Accountsshipping_address_country" options=$account_fields.shipping_address_country.options selected=$account_fields.shipping_address_country.value}
                </td>
            </tr>
            <tr>
                <td class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.Deployment_Type__c}&nbsp;<span class="required">*</span></td>
                <td class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_TYPE}&nbsp;<span class="required">*</span></td>
            	<td class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.partner_Type__c}&nbsp;</td>
		<!---<td class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_RESELL_DISCOUNT}&nbsp;</td>--!>
	    </tr>
            <tr>
                <td class="dataField" nowrap="nowrap">
                    {html_options name="Accountsdeployment_type_c" id="Accountsdeployment_type_c" options=$account_fields.deployment_type_c.options selected=$account_fields.deployment_type_c.value}
                </td>
		<td class="dataField" nowrap="nowrap">
		{html_options name="Accountsaccount_type" id="Accountsaccount_type" options=$account_fields.account_type.options selected=$account_fields.account_type.value onchange="accountTypeDependents();"}
                </td>
		<td class="dataField" nowrap="nowrap">
                    {html_options name="AccountsPartner_Type_c" id="AccountsPartner_Type_c" options=$account_fields.Partner_Type_c.options selected=$account_fields.Partner_Type_c.value disabled="true" style="background-color: #DCDCDC;"}
                </td>
		<!---<td class="dataField" nowrap="nowrap">
                    <input name="Accountsresell_discount" id="Accountsresell_discount" maxlength="36" value="{$account_fields.resell_discount.value}"  style="background-color: #DCDCDC;" disabled>
                </td>---!>
		
            </tr>
            <tr>
                <td class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.Support_Service_Level_c}&nbsp;<span class="required">*</span></td>
		<td class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_ANNUAL_REVENUE}&nbsp;<span class="required">*</span></td>
            	<td class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_INDUSTRY}&nbsp;<span class="required">*</span></td>
		<td class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_EMPLOYEES}&nbsp;<span class="required">*</span></td>
            </tr>
            <tr>
                <td class="dataField" nowrap="nowrap">
                    {html_options name="AccountsSupport_Service_Level_c" id="AccountsSupport_Service_Level_c" options=$account_fields.Support_Service_Level_c.options selected=$account_fields.Support_Service_Level_c.value}
                </td>
		<td class="dataField" nowrap="nowrap">
                    <input name="Accountsannual_revenue" id="Accountsannual_revenue" maxlength="100" value="{$account_fields.annual_revenue.value}">
                </td>
		<td class="dataField" nowrap="nowrap">
                    {html_options name="Accountsindustry" id="Accountsindustry" options=$account_fields.industry.options selected=$account_fields.industry.value}
                </td>
		<td class="dataField" nowrap="nowrap">
                    <input name="Accountsemployees" id="Accountsemployees" maxlength="100" value="{$account_fields.employees.value}">
                </td>
            </tr>
            <tr>
                <td colspan="4" class="dataLabel" nowrap="nowrap">{$MOD_ACCOUNTS.LBL_DESCRIPTION}</td>
            </tr>
            <tr>
                <td colspan="4" class="dataField" nowrap="nowrap">
                    <textarea cols="80" rows="4" name="Accountsdescription" id="Accountsdescription">{$account_fields.description.value}</textarea>
                </td>
            </tr>		
            </tbody>
            </table>
            <script type="text/javascript">
            <!--
            num_grp_sep = ',';
            dec_sep = '.';
            addToValidate('MassUpdate', 'Accountsname', 'varchar', true,'{$MOD_ACCOUNTS.LBL_ACCOUNT_NAME}' );
            addToValidate('MassUpdate', 'Accountsaccount_type', 'enum', true,'{$MOD_ACCOUNTS.Type__c}' );
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

<table class="tabForm" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
    <tr id="contactportalupdate">
        <td align="left" valign="top">
            <table cellpadding="0" border="0" cellspacing="0" width="100%">
            <tbody>
{foreach from=$contacts key=contact_id item=contact_data}
            <tr>
                <td class="dataLabel" nowrap="nowrap">Contact Name:</td>
                <td class="dataLabel" nowrap="nowrap"><a href="index.php?module=Contacts&action=DetailView&record={$contact_id}">{$contact_data.name}</a></td>
                <td class="dataLabel" nowrap="nowrap">Portal Name:</td>
                <td class="dataField" nowrap="nowrap">
			<input id="portal_name_{$contact_id}" name="portal_name_{$contact_id}" type="text" maxlength="30" value="{$contact_data.portal_name}">

			<input type="hidden" id="portal_name_existing_{$contact_id}" value="{$contact_data.portal_name}">

			<input type="button" name="btn_portal_name_{$contact_id}" title="{$APP.LBL_SELECT_BUTTON_TITLE}" accessKey="{$APP.LBL_SELECT_BUTTON_KEY}" class="button" value="{$APP.LBL_SELECT_BUTTON_LABEL}" onclick='window.open("custom/si_custom_files/choose_portal_name_popup.php?check_email={$contact_data.email1}&contact_id={$contact_id}", "choose_popup", "status=1, toolbar=1, menubar=1, location=1, scrollbars=1, width=600, height=400"); return false;'>

			<input type="hidden" id="portal_name_verified_{$contact_id}" value="true">
		</td>
                <td class="dataLabel" nowrap="nowrap"><input name="portal_active_{$contact_id}" type=checkbox {if $contact_data.portal_active}CHECKED{/if}>&nbsp;&nbsp;Portal Active</td>
		<td class="dataLabel" nowrap="nowrap"><input name="support_authorized_c_{$contact_id}" type=checkbox {if $contact_data.support_authorized_c}CHECKED{/if}>&nbsp;&nbsp;Support Authorized</td>
		<td class="dataLabel" nowrap="nowrap"><input name="billing_contact_c_{$contact_id}" type=checkbox {if $contact_data.billing_contact_c}CHECKED{/if}>&nbsp;&nbsp;Billing Contact</td>
		<td class="dataLabel" nowrap="nowrap"><input name="primary_business_c_{$contact_id}" type=checkbox {if $contact_data.primary_business_c}CHECKED{/if}>&nbsp;&nbsp;Primary Contact</td>
            </tr>
{/foreach}
			</tbody>
			</table>
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

    <td align="left"><input title="Save [Alt+S]" accesskey="S" class="button" name="button" value="Save" type="button" onclick="validation('{$DEPTFLAG}');"></td>
</tr>
</tbody>
</table>

</form>

<script type="text/javascript" src="custom/modules/Opportunities/js/OpportunityWizard.js"></script>
{$QSJAVASCRIPT}
