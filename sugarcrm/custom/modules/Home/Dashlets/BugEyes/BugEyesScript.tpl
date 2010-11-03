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

// $Id: JotPadDashletScript.tpl,v 1.6 2006/08/23 00:13:44 awu Exp $

*}


{literal}

	
	<!-- Source file --> 
	<script type="text/javascript" src="include/javascript/yui/tabview.js"></script>
<script>

if(typeof BugEyes == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions

	BugEyes = function() {
	
	    return {
	    	/**
	    	 * Called when the textarea is blurred
	    	 */
	        lookup: function(type,number, id) {
	        	BugEyes.id = id;
	        	numbers = number.split(',');
	        	for(i = 0; i < numbers.length; i++){
	        		BugEyes.queue.push({'type':type, 'number':numbers[i], 'id':id});
	        	
	        	}
	        	BugEyes.retrieve();
	        	
	        },
			
			retrieve: function(){
					if(BugEyes.queue.length < 1)return;
					
					info = BugEyes.queue.shift();
					number = info['number'];
					id = info['id'];
					type = info['type'];
					if(typeof BugEyes.tabIndex[number] == 'undefined' || BugEyes.tabIndex[number] == 0){
			        	ajaxStatus.showStatus('{/literal}{$Label.retrieving}{literal}'); // show that AJAX call is happening
			        	// what data to post to the dashlet
		    	    	postData = 'to_pdf=1&module=Home&action=CallMethodDashlet&method=lookup&id=' + id + '&number=' + number + '&type=' + type;
						var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php', 
										  {success: BugEyes.addTab, failure: BugEyes.addTab, argument:info}, postData);
					}else{
						BugEyes.tabs.activate(type + number);
					}
			},
	       
	        addTab: function(o){
	        		index = o.argument['type'] + o.argument['number'];
	     			if(typeof BugEyes.tabIndex[index] != 'undefined' && BugEyes.tabIndex[index] != 0){
	     				
	       				BugEyes.tabs.activate(index);
	       				
	        		}else{
	        			try{
	 
		        			if(typeof BugEyes.tabs == 'undefined' || BugEyes.tabs == null){
		       					var newDiv = document.createElement('div');
		       					newDiv.id = 'beTabs';
		       					document.getElementById('bugEyesTabs').appendChild(newDiv);
		        				BugEyes.tabs = new YAHOO.ext.TabPanel(newDiv.id); 
		        			
		        			}
		        			BugEyes.tabIndex[index] = 1;
		        			BugEyes.tabs.addTab(index, o.argument['number'], o.responseText);
		        			BugEyes.tabs.activate(index);
		        		}catch(e){
		        			
		        		}
	        				
	        		}
	        		BugEyes.retrieve();
	        	
	     		
	     		
	     	
				
	        },
	        removeTab: function(number){
	        BugEyes.tabIndex[number] = 0;
	        if(BugEyes.tabs.getCount() == 1){
	        	
	        	BugEyes.tabs.destroy(true);
	
	        	BugEyes.tabs = null;
	        }else{
	        	BugEyes.tabs.removeTab(number);
	        }
	       
	        	
	        },
	        init : function(){
	        	BugEyes.tabIndex = {};
	        	BugEyes.queue = new Array();
	        
	        }
	 }
	 
}();
BugEyes.init();
}

</script>
{/literal}
	   