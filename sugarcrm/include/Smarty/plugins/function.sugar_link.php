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


function smarty_function_sugar_link($params, &$smarty)
{
	if(empty($params['module'])){
		$smarty->trigger_error("sugar_link: missing 'module' parameter");
		return;
	}
	if(!empty($params['data']) && is_array($params['data'])){
		$link_url = 'index.php?';
		$link_url .= 'module=iFrames&action=index';
		$link_url .= '&record='.$params['data']['0'];
		$link_url .= '&tab=true';
    }else{
		$action = (!empty($params['action']))?$params['action']:'index';
	    
	    $link_url = 'index.php?';
	    $link_url .= 'module='.$params['module'].'&action='.$action;
	
	    if (!empty($params['record'])) { $link_url .= "&record=".$params['record']; }
	    if (!empty($params['extraparams'])) { $link_url .= '&'.$params['extraparams']; }
	}
	
	if (isset($params['link_only']) && $params['link_only'] == 1 ) {
        // Let them just get the url, they want to put it someplace
        return $link_url;
    }
	
	$id = (!empty($params['id']))?' id="'.$params['id'].'"':'';
	$class = (!empty($params['class']))?' class="'.$params['class'].'"':'';
	$style = (!empty($params['style']))?' style="'.$params['style'].'"':'';
	$title = (!empty($params['title']))?' title="'.$params['title'].'"':'';
	$accesskey = (!empty($params['accesskey']))?' accesskey="'.$params['accesskey'].'" ':'';
    $options = (!empty($params['options']))?' '.$params['options'].'':'';
    if(!empty($params['data']) && is_array($params['data']))
		$label =$params['data']['4'];
	elseif ( !empty($params['label']) )
	    $label = $params['label'];
	else
	    $label = (!empty($GLOBALS['app_list_strings']['moduleList'][$params['module']]))?$GLOBALS['app_list_strings']['moduleList'][$params['module']]:$params['module'];

    $link = '<a href="'.$link_url.'"'.$id.$class.$style.$options.'>'.$label.'</a>';
    return $link;
}