
var getTimesheetsHandler = {
	success: function(data) {
		var timesheetsDiv = document.getElementById('timesheets');
		//timesheetsDiv.innerHTML = data.responseText;
		var row_data = YAHOO.lang.JSON.parse(data.responseText);
		clear_timesheet();
		for(var i=0; i<row_data.length; i++) 
			insert_row(row_data[i]);
	},
	failure: function(data) {

	}	
};

var saveHandler = {
	success: function(data) {
		var timesheetsEl = document.getElementById('timesheets');
		//timesheetsEl.innerHTML = data.responseText;
		var row_data = YAHOO.lang.JSON.parse(data.responseText);
		insert_row(row_data);
		//clear_values();
	},
	failure: function(data) {

	}	
};

var removeRowHandler = {
	success: function(data) {
		var row_data = YAHOO.lang.JSON.parse(data.responseText);
		var rowID = 'row_'+row_data.id;
		var removedRow = document.getElementById(rowID);
		table = removedRow.parentNode;
		table.removeChild(removedRow);
		if(table.childNodes.length == 0) {
			document.getElementById('timesheets').style.display = 'none';
		}
	},
	failure: function(data) {

	}	
};

function save_time_entry() {
	var fields_validated = validate_fields();
	
	if(fields_validated) {
		YAHOO.util.Connect.setForm('EditView');
		YAHOO.util.Connect.asyncRequest('POST', 'index.php', saveHandler);
	}
}

function get_timesheets() {
	YAHOO.util.Connect.setForm('EditView');
	YAHOO.util.Connect.asyncRequest('POST', 'index.php', getTimesheetsHandler, 'action=getTimesheets');
}
/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #:17974
** Description: Timeshssets module enhancements
** Wiki customization page: 
*/
function display_account_name() {
	var taskDiv = document.getElementById('account_name');
	var account_name = account_name_list[this.value];
	if(account_name != null) taskDiv.innerHTML = "Account: "+account_name;
	else taskDiv.innerHTML = "";
}

function remove_row(e) {
	YAHOO.util.Connect.setForm('EditView');
	YAHOO.util.Connect.asyncRequest('POST', 'index.php', removeRowHandler, "action=saveTimeEntry&removed_row="+this.id);
}
/*
***** END SUGARINTERNAL CUSTOMIZATION ****
*/
function insert_row(row_data) {
	var table_id = 'timesheets';
	var form = document.getElementById('EditView');
	var table = document.getElementById(table_id);
	var row = table.insertRow(table.rows.length);
		row.id = 'row_'+row_data.id;

	var cell1 = row.insertCell(row.cells.length);
		cell1.nowrap = 'nowrap';
		cell1.width = '5%';
		cell1.innerHTML = "<img src='themes/default/images/no.gif' id='"+row_data.id+"' />";
		
	var cell2 = row.insertCell(row.cells.length);
		cell2.nowrap = 'nowrap';
		cell2.width = '10%';
		cell2.innerHTML = row_data.activity_date;

	var cell3 = row.insertCell(row.cells.length);
		cell3.nowrap = 'nowrap';
		cell3.width = '25%';
		cell3.innerHTML = "<a href='index.php?module=Tasks&action=DetailView&record="+row_data.task_id+"'>"+row_data.task_name+"</a>";

	var cell4 = row.insertCell(row.cells.length);
		cell4.nowrap = 'nowrap';
		cell4.width = '10%';
		cell4.innerHTML = row_data.activity_type;

	var cell5 = row.insertCell(row.cells.length);
		cell5.nowrap = 'nowrap';
		cell5.width = '10%';
		cell5.innerHTML = row_data.time_spent;

	var cell6 = row.insertCell(row.cells.length);
		cell6.nowrap = 'nowrap';
		cell6.width = '40%';
		cell6.innerHTML = row_data.description;

	if(table.style.display == 'none') table.style.display = 'block';

	YAHOO.util.Event.addListener(row_data.id, "click", remove_row);
}

function clear_timesheet() {
	var table = document.getElementById('timesheets');
	while(table.rows.length > 0) {
		table.lastChild.parentNode.removeChild(table.lastChild);
	}
}

function clear_values() {
	for(field in timesheet_fields) {
		document.getElementById(field).value = "";
	}
}

function validate_fields() {
	clear_all_errors();
	inputsWithErrors = new Array();
	var timesheet_fields = {'activity_date':'Date', 'task':'Task', 'activity_type':'Type', 'time_spent':'Time Spent', 'description':'Description'};
	var fields_validated = true;
	
	for(field in timesheet_fields) {
		var fieldEl = document.getElementById(field);
		if(fieldEl.value == "") {
			add_error_style('EditView', field, "Required: "+timesheet_fields[field]);
			fields_validated = false;
		}
	}
	return fields_validated;
}

YAHOO.util.Event.addListener("save", "click", save_time_entry);
YAHOO.util.Event.addListener("change_timesheet", "click", get_timesheets);
YAHOO.util.Event.addListener("task", "change", display_account_name);
