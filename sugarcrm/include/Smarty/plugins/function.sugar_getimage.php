<?php

/*

Modification information for LGPL compliance

r56990 - 2010-06-16 13:05:36 -0700 (Wed, 16 Jun 2010) - kjing - snapshot "Mango" svn branch to a new one for GitHub sync

r56989 - 2010-06-16 13:01:33 -0700 (Wed, 16 Jun 2010) - kjing - defunt "Mango" svn dev branch before github cutover

r55980 - 2010-04-19 13:31:28 -0700 (Mon, 19 Apr 2010) - kjing - create Mango (6.1) based on windex

r51719 - 2009-10-22 10:18:00 -0700 (Thu, 22 Oct 2009) - mitani - Converted to Build 3  tags and updated the build system 

r51634 - 2009-10-19 13:32:22 -0700 (Mon, 19 Oct 2009) - mitani - Windex is the branch for Sugar Sales 1.0 development

r50375 - 2009-08-24 18:07:43 -0700 (Mon, 24 Aug 2009) - dwong - branch kobe2 from tokyo r50372

r42807 - 2008-12-29 11:16:59 -0800 (Mon, 29 Dec 2008) - dwong - Branch from trunk/sugarcrm r42806 to branches/tokyo/sugarcrm

r40349 - 2008-10-07 12:27:23 -0700 (Tue, 07 Oct 2008) - jmertic - Changes for Iteration 1 of the Themes Improvements:
- Added SugarTheme and SugarThemeRegistry objects, updating everywhere in the app to use them.
- Converted the Sugar Theme to the new style, which involved:
 - moved all PHP and HTML out of the themes, into SugarView or the include/utils/layout_utils.php directory.
 - all images in the images/ directory and all css in the css/ directory.
 - removed config.php and replaced it with themedef.php.

r33134 - 2008-03-21 04:51:32 -0700 (Fri, 21 Mar 2008) - majed - templating changes

r32836 - 2008-03-14 16:48:48 -0700 (Fri, 14 Mar 2008) - majed - adds smarty functions


*/


/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sugar_include} function plugin
 *
 * Type:     function<br>
 * Name:     sugar_include<br>
 * Purpose:  Handles rendering the global file includes from the metadata files defined
 *           in templateMeta=>includes.
 * 
 * @author Aamir Mansoor (amansoor@sugarcrm.com) 
 * @author Cam McKinnon (cmckinnon@sugarcrm.com)
 * @param array
 * @param Smarty
 */

function smarty_function_sugar_getimage($params, &$smarty) {
	// error checking for parameters
	if(!isset($params['name'])) $smarty->trigger_error($GLOBALS['app_strings']['ERR_MISSING_REQUIRED_FIELDS'] . 'name');
	if(!isset($params['alt'])) $smarty->trigger_error($GLOBALS['app_strings']['ERR_MISSING_REQUIRED_FIELDS'] . 'alt');

	// set to default values if not present already
	if(!isset($params['other_attributes'])) $params['other_attributes'] = '';
	if(!isset($params['width'])) $params['width'] = null;
	if(!isset($params['height'])) $params['height'] = null;
	if(!isset($params['ext'])) $params['ext'] = '.gif';

	return SugarThemeRegistry::current()->getImage($params['name'], $params['other_attributes'], $params['width'], $params['height'], $params['ext'], $params['alt']);	
}
?>