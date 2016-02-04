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

/**
 * Class SocketTokenAuthApi receives requests from trigger and socket servers
 * to verify and save verification token.
 */
class TokenVerificationApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'verifyToken' => array(
                'reqType' => 'POST',
                'path' => array('verify', 'token'),
                'pathVars' => array(''),
                'method' => 'verifyToken',
                'shortHelp' => "Verifies server tokens",
                'longHelp' => 'include/api/help/token_verification_help.html',
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
     * @throws SugarApiExceptionEditConflict when tokens are not the same
     * @throws SugarApiExceptionInvalidParameter when id don't equal "socket" or "trigger"
     */
    public function verifyToken(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('id', 'original', 'verified'));

        $allowedIds = SugarConfig::getInstance()->get('external_valid_token_ids', array());

        if (!empty($allowedIds)) {
            if (!in_array($args['id'], $allowedIds)) {
                throw new SugarApiExceptionInvalidParameter();
            }

            /** @var Administration $admin */
            $admin = BeanFactory::getBean('Administration');

            $config = $admin->getConfigForModule('auth');
            if ($config['external_token_' . $args['id']] != $args['original']) {
                throw new SugarApiExceptionEditConflict();
            }

            $admin->saveSetting('auth', 'external_token_' . $args['id'], $args['verified'], 'base');
        }
    }
}
