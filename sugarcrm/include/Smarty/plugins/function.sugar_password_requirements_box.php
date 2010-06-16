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
********************************************************************************/

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {smarty_function_sugar_password_box} function plugin
 *
 * Type:     function<br>
 * Name:     smarty_function_sugar_password_box<br>
 * Purpose:  display the password requirement box in the User Module
 *
 * @author Aissah Fabrice {faissah at sugarcrm.com
 * @param array
 * @param Smarty
 */
 //FILE SUGARCRM flav=pro || flav=sales ONLY
function smarty_function_sugar_password_requirements_box($params, &$smarty)
{
global $current_language;
$administration_module_strings = return_module_language($current_language, 'Administration');
$pwd_settings=$GLOBALS['sugar_config']['passwordsetting'];
if ($pwd_settings['oneupper'] == '1')    $DIVFLAGS['1upcase']=$administration_module_strings['LBL_PASSWORD_ONE_UPPER_CASE']; 
if ($pwd_settings['onelower'] == '1')    $DIVFLAGS['1lowcase']=$administration_module_strings['LBL_PASSWORD_ONE_LOWER_CASE']; 
if ($pwd_settings['onenumber'] == '1')   $DIVFLAGS['1number']=$administration_module_strings['LBL_PASSWORD_ONE_NUMBER']; 
if ($pwd_settings['onespecial'] == '1')  $DIVFLAGS['1special']=$administration_module_strings['LBL_PASSWORD_ONE_SPECIAL_CHAR'];  
if ($pwd_settings['customregex'] != '')  $DIVFLAGS['regex']=$pwd_settings['regexcomment'];
if ($pwd_settings['minpwdlength'] >0 && $pwd_settings['maxpwdlength'] >0)
    $DIVFLAGS['lengths']=$administration_module_strings['LBL_PASSWORD_MINIMUM_LENGTH'].' ='.$pwd_settings['minpwdlength'].' '.$administration_module_strings['LBL_PASSWORD_AND_MAXIMUM_LENGTH'].' ='.$pwd_settings['maxpwdlength'];   
else if ($pwd_settings['minpwdlength'] >0)
        $DIVFLAGS['lengths']=$administration_module_strings['LBL_PASSWORD_MINIMUM_LENGTH'].' ='.$pwd_settings['minpwdlength'];    
    else if ($pwd_settings['maxpwdlength'] >0)
        $DIVFLAGS['lengths']=$administration_module_strings['LBL_PASSWORD_MAXIMUM_LENGTH'].' ='.$pwd_settings['maxpwdlength'];
           
if ($DIVFLAGS=='')
	return;
$table_style='';

foreach($params as $prop => $value){$table_style.= $prop."='".$value."' ";}
$box="	<table ".$table_style.">
<tr><td width='18px'></td><td></td></tr>";
foreach($DIVFLAGS as $key => $value) {
	if ($key != '')
		$box.="<tr><td> <div class='bad' id='$key'></div> </td><td>  <div align='left'>$value</div></td></tr>";    	
}
$box.="</table>";
return $box;
}            
?>