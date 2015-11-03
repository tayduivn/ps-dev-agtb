<?php
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

namespace Sugarcrm\Sugarcrm\Security\InputValidation;

/**
 *
 * This class is used by the Request class to represent the different
 * superglobals which are considered raw user input. Do not instantiate
 * this class directly but make use of InputValidation instead.
 *
 * The following superglobals are supported:
 *
 *  $_GET
 *  $_POST
 *  $_REQUEST (*)
 *
 * (*) Note that PHP populates the $_REQUEST superglobal automatically based
 * on the request_order directive in php.ini. The default advized value is GP
 * meaning that $_POST will overwrite already existing $_GET parameters.
 *
 * We take measure into our own hands which will enforce GP request_order
 * regardless of the php.ini setting when accessing $_REQUEST parameters
 * using this class.
 *
 */
class Superglobals
{
    const GET = 'GET';
    const POST = 'POST';
    const REQUEST = 'REQUEST';

    /**
     * Raw $_GET values
     * @var array
     */
    private $rawGet = array();

    /**
     * Raw $_POST values
     * @var array
     */
    private $rawPost = array();

    /**
     * Ctor
     * @param array $rawGet Key value pairs from $_GET
     * @param array $rawPost Key value pairs from $_POST
     */
    public function __construct(array $rawGet, array $rawPost)
    {
        $this->rawGet = $rawGet;
        $this->rawPost = $rawPost;
    }

    /**
     * Set $_GET value
     * @param string $key
     * @param mixed $value
     */
    public function setRawGet($key, $value)
    {
        $this->rawGet[$key] = $value;
    }

    /**
     * Set $_POST value
     * @param string $key
     * @param mixed $value
     */
    public function setRawPost($key, $value)
    {
        $this->rawPost[$key] = $value;
    }

    /**
     * Check if given $_GET parameter is available
     * @param string $key
     * @return boolean
     */
    public function hasRawGet($key)
    {
        return isset($this->rawGet[$key]);
    }

    /**
     * Check if given $_POST parameter is available
     * @param string $key
     * @return boolean
     */
    public function hasRawPost($key)
    {
        return isset($this->rawPost[$key]);
    }

    /**
     * Check if given $_REQUEST parameter is available
     * @param string $key
     * @return boolean
     */
    public function hasRawRequest($key)
    {
        return $this->hasRawPost($key) ? true : $this->hasRawGet($key);
    }

    /**
     * Get raw $_GET value
     * @param string $key Key of the $_GET parameter
     * @param mixed $default Default value to return if key not found
     * @return mixed
     */
    public function getRawGet($key, $default = null)
    {
        return $this->hasRawGet($key) ? $this->rawGet[$key] : $default;
    }

    /**
     * Get raw $_POST value
     * @param string $key Key of the $_POST parameter
     * @param mixed $default Default value to return if key not found
     * @return mixed
     */
    public function getRawPost($key, $default = null)
    {
        return $this->hasRawPost($key) ? $this->rawPost[$key] : $default;
    }

    /**
     * Get raw $_REQUEST value
     * @param string $key Key of the $_REQUEST parameter
     * @param mixed $default Default value to return if key not found
     * @return mixed
     */
    public function getRawRequest($key, $default = null)
    {
        return $this->hasRawPost($key) ? $this->getRawPost($key) : $this->getRawGet($key, $default);
    }
}
