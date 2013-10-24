<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/*

Modification information for LGPL compliance

r56990 - 2010-06-16 13:05:36 -0700 (Wed, 16 Jun 2010) - kjing - snapshot "Mango" svn branch to a new one for GitHub sync

r56989 - 2010-06-16 13:01:33 -0700 (Wed, 16 Jun 2010) - kjing - defunt "Mango" svn dev branch before github cutover

r55980 - 2010-04-19 13:31:28 -0700 (Mon, 19 Apr 2010) - kjing - create Mango (6.1) based on windex

r53865 - 2010-01-19 21:51:54 -0800 (Tue, 19 Jan 2010) - lam - updated styles for button slider

r53792 - 2010-01-18 13:37:35 -0800 (Mon, 18 Jan 2010) - roger - adding code to support slider buttons.


*/


/**
 * smarty_function_sugar_button
 * This is the constructor for the Smarty plugin.
 *
 * @param $params The runtime Smarty key/value arguments
 * @param $smarty The reference to the Smarty object used in this invocation
 */
function smarty_function_sugar_button_slider($params, &$smarty)
{
   if(empty($params['module'])) {
   	  $smarty->trigger_error("sugar_button_slider: missing required param (module)");
   } else if(empty($params['buttons'])) {
   	  $smarty->trigger_error("sugar_button_slider: missing required param (buttons)");
   } else if(empty($params['view'])) {
   	  $smarty->trigger_error("sugar_button_slider: missing required param (view)");
   }
	$module = $params['module'];
   	$view = $params['view'];
   	$buttons = $params['buttons'];
   	$str = '';
   if(is_array($buttons)) {
   	  if(count($buttons) <= 2){
   	  	foreach($buttons as $val => $button){
   	  		$str .= smarty_function_sugar_button(array('module' => $module, 'id' => $button, 'view' => $view), $smarty);
   	  	}
   	  }else{
   	  	$str  = '<div id="buttonSlide" class="yui-module">';
   	  	$str .= '<table border="0">';
   	  	$str .='<tr><td>';
   	  	$str .='<div class="yui-hd">';
   	  	for($i = 0; $i < 2; $i++){
   	  		$button = $buttons[$i];
   	  		$str .= smarty_function_sugar_button(array('module' => $module, 'id' => $button, 'view' => $view), $smarty);
   	  		$str .= ' ';
   	  	}
   	  	$str .= '</div></td>';
   	  	$str .='<td align="right"> <div class="yui-bd">';
   	 	for($i = 2; $i < count($buttons); $i++){
   	  		$button = $buttons[$i];
   	  		$str .= smarty_function_sugar_button(array('module' => $module, 'id' => $button, 'view' => $view), $smarty);
   	  		$str .= ' ';
   	 	}
   	  	$str .='</div></td>';
   	  	$str .='</tr></table>';
   	  }
   }
	return $str;
}

?>
