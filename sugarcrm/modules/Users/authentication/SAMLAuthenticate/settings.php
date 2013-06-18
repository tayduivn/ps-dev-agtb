<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'modules/Users/authentication/SAMLAuthenticate/saml.php';

$settings                           = new OneLogin_Saml_Settings();

// when using Service Provider Initiated SSO (starting at index.php), this URL asks the IdP to authenticate the user.
$settings->idpSingleSignOnUrl       = isset($GLOBALS['sugar_config']['SAML_loginurl']) ? $GLOBALS['sugar_config']['SAML_loginurl'] : '';

// the certificate for the users account in the IdP
$settings->idpPublicCertificate          = isset($GLOBALS['sugar_config']['SAML_X509Cert']) ? $GLOBALS['sugar_config']['SAML_X509Cert'] : '';

// The URL where to the SAML Response/SAML Assertion will be posted
$settings->spReturnUrl = htmlspecialchars($GLOBALS['sugar_config']['site_url']. "/rest/v10/oauth2/saml");

// Name of this application
$settings->spIssuer                         = isset($GLOBALS['sugar_config']['SAML_issuer']) ? $GLOBALS['sugar_config']['SAML_issuer'] :"php-saml";

// Tells the IdP to return the email address of the current user
$settings->requestedNameIdFormat = OneLogin_Saml_Settings::NAMEID_EMAIL_ADDRESS;
