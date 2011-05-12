/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
	var elems = new Array("address_street", "address_city", "address_state", "address_postalcode", "address_country");
    var tHasText = false;
    var syncAddressCheckbox = true;
    var originalBgColor = '#FFFFFF';  
    var Dom = YAHOO.util.Dom;
	
	function TestCheckboxReady(id) { 
	   YAHOO.util.Event.onAvailable(id, this.handleOnAvailable, this);  
	} 
	 
	TestCheckboxReady.prototype.handleOnAvailable = function(me) { 
	    for(x in elems) {
		    f = fromKey + "_" + elems[x];
		    t = toKey + "_" + elems[x];
	
		    e1 = document.getElementById(t);
		    e2 = document.getElementById(f);
            
		    if(e1 != null && typeof e1 != "undefined" && e2 != null && typeof e2 != "undefined") {
	
		        if(!tHasText && trim(e1.value) != "") {
		           tHasText = true;
		        }
		        
		        if(e1.value != e2.value) 
		        {
		           syncAddressCheckbox = false;
		           break;
		        }
		        originalBgColor = e1.style.backgroundColor;
		    }
	    }
	    
	    if(tHasText && syncAddressCheckbox) 
        {
           document.getElementById(this.id).checked = true;
	       syncFields(fromKey, toKey);
	    }	  
	} 
	
    function writeToSyncField(e) {
         fromEl = YAHOO.util.Event.getTarget(e, true);
         if(typeof fromEl != "undefined") {
            toEl = document.getElementById(fromEl.id.replace(fromKey, toKey));
            toEl.value = fromEl.value;
         }
    }
    
    function syncFields(fromKey, toKey) {
         for(x in elems) {
             f = fromKey + "_" + elems[x];
             e2 = document.getElementById(f);
             t = toKey + "_" + elems[x];
             e1 = document.getElementById(t);
             if(e1 != null && typeof e1 != "undefined" && e2 != null && typeof e2 != "undefined") {
                  if(!document.getElementById(toKey + '_checkbox').checked) {
		             Dom.setStyle(e1,'backgroundColor',originalBgColor);
		             e1.removeAttribute('readOnly');
		             YAHOO.util.Event.removeListener(e2, 'keyup'); 
		          } else {
		             e1.value = e2.value;
                     Dom.setStyle(e1,'backgroundColor','#DCDCDC');
                     e1.setAttribute('readOnly', true);
                     YAHOO.util.Event.addListener(e2, 'keyup', writeToSyncField);
                  }
             }
         } //for
    }


