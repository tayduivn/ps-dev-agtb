<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

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
            MetaDataManager::refreshModulesCache(array('Forecasts'));
            MetaDataManager::refreshSectionCache(array(MetaDataManager::MM_CONFIG));
            echo '<script>' . navigateToSidecar(buildSidecarRoute("Forecasts")) . ';</script>';
            exit();
        }

        $this->view = 'noaccess';
    }

}
