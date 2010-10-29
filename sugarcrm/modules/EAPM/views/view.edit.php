<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: view.edit.php
 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Bugs module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.edit.php');

class EAPMViewEdit extends ViewEdit {

 	function display() {
        if($GLOBALS['current_user']->is_admin || empty($this->ev->focus->id) || $this->ev->focus->isOwner($GLOBALS['current_user']->id)){
 			parent::display();
        } else {
        	ACLController::displayNoAccess();
        }
        echo <<<JS
<script>
SUGAR.forms.EapmAction = function(source, target) {
    this.source = source;
	this.target = target;
}

SUGAR.util.extend(SUGAR.forms.EapmAction, SUGAR.forms.AbstractAction, {
	exec: function() {
			var sfield = SUGAR.forms.AssignmentHandler.VARIABLE_MAP[this.source];
			if ( sfield == null || sfield.value == null )	return null;
			var transl = SUGAR.language.get('app_list_strings', 'LBL_API_TYPE_ENUM');
			var eapmvalue = SUGAR.eapm[sfield.value];
			if(eapmvalue == null || eapmvalue.authMethods == null) return null;
			keys = []
			values = []
			for(v in eapmvalue.authMethods) {
				keys.push(v);
				values.push(transl[v]);
			}
			kenum = 'enum("'+keys.join('","')+'")';
			venum = 'enum("'+values.join('","')+'")';
			setevent = new SUGAR.forms.SetOptionsAction(this.target, kenum, venum);
			setevent.exec();
	}
});
var app_type_dep = new SUGAR.forms.Dependency(new SUGAR.forms.Trigger(['application'], 'true'),
	[new SUGAR.forms.EapmAction('application','type')],
	[],
	true);
 </script>
JS;
 	}
}
?>