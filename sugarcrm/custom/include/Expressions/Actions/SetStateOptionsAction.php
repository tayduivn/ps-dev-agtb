<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once("include/Expressions/Actions/AbstractAction.php");

class SetStateOptionsAction extends AbstractAction{
	protected $countryCode =  "";
	protected $stateField =  "";
	
	function SetStateOptionsAction($params) {
		$this->params = $params;
		$this->countryCode = $params['countryCode'];
		$this->stateField = $params['stateField'];
	}
	
	/**
	 * Returns the javascript class equavalent to this php class
	 *
	 * @return string javascript.
	 */
	static function getJavascriptClass() {
		return  "
		SUGAR.forms.SetStateOptionsAction = function(countryCode, stateField) {
			this.countryCode = countryCode;
			this.stateField = stateField;
		};
				
		SUGAR.util.extend(SUGAR.forms.SetStateOptionsAction, SUGAR.forms.AbstractAction, {
			exec: function(context) {
				if (typeof(context) == 'undefined')
					context = this.context;
				
				var field = SUGAR.forms.AssignmentHandler.getElement(this.stateField);
				var stateField = this.stateField;
				if ( field == null )	return null;		
				
				// Define callback functions
				success = function(res) {
					var stateCurrentValue = field.value;
					field.options.length = 0;
					if(res.responseText == ''){
						return;
					}
					
					var state_array = JSON.parse(res.responseText);
					var state_iterator = 0;
					for (state_key in state_array){
						var optn = document.createElement('OPTION');
						optn.text = state_array[state_key];
						optn.value = state_key;
						field.options.add(optn);
						if(state_key == stateCurrentValue){
							field.selectedIndex = state_iterator;
						}
						state_iterator++;
					}
					
					var empty =  field.options.length == 1 && field.value == '';
					var visAction = new SUGAR.forms.VisibilityAction(stateField, empty ? 'false' : 'true', '');
					visAction.setContext(context);
					visAction.exec();
					if(empty){
						var setRequiredOption = new SUGAR.forms.SetRequiredAction(stateField, SUGAR.expressions.Expression.FALSE, stateField + '_label');
						setRequiredOption.setContext(context);
						setRequiredOption.exec();
					}
					else{
						if(typeof(Accounts_required_country_infodep) != 'undefined'){
							Accounts_required_country_infodep.fire();
						} 
					}
					
					if ( SUGAR.forms.AssignmentHandler.ANIMATE && !empty)
						SUGAR.forms.FlashField(field);
				}
				failure = function(){}
				
				var theCountryCode = document.getElementById(this.countryCode).value;
				var postData = \"module=Accounts&action=ajaxcountrytostate&to_pdf=1&country_code=\" + escape(theCountryCode);
				// AJAX Call
				var result = YAHOO.util.Connect.asyncRequest('POST','index.php', {success: success, failure: failure}, postData);
			}
		});";
	}

	/**
	 * Returns the javascript code to generate this actions equivalent. 
	 *
	 * @return string javascript.
	 */
	function getJavascriptFire() {
		return  "new SUGAR.forms.SetStateOptionsAction('{$this->countryCode}','{$this->stateField}')";
	}
	
	
	
	/**
	 * Applies the Action to the target.
	 *
	 * @param SugarBean $target
	 * A NoOP on the PHP side for setoptions
	 */
	function fire(&$target) {
		
		/*$expr = Parser::replaceVariables($this->expression, $target);
		$result = Parser::evaluate($expr)->evaluate();
		$field = $this->targetField;
		$target->$field = $result;*/
	}
	
	static function getActionName() {
		return "SetStateOptions";
	}
}
