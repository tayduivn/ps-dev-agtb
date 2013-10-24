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

r50375 - 2009-08-24 18:07:43 -0700 (Mon, 24 Aug 2009) - dwong - branch kobe2 from tokyo r50372

r42807 - 2008-12-29 11:16:59 -0800 (Mon, 29 Dec 2008) - dwong - Branch from trunk/sugarcrm r42806 to branches/tokyo/sugarcrm

r22745 - 2007-05-11 18:35:10 -0700 (Fri, 11 May 2007) - majed - fixes compiling issue

r22725 - 2007-05-11 16:37:35 -0700 (Fri, 11 May 2007) - clee - Added file.


*/


/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sugar_evalcolumn_old} function plugin
 *
 * Type:     function<br>
 * Name:     sugar_evalcolumn_old<br>
 * Purpose:  evaluate a string by substituting values in the rowData parameter. Used for ListViews<br>
 * 
 * @author Wayne Pan {wayne at sugarcrm.com
 * @param array
 * @param Smarty
 */
function smarty_function_sugar_evalcolumn_old($params, &$smarty)
{
    if (!isset($params['var']) || !isset($params['rowData'])) {
        if(!isset($params['var']))  
            $smarty->trigger_error("evalcolumn: missing 'var' parameter");
        if(!isset($params['rowData']))  
            $smarty->trigger_error("evalcolumn: missing 'rowData' parameter");
        return;
    }

    if($params['var'] == '') {
        return;
    }

    if(is_array($params['var'])) {
        foreach($params['var'] as $key => $value) {
            $params['var'][$key] = searchReplace($value, $params['rowData']);
        }
    }
    else {
        $params['var'] = searchReplace($params['var'], $params['rowData']);
    }

    if(isset($params['toJSON'])) {
        $json = getJSONobj();
        $params['var'] = $json->encode($params['var']);
    }
    
    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $params['var']);
    } else {
        return $params['var'];
    }
}

function searchReplace($value, &$rowData) {
    preg_match_all('/\{\$(.*)\}/U', $value, $matches);

    for($wp = 0; $wp < count($matches[0]); $wp++) {
        if(isset($rowData[$matches[1][$wp]])) 
            $value = str_replace($matches[0][$wp], $rowData[$matches[1][$wp]], $value);
        else 
            $value = str_replace($matches[0][$wp], '', $value);
    }
    return $value;
}

?>
