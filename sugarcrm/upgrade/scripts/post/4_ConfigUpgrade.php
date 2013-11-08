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
/**
 * Update config entries for CE->PRO
 */
class SugarUpgradeConfigUpgrade extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        // only do it when going from ce to non-ce
        if(!($this->from_flavor == 'ce' && $this->to_flavor != 'ce')) return;

        if(isset($this->upgrader->config['sugarbeet']))
        {
            unset($this->upgrader->config['sugarbeet']);
        }

        if(isset($this->upgrader->config['disable_team_access_check']))
        {
            unset($this->upgrader->config['disable_team_access_check']);
        }

        $defaults = array(
            'mass_actions' => array(
                'mass_update_chunk_size' => 20,
                'mass_delete_chunk_size' => 20,
            ),
            'merge_duplicates' => array(
                'merge_relate_fetch_concurrency' => 2,
                'merge_relate_fetch_timeout' => 90000,
                'merge_relate_fetch_limit' => 20,
                'merge_relate_update_concurrency' => 4,
                'merge_relate_update_timeout' => 90000,
                'merge_relate_max_attempt' => 3,
            ),
            'passwordsetting' => array(
                'minpwdlength' => '',
                'maxpwdlength' => '',
                'oneupper' => '',
                'onelower' => '',
                'onenumber' => '',
                'onespecial' => '',
                'SystemGeneratedPasswordON' => '',
                'generatepasswordtmpl' => '',
                'lostpasswordtmpl' => '',
                'customregex' => '',
                'regexcomment' => '',
                'forgotpasswordON' => false,
                'linkexpiration' => '1',
                'linkexpirationtime' => '30',
                'linkexpirationtype' => '1',
                'userexpiration' => '0',
                'userexpirationtime' => '',
                'userexpirationtype' => '1',
                'userexpirationlogin' => '',
                'systexpiration' => '0',
                'systexpirationtime' => '',
                'systexpirationtype' => '0',
                'systexpirationlogin' => '',
                'lockoutexpiration' => '0',
                'lockoutexpirationtime' => '',
                'lockoutexpirationtype' => '1',
                'lockoutexpirationlogin' => '',
            ),
        );

        foreach ($defaults as $key => $values) {
            if (isset($this->upgrader->config[$key]) && is_array($this->upgrader->config[$key]) && is_array($values)) {
                $this->upgrader->config[$key] = array_merge(
                    $values,
                    $this->upgrader->config[$key]
                );
            } else {
                $this->upgrader->config[$key] = $values;
            }
        }
    }
}
