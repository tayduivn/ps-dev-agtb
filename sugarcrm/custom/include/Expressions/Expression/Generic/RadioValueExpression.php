<?php

// HAS NOT BEEN USED YET. PLEASE TEST BEFORE USING. IF IT WORKS, REMOVE THIS COMMENT!!

require_once('include/Expressions/Expression/Generic/GenericExpression.php');
/**
 * <b>radioValue(String <i>field</i>)</b><br>
 * Returns the checked value of <i>field</i>. Note: do not pass $field, pass 'field'</i><br/>
 * ex: <i>radioValue("name")</i>
 */
class RadioValueExpression extends GenericExpression
{
	/**
	 * Returns the entire enumeration bare.
	 */
	function evaluate() {
		$params = $this->getParameters();
		$field = $params["params"];

        if (empty($field)) {
            return "";
        }
		
		if(isset($this->context->$field)){
			return $this->context->$field;
		}
		
		return "";
	}

	/**
	 * Returns the JS Equivalent of the evaluate function.
	 */
	static function getJSEvaluate() {
		return <<<EOQ
		    var params = this.getParameters();
        	var field  = params["params"];

			if (typeof(field) == "string" && field != "")
			{
				var radio_options = document.getElementsByName(field);
				if(typeof(radio_options) != 'object'){
					return "";
				}
				
				for(x in radio_options){
					if(radio_options[x].checked == true){
						return radio_options[x].value;
					}
				}
			}

			return "";
EOQ;
	}

	/**
	 * Returns the opreation name that this Expression should be
	 * called by.
	 */
	static function getOperationName() {
		return array("radioValue");
	}

	/**
	 * The first parameter is a number and the second is the list.
	 */
	function getParameterTypes() {
		return array(AbstractExpression::$STRING_TYPE);
	}

	/**
	 * Returns the maximum number of parameters needed.
	 */
	static function getParamCount() {
		return 1;
	}

	/**
	 * Returns the String representation of this Expression.
	 */
	function toString() {
	}
}

?>
