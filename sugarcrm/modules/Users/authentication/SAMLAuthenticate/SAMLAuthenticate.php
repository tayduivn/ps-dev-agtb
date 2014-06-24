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

require_once 'modules/Users/authentication/SugarAuthenticate/SugarAuthenticate.php';
require_once 'modules/Users/authentication/SugarAuthenticate/SugarAuthenticateExternal.php';

/**
 * This file is used to control the authentication process.
 * It will call on the user authenticate and controll redirection
 * based on the users validation
 */
class SAMLAuthenticate extends SugarAuthenticate implements SugarAuthenticateExternal
{
    var $userAuthenticateClass = 'SAMLAuthenticateUser';
    var $authenticationDir = 'SAMLAuthenticate';

    /**
     * pre_login
     *
     * Override the pre_login function from SugarAuthenticate so that user is
     * redirected to SAML entry point if other is not specified
     */
    function pre_login()
    {
        parent::pre_login();

        if (empty($_REQUEST['no_saml'])) {
            SugarApplication::redirect('?entryPoint=SAML');
        }
    }

    /**
     * Called when a user requests to logout
     *
     * Override default behavior. Redirect user to special "Logged Out" page in
     * order to prevent automatic logging in.
     */
    public function logout()
    {
        session_destroy();
        ob_clean();
        header('Location: index.php?module=Users&action=LoggedOut');
        sugar_cleanup(true);
    }

    /**
     * Get URL to follow to get logged in
     */
    public function getLoginUrl()
    {
        $authrequest = new OneLogin_Saml_AuthRequest(self::loadSettings());
        return $authrequest->getRedirectUrl();
    }

    /**
     * Load SAML settings
     * @return OneLogin_Saml_Settings
     */
    public static function loadSettings()
    {
        $settings = null;
        require_once 'modules/Users/authentication/SAMLAuthenticate/saml.php';
        require SugarAutoLoader::existingCustomOne('modules/Users/authentication/SAMLAuthenticate/settings.php');
        // BC conversion
        if ($settings instanceof SamlSettings) {
            $oldsettings = $settings;
            // reload default defs with correct class
            require 'modules/Users/authentication/SAMLAuthenticate/settings.php';
            foreach (get_object_vars($oldsettings) as $k => $v) {
                $settings->$k = $v;
            }
        }

        $settings_map = array('idp_sso_target_url' => 'idpSingleSignOnUrl',
            'x509certificate' => 'idpPublicCertificate',
            'assertion_consumer_service_url' => 'spReturnUrl',
            'name_identifier_format' => 'requestedNameIdFormat'
        );

        foreach ($settings_map as $old => $new) {
            if (!empty($settings->$old)) {
                $settings->$new = $settings->$old;
            }
        }

        if ($settings->spIssuer == 'php-saml' && !empty($settings->issuer) &&
             $settings->issuer != 'php-saml') {
            $settings->spIssuer = $settings->issuer;
        }

        if (!isset($settings->useXML)) {
            foreach (array('update', 'create', 'check') as $saml_item) {
                if (!empty($oldsettings->saml_settings[$saml_item])) {
                    $settings->useXML = true;
                    $settings->saml2_settings[$saml_item] = $oldsettings->saml_settings[$saml_item];
                }
            }
        }

        return $settings;
    }
}

/**
* Stub class for BC
*/
class SamlSettings {}
