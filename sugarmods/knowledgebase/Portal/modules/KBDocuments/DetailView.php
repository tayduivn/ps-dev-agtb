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
/*********************************************************************************
 * $Id: DetailView.php,v 1.74 2006/06/06 17:57:56 majed Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/DetailView/DetailViewPortal.php');
require_once('include/SubPanel/SubpanelPortal.php');
require_once('include/SubPanel/SubpanelCreatePortal.php');
require_once('include/EditView/EditViewPortal.php');

global $app_list_strings;

$module = 'KBDocuments';
$id = $_REQUEST['id'];

$dvp = new DetailViewPortal($module, $id);
$dvp->editable = false;

//Get the content body and parse for img tags
$contentBody = html_entity_decode(str_replace("&nbsp;", " ", $dvp->result['fields']['description']['value']));
preg_match_all("'<img.*?src=[\'\"](.*?)[\'\"].*?>'si", $contentBody, $matches);

$basename = '/'.basename($GLOBALS['sugar_config']['parent_site_url']).'/';
$basename_length = strlen($basename);

$replace = array();
foreach($matches[1] as $img) {
        if(strpos($img, $basename) !== false) {
           //remove basename
           $img = substr($img, strlen($basename));	
        } 
        //remove leading "/" if found
        $replace[] = $img[0] == '/' ? substr($img, 1) : $img;
}

Portal::getImages($matches[1]);
$contentBody = str_replace($matches[1], $replace, $contentBody);
//_pp(htmlentities($contentBody));
$dvp->result['fields']['description']['value'] = $contentBody;
$dvp->process();
echo $dvp->display();

// Attachments Subpanel
$attachmentFields = array('id', 'created_by', 'date_entered', 'file_ext', 'filename');
$spp = new SubpanelPortal('DocumentRevisions','KBDocuments', $id, $attachmentFields, 'include/ListView/ListViewGeneric.tpl');
$spp->setup('DocumentRevisions', 'include/ListView/ListViewGeneric.tpl', '', array());
echo $spp->display($mod_strings['LBL_KBDOC_ATTS_TITLE']);

?>