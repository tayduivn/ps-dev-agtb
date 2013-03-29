<?php
/************************************
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
require_once('include/Expressions/Expression/Date/DateExpression.php');
/**
 * <b>maxRelatedDate(Relate <i>link</i>, String <i>field</i>)</b><br>
 * Returns the highest value of <i>field</i> in records related by <i>link</i><br/>
 * ex: <i>maxRelatedDate($products, "date_closed_timestamp")</i> in Opportunities would return the <br/>
 * latest date_closed_timestamp of all related Revenue Line Items.
 */

class MaxRelatedDateExpression extends DateExpression
{
    
    /**
     * Returns the opreation name that this Expression should be
     * called by.
     */
    public static function getOperationName() {
        return array("maxRelatedDate");
    }
    
    public function evaluate() {
        $params = $this->getParameters();
        //This should be of relate type, which means an array of SugarBean objects
        $linkField = $params[0]->evaluate();
        $relfield = $params[1]->evaluate();
        $ret = 0;
        $isTimestamp = true;
        
        //if the field or relationship isn't defined, bail
        if (!is_array($linkField) || empty($linkField)) {
           return $ret; 
        }           
                        
        foreach ($linkField as $bean) {
            //set up our timestamp
            $timestamp = $bean->$relfield;
            if (isset($bean->$relfield)) {
                //if it isn't a timestamp, mark the flag as such and convert it for comparison
                if (!is_int($timestamp)) {
                    $isTimestamp = false;
                    $timestamp = strtotime($timestamp);
                }
                
                //compare
                if ( $ret < $timestamp) {
                    $ret = $timestamp;
                }
            }            
        }
        
        //if nothing was done, return an empty string
        if ($ret == 0 && $isTimestamp) {            
            return "";   
        }
        
        //return the timestamp if the field started off that way
        if ($isTimestamp) {
            return $ret;
        } 
        
        //convert the timestamp to a date and return
        $date = new DateTime();
        $date->setTimestamp($ret);
   
        return $date->format("Y-m-d");
    }
    
    //todo: javascript version here
    /**
     * Returns the JS Equivalent of the evaluate function.
     */
    public static function getJSEvaluate() 
    {
        return "";
    }
}
?>
