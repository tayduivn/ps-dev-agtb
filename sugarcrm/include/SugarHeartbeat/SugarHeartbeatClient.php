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

//BWC: nusoap library has been moved to vendor directory in 7+
if(file_exists('vendor/nusoap/nusoap.php')) {
    require_once 'vendor/nusoap/nusoap.php';
} else if(file_exists("include/nusoap/nusoap.php")) {
    require_once "include/nusoap/nusoap.php";
}

/**
 * Class SugarHeartbeatClient
 *
 * SoapClient for Sugar's heartbeat server. Currently we are using nusoap
 * because SoapClient is not a required extension for SugarCRM.
 */
class SugarHeartbeatClient extends nusoap_client
{
    /**
     * We don't use WSDL mode to avoid more traffic to the heartbeat server.
     *
     * @var string Endpoint url
     */
    const ENDPOINT = 'https://updates.sugarcrm.com/heartbeat/soap.php';

    /**
     * These parameters are already SoapClient compatible when moving away
     * from nusoap in the future.
     *
     * @var array SoapClient options
     */
    protected $defaultOptions = array(
        'connection_timeout' => 15,
        'exceptions' => 0 // unused for nusoap
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        $timeout = $this->defaultOptions['connection_timeout'];
        parent::__construct(self::ENDPOINT, false, false, false, false, false, $timeout);
    }

    /**
     * Proxy to sugarPing WSDL method
     *
     * @return mixed
     */
    public function sugarPing()
    {
        return $this->call('sugarPing', array());
    }

    /**
     * Proxy to sugarHome WSDL method
     * Encodes $info
     *
     * @param string $key License key
     * @param array $info
     * @return mixed
     */
    public function sugarHome($key, array $info)
    {
        $data = $this->encode($info);
        return $this->call('sugarHome', array('key' => $key, 'data' => $data));
    }

    /**
     * Serialize + Base64
     * @see SugarHeartbeatClient::sugarHome
     *
     * @param $value
     * @return string
     */
    protected function encode($value)
    {
        return base64_encode(serialize($value));
    }

    /**
     * Base64 decode + Unsterilize
     * @see SugarHeartbeatClient::sugarHome
     *
     * @param $value
     * @return mixed
     */
    protected function decode($value)
    {
        return unserialize(base64_decode($value));
    }
}
