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
require_once("include/Expressions/Dependency.php");
require_once("include/Expressions/Trigger.php");
require_once("include/Expressions/Expression/Parser/Parser.php");
require_once("include/Expressions/Actions/ActionFactory.php");

class DependencyManager {
	
	/**
	 * Returns a new Dependency that will power the provided calculated field.
	 *
	 * @param Array<String=>Array> $fields, list of fields to get dependencies for.
	 * @param Boolean $includeReadOnly
	 * @return array<Dependency>
	 */
	static function getCalculatedFieldDependencies($fields, $includeReadOnly = true) {
		
		$deps = array();
		require_once("include/Expressions/Actions/SetValueAction.php");
		foreach($fields as $field => $def) {
			if (isset($def['calculated']) && $def['calculated'] && !empty($def['formula'])) {
		    	$triggerFields = Parser::getFieldsFromExpression($def['formula']);
		    	$dep = new Dependency($field);
		    	$dep->setTrigger(new Trigger('true', $triggerFields));
		    	
		    	$dep->addAction(ActionFactory::getNewAction('SetValue', array('target' => $field, 'value' => $def['formula'])));

		    	if (isset($def['enforced']) && $def['enforced'] == true) {
			    	$dep->setFireOnLoad(true);
		    		if ($includeReadOnly)
		    		{
		    			$readOnlyDep = new Dependency("readOnly$field");
		    			$readOnlyDep->setFireOnLoad(true);
		    			$readOnlyDep->setTrigger(new Trigger('true', array()));
		    			$readOnlyDep->addAction(ActionFactory::getNewAction('ReadOnly', 
		    					array('target' => $field, 
		    						  'value' => 'true')));
				    			
				    	$deps[] = $readOnlyDep;
		    		}
		    	}
		    	
		    	$deps[] = $dep;
		    }
	    }
	    
	    return $deps;
	}
	
	static function getDependentFieldDependencies($fields) {
		$deps = array();
		
		foreach($fields as $field => $def) {
			if ( isset ( $def [ 'dependency' ] ) )
    		{
    			// normalize the dependency definition
    			if ( ! is_array ( $def [ 'dependency' ] ) )
    			{
    				$triggerFields = Parser::getFieldsFromExpression ( $def [ 'dependency' ] ) ;
    				$def [ 'dependency' ] = array ( array ( 'trigger' => $triggerFields , 'action' => $def [ 'dependency' ] ) ) ;
    			}
				foreach ( $def [ 'dependency' ]  as $depdef)
				{
    				$dep = new Dependency ( $field ) ;
    				if (is_array($depdef [ 'trigger' ])) {
    					$triggerFields = $depdef [ 'trigger' ];
    				} else {
    					$triggerFields = Parser::getFieldsFromExpression ( $depdef [ 'trigger' ] ) ;
    				}
    				$dep->setTrigger ( new Trigger ( 'true' , $triggerFields ) ) ;
    				$dep->addAction ( ActionFactory::getNewAction('SetVisibility', 
    					array( 'target' => $field , 'value' => $depdef [ 'action' ]))) ;
    				$dep->setFireOnLoad(true);
					$deps[] = $dep;
				}
    		}
		}
		return $deps;
	}
	
	static function getDropDownDependencies($fields) {
		$deps = array();
		global $app_list_strings;
		
		foreach($fields as $field => $def) {
			if ( $def['type'] == "enum" && isset ( $def [ 'visibility_grid' ] ) )
    		{
    			$grid = $def [ 'visibility_grid' ];
    			if (!isset($grid['values']) || empty($grid['trigger']))
    				continue;

    			$trigger_list_id = $fields[$grid [ 'trigger' ]]['options'];
    			$trigger_values = $app_list_strings[$trigger_list_id];
    			
    			$options = $app_list_strings[$def['options']];
    			$result_labels = array();
    			$result_keys = array();
    			foreach($trigger_values as $label_key => $label) {
    				if (!empty($grid['values'][$label_key])) {
    					$key_list = array();
    					$trans_labels = array();
    					foreach($grid['values'][$label_key] as $label_key) {
    						if (isset($options[$label_key]))
    						{
    							$key_list[$label_key] = $label_key;
    							$trans_labels[$label_key] = $options[$label_key];
    						}
    					}
    					$result_keys[] = 'enum("' . implode('","', $key_list) . '")';
    					$result_labels[] = 'enum("' . implode('","', $trans_labels) . '")';
    				} else {
    					$result_keys[] = 'enum("")';
    					$result_labels[] = 'enum("")';
    				}
    			}
    			
    			$keys = 'enum(' . implode(',', $result_keys) . ')';
    			$labels = 'enum(' . implode(',', $result_labels) . ')';
    			//If the trigger key doesn't appear in the child list, hide the child field.
    			$keys_expression = 'cond(equal(indexOf($' . $grid [ 'trigger' ] 
    					    . ', getDD("' . $trigger_list_id . '")), -1), enum(""), ' 
    						. 'valueAt(indexOf($' . $grid [ 'trigger' ] 
    						. ',getDD("' . $trigger_list_id . '")),' . $keys . '))';
    			$labels_expression = 'cond(equal(indexOf($' . $grid [ 'trigger' ] 
    						. ', getDD("' . $trigger_list_id . '")), -1), enum(""), '  
    			 			. 'valueAt(indexOf($' . $grid [ 'trigger' ] 
    						. ',getDD("' . $trigger_list_id . '")),' . $labels . '))';
    			$dep = new Dependency ( $field . "DDD");
    			$dep -> setTrigger( new Trigger ('true', $grid['trigger']));
    			$dep -> addAction (
    				ActionFactory::getNewAction('SetOptions', array(
    					'target' => $field,
    					'keys' => $keys_expression, 
    					'labels' => $labels_expression)));
    			$dep->setFireOnLoad(true);
    			$deps[] = $dep;
    		}
		}
		return $deps;
	}
	
	static function getPanelDependency($panel_id, $dep_expression)
    {
        $dep = new Dependency ( $panel_id . "_visibility");
        $dep -> setTrigger( new Trigger('true', Parser::getFieldsFromExpression ( $dep_expression ) ) );
        $dep -> addAction (
            ActionFactory::getNewAction('SetPanelVisibility', array(
                'target' => $panel_id,
                'value' => $dep_expression,
            ))
        );
        $dep->setFireOnLoad(true);
        
        return $dep;
    }
	
	static function getDependenciesForView($viewdef)
    {
        $deps = array();
        if (isset($viewdef['templateMeta']) && !empty($viewdef['templateMeta']['panelDependencies'])){
            foreach (($viewdef['templateMeta']['panelDependencies']) as $id => $expr)
            {
                $deps[] = self::getPanelDependency(strtoupper($id), $expr);
            }
        }
        return $deps;
    }
	
	static function getDependenciesForFields($fields) {
		return array_merge(self::getCalculatedFieldDependencies($fields),
						   self::getDependentFieldDependencies($fields),
						   self::getDropDownDependencies($fields));
	}

    /**
     * @static
     * @param  $user User, user to return SugarLogic variables for
     * @return void
     */
    public static function getJSUserVariables($user, $wrap = false)
    {
        $ret = "SUGAR.expressions.userPrefs = " . json_encode(array(
            "num_grp_sep" => $user->getPreference("num_grp_sep"),
            "dec_sep" => $user->getPreference("dec_sep"),
            "datef" => $user->getPreference("datef"),
            "timef" => $user->getPreference("timef"),
            "default_locale_name_format" => $user->getPreference("default_locale_name_format"),
        )) . ";\n";
        if ($wrap)
            $ret = "<script type=text/javascript>\n$ret</script>";
        return $ret;
    }
}
?>