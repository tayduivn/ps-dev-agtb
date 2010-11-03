<script type="text/javascript" src="include/javascript/quicksearch.js"></script>
<script type="text/javascript" src="modules/ps_Timesheets/timesheets.js"></script>
<link href="modules/ps_Timesheets/timesheets.css" rel="stylesheet" type="text/css">

<div class='moduleTitle'><h2>{$MOD.LBL_MODULE_NAME}</h2></div>
<form id='EditView' name='EditView' method='POST' action='index.php'>
<input type="hidden" name="module" value="ps_Timesheets" />
<input type="hidden" name="action" value="saveTimeEntry" />
<input type="hidden" name="to_pdf" value="1" />

<table border="1" cellspacing="10" width="100%">
<tr>
	<td class="fieldLabel">Date</td>
	<td class="fieldLabel">Task</td>
	<td class="fieldLabel">Type</td>
	<td class="fieldLabel">Time Spent</td>
	<td class="fieldLabel">Description</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>
	<input autocomplete="off" type='text' id='activity_date' name='activity_date' size='10' />
	<img border="0" src="{sugar_getimagepath file='jscalendar.gif'}" alt="{$APP.LBL_ENTER_DATE}" id="activity_date_trigger" align="absmiddle" />
	</td>
	<td><select name='task' id='task' size='1' />{$TASK_OPTIONS}</select></td>
	<td><select name='activity_type' id='activity_type' size='1' />{$ACTIVITY_TYPE_OPTIONS}</select></td>
	<td><input type='text' name='time_spent' id='time_spent' size='10' /></td>
<!---/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #:17974
** Description: Timeshssets module enhancements
** Wiki customization page: 
*/--->
	<td><input type='text' name='description' id='description' size='45' /></td>
	<td><input type='button' id='save' value='Save' /></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td colspan="5"><div id='account_name'></div></td>
</tr>
<!---/*** END SUGARINTERNAL CUSOTMIZATION ***/--->
</table>
</form>

<br/><br/><br/><br/>

<table id="timesheets" border="1" cellspacing="10" width="100%"></table>

{$QS_JAVASCRIPT}
{$CALENDAR_SETUP}
{$ACCOUNT_NAME_LIST}
