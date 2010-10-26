<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional Subscription
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
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
function getFocusBeanFeeds() {
    global $db;
    $query = "select * from SugarFeed where related_id='".$_REQUEST['record']."'";

    $results = $db->query($query);
    
    $resultArray = array();
    $feedIds = array();
    while( $row = $db->fetchByAssoc($results))
    {
        $id = $row['id'];
        $resultArray[$id] = $row;
        $feedIds[] = "'".$row['id']."'";
    }
    $query = "select * from SugarFeed where related_id in (";
    $query .= implode(",", $feedIds);
    $query .= ")";
    $results = $db->query($query);
    while( $row = $db->fetchByAssoc($results))
    {
        $parentFeedId = $row['related_id'];
        $resultArray[$parentFeedId]['replies'] = $row;
    }
    return $resultArray;
}

global $app_strings;
global $currentModule;
$feeds = getFocusBeanFeeds();

$data = "<html><head><link rel='stylesheet' type='text/css' href='cache/themes/Sugar/css/style.css'></head> <body style='margin:10px'><div class='moduleTitle'><h2>";
$data .= "Feeds for: ". $_REQUEST['name'];
$data .= "</h2></div><table width='100%' cellpadding='0' class='list view' cellspacing='0'><tr><td>";
$rowNum = 0;
foreach ($feeds as $key=>$val) {
    if ($rowNum % 2 == 0)
        $rowColor = 'evenListRowS1';
    else {
        $rowColor = 'oddListRowS1';
    }
    $data .= "<tr class=".$rowColor."><td>".$val['name'];
    
    $GLOBALS['current_dc_sugarfeed'] = 'Administrator';
    $data = preg_replace_callback('/\{([^\}]+)\.([^\}]+)\}/', create_function(
    '$matches',
    'if($matches[1] == "this"){$var = $matches[2]; return $GLOBALS[\'current_dc_sugarfeed\'];}else{return translate($matches[2], $matches[1]);}'
    ),$data);
    $data = preg_replace('/\[(\w+)\:([\w\-\d]*)\:([^\]]*)\]/', '<a href="index.php?module=$1&action=DetailView&record=$2"><img src="themes/default/images/$1.gif" border=0>$3</a>', $data);
    $data = html_entity_decode($data);
    if (!empty($val['replies'])) {
        $data .= "<br/><br/><div style='margin-left:40px'>".$val['replies']['name']."</div>";
        $data .="</td></tr>";
    }
    $data = preg_replace_callback('/\{([^\}]+)\.([^\}]+)\}/', create_function(
    '$matches',
    'if($matches[1] == "this"){$var = $matches[2]; return $GLOBALS[\'current_dc_sugarfeed\'];}else{return translate($matches[2], $matches[1]);}'
    ),$data);

    $data = preg_replace('/\[(\w+)\:([\w\-\d]*)\:([^\]]*)\]/', '<a href="index.php?module=$1&action=DetailView&record=$2"><img src="themes/default/images/$1.gif" border=0>$3</a>', $data);
  	$data = html_entity_decode($data);
  $rowNum++;

}
$data .= '</table>';
echo $data;




?>
