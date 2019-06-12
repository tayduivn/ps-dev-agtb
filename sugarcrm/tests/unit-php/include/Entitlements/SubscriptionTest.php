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

namespace Sugarcrm\SugarcrmTestUnit\inc\Entitlements;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Entitlements\Subscription;

/**
 * Class SubscriptionTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Entitlements\Subscription
 */
class SubscriptionTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::parse
     * @covers ::__get
     *
     * @dataProvider subscriptionProvider
     */
    public function testGetData($data, $expected, $expectedtAddonCount)
    {
        $subscription = new Subscription($data);
        $this->assertSame($expected['id'], $subscription->id);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $subscription->$key, "failed on property: $key!");
        }

        // addons
        if ($expectedtAddonCount > 0) {
            $this->assertSame($expectedtAddonCount, count($subscription->addons));
        }
        // not property
        $this->assertEmpty($subscription->xyz);
    }

    public function subscriptionProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","expiration_date":1898582400},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","expiration_date":1898582400},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","expiration_date":1898582400},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","expiration_date":1898582400}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":150,"account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1554170401,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1898582400,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":1898582400,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'id' => '914f07ac-3acb-3a3a-8d4f-570fe8dcae78',
                    'account_name' => 'SugarCRM Partner Portal Login',
                    'product' => 'ENT',
                    'subscription_id' => '5fd99624e58ec184c96d0520d9ab8b2d',
                    'ignore_expiration_date' => 0,
                    'quantity_c' => 150,
                ],
                4,
            ],
            [
                '{"no subscription section": {"quantity" : "100"}}',
                [
                    'id' => null,
                    'quantity' => null],
                0,
            ],
            [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":[],"emails":[],"status":"enabled","audited":1,"domains":[],"product":"","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":10,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1556597789,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"aa8834fa-6ac0-11e9-b588-02c10f456dba","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'id' => 'ffffc6a2-6ac3-11e9-b0f5-02c10f456dba',
                    'account_name' => 'iApps Test Partner Account',
                    'product' => '',
                    'subscription_id' => '47fa5aa6620415261cd7bcd2a8de6d31',
                    'ignore_expiration_date' => 0,
                    'quantity_c' => 10,
                ],
                0,
            ]
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @covers ::__construct
     * @covers ::parse
     *
     * @dataProvider subscriptionExceptionProvider
     *
     * @expectedException \Exception
     */
    public function testGetDataException($data)
    {
        new Subscription($data);
    }

    public function subscriptionExceptionProvider()
    {
        return [
            [''],
            ['{"subscription": {"no_id" : "100"}'],
        ];
    }

    /**
     * @covers ::getSubscriptions
     * @covers ::parse
     * @covers ::__get
     *
     * @dataProvider getSubscriptionsProvider
     */
    public function testGetSubscriptions($data, $expected)
    {
        $subscription = new Subscription($data);
        $this->assertSame($expected, $subscription->getSubscriptions());
    }
    public function getSubscriptionsProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            // SERVE Only
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            // SERVE Only, expired
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1287798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
            ],
            // SERVE as basic product
            [
                '{"no subscription section": {"quantity" : "100"}}',
                [],
            ],
            // SERVE only
            [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":[],"emails":[],"status":"enabled","audited":1,"domains":[],"product":"","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":10,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1556597789,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"aa8834fa-6ac0-11e9-b588-02c10f456dba","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            // SERVE + ENT
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":100,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 100,
                        'expiration_date' => 1587798000,
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            // SERVE + ENT, SERVE is expired
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1487798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":100,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 100,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            // SERVE + SELL + ENT
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}, "181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":1010,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 1010,
                        'expiration_date' => 1587798000,
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            // SERVE + SELL
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}, "181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            // new structure
            [
                // ENT+SERVE
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":1587798000,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":1587798000,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                    'CURRENT' => [
                        'quantity' => 100,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            [
                // ENT+SERVE with new key for SERVE
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":1587798000,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":1587798000,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                    'CURRENT' => [
                        'quantity' => 100,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            [
                // SERVE only
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":1587798000,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":1587798000,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            [
                // SERVE only with new key for SERVE
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":1587798000,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":1587798000,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1587798000,
                    ],
                ],
            ],
            [
                // SELL only
                '{"success":true,"error":"","subscription":{"id":"3efc5dc4-7b50-11e9-9f42-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":1589958000,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417219,"evaluation_c":0,"portal_users":0,"date_modified":1558487575,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1589958000,"subscription_id":"3779135395d186056bbcc895dc3cfc00","term_end_date_c":1589958000,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 1589958000,
                    ],
                ],
            ],
            [
                // SELL only with new key for SELL
                '{"success":true,"error":"","subscription":{"id":"3efc5dc4-7b50-11e9-9f42-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456aaa":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":1589958000,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417219,"evaluation_c":0,"portal_users":0,"date_modified":1558487575,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1589958000,"subscription_id":"3779135395d186056bbcc895dc3cfc00","term_end_date_c":1589958000,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 1589958000,
                    ],
                ],
            ],
            [
                // SELL + SERVE
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":1589958000,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SERVE","expiration_date":1589958000,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1589958000,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":1589958000,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 1589958000,
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1589958000,
                    ],
                ],
            ],
            [
                // SELL + SERVE with new key for SELL
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dbb":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":1589958000,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SERVE","expiration_date":1589958000,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1589958000,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":1589958000,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 1589958000,
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 1589958000,
                    ],
                ],
            ],
            [
                // ENT only
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","start_date_c":"","expiration_date":1898582400,"product_code_c":""},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","start_date_c":"","expiration_date":1898582400,"product_code_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","start_date_c":"","expiration_date":1898582400,"product_code_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","start_date_c":"","expiration_date":1898582400,"product_code_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":150,"product_name":"Sugar Enterprise","start_date_c":1460617200,"expiration_date":1898582400,"product_code_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1558836002,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1898582400,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":1898582400,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 150,
                        'expiration_date' => 1898582400,
                    ],
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
    /**
     * @covers ::getSubscriptionKeys
     * @covers ::getAddonProducts
     *
     * @dataProvider getSubscriptionKeysProvider
     */
    public function testGetSubscriptionKeys($data, $expected)
    {
        $subscription = new Subscription($data);
        $this->assertSame($expected, $subscription->getSubscriptionKeys());
    }
    public function getSubscriptionKeysProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            // addon, CS only
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => true,
                ],
            ],
            [
                '{"no subscription section": {"quantity" : "100"}}',
                [],
            ],
            // base, CS only
            [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":[],"emails":[],"status":"enabled","audited":1,"domains":[],"product":"","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":10,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1556597789,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"aa8834fa-6ac0-11e9-b588-02c10f456dba","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => true,
                ],
            ],
            // ENT base and CS addon
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":100,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => true,
                    'SUGAR_SERVE' => true,
                ],
            ],
            // SELL + SURVE
            [
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","expiration_date":1589958000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":10,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1589958000,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":1589958000,"term_start_date_c":1558335600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"181aee1c-7b3e-11e9-b962-02c10f456dba","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SELL' => true,
                    'SUGAR_SERVE' => true,
                ],
            ],
            // SELL only
            [
                '{"success":true,"error":"","subscription":{"id":"3efc5dc4-7b50-11e9-9f42-02c10f456dba","debug":0,"addons":[],"emails":[],"status":"enabled","audited":1,"domains":[],"product":"","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":10,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417219,"evaluation_c":0,"portal_users":0,"date_modified":1558487575,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1589958000,"subscription_id":"3779135395d186056bbcc895dc3cfc00","term_end_date_c":1589958000,"term_start_date_c":1558335600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"181aee1c-7b3e-11e9-b962-02c10f456dba","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SELL' => true,
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
}
