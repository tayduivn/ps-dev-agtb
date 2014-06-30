<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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
    //OrderReasonCode is a parameter required to fetch Firmographic info for all companies
    //if this is not set, API calls for companies in Germany will fail
    private $dnbFirmographicURL = "V2.0/organizations/%s/products/%s?OrderReasonCode=6332";
    private $dnbFirmoLiteURL = "V2/organizations/%s/products/CST_PRD_1";
    private $dnbStandardSearch = "V2.0/organizations/%s/products/DCP_STD";
    private $dnbPremiumSearch = "V2.0/organizations/%s/products/DCP_PREM";
    private $dnbLiteSearchURL = "V2/organizations/%s/products/CST_PRD_1?CountryISOAlpha2Code=US";
    private $dnbCompetitorsURL = "V4.0/organizations/%s/competitors";
    private $dnbIndustryURL = "V3.0/industries/industrycode-%s/IND_STD";
    private $dnbFinancialURL = "V3.0/organizations/%s/products/FIN_HGLT";
    private $dnbFamilyTreeURL = "V3.1/organizations/%s/products/%s";
    private $dnbCleanseMatchURL = "V3.0/organizations";
    private $dnbBALURL = "V6.0/organizations?SearchModeDescription=Advanced&findcompany=true&";
    private $dnbFindIndustryURL = "V4.0/industries?KeywordText=%s&findindustry=true";
    private $dnbFindContactsURL = "V4.0/organizations?findcontact=true&DUNSNumber-1=%s&SearchModeDescription=Advanced";
    private $dnbContactDetPremURL = "V3.0/organizations/%s/products/CNTCT_PLUS?PrincipalIdentificationNumber=%s";
    private $dnbContactDetStdURL = "V3.0/organizations/%s/products/CNTCT?PrincipalIdentificationNumber=%s";
    private $dnbNewsURL = "V3.0/organizations/%s/products/NEWS_MDA";
    private $dnbIndustryConversionURL = "V4.0/industries?IndustryCode-1=%s&ReturnOnlyPremiumIndustryIndicator=true&IndustryCodeTypeCode-1=%s&findindustry=true";
    private $dnbRefreshCheckURL = "V4.0/organizations?refresh=refresh&DunsNumber-1=%s";
    private $dnbContactsBALURL = "V6.0/organizations?CandidateMaximumQuantity=1000&findcontact=true&SearchModeDescription=Advanced";
    private $dnbApplicationId;
    private $dnbUsername;
    private $dnbPassword;
    private $dnbEnv;
    //cache time to live in seconds
    private $cacheTTL = 8640000;
    public $supportedModules = array();

    //commonly used json paths
    private $familyTreePaths = array(
        'nestedDuns' => 'SubjectHeader.DUNSNumber',
        'nestedTree' => 'Linkage.FamilyTreeMemberOrganization',
        'familyTree' => 'OrderProductResponse.OrderProductResponseDetail.Product.Organization.Linkage.FamilyTreeMemberOrganization',
        'duns' => 'OrderProductResponse.OrderProductResponseDetail.Product.Organization.SubjectHeader.DUNSNumber',
        'inquiryDet' => 'OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber'
    );

    private $commonJsonPaths = array(
        'findcompany' => 'FindCompanyResponse.FindCompanyResponseDetail.FindCandidate',
        'competitors' => 'FindCompetitorResponse.FindCompetitorResponseDetail.Competitor',
        'cleansematch' => 'GetCleanseMatchResponse.GetCleanseMatchResponseDetail.MatchResponseDetail.MatchCandidate',
        'contacts' => 'FindContactResponse.FindContactResponseDetail.FindCandidate',
        'principalIdPath' => 'PrincipalIdentificationNumberDetail.0.PrincipalIdentificationNumber'
    );

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
        parse_str($queryString, $queryParams);
        if (array_key_exists('q',$queryParams)) {
            $keyword = $queryParams['q'];
                $GLOBALS['log']->error('DNB failed, reply said: ' . print_r($reply, true));
                return $reply;
            }
        } else {
            //send error
            return array('error' => 'ERROR_BAD_REQUEST');
        }
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
     * @param string $duns_num
     * @return jsonarray
     */
    public function dnbCompetitors($duns_num)
    {
        //dnb competitors
        $cache_key = 'dnb.competitors.' . $duns_num;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . sprintf($this->dnbCompetitorsURL, $duns_num);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        // get existing duns
        $path = $this->commonJsonPaths['competitors'];
        if ($this->arrayKeyExists($reply['responseJSON'], $path)) {
            //get the list of companies from dnb
            $modifiedCompaniesList = $this->checkAndMarkDuplicateDuns($reply['responseJSON'], $path);
            if (!empty($modifiedCompaniesList)) {
                $reply['responseJSON']['FindCompetitorResponse']['FindCompetitorResponseDetail']['Competitor'] = $modifiedCompaniesList;
            }
        }
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
        $path = $this->commonJsonPaths['contacts'];
        if ($this->arrayKeyExists($reply['responseJSON'], $path)) {
            $reply['responseJSON'] = $this->checkAndMarkDuplicateContacts($reply['responseJSON'], $path);
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
        // get existing duns
        $path = $this->commonJsonPaths['cleansematch'];
        if ($this->arrayKeyExists($reply['responseJSON'], $path)) {
            //get the list of companies from dnb
            $modifiedCompaniesList = $this->checkAndMarkDuplicateDuns($reply['responseJSON'], $path);
            if (!empty($modifiedCompaniesList)) {
                $reply['responseJSON']['GetCleanseMatchResponse']['GetCleanseMatchResponseDetail']['MatchResponseDetail']['MatchCandidate'] = $modifiedCompaniesList;
            }
        }
        return $reply['responseJSON'];
    }

    /**
     * Builds A List Of Companies from DNB API for the build a list parameters
     * @param $balParams Array
     * $balParams must look like the following
     * not all keys are mandatory but atleast one of them
     * {
     *   "SalesLowRangeAmount": , <decimal> //optionsl
     *   "SalesHighRangeAmount": , <decimal> //optionsl
     *  }
     * @return jsonarray
     */
    public function dnbBALAccounts($balParams)
    {
        //convert $balParams to queryString
        $balQueryString =  http_build_query($balParams);
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . $this->dnbBALURL . $balQueryString;
        $reply = $this->makeRequest('GET', $dnbendpoint);
        if (!$reply['success']) {
            $GLOBALS['log']->error('DNB failed, reply said: ' . print_r($reply, true));
            return array('error' => 'ERROR_DNB_CONFIG');
        }
        $path = $this->commonJsonPaths['findcompany'];
        if ($this->arrayKeyExists($reply['responseJSON'], $path)) {
            $modifiedCompaniesList = $this->checkAndMarkDuplicateDuns($reply['responseJSON'],$path);
            if (!empty($modifiedCompaniesList)) {
                $reply['responseJSON']['FindCompanyResponse']['FindCompanyResponseDetail']['FindCandidate'] = $modifiedCompaniesList;
            }
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
     * Finds Contacts For a Given DUNS Number based on contact name and job title
     * @param $contactParams array can have the following keys
     * duns -- DUNS Number -- required
     * namekw -- Contact Name Key Word -- optional
     * jobkw -- Job Title Key Word -- optional
     * either the namekw or the jobkw must be provided
     * @return array
     */
    public function dnbFindContactsPost($contactParams) {
        $contactQueryString = http_build_query($contactParams);
        //dnb contacts list
        $cache_key = 'dnb.contactsearch.' . $contactQueryString;
        if (!empty($contactParams['KeywordContactText'])) {
            $contactParams['KeywordContactScopeText'] = 'Title';
        }
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv] . $this->dnbContactsBALURL . '&' . http_build_query($contactParams);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        // get existing contacts
        $path = $this->commonJsonPaths['contacts'];
        if ($this->arrayKeyExists($reply['responseJSON'], $path)) {
            $reply['responseJSON'] = $this->checkAndMarkDuplicateContacts($reply['responseJSON'], $path);
        }
        return $reply['responseJSON'];
    }

    /**
     * Get the Linkage / Family Tree For a Given DUNS Number
     * @param $ftParams
     * @return array
     */
    public function dnbFamilyTree($ftParams)
    {
        $ftQueryString = http_build_query($ftParams);
        //dnb family tree cache key
        $cache_key = 'dnb.ft.'.$ftQueryString;
        $dnbendpoint = $this->dnbBaseURL[$this->dnbEnv].sprintf($this->dnbFamilyTreeURL,$ftParams['duns_num'],$ftParams['prod_code']);
        //check if result exists in cache
        $reply = $this->dnbServiceRequest($cache_key, $dnbendpoint, 'GET');
        //family tree duplicate check
        if ($this->arrayKeyExists($reply['responseJSON'], $this->familyTreePaths['familyTree'])) {
            $reply['responseJSON'] = $this->checkAndMarkFTDuplicateDuns($reply['responseJSON']);
        }
        return $reply['responseJSON'];
    }

    /**
     * Given a family tree return an array of duns
     * if a duns has a nested family tree recursively traverse it
     * to bring in the array of duns
     * @param $familyTree
     * @return array
     */
    private function getFamilyTreeDuns($familyTree)
    {
        $dunsArray = array();
        foreach ($familyTree as &$dnbRecordObj) {
            //add the duns to the dunsArray
            $dunsArray[] = $this->getObjectValue($dnbRecordObj, $this->familyTreePaths['nestedDuns']);
            //check if the duns has a nestedFamilyTree
            $nestedFamilyTree = $this->getObjectValue($dnbRecordObj, $this->familyTreePaths['nestedTree']);
            if (!empty($nestedFamilyTree)) {
                //if the duns has nested family tree
                //then recursively get the duns
                $nestedDunsArray = $this->getFamilyTreeDuns($nestedFamilyTree);
                $dunsArray = array_merge($dunsArray, $nestedDunsArray);
            }
        }
        return $dunsArray;
    }

    /**
     * Given a family tree and an array of duns in sugar db
     * traverse through the family tree and mark the common duns as duplicates
     * @param $familyTree
     * @param $dunsArray
     * @return array
     */
    private function markFamilyTreeDuplicates($familyTree, $dunsArray)
    {
        $dnbModifiedRecordsCollection = array();
        foreach ($familyTree as &$dnbRecordObj) {
            $duns = $this->getObjectValue($dnbRecordObj, $this->familyTreePaths['nestedDuns']);
            $duns = str_pad($duns, 9, "0", STR_PAD_LEFT);
            //check if the duns has a nestedFamilyTree
            $nestedFamilyTree = $this->getObjectValue($dnbRecordObj, $this->familyTreePaths['nestedTree']);
            if (!empty($nestedFamilyTree)) {
                //if the duns has nested family tree
                //then recursively mark duplicates
                $nestedModifiedRecords = $this->markFamilyTreeDuplicates($nestedFamilyTree, $dunsArray);
                $dnbRecordObj['Linkage']['FamilyTreeMemberOrganization'] = $nestedModifiedRecords;
            }
            //if duns is there then mark it as duplicate
            if (in_array($duns, $dunsArray)) {
                $dnbRecordObj['isDupe'] = true;
            }
            $dnbModifiedRecordsCollection[] = $dnbRecordObj;
        }
        return $dnbModifiedRecordsCollection;
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
     * Lists Records already present in sugar db using the $columnName & $moduleName paramerts
     * @param $columnName
     * @param $moduleName
     * @param $recordIds array (array of id to be used in the in clause of the query)
     * @return array
     */
    private function getExistingRecords($columnName, $moduleName, $recordIds = null)
    {
        $seed = BeanFactory::newBean($moduleName);
        //if duns_num, format all duns to be 0 padded
        // for 9 digits
        if ($columnName == 'duns_num') {
            foreach ($recordIds as &$duns) {
                $duns = str_pad($duns, 9, "0", STR_PAD_LEFT);;
            }
        }
        $options = array();
        $options['offset'] = 0;
        $options['order_by'] = array(array('date_modified', 'DESC'));
        $options['add_deleted'] = true;
        $options['offset'] = 'end';
        $options['module'] = $seed->module_name;
        $options['team_security'] = false;
        $q = new SugarQuery();
        $q->from($seed, $options);
        $fields = array($columnName);
        $q->select($fields);
        $where = $q->where();
        $where->in($columnName, $recordIds);
        $where = $where->queryAnd();
        $where->equals('deleted', 0);
        $q->compileSql();
        $queryResults = $q->execute('json');
        return $queryResults;
    }

    /**
     * Gets Records that are present in the database and in the DNB service reponse
     * These Records are flagged as duplicates using the array key 'isDupe'
     * @param $dnbRecords array (dnb records to be compared with sugar db records)
     * @param $sugarRecords array (array of ids in sugar database)
     * @param $dnbPath string (path to traverse in each of the dnb records to get the recordId)
     * @param $sugarColumnName string (name of column in sugar db to check against)
     * @return array (modified dnb records with duplicates flagged)
     */
    private function getCommonRecords($dnbRecords, $sugarRecords, $dnbPath, $sugarColumnName)
    {
        $sugarRecordIds = $this->underscorePluck($sugarRecords, $sugarColumnName);
        $dnbModifiedRecordsCollection = array();
        foreach ($dnbRecords as &$dnbRecordObj) {
            $recordId = $this->getObjectValue($dnbRecordObj, $dnbPath);
            if (in_array($recordId, $sugarRecordIds)) {
                $dnbRecordObj['isDupe'] = true;
            } else if (!empty($dnbRecordObj['isDupe'])) {
                unset($dnbRecordObj['isDupe']);
            }
            $dnbModifiedRecordsCollection[] = $dnbRecordObj;
        }
        return $dnbModifiedRecordsCollection;
    }

    /**
     * Mimics the pluck function in underscore.js
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
     * Gets the value from an object using the path
     * @param $object array
     * @param $path string
     * @return value mixed Return value if it exists else return null
     */
    private function getObjectValue($object, $path)
    {
        $pathParts = explode(".", $path);
        for ($i = 0; $i < count($pathParts) ; $i++) {
            if (isset($object[$pathParts[$i]])) {
                $object = $object[$pathParts[$i]];
            } else {
                return null;
            }
        }
        return $object;
    }

    /**
     * Sets the value to an object using the path
     * @param array $object
     * @param string $path
     * @param mixed $value
     * @return array
     */
    private function setObjectValue(&$object, $path, $value)
    {
        $tempObj = & $object;
        foreach (explode('.', $path) as $key) {
            if (isset($tempObj[$key])) {
                $tempObj = & $tempObj[$key];
            }
        }
        $tempObj = $value;
    }

    /**
     * Returns a recent valid token if possible
     * @return string An authenticated token or null if authenticated token was unattainable
     */
    private function getRecentToken() {
        $dnbToken = !empty($_SESSION[$this->dnbEnv . 'dnbToken']) ?
            $_SESSION[$this->dnbEnv . 'dnbToken'] : null;
        $dnbTokenIssueTime = !empty($_SESSION[$this->dnbEnv . 'dnbTokenIssueTime']) ?
            $_SESSION[$this->dnbEnv . 'dnbTokenIssueTime'] : null;
        //check if token has expired
        $dnbToken = $this->checkToken($dnbToken, $dnbTokenIssueTime);
        return $dnbToken;
    }

    /**
     * Check if a valid token was procurable
     * @param string token. if not provided, we try to get a valid one
     * @return boolean True if token was procurable, false if not
     */
    public function checkTokenValidity($token = null)
    {
        $dnbToken = $token ?: $this->getRecentToken();
        return isset($dnbToken);
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
        $dnbToken = $this->getRecentToken();
        //check if token is valid
        $GLOBALS['log']->debug("DNB Session Token Get :".$_SESSION[$this->dnbEnv . 'dnbToken']);
        if (!$this->checkTokenValidity($dnbToken)) {
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
        return !(empty($this->dnbUsername) || empty($this->dnbPassword) || empty($this->dnbEnv));
    }

    /**
     * Gets Curl Options Array
     * @param $method GET | POST
     * @param $url
     * @param $headersArray
     * @param $header TRUE | FALSE to set CURLOPT_HEADER
     * @param $data
     * @return array
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
     * @return string token if valid token found, null otherwise
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
     * Return new DNB Authentication to access DNB api. Return null if authentication could not be verified
     * @return string token if valid token found, null otherwise
     */
    private function getAuthenticationToken()
    {
        $username = $this->dnbUsername;
        $password = $this->dnbPassword;
        $token = '';
        $curl_handle = curl_init();
        if (!array_key_exists($this->dnbEnv, $this->dnbBaseURL)) {
            return null;
        }
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
            return null;
        }
        $curl_info = curl_getinfo($curl_handle);
        if ($curl_info['http_code'] === 200) {
            preg_match("/Authorization:\s(\S*)/", $response, $tokenArray);
            if (count($tokenArray) > 0) {
                $token = $tokenArray[1];
            } else {
                return null;
            }
        } else {
            return null;
        }
        $GLOBALS['log']->debug("HTTP client response: $response");
        curl_close($curl_handle);
        $_SESSION[$this->dnbEnv . 'dnbTokenIssueTime'] = time();
        $_SESSION[$this->dnbEnv . 'dnbToken'] = $token;
        $GLOBALS['log']->debug("DNB Session Token Set :".$_SESSION[$this->dnbEnv . 'dnbToken']);
        return $token;
    }

    /**
     * Utility function to check if an array key exists in a nested associative array
     * @param $array
     * @param $path
     * @return bool
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

    /**
     * Checks the D&B List of companies for duns_num already existing in SugarDB
     * @param array $dnbApiResponse
     * @param string $path
     * @return mixed
     */
    private function checkAndMarkDuplicateDuns($dnbApiResponse,$path)
    {
        $dnbCompaniesList = $this->getObjectValue($dnbApiResponse, $path);
        $dnbDunsArray = $this->underscorePluck($dnbCompaniesList, 'DUNSNumber');
        //get the list of duns existing in sugar that match with the above list of duns
        $existingDUNSArray = json_decode($this->getExistingRecords('duns_num', 'Accounts', $dnbDunsArray), true);
        if (count($existingDUNSArray) > 0) {
            //identify the duns common in the api response and in the sugar db and make the dupe
            $modifiedCompaniesList = $this->getCommonRecords($dnbCompaniesList, $existingDUNSArray, 'DUNSNumber', 'duns_num');
            return $modifiedCompaniesList;
        } else {
            return null;
        }
    }

    /**
     * Checks Family tree response for duns_num already existing in SugarDB
     * @param array $dnbFTApiResponse
     * @return mixed
     */
    private function checkAndMarkFTDuplicateDuns($dnbFTApiResponse)
    {
        $familyTree = $this->getObjectValue($dnbFTApiResponse, $this->familyTreePaths['familyTree']);
        //get a list of duns in the family tree response -- recursive function
        $dunsArray = $this->getFamilyTreeDuns($familyTree);
        //adding the duns of the first node in the family tree to the dunsArray
        $firstNodeDuns = $this->getObjectValue($dnbFTApiResponse, $this->familyTreePaths['duns']);
        $firstNodeDuns = str_pad($firstNodeDuns, 9, "0", STR_PAD_LEFT);
        $dunsArray[] = $firstNodeDuns;
        $GLOBALS['log']->debug('ft duns count: ' . count($dunsArray));
        //omit the current DUNS from the dunsArray
        $currentDUNS = $this->getObjectValue($dnbFTApiResponse, $this->familyTreePaths['inquiryDet']);
        if (!empty($currentDUNS) && in_array($currentDUNS, $dunsArray)) {
            $dunsPos = array_search($currentDUNS, $dunsArray);
            // Remove from array
            unset($dunsArray[$dunsPos]);
        }
        //get the list of duns existing in sugar that match with the above list of duns
        $existingDUNSArray = json_decode($this->getExistingRecords('duns_num', 'Accounts', $dunsArray), true);
        if (count($existingDUNSArray) > 0) {
            $commonDuns = $this->underscorePluck($existingDUNSArray, 'duns_num');
            //identify the duns common in the api response and in the sugar db and make the dupe
            $modifiedFamilyTree = $this->markFamilyTreeDuplicates(
                $this->getObjectValue($dnbFTApiResponse, $this->familyTreePaths['familyTree']),
                $commonDuns
            );
            //marking the first node as duplicate if it is
            if (in_array($firstNodeDuns, $commonDuns)) {
                $dnbFTApiResponse['OrderProductResponse']['OrderProductResponseDetail']['Product']['Organization']['isDupe'] = true;
            }
            if ($modifiedFamilyTree) {
                $dnbFTApiResponse['OrderProductResponse']['OrderProductResponseDetail']['Product']['Organization']['Linkage']['FamilyTreeMemberOrganization'] = $modifiedFamilyTree;
            }
        }
        return $dnbFTApiResponse;
    }

    /**
     * Checks the D&B List of contacts for contacts already existing in SugarDB
     * @param array $dnbApiResponse
     * @param string $path
     * @return mixed
     */
    private function checkAndMarkDuplicateContacts($dnbApiResponse,$path)
    {
        $dnbContactsList = $this->getObjectValue($dnbApiResponse, $path);
        // get existing contacts
        $dnbPrincIdArray = array();
        //get the list of dnb principal ids from the above list of contacts
        $this->underscoreEach(
            $dnbContactsList,
            function ($contactObj) use (&$dnbPrincIdArray) {
                $dnbPrincIdArray[] = $contactObj['PrincipalIdentificationNumberDetail'][0]['PrincipalIdentificationNumber'];
            }
        );
        //get the list of principal ids existing in sugar that match with the above list of principal ids
        $existingPrincIdArray = json_decode($this->getExistingRecords('dnb_principal_id', 'Contacts', $dnbPrincIdArray), true);
        if (count($existingPrincIdArray) > 0) {
            //identify the contacts common in the api response and in the sugar db and mark the dupe
            $modifiedContactsList = $this->getCommonRecords($dnbContactsList, $existingPrincIdArray, $this->commonJsonPaths['principalIdPath'], 'dnb_principal_id');
            if ($modifiedContactsList && count($modifiedContactsList) > 0) {
                $this->setObjectValue($dnbApiResponse, $path, $modifiedContactsList);
            }
        }
        return $dnbApiResponse;
    }

    /**
     * API used to check for duplicates in D&B API Response
     * This API is primarily being created to check the browser cached responses for duplicates
     * @param array $dupeCheckParams
     * $dupeCheckParams must have two keys
     * 1. type -- Currently two possible values (duns,contacts)
     * 2. apiResponse -- The api response to be marked as duplicates
     * 3. module -- findcompany, competitors, cleansematch, familytree, contacts
     * @return array
     */
    public function dupeCheck($dupeCheckParams)
    {
        //validate parameters
        if (empty($dupeCheckParams['type']) || empty($dupeCheckParams['apiResponse']) || empty($dupeCheckParams['module'])) {
            return array('error' => 'ERROR_EMPTY_PARAM');
        }
        $type = $dupeCheckParams['type'];
        $apiResponse = $dupeCheckParams['apiResponse'];
        $module = $dupeCheckParams['module'];
        if ($type === 'duns') {
            if ($module === 'familytree') {
                $modifiedApiResponse = $this->checkAndMarkFTDuplicateDuns($apiResponse);
                return $modifiedApiResponse;
            } else {
                $path = $this->commonJsonPaths[$module];
                if (!empty($path)) {
                    $modifiedApiResponse = $this->checkAndMarkDuplicateDuns($apiResponse, $path);
                    if (!empty($modifiedApiResponse)) {
                        $this->setObjectValue($apiResponse, $path, $modifiedApiResponse);
                    }
                    return $apiResponse;
                } else {
                    return array('error' => 'ERROR_INVALID_MODULE_NAME');
                }
            }
        } else if ($type === 'contacts') {
            $path = $this->commonJsonPaths[$module];
            if (!empty($path)) {
                $modifiedApiResponse = $this->checkAndMarkDuplicateContacts($apiResponse, $path);
                if (!empty($modifiedApiResponse)) {
                    return $modifiedApiResponse;
                } else {
                    return $apiResponse;
                }
            } else {
                return array('error' => 'ERROR_INVALID_MODULE_NAME');
            }
        }
    }
}