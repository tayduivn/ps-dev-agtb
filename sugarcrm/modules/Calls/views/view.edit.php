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

require_once('include/json_config.php');

class CallsViewEdit extends ViewEdit
{
    /**
     * @const MAX_REPEAT_INTERVAL Max repeat interval.
     */
    const MAX_REPEAT_INTERVAL = 30;
    
 	/**
 	 * @see SugarView::preDisplay()
 	 */
 	public function preDisplay()
 	{
 		if($_REQUEST['module'] != 'Calls' && isset($_REQUEST['status']) && empty($_REQUEST['status'])) {
	       $this->bean->status = '';
 		} //if
        if(!empty($_REQUEST['status']) && ($_REQUEST['status'] == 'Held')) {
	       $this->bean->status = 'Held';
 		}
 		parent::preDisplay();
 	}

 	/**
 	 * @see SugarView::display()
 	 */
 	public function display()
 	{
 		global $json;
        $json = getJSONobj();
        $json_config = new json_config();
		if (isset($this->bean->json_id) && !empty ($this->bean->json_id)) {
			$javascript = $json_config->get_static_json_server(false, true, 'Calls', $this->bean->json_id);

		} else {
			$this->bean->json_id = $this->bean->id;
			$javascript = $json_config->get_static_json_server(false, true, 'Calls', $this->bean->id);

		}
 		$this->ss->assign('JSON_CONFIG_JAVASCRIPT', $javascript);

 		if($this->ev->isDuplicate){
	        $this->bean->status = $this->bean->getDefaultStatus();
 		} //if
 		
        $this->ss->assign('APPLIST', $GLOBALS['app_list_strings']);
        
        $repeatIntervals = array();
        for ($i = 1; $i <= self::MAX_REPEAT_INTERVAL; $i++) {
            $repeatIntervals[$i] = $i;
        }
        $this->ss->assign("repeat_intervals", $repeatIntervals);

        $fdow = $GLOBALS['current_user']->get_first_day_of_week();
        $dow = array();
        for ($i = $fdow; $i < $fdow + 7; $i++){
            $dayIndex = $i % 7;
            $dow[] = array("index" => $dayIndex , "label" => $GLOBALS['app_list_strings']['dom_cal_day_short'][$dayIndex + 1]);
        }
        $this->ss->assign('dow', $dow);
        $this->ss->assign('repeatData', json_encode($this->view_object_map['repeatData']));
 		
 		parent::display();
 	}
}
