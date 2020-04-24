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

class EAPMViewMicrosoftOauth2Redirect extends SugarView
{
    private $api;

    /**
     * {@inheritDoc}
     *
     * @param array $params Ignored
     */
    public function process($params = array())
    {
        global $sugar_config;
        $authResult = $this->authenticate();

        if (!empty($authResult)) {
            $response = array(
                'result' => true,
                'hasRefreshToken' => !empty($authResult['token']->getRefreshToken()),
                'eapmId' => $authResult['eapmId'],
                'emailAddress' => $this->api->getEmailAddress($authResult['eapmId']),
            );
        } else {
            $response = array(
                'result' => false,
            );
        }

        $this->ss->assign('response', $response);
        $this->ss->assign('siteUrl', $sugar_config['site_url']);
        $this->ss->display('modules/EAPM/tpls/MicrosoftOauth2Redirect.tpl');
    }

    protected function authenticate()
    {
        if (!isset($_REQUEST['code'])) {
            return false;
        }

        $this->api = new ExtAPIMicrosoftEmail();
        return $this->api->authenticate($_REQUEST['code']);
    }
}
