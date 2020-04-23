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

class SearchByModuleMultiVersionTest extends SOAPTestCase
{
    private $account;

    protected function setUp() : void
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        $this->account = SugarTestAccountUtilities::createAccount();
        $GLOBALS['db']->commit();
    }

    public function endpointProvider()
    {
        return [
            ['/service/v3/soap.php', 3],
//            ['/service/v3_1/soap.php', 2],
            ['/service/v4/soap.php', 2],
            ['/service/v4_1/soap.php', 2],
        ];
    }

    /**
     * @dataProvider endpointProvider
     */
    public function testSearchByModule($endpoint, $idIndex)
    {
        $soapURL = $GLOBALS['sugar_config']['site_url'] . $endpoint;

        $this->soapClient = new nusoapclient($soapURL, false, false, false, false, false, 600, 600);

        $this->login();
        $params = [
            'session' => $this->sessionId,
            'search_string' => $this->account->name,
            'modules' => [
                'Accounts',
            ],
            'offset' => 0,
            'max_results' => 30,
        ];

        $actual = $this->soapClient->call('search_by_module', $params);
        $this->assertGreaterThan(0, count($actual['entry_list']), 'Call must return one bean minimum');
        $this->assertEquals('Accounts', $actual['entry_list'][0]['name'], 'Bean must be account');
        $this->assertEquals(
            $this->account->id,
            $actual['entry_list'][0]['records'][0][$idIndex]['value'],
            'Bean id must be same as id of created account'
        );
    }
}
