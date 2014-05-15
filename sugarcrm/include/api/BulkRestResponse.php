<?php
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
* Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
*/

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
        if(empty($this->code)) {
            $this->code = 200;
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
