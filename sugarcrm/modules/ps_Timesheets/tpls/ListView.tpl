<script type="text/javascript" src="include/javascript/quicksearch.js"></script>
<script type="text/javascript" src="modules/ps_Timesheets/timesheets.js"></script>
<link href="modules/ps_Timesheets/timesheets.css" rel="stylesheet" type="text/css">

<div class='moduleTitle'><h2>{$MOD.LBL_MODULE_NAME}</h2></div>

<form id='EditView' name='EditView' method='POST' action='index.php'>
<input type="hidden" name="module" value="ps_Timesheets" />
<input type="hidden" name="action" value="getTimesheets" />
<input type="hidden" name="to_pdf" value="1" />

<table border='0' cellpadding='0' cellspacing='0' width='400'>
<tr>
<td class='fieldLabel'>Consultant:</td>
<td> 
<select id='user_id' name='user_id'>{$USER_LIST_OPTIONS}</select>
</td>
<td> 
<select id='month' name='month'>{$MONTH_OPTIONS}</select>
<select id='year' name='year'>{$YEAR_OPTIONS}</select>
</td>
<td><input type='button' id='change_timesheet' value='Go' /></td>
</table>
<br/><br/>

<table id='timesheetsHeader' border="1" cellspacing="10" width="100%">
<tr>
	<td class="fieldLabel" width="5%">&nbsp;</td>
	<td class="fieldLabel" width="10%">Date</td>
	<td class="fieldLabel" width="25%">Task</td>
	<td class="fieldLabel" width="10%">Type</td>
	<td class="fieldLabel" width="10%">Time Spent</td>
	<td class="fieldLabel" width="45%">Description</td>
	<td>&nbsp;</td>
</tr>
</table>
</form>

<br/>

<table id="timesheets" border="1" cellspacing="10" width="100%"></table>

{$QS_JAVASCRIPT}

