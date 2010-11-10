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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 * $Id: additionalDetails.php 13782 2006-06-06 17:58:55Z majed $
 *********************************************************************************/
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sugar_translate} function plugin
 *
 * Type:     function<br>
 * Name:     sugar_translate<br>
 * Purpose:  translates a label into the users current language
 * 
 * @author Majed Itani {majed at sugarcrm.com
 * @param array
 * @param Smarty
 */
function smarty_function_sugar_phone($params, &$smarty)
{
	if (!isset($params['value'])){
		$smarty->trigger_error("sugar_phone: missing 'value' parameter");
		return '';
	}
	
	//Check if we need usa_format
	if(!empty($params['usa_format']) && preg_match('/^([01])?[- .]?\(?(\d{3})\)?[- .]?([\da-zA-Z]{3})[- .]?([\da-zA-Z]{4})(.*?)$/', $params['value'], $matches))
	{
	   $params['value'] = $matches[1] . '(' . $matches[2] . ')' . $matches[3] . '-' . $matches[4] . $matches[5];
	}
	
	global $system_config;
    if(isset($system_config->settings['system_skypeout_on']) && $system_config->settings['system_skypeout_on'] == 1
    	&& isset($params['value']) && skype_formatted($params['value'])  ) {
    		$GLOBALS['log']->debug($params['value']);
			return '<a href="callto://'.format_skype($params['value']).'">'.$params['value'].'</a>';
    //BEGIN SUGARCRM flav=pro ONLY
    } elseif(isset($_SESSION['isMobile'])) {
        return '<a href="tel:'.format_skype($params['value']).'">'.$params['value'].'</a>';
    //END SUGARCRM flav=pro ONLY
    } else {
    	return $params['value'];
    }
}
?>
