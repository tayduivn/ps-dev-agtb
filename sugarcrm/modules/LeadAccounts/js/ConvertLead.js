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
