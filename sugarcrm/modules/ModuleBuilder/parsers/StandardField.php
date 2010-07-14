<?php
if (! defined ( 'sugarEntry' ) || ! sugarEntry)
    die ( 'Not A Valid Entry Point' ) ;
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once ('modules/DynamicFields/DynamicField.php') ;

class StandardField extends DynamicField
{
	var $custom_def = array();
	var $base_path = "";
	var $baseField;
	

    function __construct($module = '') {
        $this->module = (! empty ( $module )) ? $module :( (isset($_REQUEST['module']) && ! empty($_REQUEST['module'])) ? $_REQUEST ['module'] : '');
        $this->base_path = "custom/Extension/modules/{$this->module}/Ext/Vardefs";
    }
    
    protected function loadCustomDef($field){
    	global $beanList;
    	if (!empty($beanList[$this->module]) && is_file("custom/Extension/modules/{$this->module}/Ext/Vardefs/sugarfield_$field.php"))
    	{
    		$dictionary = array($beanList[$this->module] => array("fields" => array($field => array())));
            include("$this->base_path/sugarfield_$field.php");
            if (!empty($dictionary[$beanList[$this->module]]) && isset($dictionary[$beanList[$this->module]]["fields"][$field]))
                $this->custom_def = $dictionary[$beanList[$this->module]]["fields"][$field];
    	}
    }
    
    /**
     * Adds a custom field using a field object
     *
     * @param Field Object $field
     * @return boolean
     */
    function addFieldObject(&$field){
        global $dictionary, $beanList;
        if (empty($beanList[$this->module]))
            return false;
        
        $bean_name = $beanList[$this->module];
        if (empty($dictionary[$bean_name]) || empty($dictionary[$bean_name]["fields"][$field->name]))
            return false;

        $currdef = $dictionary[$bean_name]["fields"][$field->name];
        $this->loadCustomDef($field->name);
        $newDef = $field->get_field_def();
        
        require_once ('modules/DynamicFields/FieldCases.php') ;
        $this->baseField = get_widget ( $field->type) ;
        echo ("<pre>");
        	print_r($newDef);
        echo "</pre>";
        foreach ($field->vardef_map as $property => $fmd_col){
           
        	if ($property == "action" || $property == "label_value" || $property == "label"
            	|| ((substr($property, 0,3) == 'ext' && strlen($property) == 4))
            ) 
            	continue;
       	 		
            // Bug 37043 - Avoid writing out vardef defintions that are the default value.
            if (isset($newDef[$property]) && 
            	((!isset($currdef[$property]) && !$this->isDefaultValue($property,$newDef[$property], $this->baseField))
            		|| (isset($currdef[$property]) && $currdef[$property] != $newDef[$property])
            	)
            ){
               
                $this->custom_def[$property] = 
                    is_string($newDef[$property]) ? htmlspecialchars_decode($newDef[$property], ENT_QUOTES) : $newDef[$property];
            }
        }
        
        if (isset($this->custom_def["duplicate_merge_dom_value"]) && !isset($this->custom_def["duplicate_merge"]))
        	unset($this->custom_def["duplicate_merge_dom_value"]);
        
        $file_loc = "$this->base_path/sugarfield_{$field->name}.php";
        
		$out =  "<?php\n // created: " . date('Y-m-d H:i:s') . "\n";
        foreach ($this->custom_def as $property => $val) 
        {
        	$out .= override_value_to_string_recursive(array($bean_name, "fields", $field->name, $property), "dictionary", $val) . "\n";
        }
        
        $out .= "\n ?>";
        
        if (!file_exists($this->base_path))
            mkdir_recursive($this->base_path);
            
        if( $fh = @sugar_fopen( $file_loc, 'w' ) )
	    {
	        fputs( $fh, $out);
	        fclose( $fh );
	        return true ;
	    }
	    else
	    {
	        return false ;
	    }
    }
    
    protected function isDefaultValue($property, $value, $baseField)
    {
     	switch ($property) {
	        case "importable": 
	        //BEGIN SUGARCRM flav=pro ONLY
	        case "reportable":
	        //END SUGARCRM flav=pro ONLY
	        	return ( $value === 'true' || $value === '1' || $value === true || $value === 1 ); break;
	        case "required":
        	case "audited":
        	case "massupdate":
	        	return ( $value === 'false' || $value === '0' || $value === false || $value === 0); break;
        	case "default_value":
        	case "default":
        	case "help":
        	case "comments":
        		return ($value == "");
        	case "duplicate_merge":
	        	return ( $value === 'false' || $value === '0' || $value === false || $value === 0 || $value === "disabled"); break;
        }
        
        if (isset($baseField->$property))
        {
        	return $baseField->$property == $value;
        }
        
        return false;
    }
}

?>
