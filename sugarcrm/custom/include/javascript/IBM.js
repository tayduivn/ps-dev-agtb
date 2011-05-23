if ( typeof(SUGAR) == "undefined" ) SUGAR = {};
if ( typeof(SUGAR.IBM) == "undefined" ) SUGAR.IBM = {};

SUGAR.IBM.verifyFields = function(formname){
	if(typeof(document.forms[formname]) == 'undefined'){
		return true; // return true because this was a bad javascript call, and we don't want to block on it.
	}
	if(typeof(validate[formname]) == 'undefined'){
		return true; // no validation array
	}
	
	var fields = [];
	counter = 0;
	for(var i = 0; i < validate[formname].length; i++){
		if(validate[formname][i][requiredIndex]){
			if(fields.indexOf(validate[formname][i][nameIndex]) == -1){
				fields[counter] = validate[formname][i][nameIndex];
				// console.log(i);
				// console.log(counter);
				// console.log(fields[counter]);
				counter++;
			}
		}
	}
	
	var need_to_be_filled = [];
	var valid = true;
	var counter = 0;
	for(var i = 0; i < fields.length; i++){
		var el = null;
		if(typeof(eval("document.forms[formname]." + fields[i])) != "undefined"){
			var onForm = document.getElementById(fields[i]);
			if(onForm != null){
				var el = eval("document.forms[formname]." + fields[i]);
			}
		}
		if(el != null && typeof(el.value) != "undefined"){
			// console.log("el " + el.id + " is a valid element and has a value");
			if(el.value == ""){
				// console.log(el.id + "is blank");
				valid = false;
				need_to_be_filled[counter] = fields[i];
				counter++;
			}
		}
	}
	
	if(!valid){
		// console.log("not valid");
		return need_to_be_filled;
	}
	else{
		// console.log("valid");
		return true;
	}
}

SUGAR.IBM.requiredHover = function(need_to_be_filled){
	var url = 'index.php?module=Opportunities&action=RequiredFieldsHover&to_pdf=1';
	for(var i = 0; i < need_to_be_filled.length; i++){
		url = url + '&field' + i + '=' + need_to_be_filled[i];
	}
	SUGAR.IBM.requiredHoverFields = need_to_be_filled;
	DCMenu.loadView('Please fill out the following fields before saving.', url);
}

SUGAR.IBM.submitRequiredHover = function(form_name){
	if(typeof(SUGAR.IBM.requiredHoverFields) == 'undefined'){
		document.forms[form_name].action.value = 'Save';
		document.forms[form_name].submit();
		return true;
	}
	
	fields = SUGAR.IBM.requiredHoverFields;
	if(fields.indexOf('account_name') != -1){
		fields[fields.length] = 'account_id';
	}
	var fields_filled = true;
	
	for(var i = 0; i < fields.length; i++){
		thisfield = document.getElementsByName(fields[i]);
		var thisfield_value = '';
		for(var j = 0; j < thisfield.length; j++){
			if(typeof(thisfield[j].value) != 'undefined' && thisfield[j].value != ""){
				thisfield_value = thisfield[j].value;
			}
		}
		for(var j = 0; j < thisfield.length; j++){
			thisfield[j].value = thisfield_value;
		}
		if(thisfield_value == ''){
			// console.log(fields[i] + ' was not filled out and is required!!!');
			fields_filled = false;
		}
	}
	
	if(fields_filled){
		document.forms[form_name].action.value = 'Save';
		document.forms[form_name].submit();
		return true;
	}
	else{
		return false;
	}
}
