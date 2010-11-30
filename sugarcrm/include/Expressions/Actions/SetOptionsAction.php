<?php
/************************************
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
require_once("include/Expressions/Actions/AbstractAction.php");

class SetOptionsAction extends AbstractAction{
	protected $keysExpression =  "";
	protected $labelsExpressions =  "";
	
	function SetOptionsAction($params) {
		$this->targetField = $params['target'];
		$this->keysExpression = $params['keys'];
		$this->labelsExpression = $params['labels'];
	}
	
	/**
	 * Returns the javascript class equavalent to this php class
	 *
	 * @return string javascript.
	 */
	static function getJavascriptClass() {
		return  "
		SUGAR.forms.SetOptionsAction = function(target, keyExpr, labelExpr) {
			this.keyExpr = keyExpr;
			this.labelExpr = labelExpr;
			this.target = target;
		};
				
		SUGAR.util.extend(SUGAR.forms.SetOptionsAction, SUGAR.forms.AbstractAction, {
			exec: function() {
				var field = SUGAR.forms.AssignmentHandler.VARIABLE_MAP[this.target];
				if ( field == null )	return null;		
				
				var keys = SUGAR.forms.evalVariableExpression(this.keyExpr).evaluate();
				var labels = SUGAR.forms.evalVariableExpression(this.labelExpr).evaluate();
				var selected = '';
				
				if (keys instanceof Array && field.options != null) 
				{
					// get the options of this select
					var options = field.options;
					
					for (var i = 0; i < options.length; i++) {
					    if (options[i].selected)
					    	selected = options[i].value;
					}
					
					// empty the options
					while (options.length > 0) {
						field.remove(options[0]);
					}
					
					var new_opt;
					for (var i in keys) {
						if (labels instanceof Array)
						{
							if (typeof keys[i] == 'string')
							{
								if (typeof labels[i] == 'string') {
									new_opt = options[options.length] = new Option(labels[i], keys[i], keys[i] == selected);
								}
								else 
								{
									new_opt = options[options.length] = new Option(keys[i], keys[i], keys[i] == selected);
								}
							}
						} else //Use the keys as labels
						{
							if (typeof keys[0] == 'undefined') {
								if (typeof(keys[i]) == 'string') {
									new_opt = options[options.length] = new Option(keys[i], i);
								}
							} else {
								if (typeof(value[i]) == 'string') {
									new_opt = options[options.length] = new Option(keys[i], keys[i]);
								}
							}
						}
						if (keys[i] == selected)
							new_opt.selected = true;
					
					}
					
					if(field.value != selected)
						SUGAR.forms.AssignmentHandler.assign(this.target, field.value);
					
					//Hide fields with empty lists
					var empty =  field.options.length == 1 && field.value == '';
					var visAction = new SUGAR.forms.VisibilityAction(this.target, empty ? 'false' : 'true', '');
					visAction.exec();
					
					if ( SUGAR.forms.AssignmentHandler.ANIMATE && !empty)
						SUGAR.forms.FlashField(field);
				}
			}
		});";
	}

	/**
	 * Returns the javascript code to generate this actions equivalent. 
	 *
	 * @return string javascript.
	 */
	function getJavascriptFire() {
		return  "new SUGAR.forms.SetOptionsAction('{$this->targetField}','{$this->keysExpression}', '{$this->labelsExpression}')";
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
	
	/**
	 * Returns the definition of this action in array format.
	 *
	 */
	function getDefinition() {
		return array(	
			"action" => $this->getActionName(), 
	        "target" => $this->targetField, 
	        "value" => $this->expression,
	    );
	}
	
	static function getActionName() {
		return "SetOptions";
	}
}