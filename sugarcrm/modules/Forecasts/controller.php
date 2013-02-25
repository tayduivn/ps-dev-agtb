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


class ForecastsController extends SugarController
{
    /**
     * remap listview action to sidecar
     * @var array
     */
    protected $action_remap = array(
        'ListView' => 'sidecar'
    );

    /**
     * Actually remap the action if required.
     *
     */
    protected function remapAction(){
        $this->do_action = strtolower($this->do_action) == 'listview' ? 'ListView' : $this->do_action;
        if(!empty($this->action_remap[$this->do_action])){
            $this->action = $this->action_remap[$this->do_action];
            $this->do_action = $this->action;
        }
    }

    public function action_exportManagerWorksheet() {
        global $current_user;
        $this->view = 'ajax';

        // Load up a seed bean
        $seed = BeanFactory::getBean('ForecastManagerWorksheets');

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $seed->object_name);
        }

        $args = array();
        $args['timeperiod_id'] = isset($_REQUEST['timeperiod_id']) ? $_REQUEST['timeperiod_id'] : TimePeriod::getCurrentId();
        $args['user_id'] = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $current_user->id;
        // don't allow encoding to html for data used in export
        $args['encode_to_html'] = false;

        // base file and class name
        $file = 'include/SugarForecasting/Export/Manager.php';
        $klass = 'SugarForecasting_Export_Manager';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);

        $content = $obj->process();

        $obj->export($content);
    }


    public function action_exportWorksheet() {
        global $current_user;

        // Load up a seed bean
        $seed = BeanFactory::getBean('ForecastWorksheets');

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $seed->object_name);
        }

        $args = array();
        $args['timeperiod_id'] = isset($_REQUEST['timeperiod_id']) ? $_REQUEST['timeperiod_id'] : TimePeriod::getCurrentId();
        $args['user_id'] = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $current_user->id;
        // don't allow encoding to html for data used in export
        $args['encode_to_html'] = false;

        // base file and class name
        $file = 'include/SugarForecasting/Export/Individual.php';
        $klass = 'SugarForecasting_Export_Individual';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);

        $content = $obj->process();

        $obj->export($content);

        // Bug 59329 : Stack 88: CSV is created with some garbage info after the records
        // prevent rendering view
        sugar_cleanup(true);
    }


    /**
     * This function allows a user with Forecasts admin rights to reset the Forecasts settings so that the Forecasts wizard
     * dialog will appear once again.
     *
     */
    public function action_resetSettings() {
        global $current_user;
        if($current_user->isAdminForModule('Forecasts')) {
            $db = DBManagerFactory::getInstance();
            $db->query("UPDATE config SET value = 0 WHERE category = 'Forecasts' and name in ('is_setup', 'has_commits')");
            MetaDataManager::clearAPICache();
            //MetaDataManager::refreshModulesCache(array('Forecasts'));
            header("Location: index.php?module=Forecasts#config");
            exit();
        }

        $this->view = 'noaccess';
    }

}
