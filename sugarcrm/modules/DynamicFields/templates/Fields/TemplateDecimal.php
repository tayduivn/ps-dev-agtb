<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
* The contents of this file are subject to the SugarCRM Professional End User
* License Agreement ("License") which can be viewed at
* http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
* by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/

class TemplateDecimal extends TemplateFloat{
	var $type = 'decimal';
	var $default = null;
	var $default_value = null;
	function TemplateDecimal(){
		$this->vardef_map['precision']='ext1';
	}

    function get_field_def(){
    	$def = parent::get_field_def();
    	return $def;
    }

    function get_db_type(){
//    	$GLOBALS['log']->debug('TemplateFloat:get_db_type()'.print_r($this,true));
    	
		if ($GLOBALS['db']->dbType=='mysql')
		{
    	    $type = " DECIMAL";
        	if(!empty($this->len))
        	{
	            $precision = (!empty($this->precision)) ? $this->precision : 4; // bug 17041 tyoung - mysql requires a precision value if length is specified
	            $type .= "({$this->len},$precision)";
    		}
		}
		elseif ($GLOBALS['db']->dbType=='mssql')
		{
			$type = " decimal";
        	if(!empty($this->len))
        	{
 	            $precision = (!empty($this->precision)) ? $this->precision : 4;       		
	            $type .= "({$this->len},$precision)";
        	}
        	else
        	{
        		$type .= "(11,4)";
        	}	
		}
    	elseif ($GLOBALS['db']->dbType=='oci8')
    	{
			$precision = (!empty($this->precision))? $this->precision: 6;
			$type= " NUMBER(30,$precision) ";
    	}
    	
    	/**
		 * FOR ORACLE 
    	 * return " NUMBER($this->max_size, $this->precision)";
     	 */
    	return $type;
	}
}

?>
