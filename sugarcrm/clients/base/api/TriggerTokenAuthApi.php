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

class TriggerTokenAuthApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'verifyToken' => array(
                'reqType' => 'POST',
                'path' => array('trigger', 'token', 'verify'),
                'pathVars' => array(''),
                'method' => 'verifyTriggerToken',
                'shortHelp' => "Verifies trigger server token",
                'longHelp' => 'include/api/help/verify_trigger_token_help.html',
                'noLoginRequired' => true,
                'noEtag' => true,
                'ignoreMetaHash' => true,
                'ignoreSystemStatusError' => true,
            )
        );
    }

    /**
     * Function verifies trigger token.
     *
     * @param ServiceBase $api
     * @param array $args
     * @return string
     * @throws SugarApiExceptionEditConflict when tokens are not the same
     * @throws SugarApiExceptionMissingParameter when args does not contain all required data
     */
    public function verifyTriggerToken(ServiceBase $api, array $args)
    {
        if (empty($args['original']) || empty($args['verified'])) {
            throw new SugarApiExceptionMissingParameter();
        }

        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('auth');

        if ($config['trigger_server_token'] != $args['original']) {
            throw new SugarApiExceptionEditConflict();
        }

        $admin->saveSetting('auth', 'trigger_server_token', $args['verified'], 'base');

        return;
    }
}
