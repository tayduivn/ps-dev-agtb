<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Dav\Cal\Agent;

use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;

/**
 * Class Plugin
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Agent
 */
class Plugin extends ServerPlugin
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Plugin constructor.
     *
     * @param Validator $validator
     * @param Client $client
     */
    public function __construct(Validator $validator, Client $client)
    {
        $this->validator = $validator;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getPluginName()
    {
        return 'check-supported-client';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(Server $server)
    {
        $this->server = $server;
        $this->server->once('beforeMethod', array($this, 'checkSupportedClient'));
    }

    /**
     * Checking support current client.
     *
     * @throws Forbidden
     */
    public function checkSupportedClient()
    {
        $userAgent = $this->server->httpRequest->getHeader('User-Agent');
        $clientInfo = $this->client->parse($userAgent);
        if (!$this->validator->isSupported($clientInfo)) {
            throw new Forbidden('Client application is unsupported');
        }
    }
}
