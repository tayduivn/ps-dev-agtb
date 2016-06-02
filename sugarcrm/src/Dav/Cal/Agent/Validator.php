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

/**
 * Class ValidationSupport
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Agent
 */
class Validator
{
    /**
     * @var array
     */
    protected $supportedClients = array(
        array('Mac OS X', '>', '10.10', 'NativeCalendarApplication', '>', '316'),
        array('iOS', '>', '9.2', 'NativeCalendarApplication', '>=', '1'),
    );

    /**
     * Set supported clients.
     *
     * @param array $supportedClients
     * @return $this
     */
    public function setSupportedClients(array $supportedClients = array())
    {
        $this->supportedClients = array();
        foreach ($supportedClients as $client) {
            if (is_array($client) && (array_keys($client) === range(0, 5))) {
                $this->supportedClients[] = $client;
            }
        }
        return $this;
    }

    /**
     * Client checking support.
     *
     * @param array|null $clientInfo
     * @return bool
     */
    public function isSupported($clientInfo)
    {
        if (!$this->supportedClients && !$clientInfo) {
            return true;
        }

        foreach ($this->supportedClients as $supportedClient) {
            $platformCorrected = (!$supportedClient[0] || $supportedClient[0] == $clientInfo['platformName']) &&
                (!$supportedClient[2] ||
                    version_compare(
                        $clientInfo['platformVersion'],
                        $supportedClient[2],
                        empty($supportedClient[1]) ? '==' : $supportedClient[1]
                    )
                );

            $clientCorrected = (!$supportedClient[3] || $supportedClient[3] == $clientInfo['clientName']) &&
                (!$supportedClient[4] ||
                    version_compare(
                        $clientInfo['clientVersion'],
                        $supportedClient[5],
                        empty($supportedClient[4]) ? '==' : $supportedClient[4]
                    )
                );

            if ($platformCorrected && $clientCorrected) {
                return true;
            }
        }

        return false;
    }
}
