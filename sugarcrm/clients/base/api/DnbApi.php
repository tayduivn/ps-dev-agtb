<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
require_once('include/externalAPI/Dnb/ExtAPIDnb.php');

class DnbApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'dnbDirectGet' => array(
                'reqType' => 'GET',
                'path' => array('connector','dnb','?','?'),
                'pathVars' => array('connector','dnb','qtype','qparam'),
                'method' => 'dnbDirectGet',
                'shortHelp' => 'Invoke DNB API using GET',
                'noLoginRequired' => true,
                'longHelp' => 'include/api/help/dnb_get_help.html',
            ),
            'dnbDirectPost' => array(
                'reqType' => 'POST',
                'path' => array('connector','dnb','?'),
                'pathVars' => array('connector','dnb','qtype'),
                'method' => 'dnbDirectPost',
                'shortHelp' => 'Invoke DNB API using POST',
                'noLoginRequired' => true,
                'longHelp' => 'include/api/help/dnb_post_help.html',
            ),
        );
    }

    /**
     * gets dnb EAPM
     * @return array|bool|ExternalAPIBase
     */
    public function getEAPM()
    {
        $dnbEAPM = ExternalAPIFactory::loadAPI('Dnb',true);
        $dnbEAPM->getConnector();
        if (!$dnbEAPM->getConnectorParam('dnb_username') ||
            !$dnbEAPM->getConnectorParam('dnb_password') ||
            !$dnbEAPM->getConnectorParam('dnb_env')){
            return array('error' =>'ERROR_DNB_CONFIG');
        }
        return $dnbEAPM;
    }

    /**
     * Invokes D&B API using GET
     * @param $api
     * @param $args
     * @return mixed
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function dnbDirectGet($api,$args)
    {
        //invoke dnb api based on query type and query parameter
        $extDnbApi = $this->getEAPM();
        if (is_array($extDnbApi) && isset($extDnbApi['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $extDnbApi['error']);
        }
        if ($extDnbApi === false) {
            throw new SugarApiExceptionRequestMethodFailure($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], $args);
        }
        $queryType = $args['qtype'];
        $queryParam = $args['qparam'];
        if (!$extDnbApi->isConnectorConfigured()) {
            return array('error' =>'ERROR_DNB_CONFIG');
        }
        $result = '';
        if ($queryType === 'search'){
            $result = $extDnbApi->dnbSearch($queryParam);
        } else if ($queryType === 'profile') {
            $result = $extDnbApi->dnbProfile($queryParam);
        } else if($queryType==='competitors') {
            $result = $extDnbApi->dnbCompetitors($queryParam);
        }  else if($queryType==='industry') {
            $result = $extDnbApi->dnbIndustryInfo($queryParam);
        } else if($queryType==='financial') {
            $result = $extDnbApi->dnbFinancialInfo($queryParam);
        } else if($queryType==='familytree') {
            $result = $extDnbApi->dnbFamilyTree($queryParam);
        } else if($queryType==='firmographic') {
            $result = $extDnbApi->dnbStandardProfile($queryParam);
        } else if($queryType==='premfirmographic') {
            $result = $extDnbApi->dnbPremiumProfile($queryParam);
        } else if($queryType==='findIndustry') {
            $result = $extDnbApi->dnbIndustrySearch($queryParam);
        } else if($queryType === 'findContacts') {
            $result = $extDnbApi->dnbFindContacts($queryParam);
        } else if($queryType === 'refreshcheck') {
            $result = $extDnbApi->dnbRefreshCheck($queryParam);
        } else if($queryType === 'litefirmographic') {
            $result = $extDnbApi->dnbLiteProfile($queryParam);
        } else if($queryType === 'news') {
            $result = $extDnbApi->dnbNews($queryParam);
        }
        if (is_array($result) && isset($result['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $result['error']);
        }
        return $result;
    }

    /**
     * Invokes DNB Api using POST calls
     * @param $api
     * @param $args
     * @return mixed
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function dnbDirectPost($api,$args)
    {
        //invoke dnb api based on query type and query data
        $extDnbApi = $this->getEAPM();
        if (is_array($extDnbApi) && isset($extDnbApi['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $extDnbApi['error']);
        }
        $queryType = $args['qtype'];
        $queryData = $args['qdata']; //data posted 
        $result = '';
        if ($queryType === 'cmRequest') {
            $result = $extDnbApi->dnbCMRrequest($queryData);         
        } else if($queryType === 'bal') {
            $result = $extDnbApi->dnbBALRequest($queryData);
        } else if($queryType === 'contacts') {
            $result = $extDnbApi->dnbContactDetails($queryData);
        } else if($queryType === 'indMap') {
            $result = $extDnbApi->dnbIndustryConversion($queryData);   
        } else if($queryType==='industry') {
            $result = $extDnbApi->dnbIndustryInfoPost($queryData);
        } else if($queryType==='firmographic') {
            $result = $extDnbApi->dnbFirmographic($queryData);
        }
        if (is_array($result) && isset($result['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $result['error']);
        }
        return $result;
    }
}
