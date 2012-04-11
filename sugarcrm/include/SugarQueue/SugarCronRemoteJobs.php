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
require_once 'include/SugarQueue/SugarCronJobs.php';
require_once 'include/SugarHttpClient.php';

/**
 * CRON driver for job queue that ships jobs outside
 * @api
 */
class SugarCronRemoteJobs extends SugarCronJobs
{
    /**
     * URL for remote job server
     * @var string
     */
    protected $jobserver;

    /**
     * Just in case we'd ever need to override...
     * @var string
     */
    protected $submitURL = "submitJob";

    /**
     * REST client
     * @var string
     */
    protected $client;

    public function __construct()
    {
        parent::__construct();
        if(!empty($GLOBALS['sugar_config']['job_server'])) {
            $this->jobserver = $GLOBALS['sugar_config']['job_server'];
        }
        $this->setClient(new SugarHttpClient());
    }

    /**
    * Set client to talk to SNIP
    * @param SugarHttpClient $client
    */
    public function setClient(SugarHttpClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Return ID for this client
     * @return string
     */
    public function getMyId()
    {
        return 'CRON'.$GLOBALS['sugar_config']['unique_key'].':'.md5($this->jobserver);
    }

    /**
     * Execute given job
     * @param SchedulersJob $job
     */
    public function executeJob($job)
    {
        $data = http_build_query(array("data" => json_encode(array("job" => $job->id, "client" => $this->getMyId(), "instance" => $GLOBALS['sugar_config']['site_url']))));
        $response = $this->client->callRest($this->jobserver.$this->submitURL, $data);
        if(!empty($response)) {
            $result = json_decode($response, true);
            if(empty($result) || empty($result['ok']) || $result['ok'] != $job->id) {
                $GLOBALS['log']->debug("CRON Remote: Job {$job->id} not accepted by server: $response");
                $this->jobFailed($job);
                $job->failJob("Job not accepted by server: $response");
            }
        } else {
            $GLOBALS['log']->debug("CRON Remote: REST request failed for job {$job->id}");
            $this->jobFailed($job);
            $job->failJob("Could not connect to job server");
        }
    }

}

