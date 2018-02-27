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

use Sugarcrm\Sugarcrm\Util\Uuid;

class WebToLeadTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $campaign_id;
    private $configOptoutBackUp;

    protected function setUp()
    {
        if (is_null($this->configOptoutBackUp) && isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $campaign = SugarTestCampaignUtilities::createCampaign();
        $this->campaign_id = $campaign->id;

        $this->config_file_override = '';
        if (file_exists('config_override.php')) {
            $this->config_file_override = file_get_contents('config_override.php');
        } else {
            $this->config_file_override = '<?php' . "\r\n";
        }
    }

    protected function tearDown()
    {
        SugarAutoLoader::put('config_override.php', $this->config_file_override);

        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        if (!is_null($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        }
    }

    public function optoutDataProvider()
    {
        return array(
            array(
                false,
                array(),
                0,
            ),
            array(
                false,
                array('email_opt_out' => true),
                1,
            ),
            array(
                false,
                array('email_opt_in' => 'on'),
                0,
            ),
            array(
                false,
                array('email_opt_out' => 'off'),
                1,
            ),
            array(
                false,
                array(
                    'email_opt_in' => 'on',
                    'email_opt_out' => true,
                ),
                0,
            ),
            array(
                false,
                array(
                    'email_opt_in' => 'off',
                    'email_opt_out' => true,
                ),
                1,
            ),
            array(
                true,
                array(),
                1,
            ),
            array(
                true,
                array('email_opt_out' => true),
                1,
            ),
            array(
                true,
                array('email_opt_in' => 'on'),
                0,
            ),
            array(
                true,
                array('email_opt_out' => 'off'),
                1,
            ),
            array(
                true,
                array(
                    'email_opt_in' => 'on',
                    'email_opt_out' => true,
                ),
                0,
            ),
            array(
                true,
                array(
                    'email_opt_in' => 'off',
                    'email_opt_out' => true,
                ),
                1,
            ),
        );
    }

    /**
     * Create Web Lead with Email Address and test the different scenarios that can determine the Email Optout setting.
     *
     * @dataProvider optoutDataProvider
     */
    public function testWebToLeadRequest($configDefaultValue, $formVars, $expectedResult)
    {
        $this->setConfigOptout($configDefaultValue);

        $emails = array();
        for ($i = 0; $i <= 1; $i++) {
            $emails[] = "test{$i}_" . Uuid::uuid1() . '@testonly.app';
        }

        $requestId = Uuid::uuid1();
        $_POST = array
        (
            'first_name' => 'TestFirstName',
            'last_name' => 'TestLastName',
            'campaign_id' => $this->campaign_id,
            'redirect_url' => 'http://www.sugarcrm.com/index.php',
            'assigned_user_id' => '1',
            'team_id' => '1',
            'team_set_id' => 'Global',
            'email' => $emails[0],
            'email2' => $emails[1],
            'req_id' => $requestId,
        );
        foreach ($formVars as $key => $value) {
            $_POST[$key] = $value;
        }

        $postString = '';
        foreach ($_POST as $k => $v) {
            $postString .= "{$k}=" . urlencode($v) . '&';
        }
        $postString = rtrim($postString, '&');

        $ch = curl_init("{$GLOBALS['sugar_config']['site_url']}/index.php?entryPoint=WebToLeadCapture");
        curl_setopt($ch, CURLOPT_POST, count($_POST) + 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        ob_start();
        curl_exec($ch);
        $output = ob_get_clean();

        // First, save ids of created emails so they can be deleted at tearDown.
        foreach ($emails as $email) {
            SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress($email);
        }

        // Lastly, verify email addresses were successfully created with the expected address properties.
        for ($i = 0; $i < count($emails); $i++) {
            $ea = $this->fetchEmailAddress($emails[$i]);
            $this->assertNotEmpty($ea, 'Expected Email Address was not found: ' . $emails[$i]);
            $this->assertEquals(0, $ea['invalid_email'], 'Expected Email Address to be Valid');
            $this->assertEquals(
                $expectedResult,
                $ea['opt_out'],
                'Email Address [' . $i . '] opt_out value incorrect'
            );
        }
    }

    private function fetchEmailAddress($emailAddress = '')
    {
        $sea = BeanFactory::newBean('EmailAddresses');
        $q = new SugarQuery();
        $q->select(array('*'));
        $q->from($sea);
        $q->where()->queryAnd()
            ->equals('email_address_caps', strtoupper($emailAddress))
            ->equals('deleted', 0);
        $q->limit(1);
        $rows = $q->execute();
        if (empty($rows)) {
            return array();
        }
        return $rows[0];
    }

    private function setConfigOptout(bool $optOut)
    {
        if ($optOut) {
            $new_line = '$sugar_config[\'new_email_addresses_opted_out\'] = true;';
        } else {
            $new_line = '$sugar_config[\'new_email_addresses_opted_out\'] = false;';
        }

        SugarAutoLoader::put('config_override.php', $this->config_file_override . "\r\n" . $new_line, true);
    }
}
