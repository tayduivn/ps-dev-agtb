<?php
/********************************************************************************
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
require_once "modules/Products/Product.php";
require_once "modules/Opportunities/Opportunity.php";
class StatusAutoAdjustHook
{
    /**
     * @var $db Database Object
     */
    protected $db;
    
    /**
     * Constructor
     */
    public function StatusAutoAdjustHook()
    {
        $this->db = DBManagerFactory::getInstance();
    }
    
    /**
     * logic hook endpoint to process a bean on save
     * @param  SugarBean $bean
     * @param  string    $event
     * @param  array     $args
     */
     public function adjustStatus(SugarBean $bean, $event, $args)
    {
        $process = false;
        $changedArray = $this->db->getDataChanges($bean, array("for" => "activity"));
        
        //BEGIN SUGARCRM flav=ent ONLY
        if ($bean->module_name == "Products") {
            $process = true;
        }
        //END SUGARCRM flav=ent ONLY
        
        //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
        if ($bean->module_name == "Opportunities") {
            $process = true;
        }
        //END SUGARCRM flav=pro && flav!=ent ONLY
        
        //If we are on a module and flavor to process
        if ($process) {
            if (empty($bean->id) || $bean->new_with_id) {
                //If this is a new bean, default it to STATUS_NEW
                $bean->sales_status = Opportunity::STATUS_NEW; 
            } else if (count($changedArray) && !array_key_exists("sales_status", $changedArray)) {
                //If the bean is not new, but we have changes and one of those changed fields isn't sales_stage:
                
                //Set the bean to in progress, only if it isn't a quoted status
                if ($bean->sales_status != Product::STATUS_CONVERTED_TO_QUOTE) {
                    $bean->sales_status = Opportunity::STATUS_IN_PROGRESS;
                }
                
                //If the bean has been quoted, set the status accordingly
                if ((isset($bean->quote_id) && !empty($bean->quote_id)) && (empty($bean->fetched_row) || empty($bean->fetched_row["quote_id"]))) {
                    $bean->sales_status = Product::STATUS_CONVERTED_TO_QUOTE;
                }
                
                //If the bean has actually been closed in some fashion, reset the status to the closed stage
                if(($bean->sales_stage == Opportunity::STAGE_CLOSED_WON) || $bean->sales_stage == Opportunity::STAGE_CLOSED_LOST) {
                    $bean->sales_status = $bean->sales_stage;
                }
            }
        }
    }
}
