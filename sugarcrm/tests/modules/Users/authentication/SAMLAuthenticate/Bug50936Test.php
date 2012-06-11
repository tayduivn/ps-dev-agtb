<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
require_once('modules/Users/authentication/AuthenticationController.php');
require_once('tests/modules/Users/AuthenticateTest.php');

/**
 * Bug50936Test.php
 * This tests that we can correctly load a custom settings.php file for SAMLAuthentication when called from
 * modules/Users/authentication/SAMLAuthenticate/index.php
 *
 * This tests mimics the contents of modules/Users/authentication/SAMLAuthenticate/index.php by placing it
 * in a custom directory minus the header() function call.  We can't include that because it'd just cause other issues
 */
class Bug50936Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    var $customContents;

	public function setUp()
    {
        $GLOBALS['app'] = new SugarApplication();
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$this->sugar_config_old = $GLOBALS['sugar_config'];
    	$_REQUEST['user_name'] = 'foo';
    	$_REQUEST['user_password'] = 'bar';
    	$_SESSION['authenticated_user_id'] = true;
    	$_SESSION['hasExpiredPassword'] = false;
    	$_SESSION['isMobile'] = null;
        $GLOBALS['sugar_config']['authenticationClass'] = 'SAMLAuthenticate';
        $GLOBALS['sugar_config']['SAML_X509Cert'] = 'Bug50936_X509Cert';

        //Create the custom directory if it does not exist
        if(!is_dir('custom/modules/Users/authentication/SAMLAuthenticate')) {
           mkdir_recursive('custom/modules/Users/authentication/SAMLAuthenticate');
        }

$contents = <<<EOQ
<?php
        require('modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml.php');
        require(get_custom_file_if_exists('modules/Users/authentication/SAMLAuthenticate/settings.php'));

        \$authrequest = new SamlAuthRequest(\$settings);
        \$url = \$authrequest->create();
        echo \$url;
EOQ;

        file_put_contents('custom/modules/Users/authentication/SAMLAuthenticate/index.php', $contents);

$contents = <<<EOQ
<?php
                // this function should be modified to return the SAML settings for the current use
                \$settings = new SamlSettings();
                // when using Service Provider Initiated SSO (starting at index.php), this URL asks the IdP to authenticate the user.
                \$settings->idp_sso_target_url = 'www.sugarcrm.com';

                // the certificate for the users account in the IdP
                \$settings->x509certificate = \$GLOBALS['sugar_config']['SAML_X509Cert'];

                // The URL where to the SAML Response/SAML Assertion will be posted
                \$settings->assertion_consumer_service_url = \$GLOBALS['sugar_config']['site_url']. "/index.php?module=Users&action=Authenticate";

                // Name of this application
                \$settings->issuer = "php-saml";

                // Tells the IdP to return the email address of the current user
                \$settings->name_identifier_format = "urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress";

        ?>
EOQ;

        if(file_exists('custom/modules/Users/authentication/SAMLAuthenticate/settings.php')) {
           $this->customContents = file_get_contents('custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
        }

        file_put_contents('custom/modules/Users/authentication/SAMLAuthenticate/settings.php', $contents);
	}

	public function tearDown()
	{
        //If we had a custom settings.php file already, just restore it
        if(!empty($this->customContents))
        {
            file_put_contents('custom/modules/Users/authentication/SAMLAuthenticate/settings.php', $this->customContents);
        } else {
            unlink('custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
        }

        //Remove the test index.php file
        if(file_exists('custom/modules/Users/authentication/SAMLAuthenticate/index.php'))
        {
            unlink('custom/modules/Users/authentication/SAMLAuthenticate/index.php');
        }

	    unset($GLOBALS['current_user']);
	    $GLOBALS['sugar_config'] = $this->sugar_config_old;
	    unset($_REQUEST['login_module']);
        unset($_REQUEST['login_action']);
        unset($_REQUEST['login_record']);
        unset($_REQUEST['user_name']);
        unset($_REQUEST['user_password']);
        unset($_SESSION['authenticated_user_id']);
        unset($_SESSION['hasExpiredPassword']);
        unset($_SESSION['isMobile']);
	}

    public function testLoadCustomSettingsFromIndex()
    {
        require('custom/modules/Users/authentication/SAMLAuthenticate/index.php');
        $this->expectOutputRegex('/www\.sugarcrm\.com/', 'Failed to override custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
    }


}
?>
