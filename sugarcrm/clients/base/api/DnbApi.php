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
// A simple example class
class DnbApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'dnbdirectapi' => array(
                'reqType' => 'GET',
                'path' => array('connector','dnb','?','?'),
                'pathVars' => array('connector','dnb','qtype','qparam'),
                'method' => 'dnbdirectapi',
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

        return $dnbEAPM;
    }

    /**
     * Gets Company Search results based on keyword
     * @param $api
     * @param $args
     * @return mixed
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function dnbdirectapi($api,$args)
    {
        $args2params = array(
            'qtype' => 'qtype',
            'qparam' => 'qparam'
        );

        $params = array();
        foreach ($args2params as $argKey => $paramKey) {
            if (isset($args[$argKey])) {
                $params[] = $args[$argKey];
            }
        }

        //invoke dnb api based on query type and query parameter
        $api = $this->getEAPM();
        $queryType = $args['qtype'];
        $queryParam = $args['qparam'];

        $result = '';

        if($queryType === 'search')
        {
            $result = $api->dnbsearch($queryParam);         
        }
        else if($queryType === 'dupcheck')
        {
            // API to test sugar query
            $result = $api->dupcheck($queryParam);
        }
        else if ($queryType === 'profile') {
            $result = $api->dnbProfile($queryParam);
        } else if($queryType==='competitors') {
            $result = $api->dnbCompetitors($queryParam);
        }  else if($queryType==='industry') {
            $result = $api->dnbIndustryInfo($queryParam);
        } else if($queryType==='financial') {
            $result = $api->dnbFinancialInfo($queryParam);
        } else if($queryType==='familytree') {
            $result = $api->dnbFamilyTree($queryParam);
        } else if($queryType==='firmographic') {
            $result = $api->dnbStandardProfile($queryParam);
        } else if($queryType==='premfirmographic') {
            $result = $api->dnbPremiumProfile($queryParam);
        } else if($queryType==='findIndustry') {
            $result = $api->dnbIndustrySearch($queryParam);
        }
        else if($queryType === 'findContacts') {
            $result = $api->dnbFindContacts($queryParam);
        }
        else if($queryType === 'refreshcheck') {
            $result = $api->dnbRefreshCheck($queryParam);
        } else if($queryType === 'litefirmographic') {
            $result = $api->dnbLiteProfile($queryParam);
        }
        else if($queryType === 'news')
        {
            $result = $api->dnbNews($queryParam);
        }

        if (isset($result['errors'])) {
            $errorString = '';
            foreach($result['errors'] as $errorKey => $error) {
                $errorString .= $error['code'].str_replace(' ', '_', $error['message']);
            }
            throw new SugarApiExceptionRequestMethodFailure('errors_from_dnb: '.$errorString, $args);
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
        $args2params = array(
            'qtype' => 'qtype',
            'qdata' => 'qdata'
        );

        $params = array();
        foreach ($args2params as $argKey => $paramKey) {
            if (isset($args[$argKey])) {
                $params[] = $args[$argKey];
            }
        }

        //invoke dnb api based on query type and query data
        $api = $this->getEAPM();
        $queryType = $args['qtype'];
        $queryData = $args['qdata']; //data posted 

        $result = '';

        if($queryType === 'cmRequest')
        {
            $result = $api->dnbCMRrequest($queryData);         
        }
        else if($queryType === 'bal')
        {
            $result = $api->dnbBALRequest($queryData);
        }
        else if($queryType === 'contacts')
        {
            $result = $api->dnbContactDetails($queryData);
        }
        else if($queryType === 'indMap')
        {
            $result = $api->dnbIndustryConversion($queryData);   
        }
        else if($queryType==='industry') 
        {
            $result = $api->dnbIndustryInfoPost($queryData);
        }
        

        if (isset($result['errors'])) {
            $errorString = '';
            foreach($result['errors'] as $errorKey => $error) {
                $errorString .= $error['code'].str_replace(' ', '_', $error['message']);
            }
            throw new SugarApiExceptionRequestMethodFailure('errors_from_dnb: '.$errorString, $args);
        }
        return $result;
    }
}
