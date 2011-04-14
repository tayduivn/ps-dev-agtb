<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: SugarPDF.php 15524 2006-08-04 21:02:21Z chris $
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/

require_once("include/pdf/class.expdf.php");
/**
 * Subclass of EzPDF for SugarCRM
 * contains SugarCRM-specific private methods for handling of data for PDF
 * export
 */
class SugarPDF extends Cezpdf {
	
	/**
	 * sole constructor
	 * @param array vars Setup values for parent class, EzPDF
	 */
	function SugarPDF($vars) {
		parent::Cezpdf($vars);
	}
	
	/**
	 * takes a $bean and processes all of its list variables for character set
	 * issues
	 * @param bean object The focus bean
	 * @return bean object The focus bean with processed strings
	 */
	function handleBeanStrings($bean) {
		foreach($bean->field_defs as $k => $field) {
			if($field['type'] == 'varchar' || $field['type'] == 'text' || $field['type'] == 'enum') {
				$bean->$k = $this->handleCharset($bean->$k);
			}
		}
		
		return $bean;
	}

	/**
	 * Translates text from UTF-8 (as of SugarCRM v4.5) into the selected
	 * default character set for a given instance, abrogated by user preference.
	 * @param string text The text to be handled
	 * @return string ret The translated string.
	 */
	function handleCharset($text) {
		global $locale;
		
		$ret = $locale->translateCharset($text, 'UTF-8', $locale->getPrecedentPreference('default_export_charset'));
		return $ret;
	}
}
?>