<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=pro ONLY
require_once('include/externalAPI/Base/OAuthPluginBase.php');
require_once('include/externalAPI/Base/WebFeed.php');
require_once 'include/SugarQuery/SugarQuery.php';
require_once('include/SugarCache/SugarCache.php');

class ExtAPIDnb extends ExternalAPIBase
{
    public $connector = "ext_rest_dnb";
    private $dnbBaseURL = array(
        'stg' => 'http://services-ext-stg.dnb.com/',
        'prod' => 'https://maxcvservices.dnb.com/'
    );
    private $dnbAuthURL = "rest/Authentication";
    private $dnbSearchURL = "V4.0/organizations?KeywordText=%s&findcompany=true&SearchModeDescription=Basic";
    private $dnbFirmographicURL = "V2.0/organizations/%s/products/%s";
    private $dnbFirmoLiteURL = "V2/organizations/%s/products/CST_PRD_1";
    private $dnbStandardSearch = "V2.0/organizations/%s/products/DCP_STD";
    private $dnbPremiumSearch = "V2.0/organizations/%s/products/DCP_PREM";
    private $dnbLiteSearchURL = "V2/organizations/%s/products/CST_PRD_1?CountryISOAlpha2Code=US";
    private $dnbProfileURL = "V2/organizations/%s/products/DCP_STD?CountryISOAlpha2Code=US";
    private $dnbCompetitorsURL = "V4.0/organizations/%s/competitors";
    private $dnbIndustryURL = "V3.0/industries/industrycode-%s/IND_STD";
    private $dnbFinancialURL = "V3.0/organizations/%s/products/FIN_HGLT";
    private $dnbFamilyTreeURL = "V3.1/organizations/%s/products/LNK_FF";
    private $dnbCleanseMatchURL = "V3.0/organizations";
    private $dnbBALURL = "V4.0/organizations";
    private $dnbFindIndustryURL = "V4.0/industries?KeywordText=%s&findindustry=true";
    private $dnbFindContactsURL = "V4.0/organizations?findcontact=true&DUNSNumber-1=%s&SearchModeDescription=Advanced";
    private $dnbContactDetPremURL = "V3.0/organizations/%s/products/CNTCT_PLUS?PrincipalIdentificationNumber=%s";
    private $dnbContactDetStdURL = "V3.0/organizations/%s/products/CNTCT?PrincipalIdentificationNumber=%s";
    private $dnbNewsURL = "V3.0/organizations/%s/products/NEWS_MDA";
    private $dnbIndustryConversionURL = "V4.0/industries?IndustryCode-1=%s&ReturnOnlyPremiumIndustryIndicator=true&IndustryCodeTypeCode-1=%s&findindustry=true";
    private $dnbRefreshCheckURL = "V4.0/organizations?refresh=refresh&DunsNumber-1=%s";
    private $dnbApplicationId;
    private $dnbUsername;
    private $dnbPassword;
    private $dnbEnv;
    //cache time to live in seconds
    private $cacheTTL = 8640000;
    public $supportedModules = array();

    function __construct()
    {
        $this->dnbUsername = trim($this->getConnectorParam('dnb_username'));
        $this->dnbPassword = trim($this->getConnectorParam('dnb_password'));
        $this->dnbEnv = trim($this->getConnectorParam('dnb_env'));
        // start a session if one hasnt been started
        try {
            if (!isset($_SESSION)) {
                session_start();
            }
        } catch (Exception $e) {
            $GLOBALS['log']->debug('Tried to start new session for dnb but was not able to.');
        }
    }

    /**
     * Checks cache for cached response else invoke api using makeRequest
     * @param $cacheKey String
     * @param $endPoint String
     * @param $requestType String possible values GET or POST
     * @return array
     */
    private function dnbServiceRequest($cacheKey,$endPoint,$requestType){
        $apiResponse = sugar_cache_retrieve($cacheKey);
        //obtain results from dnb service if cache does not contain result
        if (empty($apiResponse) || $apiResponse === SugarCache::EXTERNAL_CACHE_NULL_VALUE) {
            $GLOBALS['log']->debug('Cache does not contain'.$cacheKey);
            $apiResponse = $this->makeRequest($requestType, $endPoint);
            if (!$apiResponse['success']) {
                $GLOBALS['log']->error('D&B failed, reply said: ' . print_r($apiResponse, true));
                return $apiResponse;
            } else {
                //cache the result if the dnb service response was a success
                sugar_cache_put($cacheKey, $apiResponse, $this->cacheTTL);
                $GLOBALS['log']->debug('Cached ' . $cacheKey);
            }
        } else {
            $GLOBALS['log']->debug('Getting cached results for ' . $cacheKey);
        }
        return $apiResponse;
    }

    /**
     * Searches for companies in DNB based on the keyword
     * @param $keyword company search string
     * @return jsonarray
     */
    public function dnbSearch($keyword)
    {
        $cache_key = 'dnb.search.' . $keyword;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbSearchURL, urlencode($keyword));
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        // get existing duns
        $existingDUNSArray = json_decode($this->getExistingDUNS(), true);
        $modifiedApiResponse = $this->getCommonDuns($reply['responseJSON'], $existingDUNSArray);
        $reply['responseJSON'] = $modifiedApiResponse;
        return $reply['responseJSON'];
    }

    /**
     * Checks when the duns_num was last refreshed
     * @param $duns_num unique identifier for a company
     * @return jsonarray
     */
    public function dnbRefreshCheck($duns_num)
    {
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbRefreshCheckURL, $duns_num);
        $reply = $this->makeRequest('GET', $dnbendpoint);
        if (!$reply['success']) {
            $GLOBALS['log']->error('DNB failed, reply said: ' . print_r($reply, true));
            return $reply;
        }
        return $reply['responseJSON'];
    }

    /**
     * Gets News And Social Media Info for a D-U-N-S
     * @param $duns_num unique identifier for a company
     * @return jsonarray
     */
    public function dnbNews($duns_num)
    {
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbNewsURL, $duns_num);
        $reply = $this->makeRequest('GET', $dnbendpoint);
        if (!$reply['success']) {
            $GLOBALS['log']->error('DNB failed, reply said: ' . print_r($reply, true));
            return $reply;
        }
        return $reply['responseJSON'];
    }

    /**
     * Gets Lite Company Information for a D-U-N-S
     * @param $duns_num unique identifier for a company
     * @return jsonarray
     */
    public function dnbLiteProfile($duns_num)
    {
        //dnb profile standard
        $cache_key = 'dnb.prof_lite.' . $duns_num;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbLiteSearchURL, $duns_num);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets Standard Company Information for a D-U-N-S
     * @param $duns_num unique identifier for a company
     * @return jsonarray
     */
    public function dnbStandardProfile($duns_num)
    {
        //dnb profile standard
        $cache_key = 'dnb.prof_std.' . $duns_num;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbStandardSearch, $duns_num);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets Premium Company Information for a D-U-N-S
     * @param $duns_num unique identifier for a company
     * @return jsonarray
     */
    public function dnbPremiumProfile($duns_num)
    {
        //dnb profile premium
        $cache_key = 'dnb.prof_prem.' . $duns_num;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbPremiumSearch, $duns_num);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets Company Firmographic Information Based on DUNS number
     * @param $duns_num
     * @return jsonarray
     */
    public function dnbProfile($duns_num)
    {
        return $this->dnbStandardProfile($duns_num);
    }

    /**
     * Gets Competitors for a D-U-N-S number
     * @param $duns_num
     * @return jsonarray
     */
    public function dnbCompetitors($duns_num)
    {
        //dnb competitors
        $cache_key = 'dnb.competitors.' . $duns_num;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbCompetitorsURL, $duns_num);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets Financials for a D-U-N-S number
     * @param $duns_num
     * @return jsonarray
     */
    public function dnbFinancialInfo($duns_num)
    {
        //dnb financials
        $cache_key = 'dnb.financials.' . $duns_num;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbFinancialURL, $duns_num);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets Family Tree for a DUNS number
     * @param $duns_num
     * @return jsonarray
     */
    public function dnbFamilyTree($duns_num)
    {
        //dnb family tree
        $cache_key = 'dnb.familytree.' . $duns_num;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbFamilyTreeURL, $duns_num);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets D&B Hoovers Industry Codes for a keyword
     * @param $industry_keyword
     * @return jsonarray
     */
    public function dnbIndustrySearch($industry_keyword)
    {
        //dnb industry search
        $cache_key = 'dnb.industrysearch.' . $industry_keyword;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf(
                $this->dnbFindIndustryURL,
                urlencode($industry_keyword)
            );
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets Contacts For A Given Duns Number
     * @param $duns_num
     * @return jsonarray
     */
    public function dnbFindContacts($duns_num)
    {
        //dnb contacts list
        $cache_key = 'dnb.contactsearch.' . $duns_num;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbFindContactsURL, $duns_num);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        // get existing contacts
        $dnbContactIdArray = array();
        $path = "FindContactResponse.FindContactResponseDetail.FindCandidate";
        if ($this->arrayKeyExists($reply['responseJSON'], $path)) {
            $dnbContactsList = $reply['responseJSON']['FindContactResponse']['FindContactResponseDetail']['FindCandidate'];
            $this->underscoreEach(
                $dnbContactsList,
                function ($contactObj) use (&$dnbContactIdArray) {
                    $dnbContactIdArray[] = $contactObj['PrincipalIdentificationNumberDetail'][0]['PrincipalIdentificationNumber'];
                }
            );
            $existingContacts = json_decode($this->getExistingContacts($dnbContactIdArray), true);
            if (count($existingContacts) > 0) {
                $modifiedApiResponse = $this->getCommonContacts($reply['responseJSON'], $existingContacts);
                $reply['responseJSON'] = $modifiedApiResponse;
            }
        }
        return $reply['responseJSON'];
    }

    /**
     * Gets Cleanse and Matched Data from DNB API for the cleanse and match parameters
     * @param $cmParams Array
     * cmParams array must be in the following format
     * {
     *   "IncludeCleansedAndStandardizedInformationIndicator":"true", //mandatory
     *   "CountryISOAlpha2Code":"US", //country code mandatory
     *   "cleansematch":"true",//mandatory
     *   "SubjectName":"ibm", //company name mandatory
     *   "PrimaryTownName":"town name", //optional
     *   "TerritoryName": "territory" //optional
     *  }
     * @return jsonarray
     */
    public function dnbCMRrequest($cmParams)
    {
        //convert $cmParams to queryString
        //TO DO: validate the POST parameters 
        $cmQueryString = '?' . http_build_query($cmParams);
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . $this->dnbCleanseMatchURL . $cmQueryString;
        $reply = $this->makeRequest('GET', $dnbendpoint);
        if (!$reply['success']) {
            $GLOBALS['log']->error('DNB failed, reply said: ' . print_r($reply, true));
            return array('error' => 'ERROR_DNB_CONFIG');
        }
        return $reply['responseJSON'];
    }

    /**
     * Builds A List Of Companies from DNB API for the build a list parameters
     * @param $balParams Array
     * cmParams array must be in the following format
     * {
     *   "IncludeCleansedAndStandardizedInformationIndicator":"true", //mandatory
     *   "CountryISOAlpha2Code":"US", //country code mandatory
     *   "cleansematch":"true",//mandatory
     *   "SubjectName":"ibm", //company name mandatory
     *   "PrimaryTownName":"town name", //optional
     *   "TerritoryName": "territory" //optional
     *  }
     * @return jsonarray
     */
    public function dnbBALRequest($balParams)
    {
        //convert $balParams to queryString
        //TO DO: validate the POST parameters 
        $balQueryString = '?' . http_build_query($balParams);
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . $this->dnbBALURL . $balQueryString;
        $reply = $this->makeRequest('GET', $dnbendpoint);
        if (!$reply['success']) {
            $GLOBALS['log']->error('DNB failed, reply said: ' . print_r($reply, true));
            return $reply;
        }
        return $reply['responseJSON'];
    }

    /**
     * Gets Contacts Details For Principal Identification Number and Duns Number
     * @param $contactParams
     * @return jsonarray
     */
    public function dnbContactDetails($contactParams)
    {
        $duns_num = $contactParams['duns_num'];
        $contact_id = $contactParams['contact_id'];
        $contact_type = $contactParams['contact_type'];
        $cache_key = null;
        //dnb contact
        if ($contact_type === 'dnb-cnt-prem') {
            $cache_key = 'dnb.cntprem.' . $duns_num . '.' . $contact_id;
            $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf(
                    $this->dnbContactDetPremURL,
                    $duns_num,
                    $contact_id
                );
        } else if ($contact_type === 'dnb-cnt-std'){
            $cache_key = 'dnb.cntstd.' . $duns_num . '.' . $contact_id;
            $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf(
                    $this->dnbContactDetStdURL,
                    $duns_num,
                    $contact_id
                );
        }
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets Converts the given Industry Code and Industry Type Code to Hoovers Industry Code (HIC)
     * @param $indMapParams object
     * $indMapParams must contain two keys
     * industryCode
     * industryType -- possible values are 3599(SIC),700(NAICS),19295(SIC UK),25838(HIC)
     * @return jsonarray
     */
    public function dnbIndustryConversion($indMapParams)
    {
        $industryType = $indMapParams['industryType'];
        $industryCode = $indMapParams['industryCode'];
        //dnb contact
        $cache_key = 'dnb.indMap.' . $industryType . '.' . $industryCode;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf(
                $this->dnbIndustryConversionURL,
                $industryCode,
                $industryType
            );
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Gets company information for the gives duns for the given product_code
     * @param $firmoParams object
     * $firmoParams must contain two keys
     * duns_num
     * prod_code -- possible values are CST_PRD_1,DCP_STD,DCP_PREM
     * @return jsonarray
     */
    public function dnbFirmographic($firmoParams)
    {
        $duns_num = $firmoParams['duns_num'];
        $prod_code = $firmoParams['prod_code'];
        //dnb firmographic
        $cache_key = 'dnb.' . $duns_num . '.' . $prod_code;
        if ($prod_code === 'CST_PRD_1') {
            $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbFirmoLiteURL, $duns_num);
        } else {
            $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf(
                    $this->dnbFirmographicURL,
                    $duns_num,
                    $prod_code
                );
        }
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }


    /**
     * Gets Converts the given Industry Code and Industry Type Code to Hoovers Industry Code (HIC)
     * Uses the HIC to get the Industry Information
     * @param $indMapParams object
     * $indMapParams must contain two keys
     * industryCode
     * industryType -- possible values are 3599(SIC),700(NAICS),19295(SIC UK),25838(HIC)
     * @return jsonarray
     */
    public function dnbIndustryInfoPost($indMapParams)
    {
        $reply = $this->dnbIndustryConversion($indMapParams);
        if (empty($reply['FindIndustryResponse'])) {
            $GLOBALS['log']->error('DNB failed, reply said: ' . print_r('Error Converting Industry Code', true));
            return $reply;
        } else {
            //get the HIC from the result of the industry conversion call
            $industryCodePath = "FindIndustryResponse.FindIndustryResponseDetail.IndustryCode";
            if ($this->arrayKeyExists($reply, $industryCodePath)) {
                $industryArray = $reply['FindIndustryResponse']['FindIndustryResponseDetail']['IndustryCode'];
                //get the primaru hic
                $primaryHIC = $this->underscoreFind(
                    $industryArray,
                    function ($industryObj) {
                        return $industryObj['DisplaySequence'] === 1;
                    }
                );
                if (!empty($primaryHIC)) {
                    $indCodeParam = $primaryHIC['IndustryCode'] . '-' . $primaryHIC['@DNBCodeValue'];
                    return $this->dnbIndustryInfo($indCodeParam);
                } else {
                    return array('success' => false, 'errorMessage' => 'Error Converting Industry Code');
                }
            } else {
                return array('success' => false, 'errorMessage' => 'Error Converting Industry Code');
            }
        }
    }

    /**
     * Gets Industry information for a industry code
     * @param $ind_code industry code
     * @return jsonarray
     */
    public function dnbIndustryInfo($ind_code)
    {
        //dnb industry
        $cache_key = 'dnb.industry.' . $ind_code;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbIndustryURL, $ind_code);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        return $reply['responseJSON'];
    }

    /**
     * Lists DUNS already existing in  SUGARDB
     * @param $keyword company search string
     * @return array
     */
    public function dupcheck($keyword)
    {
        $responseArray = array('responseJSON' => json_decode($this->getExistingDUNS(), true));
        return $responseArray['responseJSON'];
    }

    //TO DO parametrize this function with field name and array values
    //make a common function for duns_num and principal_identification_number
    /**
     * Lists Contacts already present in sugar db using D&B principal identification number
     * @param $principalIdsArr array (array of principal identification numbers)
     * @return array
     */
    private function getExistingContacts($principalIdsArr = null)
    {
        $seed = BeanFactory::newBean('Contacts');
        $principal_id = 'dnb_principal_id';
        $options = array();
        $options['offset'] = 0;
        $options['order_by'] = array(array('date_modified', 'DESC'));
        $options['add_deleted'] = true;
        $options['offset'] = 'end';
        $options['module'] = $seed->module_name;
        $options['team_security'] = false; //TO DO: Ask dtam why setting this to true returns 0 results
        $q = new SugarQuery();
        $q->from($seed, $options);
        $fields = array($principal_id);
        $q->select($fields);
        $where = $q->where();
        $where->in($principal_id, $principalIdsArr);
        $q->compileSql();
        $queryResults = $q->execute('json');
        return $queryResults;
    }

    //TO DO parametrize this function with field name and array values
    //make a common function for duns_num and principal_identification_number
    /**
     * Lists Accounts already present in sugar db using D&B D-U-N-S
     * @return array
     */
    private function getExistingDUNS()
    {
        $seed = BeanFactory::newBean('Accounts');
        $duns_field = 'duns_num';
        $options = array();
        $options['offset'] = 0;
        $options['order_by'] = array(array('date_modified', 'DESC'));
        $options['add_deleted'] = true;
        $options['offset'] = 'end';
        $options['module'] = $seed->module_name;
        $options['team_security'] = false; //TO DO: Ask dtam why setting this to true returns 0 results
        $q = new SugarQuery();
        $q->from($seed, $options);
        $fields = array($duns_field);
        $q->select($fields);
        $where = $q->where();
        $where->notNull($duns_field);
        $q->compileSql();
        $queryResults = $q->execute('json');
        return $queryResults;
    }

    /**
     * Gets DUNS that are present in the database and in the DNB service reponse
     * These DUNS are flagged as duplicates using the array key 'isDupe'
     * @param $dnbApiResponse array (dnb api response)
     * @param $dbDunsArray array (array of DUNS in sugar database)
     * @return array (modified dnb api response with duplicates flagged)
     */
    private function getCommonDuns($dnbApiResponse, $dbDunsArray)
    {
        //check if the particular key exists in the $dnbApiResponse
        $path = "FindCompanyResponse.FindCompanyResponseDetail.FindCandidate";
        if ($this->arrayKeyExists($dnbApiResponse, $path)) {
            $apiDuns = $this->underscorePluck(
                $dnbApiResponse['FindCompanyResponse']['FindCompanyResponseDetail']['FindCandidate'],
                'DUNSNumber'
            );
            $dbDuns = $this->underscorePluck($dbDunsArray, 'duns_num');
            if (count($apiDuns) > 0 && count($dbDuns) > 0) {
                $commonDuns = array_intersect($apiDuns, $dbDuns);
                if (count($commonDuns) > 0) {
                    for ($i = 0; $i < count($apiDuns); $i++) {
                        if (in_array($apiDuns[$i], $commonDuns)) {
                            $dnbApiResponse['FindCompanyResponse']['FindCompanyResponseDetail']['FindCandidate'][$i]['isDupe'] = true;
                        }
                    }
                    return $dnbApiResponse;
                } else {
                    return $dnbApiResponse;
                }
            } else {
                return $dnbApiResponse;
            }
        } else {
            return $dnbApiResponse;
        }
    }

    /**
     * Gets Contacts that are present in the database and in the DNB service reponse
     * These Contacts are flagged as duplicates using the array key 'isDupe'
     * @param $dnbApiResponse array (dnb api response)
     * @param $dbPrincipalIdArray array (array of principal identification nos. in sugar database)
     * @return array (modified dnb api response with duplicates flagged)
     */
    private function getCommonContacts($dnbApiResponse, $dbPrincipalIdArray)
    {
        //check if the particular key exists in the $dnbApiResponse
        $path = "FindContactResponse.FindContactResponseDetail.FindCandidate";
        if ($this->arrayKeyExists($dnbApiResponse, $path)) {
            $dnbContactsCollection = $dnbApiResponse['FindContactResponse']['FindContactResponseDetail']['FindCandidate'];
            $dbContacts = $this->underscorePluck($dbPrincipalIdArray, 'dnb_principal_id');
            $dnbModifiedContactsCollection = array();
            $this->underscoreEach(
                $dnbContactsCollection,
                function ($contactObj) use (&$dbContacts, &$dnbModifiedContactsCollection) {
                    $contactId = $contactObj['PrincipalIdentificationNumberDetail'][0]['PrincipalIdentificationNumber'];
                    if (in_array($contactId, $dbContacts)) {
                        $contactObj['isDupe'] = true;
                    }
                    $dnbModifiedContactsCollection[] = $contactObj;
                }
            );
            $dnbApiResponse['FindContactResponse']['FindContactResponseDetail']['FindCandidate'] = $dnbModifiedContactsCollection;
        }
        return $dnbApiResponse;
    }

    /**
     * Mimics the pluck function in underscore.js
     * This code is a modification adapted from http://brianhaveri.github.io/Underscore.php/
     * @param $collection array
     * @param $key string
     * @return array
     */
    private function underscorePluck($collection, $key)
    {
        $return = array();
        foreach ($collection as $item) {
            foreach ($item as $k => $v) {
                if ($k === $key) {
                    $return[] = $v;
                }
            }
        }
        return $return;
    }

    /**
     * Mimics the each function in underscore.js
     * This code is a modification adapted from http://brianhaveri.github.io/Underscore.php/
     * @param $collection array
     * @param $iterator function
     * @return null
     */
    private function underscoreEach($collection = null, $iterator = null)
    {
        if (is_null($collection) || count($collection) === 0) {
            return null;
        }
        foreach ($collection as $k => $v) {
            call_user_func($iterator, $v, $k, $collection);
        }
        return null;
    }

    /**
     * Mimics the find function in underscore.js
     * This code is a modification adapted from http://brianhaveri.github.io/Underscore.php/
     * @param $collection array
     * @param $iterator function
     * @return object
     */
    private function underscoreFind($collection = null, $iterator = null)
    {
        if (is_null($collection) || count($collection) === 0) {
            return null;
        }
        foreach ($collection as $val) {
            if (call_user_func($iterator, $val)) {
                return $val;
            }
        }
    }

    /**
     * Invokes REST API
     * @param $requestMethod Method type GET|POST
     * @param $url Service End Point
     * @param $urlParams Parameters to be appended to URL
     * @param $postData Data to be posted for POST method
     * @return array
     */
    private function makeRequest($requestMethod, $url, $urlParams = null, $postData = null)
    {
        //check if connector is configured
        if (!$this->isConnectorConfigured()) {
            return array('error' => 'ERROR_DNB_CONFIG');
        }
        //check if token has expired
        $dnbToken = !empty($_SESSION[$this->dnbEnv . 'dnbToken']) ? $_SESSION[$this->dnbEnv . 'dnbToken'] : null;
        $dnbTokenIssueTime = !empty($_SESSION[$this->dnbEnv . 'dnbTokenIssueTime']) ? $_SESSION[$this->dnbEnv . 'dnbTokenIssueTime'] : null;
        $dnbToken = $this->checkToken($dnbToken, $dnbTokenIssueTime);
        if ($dnbToken === '') {
            return array('success' => false, 'errorMessage' => 'Error Obtaining Authorization Token');
        }
        $dnbApplicationId = $this->dnbApplicationId;
        $curl_handle = curl_init();
        if (!empty($dnbApplicationId)) {
            $curl_headers = array(
                "Authorization: $dnbToken",
                "ApplicationId: $dnbApplicationId",
                "Accept: application/json",
                "Content-type: application/json"
            );
        } else {
            $curl_headers = array(
                "Authorization: $dnbToken",
                "Accept: application/json",
                "Content-type: application/json"
            );
        }
        // setting curl options
        curl_setopt_array($curl_handle, $this->getCurlOptions($requestMethod, $url, $curl_headers, false));
        $response = curl_exec($curl_handle);
        if ($response === false) {
            $curl_errno = curl_errno($curl_handle);
            $curl_error = curl_error($curl_handle);
            $GLOBALS['log']->error("HTTP client: cURL call failed: error $curl_errno: $curl_error");
            return array('error' => 'ERROR_CURL_' . $curl_errno);
        }
        $GLOBALS['log']->debug("HTTP client response: $response");
        curl_close($curl_handle);
        $response = json_decode($response, true);
        if (empty($response)) {
            return array('success' => false, 'errorMessage' => 'Error in JSON Decoding');
        }
        return array('success' => true, 'responseJSON' => $response);
    }

    /**
     * Check if Connector Framework Is Configured
     * @return bool
     */
    public function isConnectorConfigured()
    {
        if (empty($this->dnbUsername) || empty($this->dnbPassword) || empty($this->dnbEnv)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Gets Curl Options Array
     * @param $method GET | POST
     * @param $url
     * @param $headersArray
     * @param $header TRUE | FALSE to set CURLOPT_HEADER
     * @param $data
     */
    private function getCurlOptions($method, $url, $headersArray, $header, $data = null)
    {
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => $header,
            CURLOPT_POST => ((strcmp('POST', $method) === 0) ? true : false),
            CURLOPT_HTTPHEADER => $headersArray,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLINFO_HEADER_OUT => false
        );
        /* CURL SET PROXY CONFIG USING SUGAR SYSTEM SETTINGS */
        $proxy_config = Administration::getSettings('proxy');
        if (!empty($proxy_config) &&
            !empty($proxy_config->settings['proxy_on']) &&
            $proxy_config->settings['proxy_on'] === 1
        ) {
            $curl_options[CURLOPT_PROXY] = $proxy_config->settings['proxy_host'];
            $curl_options[CURLOPT_PROXYPORT] = $proxy_config->settings['proxy_port'];
            if (!empty($proxy_settings['proxy_auth'])) {
                $curl_options[CURLOPT_PROXYUSERPWD] = $proxy_settings['proxy_username'] . ':' . $proxy_settings['proxy_password'];
            }
        }
        return $curl_options;
    }

    /**
     * Checks if DNB Token is valid
     * DNB Token expires after 8 hours of idle time
     * @param $dnbToken
     * @param $dnbTokenIssueTime
     * @return string
     */
    private function checkToken($dnbToken, $dnbTokenIssueTime)
    {
        $isTokenSet = !empty($dnbToken);
        $isTokenExpired = ((time() - (!empty($dnbTokenIssueTime) ? $dnbTokenIssueTime : time(
                ))) > 28800) ? true : false;
        if (!$isTokenSet || ($isTokenSet && $isTokenExpired)) {
            return $this->getAuthenticationToken();
        } else {
            $GLOBALS['log']->debug('Using Valid D&B Old Token');
            return $dnbToken;
        }
    }

    /**
     * Return new DNB Authentication to acces DNB api
     * @return string
     */
    private function getAuthenticationToken()
    {
        $username = $this->dnbUsername;
        $password = $this->dnbPassword;
        $token = '';
        $curl_handle = curl_init();
        $auth_url = $this->dnbBaseURL[$this->dnbEnv] . $this->dnbAuthURL;
        $curl_headers = array(
            "x-dnb-user: $username",
            "x-dnb-pwd: $password",
            "Accept: application/json",
            "Content-type: application/json"
        );
        // setting curl options
        curl_setopt_array($curl_handle, $this->getCurlOptions('POST', $auth_url, $curl_headers, true));
        $response = curl_exec($curl_handle);
        if ($response === false) {
            $curl_errno = curl_errno($curl_handle);
            $curl_error = curl_error($curl_handle);
            $GLOBALS['log']->debug("HTTP client: cURL call failed: error $curl_errno: $curl_error");
            return array('error' => 'ERROR_CURL_' . $curl_errno);
        }
        $curl_info = curl_getinfo($curl_handle);
        if ($curl_info['http_code'] === 200) {
            preg_match("/Authorization:\s(\S*)/", $response, $tokenArray);
            if (count($tokenArray) > 0) {
                $token = $tokenArray[1];
            } else {
                return;
            }
        } else {
            return;
        }
        $GLOBALS['log']->debug("HTTP client response: $response");
        curl_close($curl_handle);
        $_SESSION[$this->dnbEnv . 'dnbTokenIssueTime'] = time();
        $_SESSION[$this->dnbEnv . 'dnbToken'] = $token;
        $GLOBALS['log']->debug('New DNB Token Issued');
        return $token;
    }

    /**
     * Utility function to check if an array key exists in a nested associative array
     * @param $array -- Associative Array
     * @param $path -- string -- '.' delimited path to the particular key in the associative array
     * @return TRUE | FALSE
     */
    private function arrayKeyExists($array, $path)
    {
        $keyArray = explode('.', $path);
        if (count($keyArray) > 0) {
            for ($i = 0; $i < count($keyArray); $i++) {
                if (is_null($array) || !array_key_exists($keyArray[$i], $array)) {
                    return false;
                }
                $array = $array[$keyArray[$i]];
            }
            return true;
        } else {
            return false;
        }
    }
}