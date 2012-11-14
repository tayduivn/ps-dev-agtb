/**
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
//FILE SUGARCRM flav=ent ONLY

function validatePortalName(e) {
    var portalName = document.getElementById('portal_name'); 
    var portalNameExisting = document.getElementById("portal_name_existing"); 
    var portalNameVerified = document.getElementById('portal_name_verified');
	if(typeof(portalName.parentNode.lastChild) != 'undefined' &&
        portalName.parentNode.lastChild.tagName =='SPAN'){
	   portalName.parentNode.lastChild.innerHTML = '';
	}

    if(portalName.value == portalNameExisting.value) {
       return;
    }
    
	var callbackFunction = function success(data) {
	    //data.responseText contains the count of portal_name that matches input field
		count = data.responseText;	
		if(count != 0) {
		   add_error_style('form_EmailQCView_Contacts', 'portal_name', SUGAR.language.get('app_strings', 'ERR_EXISTING_PORTAL_USERNAME'));
		   for(wp = 1; wp <= 10; wp++) {
			   window.setTimeout('fade_error_style(style, ' + wp * 10 + ')', 1000 + (wp * 50));
		   }
		   portalName.focus();
		}
		
	    if(portalNameVerified.parentNode.childNodes.length > 1) {
	       portalNameVerified.parentNode.removeChild(portalNameVerified.parentNode.lastChild);
	    }
	    
        verifiedTextNode = document.createElement('span');
        verifiedTextNode.innerHTML = '';
	    portalNameVerified.parentNode.appendChild(verifiedTextNode);
	    
		portalNameVerified.value = count == 0 ? "true" : "false";
		verifyingPortalName = false;
	}

    if(portalNameVerified.parentNode.childNodes.length > 1) {
       portalNameVerified.parentNode.removeChild(portalNameVerified.parentNode.lastChild);
    }

    if(portalName.value != '' && !verifyingPortalName) {    
       document.getElementById('portal_name_verified').value = "false";
	   verifiedTextNode = document.createElement('span');
	   portalNameVerified.parentNode.appendChild(verifiedTextNode);
       verifiedTextNode.innerHTML = SUGAR.language.get('app_strings', 'LBL_VERIFY_PORTAL_NAME');
       verifyingPortalName = true;
	   var cObj = YAHOO.util.Connect.asyncRequest('POST', 'index.php?module=Contacts&action=ValidPortalUsername&portal_name=' + portalName.value, {success: callbackFunction, failure: callbackFunction});
    }
}

