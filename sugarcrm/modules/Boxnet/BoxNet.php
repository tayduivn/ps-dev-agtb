<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
class BoxNet extends SugarBean{
	var $disable_vardefs = true;
	
	
	
	
	
	function display($width='100%', $height='345', $full_site_link=true){
		$site_url = $GLOBALS['sugar_config']['site_url'];
		if(!is_admin($GLOBALS['current_user'])){
			$cant_register_text = "If you do not have a valid login, please contact your Sugar administrator.";
			$params = "&can_register=0&cant_register_text=" . urlencode($cant_register_text);	
		}else{
			$can_register_text = "Click here to register";
			$params = '&register_on_box=1&can_register_text=' . urlencode($can_register_text);	
		}
		$text = '';
		if($full_site_link){
			$text = '<div align="center"><a href="index.php?module=Boxnet&action=index">Full Site</a></div>';	
		}
		return <<<BOXNET
		<embed src="http://www.box.net/static/flash/box_explorer.swf?sfa=1&fao=1&v=1&apiKey=yxlcvz2ke28e7zcdcdc96flhqvrrh3jm{$params}&partner_user_name={$GLOBALS['current_user']->name}&partner_user_email={$GLOBALS['current_user']->email1}" width="{$width}" height="{$height}" wmode="transparent" type="application/x-shockwave-flash"></embed>
		$text
BOXNET;
	
	}

}
