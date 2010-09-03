<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 *(i) the "Powered by SugarCRM" logo and
 *(ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright(C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
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
    $settings->idp_sso_target_url       = $GLOBALS['sugar_config']['SAML_loginurl'];
    
    // the certificate for the users account in the IdP
    $settings->x509certificate          = $GLOBALS['sugar_config']['SAML_X509Cert'];

    return $settings;
  }
  
?>
