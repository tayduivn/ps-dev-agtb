function checkCaseStatusDependentDropdown(){
        if(document.EditView.status.value == 'Closed' || document.EditView.status.value == 'Closed No Response'){
                removeFromValidate('EditView', 'case_source_c');
                removeFromValidate('EditView', 'means_of_prevention_c');
                removeFromValidate('EditView', 'resolution');
                addToValidate('EditView', 'case_source_c', 'enum', true,'Case Source' );
                addToValidate('EditView', 'means_of_prevention_c', 'enum', true,'Means of Prevention' );
                addToValidate('EditView', 'resolution', 'text', true,'Resolution' );
        }
        else if(document.EditView.status.value == 'Closed Feature' || document.EditView.status.value == 'Closed Defect') {
                removeFromValidate('EditView', 'case_source_c');
                removeFromValidate('EditView', 'means_of_prevention_c');
                removeFromValidate('EditView', 'resolution');
                addToValidate('EditView', 'case_source_c', 'enum', false,'Case Source' );
                addToValidate('EditView', 'means_of_prevention_c', 'enum', false,'Means of Prevention' );
                addToValidate('EditView', 'resolution', 'text', false,'Resolution' );
        }
        else{
                removeFromValidate('EditView', 'case_source_c');
                removeFromValidate('EditView', 'means_of_prevention_c');
                removeFromValidate('EditView', 'resolution');
                addToValidate('EditView', 'case_source_c', 'enum', false,'Case Source' );
                addToValidate('EditView', 'means_of_prevention_c', 'enum', false,'Means of Prevention' );
                addToValidate('EditView', 'resolution', 'text', false,'Resolution' );
        }
/*
 @author: EDDY, DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15071, 16288
** Bug 11900 Product category a Required Field
** Description: Added product category field to validation.  Product category is required only if related to is set to product and stats is not new.
*/
	if(document.EditView.status.value != '' && document.EditView.status.value != 'New' && document.EditView.related_to_c.value == 'product'){
                removeFromValidate('EditView', 'product_category_c');
		addToValidate('EditView', 'product_category_c', 'enum', true,'Product Category' );
        }else if((document.EditView.status.value == '' || document.EditView.status.value == 'New') && document.EditView.related_to_c.value != 'product'){
	        removeFromValidate('EditView', 'product_category_c');
		addToValidate('EditView', 'product_category_c', 'enum', false,'Product Category' );
        }else {
		removeFromValidate('EditView', 'product_category_c');
                addToValidate('EditView', 'product_category_c', 'enum', false,'Product Category' );
	}
}

function checkAccountTypeDependentDropdown(do_this){
	if (document.getElementById('account_type').value == 'Partner' || document.getElementById('account_type').value == 'Partner-Pro'|| document.getElementById('account_type').value == 'Partner-Ent') {
	document.getElementById('end_user_requires_agreement_c').style.visibility = 'visible';
	document.getElementById('end_user_requires_agreement_c_label').style.visibility = 'visible';
	} else {
	document.getElementById('end_user_requires_agreement_c').style.visibility = 'hidden';
	document.getElementById('end_user_requires_agreement_c_label').style.visibility = 'hidden';
	}
	if(do_this) {		
        	if(document.EditView.account_type.value == 'Partner' || document.EditView.account_type.value == 'Partner-Ent' || document.EditView.account_type.value == 'Partner-Pro'){
			document.getElementById('reference_code_c').disabled = false;
        	}
		else{
			document.getElementById('reference_code_c').disabled = true;
		}
	}
	if(document.EditView.account_type.value == 'Customer-Express'
                || document.EditView.account_type.value == 'Customer'
                || document.EditView.account_type.value == 'Customer-Ent'
                || document.EditView.account_type.value == 'network'
                || document.EditView.account_type.value == 'Customer-Other'
                || document.EditView.account_type.value == 'Customer-OEM'
		|| document.EditView.account_type.value == 'Partner'
		|| document.EditView.account_type.value == 'Partner-Pro'
		|| document.EditView.account_type.value == 'Partner-Ent'
	) {	
	        removeFromValidate('EditView', 'Support_Service_Level_c');
                removeFromValidate('EditView', 'industry');
		removeFromValidate('EditView', 'employees');
		removeFromValidate('EditView', 'annual_revenue');
		removeFromValidate('EditView', 'deployment_type_c');
		removeFromValidate('EditView', 'Partner_Type_c');
                removeFromValidate('EditView', 'resell_discount');
		removeFromValidate('EditView', 'billing_address_street');
		removeFromValidate('EditView', 'billing_address_postalcode');
		removeFromValidate('EditView', 'billing_address_country');
		removeFromValidate('EditView', 'shipping_address_street');
                removeFromValidate('EditView', 'shipping_address_postalcode');
                removeFromValidate('EditView', 'shipping_address_country');
		addToValidate('EditView', 'Support_Service_Level_c', 'enum', true, 'Support Service Level' );
		addToValidate('EditView', 'industry', 'enum', true, 'Industry' );
		addToValidate('EditView', 'employees', 'enum', true, 'Employees' );
		addToValidate('EditView', 'annual_revenue', 'enum', true, 'Annual Revenue' );
		addToValidate('EditView', 'deployment_type_c', 'enum', true, 'Deployment Type' );
		addToValidate('EditView', 'Partner_Type_c', 'enum', false, 'Partner Type' );
                addToValidate('EditView', 'resell_discount', 'enum', false, 'Resell discount' );
        	addToValidate('EditView', 'billing_address_street', 'enum', true, 'Billing Address Street' );
		addToValidate('EditView', 'billing_address_postalcode', 'enum', true, 'Billing Address Zip' );
		addToValidate('EditView', 'billing_address_country', 'enum', true, 'Billing Address Country' );
		addToValidate('EditView', 'shipping_address_street', 'enum', true, 'Shipping Address Street' );
		addToValidate('EditView', 'shipping_address_postalcode', 'enum', true, 'Shipping Address Zip' );
		addToValidate('EditView', 'shipping_address_country', 'enum', true, 'Shipping Address Country' );
	}
	else {
		removeFromValidate('EditView', 'Support_Service_Level_c');
                removeFromValidate('EditView', 'industry');
                removeFromValidate('EditView', 'employees');
                removeFromValidate('EditView', 'annual_revenue');
                removeFromValidate('EditView', 'deployment_type_c');
                removeFromValidate('EditView', 'Partner_Type_c');
                removeFromValidate('EditView', 'resell_discount');
                addToValidate('EditView', 'Support_Service_Level_c', 'enum', false, 'Support Service Level' );
                addToValidate('EditView', 'industry', 'enum', false, 'Industry' );
                addToValidate('EditView', 'employees', 'enum', false, 'Employees' );
                addToValidate('EditView', 'annual_revenue', 'enum', false, 'Annual Revenue' );
                addToValidate('EditView', 'deployment_type_c', 'enum', false, 'Deployment Type' );
                addToValidate('EditView', 'Partner_Type_c', 'enum', false, 'Partner Type' );
                addToValidate('EditView', 'resell_discount', 'enum', false, 'Resell discount' );
	}
	if(document.EditView.account_type.value == 'Partner'
		|| document.EditView.account_type.value == 'Partner-Pro'
		|| document.EditView.account_type.value == 'Partner-Ent'
		|| document.EditView.account_type.value == 'SugarExchange Partner: Premium'
		|| document.EditView.account_type.value == 'SugarExchange Partner: Standard'
	) {
		removeFromValidate('EditView', 'Partner_Type_c');
		removeFromValidate('EditView', 'resell_discount');	
		addToValidate('EditView', 'Partner_Type_c', 'enum', true, 'Partner Type' );
		addToValidate('EditView', 'resell_discount', 'enum', true, 'Resell discount' );			
	}
}


var first_time_accessing_this = true;
var global_current_label = '';
var global_current_value = '';
function setOpportunityTypesFromRevType(do_rtop, opportunity_type_arr){
	if(!do_rtop)
		return false;
	
	var revenue_type = document.getElementById('Revenue_Type_c').value;
	var opp_type_sub = opportunity_type_arr[revenue_type];
	
	if(first_time_accessing_this){
		first_time_accessing_this = false;
		selected_index = document.getElementById('opportunity_type').selectedIndex;
		global_current_label = document.getElementById('opportunity_type')[selected_index].label;
		global_current_value = document.getElementById('opportunity_type')[selected_index].value;
	}
	current_label = global_current_label;
	current_value = global_current_value;
	
	document.EditView.opportunity_type.options.length = 0;
	var iterator = 0;
	var found_current = false;
	for (var i in opp_type_sub){
		document.EditView.opportunity_type.options[iterator] = new Option(opp_type_sub[i], i);
		
		if(current_value == i){
			found_current = true;
			document.EditView.opportunity_type.options[iterator].selected = true;
		}
		
		iterator++;
	}
	
	if(!found_current){
		document.EditView.opportunity_type.options[iterator] = new Option(current_label, current_value);
		document.EditView.opportunity_type.options[iterator].selected = true;
	}
}

function setGlobalVarCurrentOpportunityType(){
	selected_index = document.getElementById('opportunity_type').selectedIndex;
	global_current_label = document.getElementById('opportunity_type')[selected_index].label;
	global_current_value = document.getElementById('opportunity_type')[selected_index].value;
}

//** BEGIN  CUSTOMIZATION EDDY :: ITTix 13077
function checkOppClosedReasonDependentDropdown(ddNodeID,rendered,selectedval,prefix){
    
    if(typeof(selectedval) == 'undefined'){selectedval ='';}
    if(typeof(prefix) == 'undefined'){prefix ='';}

	ddNode = document.getElementById(ddNodeID);
	parentNode = ddNode.parentNode;//document.getElementById('closedDetailsParentSpan');
	parentNode.removeChild(ddNode);
	document.getElementById('primary_reason_competitor_c').disabled = true;

	selectObj = document.createElement('select');
	selectObj.name  = prefix+'closed_lost_reason_detail_c';
	selectObj.id  = 'closed_lost_reason_detail_c';

        optiona1 = new Option("Sugar CE");
        optiona2 = new Option("SalesForce");
        optiona3 = new Option("Sage (ACT,SalesLogix, etc)");
        optiona4 = new Option("Microsoft");
        optiona5 = new Option("Siebel");
        optiona6 = new Option("In House/ Home Grown System");
        optiona7 = new Option("Staying with Current System");
        optiona8 = new Option("Other");
        optiona9 = new Option("Not disclosed");
        optionb1 = new Option("Lack of success/adoption");
        optionb2 = new Option("Not seeing ROI");
        optionb3 = new Option("Business Model Change");
        optionb4 = new Option("Monthly Payment");
        optionb5 = new Option("Time Line to Purchase Pushed Out");
//        optionb5 = new Option("Other");
        optionc1 = new Option("No Response");
        optionc2 = new Option("Invalid Data");
		
        optionvala1 = 'Sugar CE';
        optionvala2 = 'SalesForce';
        optionvala3 = 'Sage (ACT, SalesLogix, etc)';
        optionvala4 = 'Microsoft';
        optionvala5 = 'Siebel';
        optionvala6 = 'In House/ Home Grown System';
        optionvala7 = 'Staying with Current System';
        optionvala8 = 'Other';
        optionvala9 = 'Not Disclosed';
        optionvalb1 = 'Lack of success/adoption';
        optionvalb2 = 'Not seeing ROI';
        optionvalb3 = 'Business model change';
        optionvalb4 = 'Monthly Payment';
        optionvalb5 = 'Time Line to Purchase Pushed Out';
//        optionvalb5 = 'Other';
        optionvalc1 = 'No Response';
        optionvalc2 = 'Invalid Data';

	//start by clearing out any validations and clearing out options list
	if(rendered){
		removeFromValidate('EditView', 'closed_lost_reason_detail_c');
	        removeFromValidate('EditView', 'closed_lost_description');
                removeFromValidate('EditView', 'primary_reason_competitor_c');
	}
    //if closed_reason value is competitor, then set dependant dropdown and add validation
    if(document.getElementById('closed_lost_reason_c').value == 'Competitor'){
        selectObj.options[0] = optiona1;
        selectObj.options[0].value =optionvala1; 
        selectObj.options[1] = optiona2;
        selectObj.options[1].value = optionvala2;
        selectObj.options[2] = optiona3;
        selectObj.options[2].value = optionvala3;
        selectObj.options[3] = optiona4;
        selectObj.options[3].value = optionvala4;
        selectObj.options[4] = optiona5;
        selectObj.options[4].value = optionvala5;
        selectObj.options[5] = optiona6;
        selectObj.options[5].value = optionvala6;
        selectObj.options[6] = optiona7;
        selectObj.options[6].value = optionvala7;
        selectObj.options[7] = optiona8;
        selectObj.options[7].value = optionvala8;
        selectObj.options[8] = optiona9;
        selectObj.options[8].value = optionvala9;
       //set selected value 
	for (var i=0; i<selectObj.options.length; i++){
		if(selectObj[i].value == selectedval){
			selectObj[i].selected = true;
		}
	}
	//add node 
        parentNode.appendChild(selectObj);
	document.getElementById('primary_reason_competitor_c').disabled = false;

	//add validation if fields have rendered
	if(rendered){
        	addToValidate('EditView', 'closed_lost_reason_detail_c', 'enum', true,'Closed Lost Reason Detail' );
	        addToValidate('EditView', 'closed_lost_description', 'Text', true,'Closed Lost Description' );            
        	addToValidate('EditView', 'primary_reason_competitor_c', 'Text', true,'Primary reason for competitor' );
	}
    }else if(document.getElementById('closed_lost_reason_c').value == 'Unable To Contact'){
        selectObj.options[0] = optionc1;
        selectObj.options[0].value =optionvalc1; 
        selectObj.options[1] = optionc2;
        selectObj.options[1].value = optionvalc2;
       //set selected value 
	for (var i=0; i<selectObj.options.length; i++){
		if(selectObj[i].value == selectedval){
			selectObj[i].selected = true;
		}
	}
	//add node 
        parentNode.appendChild(selectObj);
	document.getElementById('primary_reason_competitor_c').value = '';

	//add validation if fields have rendered
        if(rendered){
                addToValidate('EditView', 'closed_lost_reason_detail_c', 'enum', true,'Closed Lost Reason Detail' );
                addToValidate('EditView', 'closed_lost_description', 'Text', true,'Closed Lost Description' );
	}
    }else if(document.getElementById('closed_lost_reason_c').value == 'Abandoning CRM'){
    //if closed_reason value is competitor, then set dependant dropdown and add validation
        selectObj.options[0] = optionb1;
        selectObj.options[0].value =optionvalb1; 
        selectObj.options[1] = optionb2;
        selectObj.options[1].value = optionvalb2
        selectObj.options[2] = optionb3
        selectObj.options[2].value = optionvalb3; 
        selectObj.options[3] = optionb4;
        selectObj.options[3].value = optionvalb4;
        selectObj.options[4] = optionb5;
        selectObj.options[4].value = optionvalb5;
       //set selected value 
	for (var i=0; i<selectObj.options.length; i++){
		if(selectObj[i].value == selectedval){
			selectObj[i].selected = true;
		}
	}
	//add node 
        parentNode.appendChild(selectObj);
	document.getElementById('primary_reason_competitor_c').value = '';

	//add validation if fields have rendered
	if(rendered){
	        addToValidate('EditView', 'closed_lost_reason_detail_c', 'enum', true,'Closed Lost Reason Detail' );
	        addToValidate('EditView', 'closed_lost_description', 'Text', true,'Closed Lost Description' );    
	}
   }else{
	selectObj.disabled = true;
        parentNode.appendChild(selectObj);
	document.getElementById('primary_reason_competitor_c').value = '';
	document.getElementById('closed_lost_reason_detail_c').value = '';
   //for any other value, set the dropdown to blank
	if(rendered){
		removeFromValidate('EditView', 'closed_lost_reason_detail_c');
                removeFromValidate('EditView', 'closed_lost_description');
                removeFromValidate('EditView', 'primary_reason_competitor_c');

		if(document.getElementById('closed_lost_reason_c').value != 'None' && document.getElementById('closed_lost_reason_c').value != '' ){
	        	addToValidate('EditView', 'closed_lost_description', 'Text', true,'Closed Lost Description' );    
		}
    	}
    }


}
//** END  CUSTOMIZATION EDDY :: ITTix 13077

//If Sales Stage = Closed Lost, Closed Lost Reason must be required
function checkOpportunitySalesStage() {
	if(document.EditView.sales_stage.value == 'Closed Lost'){	
		removeFromValidate('EditView', 'closed_lost_reason_c');
		addToValidate('EditView', 'closed_lost_reason_c', 'enum', true,'Closed Lost Reason' );	
	}	
	else {
		removeFromValidate('EditView', 'closed_lost_reason_c');
                addToValidate('EditView', 'closed_lost_reason_c', 'enum', false,'Closed Lost Reason' );
	}
}
