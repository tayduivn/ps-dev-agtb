<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 */

/**
 * REST response class
 */
class RestResponse extends Zend_Http_Response
{
    // Response encodings
    const RAW = 0;
    const JSON = 1;
    const JSON_HTML = 2;

    /**
     * Response type
     * @var unknown_type
     */
    protected $type = self::RAW;

    public function __construct()
    {
        $this->code = 200;
        $this->version = '1.1';
    }

    /**
     * Set a response header
     * @param string $header
     * @param string $info
     * @return bool
     */
    public function setHeader($header, $info) {
        $this->headers[$header] = $info;
        return $this;
    }

    /**
     * Check if the response headers have a header set
     * @param string $header
     * @return bool
     */
    public function hasHeader($header) {
        return array_key_exists($header, $this->headers);
    }

    /**
     * Set response content
     * @param string $data
     * @return RestResponse
     */
    public function setContent($data)
    {
        $this->body = $data;
        return $this;
    }

    /**
     * Set the response type
     * @param int $type
     * @param bool $resetContentType Reset content type?
     * @return RestResponse
     */
    public function setType($type, $resetContentType = false)
    {
        $this->type = $type;
        if($resetContentType) {
            $this->setContentTypeByType();
        }
        return $this;
    }

    /**
     * Set HTTP status
     * @param int $code
     * @return RestResponse
     */
    public function setStatus($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Set content type according to response type
     * @return RestResponse
     */
    protected function setContentTypeByType()
    {
        if($this->type == self::JSON_HTML) {
            $this->headers["Content-Type"] = "text/html";
        }
        if($this->type == self::JSON) {
            $this->headers["Content-Type"] = "application/json";
        }
        return $this;
    }

    /**
     * Returns content to be sent to the client
     * @return string
     */
    public function sendContent()
    {
        switch($this->type) {
            case self::RAW:
                $response = $this->body;
                break;
            case self::JSON:
            case self::JSON_HTML:
                $response = json_encode($this->body);
                break;
        }
    	if($this->type == self::JSON_HTML) {
    		$response = htmlentities($response);
    	}
    	if(!isset($this->headers["Content-Type"])) {
    	    $this->setContentTypeByType();
    	}
    	return $response;
    }

    /**
     * Send the response headers
     * @return bool
     */
    public function sendHeaders()
    {
    	if(headers_sent()) {
    		return false;
    	}
    	if($this->code != 200) {
    	    $text = self::responseCodeAsText($this->code, $this->version != '1.0');
    	    header("HTTP/{$this->version} {$this->code} {$text}");
    	    $this->headers['Status'] = "{$this->code} {$text}";
    	}
    	foreach($this->headers AS $header => $info) {
    		header("{$header}: {$info}");
    	}
    	return true;
    }

    /**
     * generateETagHeader
     *
     * This function generates the necessary cache headers for using ETags with dynamic content. You
     * simply have to generate the ETag, pass it in, and the function handles the rest.
     *
     * @param string $etag ETag to use for this content.
     */
    protected function generateETagHeader($etag)
    {
    	if(isset($_SERVER["HTTP_IF_NONE_MATCH"])){
    		if($etag == $_SERVER["HTTP_IF_NONE_MATCH"]){
    		    // Same data, clean it up and return 304
    		    $this->body = '';
                $this->headers = array();
                $this->code = 304;
                $this->type = self::RAW;
                return;
    		}
    	}
    	$this->setHeader('ETag', $etag);
    }

    /**
     * Set Post Headers
     * @return bool
     */
    public function setPostHeaders()
    {
    	$this->setHeader('Cache-Control', 'no-cache, must-revalidate');
    	$this->setHeader('Pragma', 'no-cache');
    	$this->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
    	return true;
    }

    /**
     * Send the response out
     */
    public function send()
    {
        $this->sendHeaders();
        echo $this->sendContent();
    }

}

