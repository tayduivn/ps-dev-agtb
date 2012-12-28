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
            'massUpdate' => array(
                'reqType' => 'PUT',
                'path' => array('MassUpdate'),
                'pathVars' => array(''),
                'jsonParams' => array('filter'),
                'method' => 'massUpdate',
                'shortHelp' => 'An API to handle mass update.',
                'longHelp' => 'include/api/help/massUpdate.html',
            ),
        );
    }

    /**
     * The max number of mass update records will be processed synchronously.
     */
    const MAX_MASS_UPDATE = 100;

    /**
     * @var bool to indicate whether this is a request to delete records
     */
    protected $delete = false;

    /**
     * @var string job id
     */
    protected $jobId = null;

    /**
     * To perform massupdate, either update or delete, based on the args parameter
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return String
     */
    public function massUpdate($api, $args)
    {
        $this->requireArgs($args, array('massupdate_params'));
        $this->requireArgs($args['massupdate_params'], array('module'));

        $mu_params = $args['massupdate_params'];

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

        if (!empty($mu_params['delete'])) {
            // mass delete
            $this->delete = true;
            $mu_params['Delete'] = $mu_params['delete'] = true;
        }
        if (isset($mu_params['entire']) && empty($mu_params['entire'])) {
            unset($mu_params['entire']);
        }

        // check ACL
        $bean = BeanFactory::newBean($mu_params['module']);
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
}
