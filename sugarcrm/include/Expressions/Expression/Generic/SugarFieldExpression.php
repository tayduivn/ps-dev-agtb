
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
require_once('include/Expressions/Expression/Generic/GenericExpression.php');
/**
 * <b>related(Relate <i>link</i>, String <i>field</i>)</b><br>
 * Returns the value of <i>field</i> in the related module <i>link</i><br/>
 * ex: <i>related($accounts, "industry")</i>
 */
class SugarFieldExpression extends GenericExpression
{

    function __construct($varName){
        $this->varName = $varName;
    }
    /**
	 * Returns the entire enumeration bare.
	 */
	function evaluate() {
		if (empty($this->varName))
            return "";
        $fieldName = $this->varName;

        if (!isset($this->context))
        {
            //If we don't have a context provided, we have to guess. This can be a large performance hit.
            $this->setContext();
        }

        if (empty($this->context->field_defs[$fieldName]))
            throw new Exception("Unable to find field {$fieldName}");

        $def = $this->context->field_defs[$fieldName];

        switch($def['type']) {
            case 'link':
                return $this->getLinkField($fieldName);
            case 'datetime':
            case 'datetimecombo':
                $date = TimeDate::fromDb($this->context->$fieldName);
                TimeDate::getInstance()->tzUser($date);
                return $date;
            case 'date':
                $date = TimeDate::fromDbDate($this->context->$fieldName);
                TimeDate::getInstance()->tzUser($date);
                return $date;
            case 'time':
                return TimeDate::fromUserTime(TimeDate::getInstance()->to_display_time($this->context->$fieldName));
        }
        return $this->context->$fieldName;
	}

    protected function setContext()
    {
        $module = $_REQUEST['module'];
        $id = $_REQUEST['record'];
        $focus = $this->getBean($module);
        $focus->retrieve($id);
        $this->context = $focus;
    }

    protected function getBean($module)
    {
       global $beanList;
       if (empty($beanList[$module]))
           throw new Exception("No bean for module $module");
       $bean = $beanList[$module];
       return new $bean();
    }

    protected function getLinkField($fieldName)
    {
        if(!$this->context->load_relationship($fieldName))
            throw new Exception("Unable to load relationship $fieldName");

        if(empty($this->context->$fieldName))
            throw new Exception("Relationship $fieldName was not set");



        $rmodule = $this->context->$fieldName->getRelatedModuleName();

        //now we need a seed of the related module to load.
        $seed = $this->getBean($rmodule);

        return $this->context->$fieldName->getBeans($seed);
    }



	/**
	 * Returns the JS Equivalent of the evaluate function.
	 */
	static function getJSEvaluate() {
		return <<<EOQ
		    var params = this.getParameters();
			var linkField = params[0].evaluate();
			var relField = params[1].evaluate();

			if (typeof(linkField) == "string" && linkField != "")
			{
                //We just have a field name, assume its the name of a link field
                //and the parent module is the current module.
                //Try and get the current module and record ID
                var module = SUGAR.forms.AssignmentHandler.getValue("module");
                var record = SUGAR.forms.AssignmentHandler.getValue("record");
                if (!module || !record)
                    return "";
                var url = "index.php?" + SUGAR.util.paramsToUrl({
                    module:"ExpressionEngine",
                    action:"execFunction",
                    id: record,
                    tmodule:module,
                    "function":"related",
                    params: YAHOO.lang.JSON.stringify(['\$' + linkField, '"' + relField + '"'])
                });
                //The response should the be the JSON encoded value of the related field
                return YAHOO.lang.JSON.parse(http_fetch_sync(url).responseText);
			} else if (typeof(rel) == "object") {
			    //Assume we have a Link object that we can delve into.
			    //This is mostly used for n level dives through relationships.
			    //This should probably be avoided on edit views due to performance issues.

			}

			console.log("fell through");
			return "";
EOQ;
	}

	/**
	 * Returns the opreation name that this Expression should be
	 * called by.
	 */
	static function getOperationName() {
		return array("sugar");
	}

	/**
	 * The first parameter is a number and the second is the list.
	 */
	function getParameterTypes() {
		return array(AbstractExpression::$RELATE_TYPE, AbstractExpression::$STRING_TYPE);
	}

	/**
	 * Returns the maximum number of parameters needed.
	 */
	static function getParamCount() {
		return 2;
	}

	/**
	 * Returns the String representation of this Expression.
	 */
	function toString() {
	}
}

?>