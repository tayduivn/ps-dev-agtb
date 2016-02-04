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
namespace Sugarcrm\Sugarcrm\Dav\Base\Auth;

use Sabre\DAV\Auth\Backend;

/**
 * Provide DAV clients authentication using SugarCRM username and password
 * Class SugarAuth
 * @package Sugarcrm\Sugarcrm\Dav\Base\Auth
 */
class SugarAuth extends Backend\AbstractBasic
{
    protected $principalPrefix = 'principals/users/';

    /**
     * @inheritdoc
     */
    protected function validateUserPass($username, $password)
    {
        $auth = $this->getSugarAuthController();
        if ($auth) {
            $authResult = $auth->login($username, $password, array('noRedirect' => true));
            $currentUser = $this->getCurrentUser();
            if ($authResult && $currentUser && $currentUser->load_relationship('email_addresses_primary')) {
                return (bool)$currentUser->email_addresses_primary->getBeans(array('where' => 'primary_address = 1'));
            }
        }

        return false;
    }

    /**
     * Get current user
     * @return \User | null
     */
    protected function getCurrentUser()
    {
        return isset($GLOBALS['current_user']) ? $GLOBALS['current_user'] : null;
    }

    /**
     * Retrieve SugarAuthenticate controller for check username and password
     * @return \AuthenticationController
     */
    protected function getSugarAuthController()
    {
        return \AuthenticationController::getInstance('SugarAuthenticate');
    }
}
