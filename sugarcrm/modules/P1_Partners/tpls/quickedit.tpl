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
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td align="left" class="dataLabel" style="padding-bottom:10px;">
        <input title="Save [Alt+S]" accesskey="S" class="button" name="button" value="Save" type="button" onclick="validation();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input title="Create Eval [Alt+E]" accesskey="E" class="button" name="button" value="Eval" type="button" onclick=";YAHOO.example.container.panel1.hide();getformContentsEvalWiz('{$opp_fields.id.value}');YAHOO.example.container.panel3.show();">
   </td>
    <td align="left" class="dataLabel" style="padding-bottom:10px;">
        
   </td>
   </tr>
</tbody>
</table>

{* added DetailView form with record and module fields in order to get js navigations to work for subpanels *}
<form id='DetailView' name = 'DetailView'>
<input type="hidden" name="record" id="record" value="{$opp_fields.id.value}" />
<input type="hidden" name="module" id="module" value="P1_Partners" />
</form>

<input type="hidden" name="P1_PartnersQuickEditSave" id="P1_PartnersQuickEditSave" value="P1_PartnersQuickEditSave" /> 
<input type="hidden" name="Opportunitiesid" id="Opportunitiesid" value="{$opp_fields.id.value}" />
<input type="hidden" name="Opportunitiessixtymin_opp_c" id="Opportunitiessixtymin_opp_c" value="{$opp_fields.sixtymin_opp_c.value}" />
<input name="module" id="module" value="P1_Partners" type="hidden" />
<input name="action" id="action" value="index" type="hidden" />
<input name="return_module" id="return_module" value="P1_Partners" type="hidden" />
<input name="return_action" id="return_action" value="index" type="hidden"/>

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
                <td class="dataLabel" width="16%">{$MOD_OPPORTUNITIES.LBL_OPPORTUNITY_NAME}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%" colspan="2">
                    <input name="Opportunitiesname" id="Opportunitiesname" size="50" maxlength="255" value="{$opp_fields.name.value}" type="text">
                </td>

            </tr>
	    <tr>
                <td class="dataLabel" width="16%">{$MOD_OPPORTUNITIES.LBL_LIST_ACCOUNT_NAME}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%" colspan="2">
                    <input name="Accountsname" id="Accountsname" size="50" maxlength="255" value="{$account_name}" type="text">
                </td>

            </tr>
	    {if $opp_fields.campaign_id.value}
	    <tr>
                <td class="dataLabel" width="16%">{$MOD_OPPORTUNITIES.LBL_CAMPAIGN}&nbsp;</td>
                <td class="dataField" width="37%" colspan="3">
                	<a href="index.php?module=Campaigns&action=DetailView&record={$opp_fields.campaign_id.value}" target="_blank">{$campaign_name}</a>
		</td>
            </tr>
	    {/if}
	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_TYPE}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%" colspan="1">
                    {html_options id="Opportunitiesopportunity_type" name="Opportunitiesopportunity_type" options=$opp_fields.opportunity_type.options selected=$opp_fields.opportunity_type.value}
                </td>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_SALES_STAGE}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="37%" colspan="3">
                    {html_options id="Opportunitiessales_stage" name="Opportunitiessales_stage" options=$opp_fields.sales_stage.options selected=$opp_fields.sales_stage.value onchange="javascript: showdiv();"}
                </td>

            </tr>
            <tr><td colspan="4">
		 <div id="div_info"></div>
	    </td></tr>
            <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_DATE_CLOSED}&nbsp;<span class="required">*</span>&nbsp;</td>
                <td class="dataField" width="12%" >
                    <input name="Opportunitiesdate_closed" onblur="parseDate(this, '{$CAL_DATEFORMAT}');" size="12" maxlength="10" id="Opportunitiesdate_closed" value="{$opp_fields.date_closed.value}" type="text">&nbsp;
                    <img src="themes/default/images/jscalendar.gif" alt="Enter Date" id="Opportunitiesdate_closed_trigger" align="absmiddle">
	
                </td>
		<td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_MAX_SCORE}:&nbsp;</td>
		<td class="dataLabel" width="12%">{$MAX_SCORE}&nbsp;</td>

            </tr>
	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.Partner_Assigned_To_c}:</td>
                <td class="dataField" colspan="3">
                        {html_options id="Opportunitiespartner_assigned_to_c" name="Opportunitiespartner_assigned_to_c" options=$opp_fields.partner_assigned_to_c.options selected=$opp_fields.partner_assigned_to_c.value}
                </td>
            </tr>
	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_AMOUNT}&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="12%"><input name="Opportunitiesamount" id="Opportunitiesamount" value="{$opp_fields.amount.value}" type="text"></td>
		<td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_USERS}&nbsp;<span class="required">*</span></td>
                <td class="dataField"><input name="Opportunitiesusers" id="Opportunitiesusers" value="{$opp_fields.users.value}" type="text"></td>
            </tr>
	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_CURRENT_SOLUTION}:&nbsp;<span class="required">*</span></td>
                <td class="dataField" width="12%"><input name="Opportunitiescurrent_solution" id="Opportunitiescurrent_solution" value="{$opp_fields.current_solution.value}" type="text"></td>
            	<td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_COMPETITOR_1}:&nbsp;<span class="required">*</span></td>
                <td class="dataField"><input name="Opportunitiescompetitor_1" id="Opportunitiescompetitor_1" value="{$opp_fields.competitor_1.value}" type="text"></td>
	    </tr>
	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_NEXT_STEP_DUE_DATE}:&nbsp;</td>
                <td class="dataField" width="12%"><input name="Opportunitiesnext_step_due_date" onblur="parseDate(this, '{$CAL_DATEFORMAT}');" size="12" maxlength="10" id="Opportunitiesnext_step_due_date" value="{$opp_fields.next_step_due_date.value}" type="text">
			<img src="themes/default/images/jscalendar.gif" alt="Enter Date" id="Opportunitiesnext_step_due_date_trigger" align="absmiddle">

		</td>
            	<td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_COMPETITOR_2}:</td>
                <td class="dataField"><input name="Opportunitiescompetitor_2" id="Opportunitiescompetitor_2" value="{$opp_fields.competitor_2.value}" type="text"></td>
	    </tr>
	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_NEXT_STEP}</td>
                <td class="dataField" width="37%" colspan="3"><textarea name="Opportunitiesnext_step" id="Opportunitiesnext_step" rows="2" cols="50">{$opp_fields.next_step.value}</textarea></td>
            </tr>
 	    <tr>
                <td class="dataLabel" width="12%">{$MOD_OPPORTUNITIES.LBL_DESCRIPTION}</td>
                <td class="dataField" width="37%" colspan="3">
                    <textarea name="Opportunitiesdescription" id="Opportunitiesdescription" rows="4" cols="50">{$opp_fields.description.value}</textarea>
                </td>
            </tr>
	    <tr>
                <td class="dataLabel" width="12%">Note Subject:&nbsp;</td>
                <td class="dataField" width="37%" colspan="3"><input name="Notessubject" id="Notessubject" value="" type="text" size="43"></td>
            </tr>

	    <tr>
                <td class="dataLabel" width="12%">Note Description:</td>
                <td class="dataField" width="37%" colspan="3">
                    <textarea name="Notesdescription" id="Notesdescription" rows="2" cols="50"></textarea>
                </td>
            </tr>
<tr>
		<td class="dataLabel" width="15%">{$MOD_OPPORTUNITIES.LBL_EVALUATION_START_DATE}:&nbsp;</td>
		<td class="dataLabel" width="25%">{$opp_fields.evaluation_start_date.value}&nbsp;</td>
		<td class="dataLabel" width="15%">{$MOD_OPPORTUNITIES.Evaluation_Close_Date__c}&nbsp;</td>
		<td class="dataLabel" width="25%">{$opp_fields.Evaluation_Close_Date_c.value}&nbsp;</td>
</tr>
<tr>
			<td class="dataLabel" width="15%">{$MOD_OPPORTUNITIES.LBL_EVAL_URL}:&nbsp;</td>
			<td class="dataLabel" width="25%">{$opp_fields.eval_url_c.value}&nbsp;</td>
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

{if (is_array($opp_notes) && count($opp_notes) > 0) || (is_array($opp_emails) && count($opp_emails) > 0)}
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
                <tr><td class="dataLabel" nowrap="nowrap" colspan="4"><b>History</b></td></tr>
                <tr>
                        <td class="dataLabel" nowrap="nowrap">&nbsp;</td>
                        <td class="dataLabel" nowrap="nowrap">Subject</td>
                        <td class="dataLabel" nowrap="nowrap">Status</td>
                        <td class="dataLabel" nowrap="nowrap">Date Modified</td>
                </tr>
{foreach from=$opp_notes key=cnt item=opp_id}
        {foreach from=$opp_id key=opp_id item=opp_note}
                <tr>
                        <td><img src="themes/Sugar/images/Notes.gif" alt="Notes" height="16" width="16" border='0' /></td>
                        <td class="dataLabel" nowrap="nowrap"><a href="index.php?module=Notes&action=DetailView&record={$opp_note.id}" target="_blank">{$opp_note.name}</a></td>
                        <td class="dataLabel" nowrap="nowrap">Note</td>
                        <td class="dataLabel" nowrap="nowrap">{$opp_note.date_modified}</td>
                </tr>
        {/foreach}

{/foreach}
{foreach from=$opp_emails key=email_cnt item=email_opp_id}
        {foreach from=$email_opp_id key=email_opp_id item=opp_email}
                <tr>
                        <td><img src="themes/Sugar/images/Emails.gif" alt="Emails" height="16" width="16" border='0' /></td>
                        <td class="dataLabel" nowrap="nowrap"><a href="index.php?module=Emails&action=DetailView&record={$opp_email.id}" target="_blank">{$opp_email.name}</a></td>
                        <td class="dataLabel" nowrap="nowrap">{$opp_email.status}</td>
                        <td class="dataLabel" nowrap="nowrap">{$opp_email.date_modified}</td>
                </tr>
        {/foreach}

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
{/if}

{if is_array($contacts) && count($contacts) > 0}
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
			<td class="dataLabel" nowrap="nowrap">Contact Name:</td>
			<td class="dataLabel" nowrap="nowrap">Office Phone:</td>
			<td class="dataLabel" nowrap="nowrap">Email: </td>
		</tr>
{foreach from=$contacts key=contact_id item=contact_data}
            <tr>
                <td class="dataLabel" nowrap="nowrap"><a target="_blank" href="index.php?module=Contacts&action=DetailView&record={$contact_id}">{$contact_data.name}</a></td>
                <td class="dataField" nowrap="nowrap">{if $user_office_phone ne "" and $contact_data.phone_work ne ""}<a href="#" title="Click to call" onclick="Popup=window.open('https://sugarinternal.sugarondemand.com/index.php?entryPoint=clicktocall&dial_first={$user_office_phone}&dial_second={$contact_data.trimmed_phone_work}','Popup','toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=250,height=250,left=1050,top=500'); return false;"><img style="text-align:middle;" src="/themes/default/images/Calls.gif" alt="Make a call." width="16px" height="16px" border=0 /></a>{/if} &nbsp;&nbsp;{if $contact_data.phone_work ne ""}{$contact_data.phone_work}{/if}</td>
		<td class="dataField" nowrap="nowrap"><a href="mailto:{$contact_data.email1}">{$contact_data.email1}</a></td>
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
{/if}


<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td align="left" class="dataLabel" style="padding-top:10px;">
	<input title="Save [Alt+S]" accesskey="S" class="button" name="button" value="Save" type="button" onclick="validation();">&nbsp;&nbsp;
   </td>
</tr>
</tbody>
</table>

