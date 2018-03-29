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

use Sugarcrm\Sugarcrm\IdentityProvider\OAuth2StateRegistry;

class UsersViewOAuth2Authenticate extends SidecarView
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = false;
    }

    /**
     * @inheritdoc
     */
    public function preDisplay($params = array()) : void
    {
        $code = $this->request->getValidInputGet('code');
        $scope = $this->request->getValidInputGet('scope');
        $state = $this->request->getValidInputGet('state');
        if (!$code || !$scope || !$state) {
            $this->redirect();
        }

        $stateRegistry = $this->getStateRegistry();
        $isStateRegistered = $stateRegistry->isStateRegistered($state);
        $stateRegistry->unregisterState($state);
        if (!$isStateRegistered) {
            $this->redirect();
        }

        $oAuthServer = \SugarOAuth2Server::getOAuth2Server();

        try {
            $this->authorization = $oAuthServer->grantAccessToken([
                'grant_type' => 'authorization_code',
                'code' => $code,
                'scope' => $scope,
            ]);
        } catch (\Exception $e) {
            $this->redirect();
        }

        parent::preDisplay($params);
    }

    /**
     * @return OAuth2StateRegistry
     */
    protected function getStateRegistry() : OAuth2StateRegistry
    {
        return new OAuth2StateRegistry();
    }

    /**
     * Redirects to the main page.
     */
    protected function redirect(): void
    {
        $sugarConfig = \SugarConfig::getInstance();
        SugarApplication::redirect($sugarConfig->get('site_url'));
    }
}
