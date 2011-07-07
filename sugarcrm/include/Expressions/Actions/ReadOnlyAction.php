<?php
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
require_once("include/Expressions/Actions/AbstractAction.php");

class ReadOnlyAction extends AbstractAction{
	protected $expression =  "";
	
	function ReadOnlyAction($params) {
		$this->targetField = $params['target'];
		$this->expression = $params['value'];
	}
	
/**
	 * Returns the javascript class equavalent to this php class
	 *
	 * @return string javascript.
	 */
	static function getJavascriptClass() {
		return  "
		SUGAR.forms.ReadOnlyAction = function(target, expr) {
			this.target = target;
			this.expr = expr;
		}

		SUGAR.util.extend(SUGAR.forms.ReadOnlyAction, SUGAR.forms.AbstractAction, {

			/**
			 * Triggers the style dependencies.
			 */
			exec: function()
			{
				var el = SUGAR.forms.AssignmentHandler.getElement(this.target);
				if (!el)
				    return;
				var val = SUGAR.forms.evalVariableExpression(this.expr).evaluate();
				if (val == SUGAR.expressions.Expression.TRUE) {
					el.readOnly = true;
					YAHOO.util.Dom.setStyle(el, 'background-color', '#EEE');
					if (!SUGAR.isIE)
					   YAHOO.util.Dom.setStyle(el, 'color', '#22');
				} else if (val == SUGAR.expressions.Expression.FALSE){
					el.readOnly = false;
					YAHOO.util.Dom.setStyle(el, 'background-color', '');
					if (!SUGAR.isIE)
                        YAHOO.util.Dom.setStyle(el, 'color', '');
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
		return "new SUGAR.forms.ReadOnlyAction('{$this->targetField}','{$this->expression}')";
	}
	
	/**
	 * Applies the Action to the target.
	 *
	 * @param SugarBeam $target
	 */
	function fire(&$target) {
		//This is a no-op under PHP
	}
	
	static function getActionName() {
		return "ReadOnly";
	}
	
}