{*

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

// $Id: QuickCreateScript.tpl,v 1.1 2006/10/11 00:53:31 clint Exp $

*}

<script src='include/javascript/popup_parent_helper.js'></script>
{literal}<script>
if(typeof QuickCreateDash == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
	QuickCreateDash = function() {
	    return {
	    	/**
	    	 * Called when the textarea is blurred
	    	 */
	        quickSwitch: function(value, id) {
	        	ajaxStatus.showStatus('{/literal}{$LBL.LOADING}{literal}'); // show that AJAX call is happening
	        	// what data to post to the dashlet
    	    	postData = 'to_pdf=1&module=Home&action=CallMethodDashlet&method=QuickSwitch&id=' + id + '&load=' + value;
				var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php', 
								  {success: QuickCreateDash.loaded, failure: QuickCreateDash.loaded}, postData);
	        	window.setTimeout('ajaxStatus.hideStatus()', 2000);
	        },
		   
		    /**
	    	 * handle the response of the saveText method
	    	 */
	        loaded: function(data) {
	        	eval(data.responseText);
	           	ajaxStatus.showStatus('{/literal}{$LBL.LOADED}{literal}');
	           	if(typeof result != 'undefined') {
					theDiv = document.getElementById('quickcreatedash_' + result['id']);
					
					theDiv.innerHTML = result['form'];
					SUGAR.util.evalScript(theDiv.innerHTML);
				}
				theDiv.style.display = '';
	           	window.setTimeout('ajaxStatus.hideStatus()', 2000);
	        },
	   
	    
	    	inlineSave: function(id, theForm, subpanel) {
	    	
			ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));
			var success = function(data) {
				
				if(subpanel == 'projects') subpanel = 'project';
				try {
					eval('result = ' + data.responseText);
				}
				catch (err) {
				}
				
				if (typeof(result) != 'undefined' && result != null && typeof(result['status']) != 'undefined' && result['status'] !=null && result['status'] == 'dupe') {
					document.location.href = "index.php?" + result['get'];
					return;
				}
				else {
					ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVED'));
					window.setTimeout('ajaxStatus.hideStatus()', 1000);
					QuickCreateDash.quickSwitch(document.getElementById('qcmodule' + data.argument).value, data.argument);
				}
			}
			
			YAHOO.util.Connect.setForm(theForm); 			
			var cObj = YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success: success, failure: success, argument: id});					  
			return false;
			}
		}
	}();
}
</script>{/literal}