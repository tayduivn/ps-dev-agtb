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
 * $Id: ListView.php,v 1.71 2006/06/06 17:57:56 majed Exp $
 * Description: 
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/Sugar_Smarty.php');
require_once('install/install_utils.php');
global $portal, $app_strings;
global $app_string;

// Check cache?
$filename = 'cache/modules/FAQ/faq.html';

if(file_exists($filename)) {
   $exp_milliseconds = isset($GLOBALS['sugar_config']['faq_cache_time']) ? $GLOBALS['sugar_config']['faq_cache_time'] : 86400000;
   if((time() - filectime($filename)) < $exp_milliseconds) {
	  echo file_get_contents($filename);
	  return;	
   }
   $GLOBALS['log']->info("Rebuilding cache/modules/FAQ/faq.html file");
}

ob_start();
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_TITLE'], false) . '</br>'; 

$tags = $portal->getChildTags('FAQs');
$tree = array();
$docs_found = $portal->getTagDocs('FAQs');
if(!empty($docs_found) && count($docs_found) > 0) {
   $tree['FAQs'] = $docs_found;
}

foreach($tags as $tag) {
   unset($subdocs);
   $subdocs = array();   
   $docs_found = $portal->getTagDocs($tag['id']);
   foreach($docs_found as $d) {
   	  	   $subdocs[$d['doc_id']] = $d;
   }   
   recursiveFind(array($tag), $subdocs, $portal);
   if(count($subdocs) > 0) {
      $tree[$tag['name']] = $subdocs;
   }
}

// If there are no entries, just stop
if(count($tree) == 0) {
   echo $mod_strings['LBL_FAQ_EMPTY'];
   echo '<br><img src="include/images/blank.gif" height="250">';
   return;	
}

$document_contents = array();
$document_attachments = array();
$basename = '/'.basename($GLOBALS['sugar_config']['parent_site_url']).'/';
$attachmentFields = array('id', 'created_by', 'date_entered', 'file_ext', 'filename');
foreach($tree as $node) {
   foreach($node as $document) {
   	   $id = $document['doc_id'];
   	   
   	   if(empty($document_attachments[$id])) {
	   	   $attachments = $portal->getRelated('KBDocuments', 'DocumentRevisions', $id, $attachmentFields);
	   	   if($attachments && isset($attachments['entry_list']) && count($attachments['entry_list']) > 0) {
	          $document_attachments[$id] = $attachments['entry_list'];
	          //_pp($attachments['entry_list']);
	   	   }
   	   }
   	   
   	   if(empty($document_contents[$id])) {
   	      $contentBody = $portal->getKBDocumentBody($id);
   	      $contentBody = html_entity_decode(str_replace("&nbsp;", " ", $contentBody));
   	      preg_match_all("'<img.*?src=[\'\"](.*?)[\'\"].*?>'si", $contentBody, $matches);
          Portal::getImages($matches[1]);
          $replace = array();
          foreach($matches[1] as $img) {
		        if(substr($img, 0, strlen($basename)) == $basename) {
		           //remove basename
		           $img = substr($img, strlen($basename));	
		        } 
		        //remove leading "/" if found
		        $replace[] = $img[0] == '/' ? substr($img, 1) : $img; 
          } //foreach
          $contentBody = str_replace($matches[1], $replace, $contentBody);
          $document_contents[$id] = $contentBody;
   	   } //if
   }
}

global $theme;
$smarty = new Sugar_Smarty();
$smarty->assign('mod_strings', $mod_strings);
$smarty->assign('tree', $tree);
$smarty->assign('document_contents', $document_contents);
$smarty->assign('document_attachments', $document_attachments);
$smarty->assign('theme', $theme);
$smarty->display('modules/FAQ/TreeView.tpl');

$fileContents = ob_get_contents();

if(!file_exists('cache/modules/FAQ')) {
   mkdir('cache/modules/FAQ');
   make_writable('cache/modules/FAQ');
}

$handle = fopen($filename, 'w');
if($handle) { 
   fwrite($handle, $fileContents);
   fclose($handle);
}

ob_end_flush();


/**
 * recursiveFind
 * This is a function to recursively scan a tag for its subtags
 * and documents.  Any document(s) found are added to the the
 * initial subtag array.
 */
function recursiveFind($subtags, &$tagdocs, &$portal) {

   foreach($subtags as $st) {
   	       $x = $portal->getChildTags($st['id']);
   	       
   	       foreach($x as $childTag) {
   	          $addDocs = $portal->getTagDocs($childTag['id']);
   	          foreach($addDocs as $d) {
   	          	  if(empty($tagdocs[$d['doc_id']])) {
   	          	  	 $tagdocs[$d['doc_id']] = $d;
   	          	  }
   	          }

   	          recursiveFind(array($childTag), $tagdocs, $portal);
   	       }
   }
}

?>
