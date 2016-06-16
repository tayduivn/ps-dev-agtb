<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('include/MVC/View/SugarView.php');

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use Sugarcrm\Sugarcrm\Security\InputValidation\Request;

class CalendarViewGetGR extends SugarView {

    /**
     * @deprecated Use __construct() instead
     */
    public function CalendarViewGetGR($bean = null, $view_object_map = array(), Request $request = null)
    {
        self::__construct($bean, $view_object_map, $request);
    }

    public function __construct($bean = null, $view_object_map = array(), Request $request = null)
    {
        parent::__construct($bean, $view_object_map, $request);
    }

	function process(){
		$this->display();
	}
	
	function display(){
		error_reporting(0);
		require_once('include/json_config.php');
		global $json;
        	$json = getJSONobj();
        	$json_config = new json_config();
            $module = InputValidation::getService()->getValidInputRequest('type', 'Assert\Mvc\ModuleName', '');
            $record = InputValidation::getService()->getValidInputRequest('record', 'Assert\Guid', '');
            $GRjavascript = $json_config->getFocusData($module, $record);
        	ob_clean();
        	echo $GRjavascript;
	}	

}
