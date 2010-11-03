/**
 * colors.js javascript file
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */
 
// $Id: MassUpdate.js 23344 2007-06-05 20:32:59Z eddy $
function accountTypeDependents () {
	if(document.getElementById('Accountsaccount_type').value == 'Partner') {
        document.getElementById('AccountsPartner_Type_c').disabled = false;
        //document.getElementById('Accountsresell_discount').disabled = false;
        document.getElementById('AccountsPartner_Type_c').style.backgroundColor= "#ffffff";
	    document.getElementById('Accountsresell_discount').style.backgroundColor = "#ffffff";
	} else {
	    document.getElementById('AccountsPartner_Type_c').disabled = true;
        //document.getElementById('Accountsresell_discount').disabled = true;
        document.getElementById('AccountsPartner_Type_c').style.backgroundColor = "#DCDCDC";
	    document.getElementById('Accountsresell_discount').style.backgroundColor = "#DCDCDC";
	}
}
document.getElementById('MassUpdate').onsubmit = function()
{
    validated = true;
    if ( document.getElementById('select_account') 
            && document.getElementById('select_account').checked 
            && this.account_id.value == '' ) {
        requiredTxt = SUGAR.language.get('app_strings', 'ERR_MISSING_REQUIRED_FIELDS');
        fieldTxt = SUGAR.language.get('mod_strings', 'LBL_ACCOUNT_NAME');
        add_error_style('MassUpdate','account_id', requiredTxt + " " + fieldTxt);
        validated = false;
    }
    if ( document.getElementById('create_account') && document.getElementById('create_account').checked ) {
        validated = validated && validate_form('MassUpdate', 'Accounts');
        if ( isChecked('newaccountnote') )
            validated = validated && validate_form('MassUpdate', 'AccountNotes');
    }
    if ( isChecked('newopportunity') ) {
        validated = validated && validate_form('MassUpdate', 'Opportunities');
        if ( isChecked('newoppnote') )
            validated = validated && validate_form('MassUpdate', 'OpportunityNotesname');
    }
    if ( isChecked('newmeeting') )
        validated = validated && validate_form('MassUpdate', 'Appointments');
    
    return validated;
}

function isChecked(field) 
{
    return eval("document.forms['MassUpdate']."+field+".checked");
}

function checkAccountRadio(id,checked)
{
    if ( id == 'select_account' && checked
            && document.getElementById('existingaccount').style.display == 'none' ) 
        toggleDisplay("existingaccount");
    if ( id == 'select_account' && checked
            && document.getElementById('newaccount').style.display != 'none' )
        toggleDisplay("newaccount");
    if ( id == 'create_account' && checked
            && document.getElementById('existingaccount').style.display != 'none' ) 
        toggleDisplay("existingaccount");
    if ( id == 'create_account' && checked
            && document.getElementById('newaccount').style.display == 'none' )
        toggleDisplay("newaccount");
}

function checkContact(id)
{
    contacts = document.getElementsByName('mass[]');
    
    for ( i = 0; i < contacts.length; i++ ) {
        if ( contacts[i].value == id ) {
            if ( !contacts[i].checked ) {
                contacts[i].checked = true;
                sListView.check_item(contacts[i], document.MassUpdate);
            }
            return;
        }
    }
}

function validation(departmentFlag) {
    cDateString = document.getElementById('Opportunitiesjscal_field');
    cdate = new Date(cDateString.value);
    todate = new Date();
    todate.setMilliseconds(0);
    todate.setSeconds(0);
    todate.setHours(0);
    todate.setMinutes(0);
    dateflag = (cdate.getTime()!=todate.getTime());

	if(document.getElementById('Opportunitiesname').value == '') {
                alert('Missing required field: Opportunity Name');
                document.getElementById('Opportunitiesname').focus();
                return false;
	}
	else if(departmentFlag == 'true' && document.getElementById('Opportunitiespartner_assigned_to_c').value == '') {
                alert('User is Channel Manager. Missing required field: Partner Assigned to');
                document.getElementById('Opportunitiespartner_assigned_to_c').focus();
                return false;
	}
	else if(document.getElementById('Opportunitiesopportunity_type').value == '') {
                alert('Missing required field: Opportunity Type');
                document.getElementById('Opportunitiesopportunity_type').focus();
                return false;
	}
	else if(document.getElementById('Opportunitiesjscal_field').value == '') {
                alert('Missing required field: Opportunity Close Date');
                document.getElementById('Opportunitiesjscal_field').focus();
                return false;
	}
	else if(document.getElementById('Opportunitiessales_stage').value == '') {
                alert('Missing required field: Opportunity Sales Stage');
                document.getElementById('Opportunitiessales_stage').focus();
                return false;
	}
	else if(document.getElementById('Opportunitiesamount').value == '') {
                alert('Missing required field: Opportunity Amount');
                document.getElementById('Opportunitiesamount').focus();
                return false;
	}
	else if(document.getElementById('Opportunitiesorder_number').value == '') {
                alert('Missing required field: Order Number ');
                document.getElementById('Opportunitiesorder_number').focus();
                return false;
	}
	else if(document.getElementById('OpportunitiesTerm_c').value == '') {
                alert('Missing required field: Opportunity Term');
                document.getElementById('OpportunitiesTerm_c').focus();
                return false;
	}
	else if(document.getElementById('OpportunitiesRevenue_Type_c').value == '') {
                alert('Missing required field: Revenue Type');
                document.getElementById('OpportunitiesRevenue_Type_c').focus();
                return false;
	}
	else if(document.getElementById('OpportunitiesRevenue_Type_c').value == 'Renewal' && document.getElementById('Opportunitiesrenewal_date_c').value == '') {
		alert('Missing required field: Renewal Date');
                document.getElementById('Opportunitiesrenewal_date_c').focus();
                return false;
	}
	else if(document.getElementById('Opportunitiescurrent_solution').value == '') {
                alert('Missing required field: Current solution ');
                document.getElementById('Opportunitiescurrent_solution').focus();
                return false;
	}
	else if(document.getElementById('Opportunitiescompetitor_1').value == '') {
                alert('Missing required field:  Competitor');
                document.getElementById('Opportunitiescompetitor_1').focus();
                return false;
	}
	else if(document.getElementById('Opportunitiesusers').value == '') {
                alert('Missing required field: No. of users');
                document.getElementById('Opportunitiesusers').focus();
                return false;
	}
	else if(document.getElementById('Accountsname').value == '') {
                alert('Missing required field: Account Name');
                document.getElementById('Accountsname').focus();
                return false;
	}
	else if(document.getElementById('Accountsbilling_address_street').value == '') {
                alert('Missing required field: Account Billing Street Address');
                document.getElementById('Accountsbilling_address_street').focus();
                return false;
	}
	else if(document.getElementById('Accountsbilling_address_city').value == '') {
                alert('Missing required field: Billing City');
                document.getElementById('Accountsbilling_address_city').focus();
                return false;
	}
	else if(document.getElementById('Accountsbilling_address_state').value == '') {
                alert('Missing required field: Billing State');
                document.getElementById('Accountsbilling_address_state').focus();
                return false;
	}
	else if(document.getElementById('Accountsbilling_address_postalcode').value == '') {
                alert('Missing required field: Billing zip');
                document.getElementById('Accountsbilling_address_postalcode').focus();
                return false;
	}
	else if(document.getElementById('Accountsbilling_address_country').value == '') {
                alert('Missing required field: Billing Country');
                document.getElementById('Accountsbilling_address_country').focus();
                return false;
	}
	else if(document.getElementById('Accountsdeployment_type_c').value == '') {
                alert('Missing required field: Deployment Type');
                document.getElementById('Accountsdeployment_type_c').focus();
                return false;
	}
    else if(document.getElementById('Accountsaccount_type').value == '') {
                alert('Missing required field: Account Type');
                document.getElementById('Accountsaccount_type').focus();
                return false;
	}
    else if(document.getElementById('Accountsaccount_type').value == 'Prospect-partner' || document.getElementById('Accountsaccount_type').value == 'Prospect') {
                alert('Account Type must be a customer instead of a Prospect');
                document.getElementById('Accountsaccount_type').focus();
                return false;
    }
	else if(document.getElementById('Opportunitiesopportunity_type').value == 'Partner Fees'  && document.getElementById('Accountsaccount_type').value != 'Partner') {
                alert('Missing required field: Account Type should be Partner since the Opportunity Type is Partner Fees');
                document.getElementById('Accountsaccount_type').focus();
                return false;
        }
	else if(document.getElementById('Accountsindustry').value == '') {
                alert('Missing required field: Industry');
                document.getElementById('Accountsindustry').focus();
                return false;
	}
	else if(document.getElementById('Accountsemployees').value == '') {
                alert('Missing required field: No. of Employees');
                document.getElementById('Accountsemployees').focus();
                return false;
	}
	else if(document.getElementById('AccountsSupport_Service_Level_c').value == '') {
                alert('Missing required field: Support Service Level');
                document.getElementById('AccountsSupport_Service_Level_c').focus();
                return false;
	}
	else if(document.getElementById('Accountsannual_revenue').value == '') {
                alert('Missing required field: Annual Revenue');
                document.getElementById('Accountsannual_revenue').focus();
                return false;
	}
        else if(dateflag && !(confirm("This Opportunity's expected close date is set in the past or in the future. Choose ok to update the expected closed day to today and save. Otherwise choose cancel to abort."))) {
	    return false;
	}
	else if( document.getElementById('Accountsaccount_type').value != ''){
		accTypeVal = document.getElementById('Accountsaccount_type').value;
		accTypeVal = accTypeVal.toLowerCase();
		if(accTypeVal.indexOf('partner')>=0){
			if(document.getElementById('AccountsPartner_Type_c').value == ''){
		                alert('Missing required field: Partner Type');
                		document.getElementById('AccountsPartner_Type_c').focus();
				return false;
			//}else if (document.getElementById('Accountsresell_discount').value == ''){
		        //        alert('Missing required field: Resell Discount');
        		 //       document.getElementById('Accountsresell_discount').focus();
			//	return false;
			}else{
				document.MassUpdate.submit();
			}
		
		}else{
			document.MassUpdate.submit();
		}

	}else{
    	        document.MassUpdate.submit();         
	}
}
