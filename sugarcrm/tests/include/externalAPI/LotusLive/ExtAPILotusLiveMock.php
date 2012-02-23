<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * ExtAPILotusLiveMock.php
 *
 * This is a mock object to simulate calls to the ExtAPILotusLive class
 *
 * @author Collin Lee
 *
 */

require_once('include/externalAPI/LotusLive/ExtAPILotusLive.php');

class ExtAPILotusLiveMock extends ExtAPILotusLive
{
    var $sugarOauthMock;

    function __construct()
    {
        parent::__construct();
        $this->api_data = array();
        $this->api_data['subscriberId'] = '';
    }

    /**
     * getErrorStringFromCode
     * This method overrides a protected method
     *
     *
     */
    public function getErrorStringFromCode($error='')
    {
        return parent::getErrorStringFromCode($error);
    }

    /**
     * getClient
     * This method is used to override the getClient method
     *
     * @return mixed The SugarOauth instance
     */
    public function getClient()
    {
        return $this->sugarOauthMock;
    }
}
