<?php

require_once("include/Expressions/Expression/Boolean/BooleanExpression.php");

/**
 * <b>isEmailEmpty()</b><br>
 * Returns true if there are no email addresses associated to this record<br/>
 * ex: <i>isEmailEmpty()</i> = true
 */
class IsEmailEmptyExpression extends BooleanExpression {
	/**
	 * Returns itself when evaluating.
	 */
	function evaluate() {
		$params = $this->getParameters();

 		if (!isset($this->context))
		{
			//If we don't have a context provided, we have to guess. This can be a large performance hit.
			$this->setContext();
		}
		
		if(empty($this->context->id))
		{
			return AbstractExpression::$TRUE;
		}
		
		if(!is_object($this->context->emailAddress))
		{
            		throw new Exception("This module does not have email addresses");
		}
		
		$email_addresses = $this->context->emailAddress->getAddressesByGUID($this->context->id, $_REQUEST['module']);
		$all_empty = true;
		foreach($email_addresses as $address)
		{
			if(!empty($address))
			{
				$all_empty = false;
			}
		}
		if ( $all_empty )	return AbstractExpression::$TRUE;
		return AbstractExpression::$FALSE;
	}

	protected function setContext()
	{
		$module = $_REQUEST['module'];
		$id = $_REQUEST['record'];
		$focus = $this->getBean($module);
		$focus->retrieve($id);
		$this->context = $focus;
	}

	/**
	 * Returns the JS Equivalent of the evaluate function.
	 */
	static function getJSEvaluate() {
		return <<<EOQ
			if(SUGAR.forms.AssignmentHandler.lastView == 'EditView')
			{
				var module = SUGAR.forms.AssignmentHandler.getValue("module");
				var return_val = SUGAR.expressions.Expression.TRUE;
				var increment = 0;
				var email = '';
				var current_email = document.getElementById(module + '0' + 'emailAddress' + increment);
				while(current_email != null){
					if(current_email.value != "")
					{
						return_val = SUGAR.expressions.Expression.FALSE;
					}
					increment++;
					current_email = document.getElementById(module + '0' + 'emailAddress' + increment);
				}
				return return_val;
			}
			else
			{
            			throw new Exception("isEmailEmpty() is only supported on EditView");
			}
EOQ;
	}

	/**
	 * Any generic type will suffice.
	 */
	function getParameterTypes() {
		return array();
	}

	/**
	 * Returns the maximum number of parameters needed.
	 */
	static function getParamCount() {
		return 0;
	}

	/**
	 * Returns the opreation name that this Expression should be
	 * called by.
	 */
	static function getOperationName() {
		return "isEmailEmpty";
	}

	/**
	 * Returns the String representation of this Expression.
	 */
	function toString() {
	}
}
?>
