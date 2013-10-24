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

r51719 - 2009-10-22 10:18:00 -0700 (Thu, 22 Oct 2009) - mitani - Converted to Build 3  tags and updated the build system

r51634 - 2009-10-19 13:32:22 -0700 (Mon, 19 Oct 2009) - mitani - Windex is the branch for Sugar Sales 1.0 development

r50752 - 2009-09-10 15:18:28 -0700 (Thu, 10 Sep 2009) - dwong - Merged branches/tokyo from revision 50372 to 50729 to branches/kobe2
Discard lzhang r50568 changes in Email.php and corresponding en_us.lang.php

r50375 - 2009-08-24 18:07:43 -0700 (Mon, 24 Aug 2009) - dwong - branch kobe2 from tokyo r50372

r42997 - 2009-01-06 13:04:49 -0800 (Tue, 06 Jan 2009) - jmertic - Create a helpInline icon, used for the help icon inside a form. Updated smarty plugin sugar_help to grab the correct icon.

r42807 - 2008-12-29 11:16:59 -0800 (Mon, 29 Dec 2008) - dwong - Branch from trunk/sugarcrm r42806 to branches/tokyo/sugarcrm

r40524 - 2008-10-14 05:27:28 -0700 (Tue, 14 Oct 2008) - jmertic - Fixed a few problems found from the big checkin earlier.

r40493 - 2008-10-13 14:10:05 -0700 (Mon, 13 Oct 2008) - jmertic - Globally change theme image access to use SugarTheme::getImageURL() and SugarTheme::getImage(), instead of previous methods of using getImagePath(), get_image(), or using the $image_path global.

r34454 - 2008-04-21 05:58:55 -0700 (Mon, 21 Apr 2008) - jmertic - Bug 21449: Added new help icon from jyim, and updated smarty function sugar_help to use it.
Added:
- themes/default/images/help.gif
Touched:
- include/SugarSmarty/plugins/function.sugar_help.php

r33864 - 2008-04-07 19:46:34 -0700 (Mon, 07 Apr 2008) - nsingh - more help stuff and UI changes.

r32849 - 2008-03-16 02:50:52 -0700 (Sun, 16 Mar 2008) - majed - reverts changes that for getWebPath template

r32667 - 2008-03-11 17:16:04 -0700 (Tue, 11 Mar 2008) - majed - changes for templating

r32202 - 2008-02-29 11:05:08 -0800 (Fri, 29 Feb 2008) - jmertic - Have sugar_help icon be the magnifying glass and the window not have a title.
Update usage in step1.tpl and step3.tpl.

r32200 - 2008-02-29 10:44:55 -0800 (Fri, 29 Feb 2008) - jmertic - Add string placeholders for several tooltips on Step 3.
Pushed code for help popups into smarty function sugar_help.


*/


/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 *
 * This is a Smarty plugin to handle the creation of jquery ui dialog popups for inline help
 *
 * NOTE: Be sure to include the following code somewhere on the page you'll be using this on.
 *
 *
 * @author John Mertic {jmertic@sugarcrm.com}
 */

/**
 * smarty_function_sugar_help
 * This is the constructor for the Smarty plugin.
 *
 * @param $params The runtime Smarty key/value arguments
 * @param $smarty The reference to the Smarty object used in this invocation
 */
function smarty_function_sugar_help($params, &$smarty)
{
    $text = str_replace("'","\'",htmlspecialchars($params['text']));
	//append any additional parameters.
	$click  = "return SUGAR.util.showHelpTips(this,'$text'";

	if (count( $params) > 1){

			$click .=",'".$params['myPos']."','".$params['atPos']."'";
	}
    $helpImage = SugarThemeRegistry::current()->getImageURL('helpInline.png');
	$click .= " );" ;
    $alt_tag = $GLOBALS['app_strings']['LBL_ALT_INFO'];
    return <<<EOHTML
<img border="0"
    onclick="$click"
    src="$helpImage"
    alt="$alt_tag"
    class="inlineHelpTip"
    />
EOHTML;
}

?>
