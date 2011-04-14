<?php
 //FILE SUGARCRM flav=een ONLY
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
require_once("include/Expressions/Expression/Boolean/BooleanExpression.php");

/**
 * <b>isValidDate(String date)</b><br/>
 * Returns true if <i>date</i> is a valid date string or is empty.
 *
 */
class IsValidDateExpression extends BooleanExpression {
	/**
	 * Returns itself when evaluating.
	 */
	function evaluate() {
		$dtStr = $this->getParameters()->evaluate();
		$date_reg_positions = array( 'Y'=>1 ,'m'=>2,'d'=>3 );
		$date_reg_format    = '/(^[0-9]{4})[-\/.]([0-9]{1,2})[-\/.]([0-9]{1,2})$/';
		if(strlen($dtStr) == 0) return AbstractExpression::$TRUE;
	    // Check that we have numbers
		$dateParts = array();
	    if(!preg_match($date_reg_format, $dtStr, $dateParts))
		{
		 	return AbstractExpression::$FALSE;
		}
	    $m = '';
	    $d = '';
	    $y = '';
	    
	   //preg_match( $date_reg_format, $dtStr, $dateParts);

	    foreach ( $date_reg_positions as $key => $index )
	    {
	        if($key == 'm') {
	           $m = $dateParts[$index];
	        } else if($key == 'd') {
	           $d = $dateParts[$index];
	        } else {
	           $y = $dateParts[$index];
	        }
	    }
	   // _pp("Y = $y, m=$m, d=$d");

	    // reject negative years
	    if ($y < 1)
	        return AbstractExpression::$FALSE;
	    // reject month less than 1 and greater than 12
	    if ($m > 12 || $m < 1)
	        return AbstractExpression::$FALSE;

	    // Check that date is real
	    $dd = cal_days_in_month(CAL_GREGORIAN, $m, $y);
	    
	    // reject days less than 1 or days not in month (e.g. February 30th)
	    if ($d < 1 || $d > $dd)
	        return AbstractExpression::$FALSE;
		return AbstractExpression::$TRUE;
	}

	/**
	 * Returns the JS Equivalent of the evaluate function.
	 */
	static function getJSEvaluate() {
		return <<<EOQ
		var dtStr = this.getParameters().evaluate();
		var date_reg_positions = {'Y': 1,'m': 2,'d': 3};
		var date_reg_format = '(^[0-9]{4})[-/.]([0-9]{1,2})[-/.]([0-9]{1,2})$';
		if(dtStr.length == 0) return SUGAR.expressions.Expression.TRUE;
	    // Check that we have numbers
		var myregexp = new RegExp(date_reg_format)
		if(!myregexp.test(dtStr))	return SUGAR.expressions.Expression.FALSE;
	    var m = '';
	    var d = '';
	    var y = '';
	    var dateParts = dtStr.match(date_reg_format);
	    for(key in date_reg_positions) {
	        index = date_reg_positions[key];
	        if(key == 'm') {
	           m = dateParts[index];
	        } else if(key == 'd') {
	           d = dateParts[index];
	        } else {
	           y = dateParts[index];
	        }
	    }
	    // Check that date is real
	    var dd = new Date(y,m,0);
	    // reject negative years
	    if (y < 1)
	        return SUGAR.expressions.Expression.FALSE;
	    // reject month less than 1 and greater than 12
	    if (m > 12 || m < 1)
	        return SUGAR.expressions.Expression.FALSE;
	    // reject days less than 1 or days not in month (e.g. February 30th)
	    if (d < 1 || d > dd.getDate())
	        return SUGAR.expressions.Expression.FALSE;
		return SUGAR.expressions.Expression.TRUE;
EOQ;
	}

	/**
	 * Any generic type will suffice.
	 */
	function getParameterTypes() {
		return array("string");
	}

	/**
	 * Returns the maximum number of parameters needed.
	 */
	static function getParamCount() {
		return 1;
	}

	/**
	 * Returns the opreation name that this Expression should be
	 * called by.
	 */
	static function getOperationName() {
		return "isValidDate";
	}

	/**
	 * Returns the String representation of this Expression.
	 */
	function toString() {
	}
}
?>
