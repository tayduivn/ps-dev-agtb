<?php
  // these are account wide configuration settings

  // the URL where to the SAML Response/SAML Assertion will be posted
  define('const_assertion_consumer_service_url', $GLOBALS['sugar_config']['site_url']. "/index.php?module=Users&action=Authenticate");
  // name of this application
  define('const_issuer', "php-saml");
  // tells the IdP to return the email address of the current user
   define('const_name_identifier_format', "urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress");

  function get_user_settings() {
    // this function should be modified to return the SAML settings for the current user

    $settings                           = new Settings();
    // when using Service Provider Initiated SSO (starting at index.php), this URL asks the IdP to authenticate the user. 
    $settings->idp_sso_target_url       = "https://app.onelogin.com/saml/signon/8910";
    // the certificate for the users account in the IdP
    $settings->x509certificate          = "-----BEGIN CERTIFICATE-----
MIIBrTCCAaGgAwIBAgIBATADBgEAMGcxCzAJBgNVBAYTAlVTMRMwEQYDVQQIDApD
YWxpZm9ybmlhMRUwEwYDVQQHDAxTYW50YSBNb25pY2ExETAPBgNVBAoMCE9uZUxv
Z2luMRkwFwYDVQQDDBBhcHAub25lbG9naW4uY29tMB4XDTEwMDcyNzIxMTIxNloX
DTE1MDcyNzIxMTIxNlowZzELMAkGA1UEBhMCVVMxEzARBgNVBAgMCkNhbGlmb3Ju
aWExFTATBgNVBAcMDFNhbnRhIE1vbmljYTERMA8GA1UECgwIT25lTG9naW4xGTAX
BgNVBAMMEGFwcC5vbmVsb2dpbi5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJ
AoGBAMQ9x6q9iL50BEmrUd+4PlBFQGWGYMPKDvGtIu70q5btYXnhX/yJw+RpR7w5
aTIxfdxSTE6mVIX5AS207Ns0sQrrK4XLSWrAqfgIdBKbf4gN6PqjBG4P/escPJFQ
vrZ2+kGyQqCRrehR3IU9SEOXXFElMdW+LA2qVFA+CL7BgXYVAgMBAAEwAwYBAAMB
AA==
-----END CERTIFICATE-----";

    return $settings;
  }
  
?>