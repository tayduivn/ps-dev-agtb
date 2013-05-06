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


require_once('include/SugarQueue/jobs/SugarJobMassUpdate.php');
require_once('include/api/SugarApi.php');

/*
 * Mass Update API implementation
 */
class MassUpdateApi extends SugarApi {

    /**
     * This function registers the mass update Rest api
     */
    public function registerApiRest() {
        return array(
            'massUpdatePut' => array(
                'reqType' => 'PUT',
                'path' => array('<module>','MassUpdate'),
                'pathVars' => array('module',''),
                'jsonParams' => array('filter'),
                'method' => 'massUpdate',
                'shortHelp' => 'An API to handle mass update.',
                'longHelp' => 'include/api/help/module_massupdate_put_help.html',
            ),
            'massUpdateDelete' => array(
                'reqType' => 'DELETE',
                'path' => array('<module>','MassUpdate'),
                'pathVars' => array('module',''),
                'jsonParams' => array('filter'),
                'method' => 'massDelete',
                'shortHelp' => 'An API to handle mass delete.',
                'longHelp' => 'include/api/help/module_massupdate_delete_help.html',
            ),
        );
    }

    /**
     * The max number of mass update records will be processed synchronously.
     */
    const MAX_MASS_UPDATE = 1;

    /**
     * @var bool to indicate whether this is a request to delete records
     */
    protected $delete = false;

    /**
     * @var string job id
     */
    protected $jobId = null;

    /**
     * To perform mass delete
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return String
     */
    public function massDelete($api, $args)
    {
        $this->requireArgs($args, array('massupdate_params', 'module'));
        $this->delete = true;
        $args['massupdate_params']['Delete'] = true;
        
        // SC-1021: add 'creation date' filter if 'delete all'
        if (!empty($args['massupdate_params']['entire'])) { 
            unset($args['massupdate_params']['uid']);

            if (empty($args['massupdate_params']['filter'])) {
                $args['massupdate_params']['filter'] = array();
            }

            $args['massupdate_params']['filter'][] = array('date_entered' => array('$lt' => TimeDate::getInstance()->nowDb()));
        }

        return $this->massUpdate($api, $args);
    }

    /**
     * To perform massupdate, either update or delete, based on the args parameter
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return String
     */
    public function massUpdate($api, $args)
    {
        $this->requireArgs($args, array('massupdate_params', 'module'));

        $mu_params = $args['massupdate_params'];
        $mu_params['module'] = $args['module'];

        // should have either uid or entire specified
        if (empty($mu_params['uid']) && empty($mu_params['entire']))
        {
            throw new SugarApiExceptionMissingParameter("You must mass update at least one record");
        }

        // special handling for Sync due to front end and back end inconsistency
        if ($mu_params['module']=='Contacts' && isset($mu_params['sync_contact'])) {
            $mu_params['Sync'] = $mu_params['sync_contact'];
            unset($mu_params['sync_contact']);
        }

        if (isset($mu_params['entire']) && empty($mu_params['entire'])) {
            unset($mu_params['entire']);
        }

        if(isset($mu_params['team_name'])) {
            if(isset($mu_params['team_name_type']) && $mu_params['team_name_type'] == "1") {
                $mu_params['team_name_type'] = "add";
            } else {
                $mu_params['team_name_type'] = "replace";
            }
        }

        // check ACL
        $bean = BeanFactory::newBean($mu_params['module']);
        if (!$bean instanceof SugarBean) {
            throw new SugarApiExceptionInvalidParameter("Invalid bean, is module valid?");
        }
        $action = $this->delete? 'delete': 'save';
        if (!$bean->ACLAccess($action))
        {
            throw new SugarApiExceptionNotAuthorized('No access to mass update records for module: '.$mu_params['module']);
        }

        // convert params to the format expected by downstream classes
        $uidCount = isset($mu_params['uid']) ? count($mu_params['uid']) : 0;
        $this->convertParams($mu_params);

        global $sugar_config;
        $asyncThreshold = isset($sugar_config['max_mass_update']) ? $sugar_config['max_mass_update'] : self::MAX_MASS_UPDATE;
        if (!empty($mu_params['entire']) || ($uidCount>$asyncThreshold))
        {
            // create a job queue consumer for this
            $massUpdateJob = new SugarJobMassUpdate();
            $this->jobId = $massUpdateJob->createJobQueueConsumer($mu_params);

            return array('status'=>'queued', 'jobId'=>$this->jobId);
        }

        SugarJobMassUpdate::preProcess($mu_params);

        require_once("include/MassUpdate.php");
        $mass = new MassUpdate();
        $mass->setSugarBean($bean);

        // action
        $mass->handleMassUpdate(false, true);

        return array('status'=>'done');
    }

    /**
     * This function returns job id.
     * @return String job id
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * This function converts massupdate params to the format expected by downstream classes.
     * @param $mu_params reference to massupdate parameters
     */
    protected function convertParams(&$mu_params)
    {
        if (is_array($mu_params)) {
            $this->convertUID($mu_params);
            $this->convertTeamArray($mu_params);
            $this->convertProspectListsArray($mu_params);
        }
    }

    /**
     * This function converts uid to the format expected by downstream classes.
     * @param $mu_params reference to massupdate parameters
     */
    protected function convertUID(&$mu_params)
    {
        if (!empty($mu_params['uid'])) {
            $mu_params['uid'] = implode(',', $mu_params['uid']);
        } else {
            //downstream classes do not deal well with empty values for uid
            unset($mu_params['uid']);
        }
    }

    /**
     * This function converts team_name to the format expected by downstream classes.
     * @param $mu_params reference to massupdate parameters
     */
    protected function convertTeamArray(&$mu_params)
    {
        if (!empty($mu_params['team_name']) && is_array($mu_params['team_name']))
        {
            foreach ($mu_params['team_name'] as $idx=>$team)
            {
                if (is_array($team) && isset($team['id'])) {
                    $mu_params['team_name_collection_'.$idx] = $team['id'];
                    $mu_params['id_team_name_collection_'.$idx] = $team['id'];
                    if (!empty($team['primary'])) {
                        $mu_params['primary_team_name_collection'] = $idx;
                    }
                }
            }
        }
    }

    /**
     * This function converts team_name to the format expected by downstream classes.
     * @param $mu_params reference to massupdate parameters
     */
    protected function convertProspectListsArray(&$mu_params)
    {
        if (!empty($mu_params['prospect_lists'])) {
            $prospect_lists = implode(',', $mu_params['prospect_lists']);
            if ($this->delete) {
                $mu_params['remove_from_prospect_lists'] = $prospect_lists;
            } else {
                $mu_params['add_to_prospect_lists'] = $prospect_lists;
            }
            unset($mu_params['prospect_lists']);
        }
    }
}
