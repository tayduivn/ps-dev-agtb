{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}
<!--//FILE SUGARCRM flav=int ONLY -->

<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/yui/build/paginator/paginator-min.js'}"></script>
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/activitiesReports.js'}"></script>

<table width="100%" cellpadding="1" cellspacing="1" border="0" >
	<tr>
		<td style="padding-bottom: 2px;" colspan=6>
			<form name="EditView" id="EditView" method="post" action="index.php">
{sugar_csrf_form_token}
				<input type="hidden" name="module" value="Activities" />
				<input type="hidden" name="run_report" id="run_report" value="0" />
				<input type="hidden" name="export_report" id="export_report" value="0" />
				<input type="hidden" name="to_pdf" id="to_pdf" value="1" />
				<input type="hidden" name="action" id="action" value="ActivitiesReports" />
		</td>
	</tr>
	<tr>
		<td width="10%">{$MOD.LBL_SELECT_MODULE}:<span class="required">*</span></td>
		<td><select id='parent_type' name='parent_type' onChange='changeParentQS(this);clearFields(false);'>
			{foreach from=$PARENT_TYPES key="KEY" item="PARENT"}
				{if $PARENT_TYPE == $KEY}
					<option value="{$KEY}" selected>{$PARENT}</option>
				{else}
					<option value="{$KEY}">{$PARENT}</option>
				{/if}

			{/foreach}		
			</select>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_SELECT_RECORD}:<span class="required">*</span></td>
		<td>
		<input id="parent_name" class="sqsEnabled" type="text" autocomplete="off" value="{$object_name}" size="" tabindex="p" name="parent_name"/>
		<input id="parent_id" type="hidden" value="{$object_id}" name="parent_id"/>
		<input id="object_name" type="hidden" value="{$object_name}" name="object_name"/>
		<input type="button" onclick='open_popup(document.EditView.parent_type.value, 600, 400, "", true, false, {ldelim}"call_back_function":"set_return","form_name":"EditView","field_to_name_array":{ldelim}"id":"parent_id","name":"parent_name"{rdelim}{rdelim}, "single", true);' value="Select" class="button" title="Select" tabindex="p" name="btn_parent_name"/>
		<input type="button" value='{$MOD.LBL_CLEAR}' onclick="clearFields(true);" class="button"  title="Clear" tabindex="p" name="btn_clr_parent_name"/>
		</td>
	</tr>	
	<tr>
		<td>{$MOD.LBL_FILTER_DATE_RANGE_START}: </td>
		<td>

		<input name='date_start' id='date_start' tabindex='2' size='11' maxlength='10' type="text" value="{$DATE_START}">
		{sugar_getimage name="jscalendar" ext=".gif" alt=$USER_DATEFORMAT other_attributes='align="absmiddle" id="date_start_trigger" onclick="parseDate(document.getElementById(\'date_start\'), \'{$CALENDAR_DATEFORMAT}\');" '}&nbsp;</td>
		 
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_FILTER_DATE_RANGE_FINISH}: </td>
		<td><input name="date_finish" id="date_finish" type="input" tabindex='2' size='11' maxlength='10' value='{$DATE_FINISH}' />
		{sugar_getimage name="jscalendar" ext=".gif" alt=$USER_DATEFORMAT other_attributes='align="absmiddle" id="date_finish_trigger" onclick="parseDate(document.getElementById(\'date_finish\'), \'{$CALENDAR_DATEFORMAT}\');" '}&nbsp;</td>
		 
		</td>
	</tr>
	<tr>
		<td colspan=2><br/><input class="button" type="button" name="button" value="{$MOD.LBL_RUN_REPORT_BUTTON_LABEL}" onclick="submitForm('run');"  />
		&nbsp;&nbsp;<input class="button" type="button" name="button" value="{$MOD.LBL_EXPORT}" onclick="submitForm('export');"  />
		&nbsp;&nbsp;<input class="button" type="button" name="button" value="{$MOD.LBL_CLEAR}" onclick="clearFields(false);"  /></td>
		

	</tr>

</form>
</table>
<br/>
<div id="activitiesDiv" width="100%"></div> 

<script type="text/javascript">
Calendar.setup ({literal}{{/literal}
	inputField : "date_start", ifFormat : '{$CALENDAR_DATEFORMAT}', showsTime : false, button : "date_start_trigger", singleClick : true, step : 1, weekNumbers:false{literal}}{/literal});
Calendar.setup ({literal}{{/literal}
	inputField : "date_finish", ifFormat : '{$CALENDAR_DATEFORMAT}', showsTime : false, button : "date_finish_trigger", singleClick : true, step : 1, weekNumbers:false{literal}}{/literal});
</script>
{$quicksearch_js}
<script type="text/javascript">
enableQS(false);

function submitForm(type) {ldelim}
	//clear_all_errors();
	if (trim(document.getElementById('parent_id').value) == '') {ldelim}
		//add_error_style('EditView', 'parent_id', requiredTxt);
		alert(requiredTxt);
		return;
	{rdelim}
	
	if (type == 'export') {ldelim}
		document.EditView.object_name.value=document.getElementById('parent_name').value;
		document.EditView.export_report.value='1';
		document.getElementById('EditView').submit();
		
	{rdelim}
	else {ldelim}
		document.EditView.object_name.value=document.getElementById('parent_name').value;
		document.EditView.export_report.value='0';
		document.EditView.run_report.value='1';
		YAHOO.util.Connect.setForm(document.getElementById("EditView"));
		openConnection = YAHOO.util.Connect.asyncRequest('POST', 'index.php', {ldelim} success: success, failure:{ldelim}{rdelim}});
		
		//document.getElementById('EditView').submit();
	{rdelim}
{rdelim}

function clearFields(skipDate) {ldelim}
	document.getElementById('object_name').value = '';
	document.getElementById('parent_name').value = ''; 
	document.getElementById('parent_id').value = ''; 
	if (!skipDate) {ldelim}
		document.getElementById('date_start').value = ''; 
		document.getElementById('date_finish').value = '';
		document.getElementById('activitiesDiv').innerHTML = ''; 
	{rdelim} 

{rdelim}

{literal}
if (typeof(changeParentQS) == 'undefined'){
function changeParentQS(field) {
	field = YAHOO.util.Dom.get(field);
    var form = field.form;
    var sqsId = form.id + "_" + field.id;
    var typeField =  form.elements.parent_type;
    var new_module = typeField.value;
    if(typeof(disabledModules[new_module]) != 'undefined') {
		sqs_objects[sqsId]["disable"] = true;
		field.readOnly = true;
	} else {
		sqs_objects[sqsId]["disable"] = false;
		field.readOnly = false;
    }
	//Update the SQS globals to reflect the new module choice
    sqs_objects[sqsId]["modules"] = new Array(new_module);
    if (typeof(QSFieldsArray[sqsId]) != 'undefined')
    {
        QSFieldsArray[sqsId].sqs.modules = new Array(new_module);
    }
	if(typeof QSProcessedFieldsArray != 'undefined')
    {
	   QSProcessedFieldsArray[sqsId] = false;
    }
    enableQS(false);
}}
{/literal}

function set_return(popup_reply_data) {ldelim}
	var form_name = popup_reply_data.form_name;
	var name_to_value_array = popup_reply_data.name_to_value_array;
 	document.getElementById('parent_id').value = name_to_value_array['parent_id'];
 	if (name_to_value_array['name'] == 'undefined')
	 	document.getElementById('parent_name').value = name_to_value_array['parent_id'];
	else
	 	document.getElementById('parent_name').value = name_to_value_array['parent_name'];
{rdelim}
</script>
