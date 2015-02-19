<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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
     * @param $api
     * @param $args
     * @return string
     */
    public function verifySocketToken($api, $args)
    {
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('auth');

        if (!empty($args['original']) && !empty($args['verified'])
            && $config['socket_token'] == $args['original']
        ) {
            $admin->saveSetting('auth', 'socket_token', $args['verified'], 'base');

            return json_encode(array(
                'verified' => true,
                'token' => $args['verified']
            ));
        } else {
            throw new SugarApiExceptionEditConflict();
        }
    }
}
