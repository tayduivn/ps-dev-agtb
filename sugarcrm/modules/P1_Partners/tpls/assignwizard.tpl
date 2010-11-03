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
{$TOGGLE_SCRIPT}
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td align="left" class="dataLabel" style="padding-bottom:10px;">
        <input title="Save [Alt+S]" accesskey="S" class="button" name="button" value="Save & Send Email" type="button" onclick="validationWiz();">&nbsp;&nbsp;
        <input class="button" type="button" value="Cancel" name="assign_wiz_hide" id="assign_wiz_hide"  onclick="toggleContainer();">
   </td>
</tr>
</tbody>
</table>

<input type="hidden" name="P1_Partnersopp_ids" id="P1_Partnersopp_ids" value="{$opp_id}" />
<input type="hidden" name="P1_Partnersuser_email" id="P1_Partnersuser_email" value="{$P1_Partnersuser_email}" />
<input type="hidden" name="P1_PartnersAssignWizardSave" id="P1_PartnersAssignWizardSave" value="P1_PartnersAssignWizardSave" /> 
<input name="return_module" id="return_module" value="P1_Partners" type="hidden">
<input name="return_action" id="return_action" value="index" type="hidden">

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
                <td class="dataLabel" width="12%" colspan="2">{$MOD_ASSIGN_TO_OPPS}:</td>
            </tr>
            <tr>
                <td class="dataLabel" width="60%" colspan="2" style="padding: 10px 0px 10px 20px;">
                	<table cellpadding="0" border="0" cellspacing="0" width="60%">
			<tbody>
			<tr>
				<td class="dataLabel" nowrap="nowrap" width="20%"><b>Account Name</b></td>
				<td class="dataLabel" nowrap="nowrap" width="20%"><b>Opportunity Size</b></td>
				<td class="dataLabel" nowrap="nowrap" width="20%"><b>Partner Assigned To</b></td>
			</tr>
			{foreach from=$oppAccounts key=opp_id item=opp_data}
			<tr>
				<td class="dataLabel" nowrap="nowrap" width="20%">{$opp_data.account_name}</td>
				<td class="dataLabel" nowrap="nowrap" width="20%">{sugar_currency_format var=$opp_data.opp_amount currency_id=$CURRENCY_ID}</td>	
				<td class="dataLabel" nowrap="nowrap" width="20%">{$opp_data.partner_account_name}</td>
			</tr>
			{/foreach}
			</tbody>
			</table>
		</td>
            </tr>

	    <tr>
                <td class="dataLabel" width="25%">{$MOD_ASSIGN_PARTNER_ASSIGNED_TO}: <span class="required">*</span>&nbsp;</td>
		<td class="dataField">
			{html_options id="P1_Partnerspartner_assigned_to_c" name="P1_Partnerspartner_assigned_to_c" options=$opp_fields.partner_assigned_to_c.options onchange="javascript: showContacts();"}
		</td>
            </tr>
			<tr>
                <td class="dataLabel" colspan="2">
                        <div id="div_info"></div>
                </td>
            </tr>
		  <tr>
                <td class="dataLabel" width="25%"><b>Outgoing Partner Email:</b> (<a href="javascript: toggleId('partnerEmail')" id="partnerEmailLink">Show Email</a>)</td>
		<td class="dataField">
		
		</td>
            </tr>
			
		</table>

		<table border="0" cellpadding="0" cellspacing="0" style="display: none;" id="partnerEmail">
	    <tr>
                <td class="dataLabel" width="25%" style="padding-top:10px;">{$MOD_ASSIGN_PARTNER_SUBJECT}: <span class="required">*</span>&nbsp;</td>
                <td class="dataField" style="padding-top:10px;">
                	<input type="text" name="P1_Partnersemail_subject" id="P1_Partnersemail_subject" value="New opportunities from SugarCRM" size="58">
		</td>
            </tr>

	    <tr>
		<td class="dataLabel" width="37%" colspan="2" style="padding-top:15px;">{$MOD_ASSIGN_PARTNER_EMAIL}: <span class="required">*</span>&nbsp;</td>
	    </tr>
	
	   <tr>
		<td class="dataLabel" width="37%" colspan="2"><textarea id='P1_Partnersbody_html' name="P1_Partnersbody_html" rows="20" cols="80">{$BODY_HTML}</textarea></td>
	   </tr>


    </tbody>
    </table>
    </td>
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

                <tr>
                        <td class="dataLabel" width="37%" colspan="2" style="padding:10px 0px 10px 0px">
			<p style="padding-bottom:10px;"><b>SEND CUSTOMER EMAIL:</b> (<a href="javascript: toggleId('customerEmail')" id="customerEmailLink">Show Email</a>)</p>
			<p><input type="checkbox" name="P1_Partners_check_send_contact_mail" id="P1_Partners_check_send_contact_mail" value="send_contact_mail" checked>&nbsp; Send the following email to the Opportunities contacts once these opportunities are accepted by the partner selected above<br /><span style="font-size:10px;padding-left:20px;">(Note: This email is sent ONLY when this opportunity is ACCEPTED by the assigned partner)</span></p></td>
                </tr>
				</table>
				<table cellpadding="0" border="0" cellspacing="0" width="100%" style="display: none;" id="customerEmail">
                <tr>
                        <td class="dataLabel" width="25%" style="padding-top:10px;">Customer Email Subject: <span class="required">*</span>&nbsp;</td>
                        <td class="dataField" style="padding-top:10px;">
                                <input type="text" name="P1_Partners_contactmail_subject" id="P1_Partners_contactmail_subject" value="Thank you for your interest in SugarCRM" size="58">
                        </td>
                </tr>
                <tr>
                        <td class="dataLabel" width="37%" colspan="2"><textarea id='P1_Partners_contactmail_body_html' name="P1_Partners_contactmail_body_html" rows="20" cols="80">{$CONTACTEMAIL_BODY_HTML}</textarea></td>
                </tr>

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
    <td align="left" class="dataLabel" style="padding-top:10px;">
	<input title="Save [Alt+S]" accesskey="S" class="button" name="button" value="Save & Send Email" type="button" onclick="validationWiz();">&nbsp;&nbsp;
   	<input class="button" type="button" value="Cancel" name="assign_wiz_hide1" id="assign_wiz_hide1"  onclick="toggleContainer();"> 
   </td>
</tr>
</tbody>
</table>

