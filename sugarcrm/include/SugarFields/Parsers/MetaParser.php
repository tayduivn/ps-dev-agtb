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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * MetaParser.php
 *
 * This is a utility base file to parse HTML
 * @author Collin Lee
 * @api
 */
class MetaParser {


function __construct() {

}

/**
 * hasMultiplePanels
 * This is a utility function to determine if a given set of panels as defined in a metadata file contain mutiple panels
 *
 * @param Array $panels Array of panels as defined in a metadata file
 * @return bool Returns true if there are multiple panels defined; false otherwise
 */
function hasMultiplePanels($panels) {

   if(!isset($panels) || empty($panels) || !is_array($panels)) {
   	  return false;
   }

   if(is_array($panels) && (count($panels) == 0 || count($panels) == 1)) {
   	  return false;
   }

   foreach($panels as $panel) {
   	  if(!empty($panel) && !is_array($panel)) {
   	  	 return false;
   	  } else {
   	  	 foreach($panel as $row) {
   	  	    if(!empty($row) && !is_array($row)) {
   	  	       return false;
   	  	    } //if
   	  	 } //foreach
   	  } //if-else
   } //foreach

   return true;
}


/**
 * parseDelimiters
 * This is a utility function that helps to insert Smarty delimiters into a block of code
 *
 * @param string $javascript String contents of javascript
 * @return string Formatted javascript String with Smarty tags applied
 */
function parseDelimiters($javascript) {
    $newJavascript = '';
    $scriptLength = strlen($javascript);
    $count = 0;
    $inSmartyVariable = false;

    while($count < $scriptLength) {

          if($inSmartyVariable) {
             $start = $count;
             $numOfChars = 1;
             while(isset($javascript[$count]) && $javascript[$count] != '}') {
                   $count++;
                   $numOfChars++;
             }

             $newJavascript .= substr($javascript, $start, $numOfChars);
             $inSmartyVariable = false;

          } else {

              $char = $javascript[$count];
              $nextChar = ($count + 1 >= $scriptLength) ? '' : $javascript[$count + 1];

              if($char == "{" && $nextChar == "$") {
                 $inSmartyVariable = true;
                 $newJavascript .= $javascript[$count];
              } else if($char == "{") {
                 $newJavascript .=  " {ldelim} ";
              } else if($char == "}") {
                 $newJavascript .= " {rdelim} ";
              } else {
                 $newJavascript .= $javascript[$count];
              }
          }
          $count++;
    } //while

    return $newJavascript;
}

}
