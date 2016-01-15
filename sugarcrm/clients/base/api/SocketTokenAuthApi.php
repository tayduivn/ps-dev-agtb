<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

/**
 * Class SocketTokenAuthApi
 */
class SocketTokenAuthApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'verifyToken' => array(
                'reqType' => 'POST',
                'path' => array('verifySocketToken'),
                'pathVars' => array(''),
                'method' => 'verifySocketToken',
                'shortHelp' => "Verifies socket token",
                'longHelp' => 'include/api/help/verify_socket_token_help.html',
                'noLoginRequired' => true,
                'noEtag' => true,
                'ignoreMetaHash' => true,
                'ignoreSystemStatusError' => true,
            )
        );
    }

    /**
     * Function verifies socket token.
     *
     * @param ServiceBase $api
     * @param array $args
     * @return string
     * @throws SugarApiExceptionEditConflict when tokens are not the same
     * @throws SugarApiExceptionMissingParameter when args does not contain all required data
     */
    public function verifySocketToken(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('original', 'verified'));

        $admin = BeanFactory::getBean('Administration');
        /* @var $config Administration */
        $config = $admin->getConfigForModule('auth');

        if ($config['socket_token'] != $args['original']) {
            throw new SugarApiExceptionEditConflict();
        }

        $admin->saveSetting('auth', 'socket_token', $args['verified'], 'base');

        return json_encode(array(
            'verified' => true,
            'token' => $args['verified']
        ));
    }
}
