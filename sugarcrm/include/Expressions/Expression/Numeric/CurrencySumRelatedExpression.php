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
require_once('include/Expressions/Expression/Numeric/NumericExpression.php');
/**
 * <b>rollupCurrencySum(Relate <i>link</i>, String <i>field</i>)</b><br>
 * Returns the sum of the values of <i>field</i> in records related by <i>link</i><br/>
 * ex: <i>rollupCurrencySum($products, "likely_case")</i> in Opportunities would return the <br/>
 * sum of the likely_case field converted to base currency for all the products related to this Opportunity
 */
class CurrencySumRelatedExpression extends SumRelatedExpression
{
	/**
	 * Returns the entire enumeration bare.
	 */
	public function evaluate() {
		$params = $this->getParameters();
		//This should be of relate type, which means an array of SugarBean objects
        $linkField = $params[0]->evaluate();
        $relfield = $params[1]->evaluate();

		$ret = 0;

		if (!is_array($linkField) || empty($linkField))
            return $ret;

        foreach($linkField as $bean)
        {
            if (!empty($bean->$relfield)) {
                $ret = SugarMath::init($ret)->add(SugarCurrency::convertWithRate($bean->$relfield, $bean->base_rate))->result();
            }
        }

        return $ret;
	}

	/**
	 * Returns the JS Equivalent of the evaluate function.
	 */
	public static function getJSEvaluate() {
		return <<<EOQ
		    var params = this.getParameters();
			var linkField = params[0].evaluate();
			var relField = params[1].evaluate();

			if (typeof(linkField) == "string" && linkField != "")
			{
                return this.context.getRelatedField(linkField, 'rollupCurrencySum', relField);
			} else if (typeof(rel) == "object") {
			    //Assume we have a Link object that we can delve into.
			    //This is mostly used for n level dives through relationships.
			    //This should probably be avoided on edit views due to performance issues.

			}

			return "";
EOQ;
	}

	/**
	 * Returns the opreation name that this Expression should be
	 * called by.
	 */
	public static function getOperationName() {
		return array("rollupCurrencySum");
	}

	/**
	 * The first parameter is a number and the second is the list.
	 */
    public static function getParameterTypes() {
		return array(AbstractExpression::$RELATE_TYPE, AbstractExpression::$STRING_TYPE);
	}

	/**
	 * Returns the maximum number of parameters needed.
	 */
	public static function getParamCount() {
		return 2;
	}
}

?>
