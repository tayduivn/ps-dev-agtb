<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
********************************************************************************/

require_once('modules/Users/authentication/SAMLAuthenticate/SAMLAuthenticate.php');

class SamlAuthTest extends  Sugar_PHPUnit_Framework_TestCase
{
    public function startUp()
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile('custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testSettingsConfig()
    {
        global $sugar_config;
        $sugar_config['SAML_loginurl'] = 'loginURL';
        $sugar_config['SAML_X509Cert'] = 'TestCert';
        $sugar_config['SAML_issuer'] = 'testIssuer';
        $settings = SAMLAuthenticate::loadSettings();
        $this->assertEquals('loginURL', $settings->idpSingleSignOnUrl);
        $this->assertEquals('TestCert', $settings->idpPublicCertificate);
        $this->assertEquals('testIssuer', $settings->spIssuer);
    }

    public function testSettingsBC()
    {
        $contents = <<<EOQ
<?php
\$settings = new SamlSettings();
\$settings->idp_sso_target_url = 'www.sugarcrm.com';
\$settings->x509certificate = 'TestCert';
\$settings->assertion_consumer_service_url = 'testURL';
\$settings->issuer = "php-saml";
\$settings->name_identifier_format = "testID";
\$settings->saml_settings['check']['user_name'] = '//root';
EOQ;
       SugarAutoLoader::put('custom/modules/Users/authentication/SAMLAuthenticate/settings.php', $contents);
       $settings = SAMLAuthenticate::loadSettings();
       $this->assertEquals('www.sugarcrm.com', $settings->idpSingleSignOnUrl);
       $this->assertEquals('TestCert', $settings->idpPublicCertificate);
       $this->assertEquals('testURL', $settings->spReturnUrl);
       $this->assertEquals('testID', $settings->requestedNameIdFormat);
       $this->assertTrue($settings->useXML);
       $this->assertEquals('//root', $settings->saml2_settings['check']['user_name']);
    }

    public function testSettingsIssuer()
    {
        global $sugar_config;
        $sugar_config['SAML_issuer'] = 'testIssuer';
        $contents = <<<EOQ
<?php
\$settings = new SamlSettings();
\$settings->idp_sso_target_url = 'www.sugarcrm.com';
\$settings->x509certificate = 'TestCert';
\$settings->assertion_consumer_service_url = 'testURL';
\$settings->issuer = "php-saml";
\$settings->name_identifier_format = "testID";
EOQ;
        SugarAutoLoader::put('custom/modules/Users/authentication/SAMLAuthenticate/settings.php', $contents);
        $settings = SAMLAuthenticate::loadSettings();
        $this->assertObjectNotHasAttribute('useXML', $settings);
        $this->assertEquals('testIssuer', $settings->spIssuer);
    }

}
