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
*}
{literal}
<script src="include/javascript/yui/YAHOO.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script src="include/javascript/yui/dom.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script src="include/javascript/yui/event.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script src="include/javascript/yui/connection.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script src="include/javascript/yui/animation.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script type="text/javascript" src="include/javascript/yui/ext/yui-ext.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script type="text/javascript" src="include/javascript/yui/ext/ddgrid.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script type="text/javascript" src="include/javascript/yui/container.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script type="text/javascript" src="include/javascript/yui/ext/data/XMLChildDataModel.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script type="text/javascript" src="include/javascript/yui/ext/grid/ChildGridView.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script type="text/javascript" src="include/javascript/yui/tabview.js?s={/literal}{$sugar_version}{literal}&c={/literal}{$js_custom_version}{literal}"></script>
<script>
 /*
        *  a reference to an instance of PackageManagerGrid
        */
        var _pmg;

if(typeof PortalSync == 'undefined') {
	PortalSync = function() {
        var _loadingBar;
        var _session;
	var _timeoutID;
	    return {
	   		login: function() {
	   			username = document.getElementById('portal_user_name').value;
	   			password = document.getElementById('portal_password').value;
	   			if(username!= '' && password != ''){
	        		PortalSync.showWaiting('{/literal}{$MOD.MSG_LOGGING_IN}{literal}');
	        		postData = 'to_pdf=1&method=login&user_name='+username+'&password='+password;
					var cObj = YAHOO.util.Connect.asyncRequest('POST','HandleAjaxCall.php', 
								  {success: PortalSync.loginComplete, failure: PortalSync.loginComplete}, postData);
				}else{
					alert('{/literal}{$MOD.MSG_FILL_ALL_FIELDS}{literal}');
				}
	        },
	        beginSync: function() {
	        	_loadingBar.setHeader('{/literal}{$MOD.MSG_SYNCING_FILES}{literal}');
	        	postData = 'to_pdf=1&method=beginSync&session='+_session;
				var cObj = YAHOO.util.Connect.asyncRequest('POST','HandleAjaxCall.php', 
								  {success: PortalSync.syncComplete, failure: PortalSync.syncFailed}, postData);
	            _timeoutID = window.setTimeout(PortalSync.syncFailed, 5 * 60 * 1000);
	        },
	        loginComplete: function(data){
	            eval(data.responseText);
        		if(result['result'] == '-1'){
        			alert('{/literal}{$MOD.MSG_LOGIN_FAILED}{literal}');
        			PortalSync.hideWaiting();
        		} else {
        			_session = result['result'];
        			PortalSync.beginSync();
        		}
	        },
	        syncComplete: function(data){
	        	eval(data.responseText);
	        	if(typeof result != 'undefined') {
	        		PortalSync.hideWaiting();
	        		document.getElementById('syncPortal').innerHTML = '<h3>Sync Complete</h3>';
				}
	        },
	        showWaiting: function(msg){
	        	_loadingBar = 
					new YAHOO.widget.Panel("wait",  
													{ width:"240px", 
															  fixedcenter:true, 
															  close:false, 
															  draggable:false, 
															  modal:true,
															  visible:false,
															  effect:{effect:YAHOO.widget.ContainerEffect.FADE, duration:0.5} 
															} 
														);

					_loadingBar.setHeader(msg);
					_loadingBar.setBody("<img src=\"include/javascript/yui/assets/rel_interstitial_loading.gif\"/>");
					_loadingBar.render(document.body);
					_loadingBar.show();
	        },
	        hideWaiting: function(){
	        	_loadingBar.hide();
			window.clearTimeout(_timeoutID);
	        },
	        syncFailed: function(){
	            PortalSync.hideWaiting();
	        	document.getElementById('syncPortal').innerHTML = '<h3>Portal Sync Failed</h3>The sync operation was unsuccessful and timed out after 5 minutes.  Please contact the administrator to troubleshoot this problem.';
	        }
	    };
	}();
}
</script>
{/literal}
