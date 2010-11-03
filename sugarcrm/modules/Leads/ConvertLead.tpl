<!--
/*********************************************************************************
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
-->

<span class="color">{$ERROR}</span>
<script>
function isChecked(field) {ldelim}
	return eval("document.forms['ConvertLead']."+field+".checked");
 {rdelim}
function checkOpportunity(){ldelim}
		if(!isChecked('newopportunity')){ldelim}
			return true;
		{rdelim}

		
		removeFromValidate('ConvertLead', 'Opportunitiesaccount_name');
		if(validate_form('ConvertLead', 'Opportunities')){ldelim}

			if(this.document.forms['ConvertLead'].selectedAccount.value != ''){ldelim}
				return true;
			{rdelim}
			if(!isChecked('newaccount')){ldelim}
				alert('{$OPPNEEDSACCOUNT}');
				return false;					
			{rdelim}
			return true;
		{rdelim}
		return false;

{rdelim}
</script>
{$DUPLICATEFORMBODY}
{if empty($DUPLICATEFORMBODY)}
<form action='index.php' method='post' name='ConvertLead' onsubmit="return (validate_form('ConvertLead', 'Contacts') 
&& (!isChecked('newaccount') || validate_form('ConvertLead', 'Accounts')) 
{$CHECKOPPORTUNITY} 
&& (!isChecked('newmeeting') || validate_form('ConvertLead', 'Appointments'))
&& (!isChecked('newcontactnote') || validate_form('ConvertLead', 'ContactNotes')) 
&& (!isChecked('newaccountnote') || !isChecked('newaccount') || validate_form('ConvertLead', 'AccountNotes')) 
&& (!isChecked('newoppnote') || !isChecked('newopportunity') || validate_form('ConvertLead', 'OpportunityNotesname')) 
);">

<p>	<table  width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
	    <td align="left">
            <input title='{$APP.LBL_SAVE_BUTTON_TITLE}' accessKey='{$APP.LBL_SAVE_BUTTON_KEY}' class='button' type='submit' name='button' value='{$APP.LBL_SAVE_BUTTON_LABEL}' {$SAVE_BUTTON_DISPLAY}>
            <input title="{$APP.LBL_CANCEL_BUTTON_TITLE}"  onclick="document.location.href='index.php?module=Leads&action=DetailView&record={$RECORD}'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " {$CANCEL_BUTTON_DISPLAY}>
        </td>
	</tr>
	</table></p>
    
<input type="hidden" name="module" value="Leads">
<input type="hidden" name="action" value="ConvertLead">
<input type="hidden" name="handle" value="Save">
<input type="hidden" name="record" value="{$RECORD}">
	<script>
		function toggleDisplay(id){ldelim}
			if(this.document.getElementById( id).style.display=='none'){ldelim}
				this.document.getElementById( id).style.display='inline'
			{rdelim}else{ldelim}
				this.document.getElementById(  id).style.display='none'
			{rdelim}
		{rdelim}
	</script>
<p>	<table class='{$TABLECLASS}' cellpadding="0" cellspacing="0" width="100%" border="0" >
	<tr><td>
	<table cellpadding="0" cellspacing="0" width="100%" border="0" >

	{foreach from=$ROWVALUES item=row}
		<tr><td>{$row} </td></tr>
	{/foreach}

	<tr ><td valign='top' align='left' border='0' class="{$CLASS}"><h4 class="{$CLASS}">{$FORMHEADER}</h4></td></tr>
	<tr><td  valign='top' align='left'>{$FORMBODY}{$FORMFOOTER}{$POSTFORM}</td></tr>

	</table>
	</td>
	</tr>
	</table></p>
	
<p>	<table class='{$TABLECLASS}' cellpadding="0" cellspacing="0" width="100%" border="0" >
	<tr><td>
	<table cellpadding="0" cellspacing="0" width="100%" border="0" >

	<td class="{$CLASS}"><h4 class="{$CLASS}">{$RELATED_RECORDS_HEADER}</h4></td>

	{foreach from=$Related_records item=related}
		<tr><td  valign='top' align='left'  border='0'>{$related.FORMBODY}{$related.FORMFOOTER}{$related.POSTFORM}</td></tr>
	{/foreach}

	</table>
	</td>
	</tr>
	</table></p>

<p>	<table  width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
	    <td align="left">
            <input title='{$APP.LBL_SAVE_BUTTON_TITLE}' accessKey='{$APP.LBL_SAVE_BUTTON_KEY}' class='button' type='submit' name='button' value='{$APP.LBL_SAVE_BUTTON_LABEL}' {$SAVE_BUTTON_DISPLAY}>
            <input title="{$APP.LBL_CANCEL_BUTTON_TITLE}"  onclick="document.location.href='index.php?module=Leads&action=DetailView&record={$RECORD}'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " {$CANCEL_BUTTON_DISPLAY}>
        </td>
	</tr>
	</table></p>
	</form>
{/if}
