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

<script src='custom/include/javascript/custom_javascript.js'></script>
<script type="text/javascript" src="{sugar_getjspath file='include/SugarFields/Fields/Address/SugarFieldAddress.js'}"></script>
<script type="text/javascript">

</script>
<p id="moduletitle">
{* added DetailView form with record and module fields in order to get js navigations to work for subpanels *}
<form id='DetailView' name = 'DetailView'>
<input type="hidden" name="record" id="record" value="{$opp_fields.id.value}" />
<input type="hidden" name="module" id="module" value="P1_Partners" />
</form>


<input type="hidden" name="P1_PartnersEvalWizardSave" id="P1_PartnersEvalWizardSave" value="P1_PartnersEvalWizardSave" /> 
<input type="hidden" name="Opportunitiesid" id="Opportunitiesid" value="{$opp_fields.id.value}" />
<input type="hidden" name="opp_account_id" id="opp_account_id" value="{$opp_account_id}" />
<input type="hidden" name="OpportunitiesSaleStage" id="OpportunitiesSalesStage" value="{$opp_fields.sales_stage.value}" />
<input name="module" id="module" value="P1_Partners" type="hidden" />
<input name="action" id="action" value="index" type="hidden" />
<input name="return_module" id="return_module" value="P1_Partners" type="hidden" />
<input name="return_action" id="return_action" value="index" type="hidden"/>
<input type="hidden" value="false" name="eval_update" id ="eval_update" />	
<br>Please use this wizard to provision an evaluation instance of Sugar Enterprise or Sugar Professional.
<br>&nbsp;

	<br> <h3>Opportunity: <a href="/index.php?module=Opportunities&action=EditView&record={$opp_fields.id.value}" target="_blank">{$opp_fields.name.value}</a></h3>
<table class="tabForm" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
		<tr>By default:</tr>
	<tr><td>- The number of users is 10.</td></tr>
	<tr><td>- The evaluation instance provisioned will have no demo data.</td></tr>
	<tr><td>- The welcome email will be sent to the primay email of the opportunity's account.</td></tr>
</td></tr>
    <tr id="evalwizdiv">
        <td align="left" valign="top">
            <table border="0" cellpadding="0" cellspacing="0">
            <tbody>
			<tr>
                <td class="dataLabel" width="20%">DataCenter:&nbsp;<span class="required">*</span></td>
				<td class="dataField" width="80%" >
				<input type="radio" name="data_center" value="us" checked onclick="set_od_domain('.sugarondemand.com');" /> USA
				&nbsp;&nbsp;<input type="radio" name="data_center" value="emea" onclick="set_od_domain('.sugaropencloud.eu');" /> EMEA
				</TD>
            </tr>
			<tr>
                <td class="dataLabel" width="20%">Eval Name:&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="80%" >
http://<INPUT type="text" size="25" id="instance_name" name="instance_name" onkeyup="jaxit('prood_check_instance.php?iname=');">&nbsp;<div id="od_domain" style="display:inline">.sugarondemand.com</div>
<br /><div id="jaxresult">&nbsp;</div>
<input type="hidden" name="invalidinstance" id="invalidinstance" value="1" /> 
                </td>
            </tr>
			<tr>
                <td class="dataLabel" width="20%">Flavor:&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="80%" ><input type="radio" id = "flavor" name="flavor" value="ent" checked>Enterprise&nbsp;&nbsp;<input type="radio" id = "flavor" name="flavor" value="pro" >Professional
				</td>
			</tr>
            <tr>
                <td class="dataLabel" width="20%">End Date:&nbsp;<span class="required">*</span>&nbsp;</td>
                <td class="dataField" width="80%" >
                    <input name="eval_end_date" onblur="parseDate(this, '{$CAL_DATEFORMAT}');" size="12" maxlength="10" id="eval_end_date" value="" type="text">&nbsp;
                    <img src="themes/default/images/jscalendar.gif" alt="Enter Date" id="EvalEndDate_trigger" align="absmiddle">
	
                </td>
            </tr>
			<tr>
				<td width="30%" colspan="2>
				<input type="hidden" value="10" name="numUsers" />	
				<div id="contactsew" >
				{if $account_email != ""} The welcome email will be sent to <b>{$account_email}</b>. <BR><BR> Click below to change the recipient to an account contact. <input type="hidden" value="true" name="email_flag" id ="email_flag" >{else} Account has no default email. Please set one in the account or select a contact email below. {/if} <BR></div>
				<input id="email_select_button"  title="Select Email" class="button" name="button" value="Select Account Contact" type="button" onclick="showContactsEW(); document.getElementById('email_select_button').style.visibility = 'hidden';">&nbsp;&nbsp;
				</td> 
			</tr>

			</tbody>
			</table>
    </td>
</tbody>
</table>
</td>
</tr>

<tr><td>&nbsp;</td></tr>
			
</tbody>
</table>
{*style="display:inline" *}
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>

<tr>
    <td align="left" class="dataLabel" style="padding-top:10px;">
	<input title="Submit [Alt+S]" accesskey="S" class="button" name="button" value="Submit" type="button" onclick="submitEvalReq()">&nbsp;&nbsp;
   </td>
    <td align="right" class="dataLabel" style="padding-top:10px; text-align: right;">
	<span class="required">*</span> Indicates a required field.
	</td>
</tr>
</tbody>
</table>

