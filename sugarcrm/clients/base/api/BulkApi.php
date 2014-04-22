<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

/**
 * Bulk API calls
 *
 */
class BulkApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'bulkCall' => array(
                'reqType' => 'POST',
                'path' => array('bulk'),
                'pathVars' => array(''),
                'method' => 'bulkCall',
                'shortHelp' => 'Bulk API call',
                'longHelp' => 'include/api/help/help_bulk.html',
            ),
        );
    }

    /**
     * Bulk API call
     * @param ServiceBase $api
     * @param array $args
     * @throws SugarApiExceptionMissingParameter
     * @return array
     */
    public function bulkCall($api, $args)
    {
        $this->requireArgs($args,array('requests'));
        $restResp = new BulkRestResponse($_SERVER);
        $restClass = get_class($api);
        foreach($args['requests'] as $name => $request) {
            if(empty($request['url'])) {
                $GLOBALS['log']->fatal("Bulk Api: URL missing for request $name");
                throw new SugarApiExceptionMissingParameter("Invalid request - URL is missing");
            }

            $restReq = new BulkRestRequest($request);
            $restResp->setRequest($name);
            /**
             * @var $rest RestService
             */
            $rest = new BulkRestService($api);
            $rest->setRequest($restReq);
            $rest->setResponse($restResp);
            $rest->execute();

        }
        return $restResp->getResponses();
    }
}

/**
 * Bulk API Rest service class
 * Shortcuts some functions that we don't need to do on bulk requests
 */
class BulkRestService extends RestService
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
        parent::__construct();
    }

    /**
     * Shortcut authentication since we're already authenticated before
     * @see RestService::authenticateUser()
     */
    protected function authenticateUser()
    {
        $this->user = $this->parent->user;
        return array('isLoggedIn' => true, 'exception' => false);
    }

    /**
     * Don't check metadata - top request checks it
     * @see RestService::isMetadataCurrent()
     */
    protected function isMetadataCurrent()
    {
        return true;
    }

    /**
     * Don't load envt - top request loads it
     * @see ServiceBase::loadUserEnvironment()
     */
    protected function loadUserEnvironment()
    {
    }

    /**
     * Never release session
     * @see ServiceBase::releaseSession()
     */
    protected function releaseSession()
    {
    }
}

/**
 * Request class for bulk requests
 */
class BulkRestRequest extends RestRequest
{
    /**
     * Construct request from request data
     * @param array $request
     */
    public function __construct($request)
    {
        $svars = $_SERVER;
        $rvars = array();

        $rvars['__sugar_url'] = $request['url'];
        if(!empty($request['headers'])) {
            foreach($request['headers'] as $hname => $hval) {
                $svars['HTTP_'.str_replace("-", "_", strtoupper($hname))] = $hval;
            }
        }
        if(!empty($request['method'])) {
            $svars['REQUEST_METHOD'] = $request['method'];
        } else {
            $svars['REQUEST_METHOD'] = 'GET';
        }

        if(isset($request['data'])) {
            $this->postContents =  $request['data'];
        }

        parent::__construct($svars, $rvars);
    }
}

/**
 * Response class for bulk requests
 * Aggregates multiple responses on send()
 */
class BulkRestResponse extends RestResponse
{
    /**
     * Current request name
     * @var string
     */
    protected $reqName;

    /**
     * Request results
     * @var array
     */
    protected $results = array();

    /**
     * Set request name
     * @param string $name
     */
    public function setRequest($name)
    {
        $this->reqName = $name;
        return $this;
    }

    /**
     * Get accumulated responses
     * @return array
     */
    public function getResponses()
    {
        return $this->results;
    }

    /**
     * Map of fields to record: RestResponse => JSON
     * @var array
     */
    protected $fieldMap = array(
        'body' => 'contents',
        'headers' => 'headers',
        'code' => 'status',
    );

    /**
     * Instead of sending, record the request data
     * @see RestResponse::send()
     */
    public function send()
    {
        switch($this->type) {
            case self::FILE:
                if(!file_exists($this->filename)) {
                    $this->body = '';
                    $this->headers = array();
                    $this->code = 404;
                } else {
                    $this->setHeader("Content-Length", filesize($this->filename));
                    $this->body = file_get_contents($this->filename);
                }
                break;
            case self::JSON:
            case self::JSON_HTML:
                // keep as-is
                break;
            default:
                 $this->body = $this->processContent();
        }

        foreach($this->fieldMap as $prop => $data) {
            $this->results[$this->reqName][$data] = $this->$prop;
            if(is_array($this->$prop)) {
                $this->$prop = array();
            } else {
                $this->$prop = null;
            }
        }
        // reset type for next one
        $this->type = self::RAW;
    }
}