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
use Sugarcrm\Sugarcrm\Entitlements\SubscriptionManager;
use Sugarcrm\Sugarcrm\Entitlements\Subscription;

/**
 * Class SubscriptionManagerTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Entitlements\SubscriptionManager
 */
class SubscriptionManagerTest extends TestCase
{
    /**
     * @covers ::getUserSubscriptions
     * @covers ::getSystemSubscriptions
     * @covers ::getSubscription
     * @covers ::instance
     * @covers ::getSystemSubscriptionKeys
     * @covers ::getUserDefaultLicenseTypes
     * @covers ::getAllSupportedProducts
     *
     * @dataProvider getUserSubscriptionsProvider
     */
    public function testGetUserSubscriptions($data, $userLicenseType, $isAdmin, $expected)
    {
        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLicenseType'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getLicenseType')
            ->will($this->returnValue($userLicenseType));

        $userMock->is_admin = $isAdmin;
        
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubscription', 'getLicenseKey'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSubscription')
            ->will($this->returnValue(new Subscription($data)));

        $subMock->expects($this->any())
            ->method('getLicenseKey')
            ->will($this->returnValue('any_license_key'));

        $this->assertSame($expected, $subMock->getUserSubscriptions($userMock));
    }

    public function getUserSubscriptionsProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            // user's license Type is empty, product is SERVE + ENT
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":1010,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                true,
                ['CURRENT'],
            ],
            // user's license Type is SERVE, product is SERVE
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE'],
                false,
                ['SUGAR_SERVE'],
            ],
            // user's license Type is SERVE, product is SERVE + ENT
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":1010,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE'],
                false,
                ['SUGAR_SERVE'],
            ],
            // user's license Type is CURRENT, product is ENT only
            [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","expiration_date":1898582400},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","expiration_date":1898582400},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","expiration_date":1898582400},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","expiration_date":1898582400}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":150,"account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1554170401,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1898582400,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":1898582400,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['CURRENT'],
                false,
                ['CURRENT'],
            ],
            // user's license Type is SERVE, product is ENT only
            [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","expiration_date":1898582400},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","expiration_date":1898582400},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","expiration_date":1898582400},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","expiration_date":1898582400}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":150,"account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1554170401,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1898582400,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":1898582400,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE'],
                false,
                [],
            ],
            // user is admin, license Type is SERVE, product is ENT only
            [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","expiration_date":1898582400},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","expiration_date":1898582400},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","expiration_date":1898582400},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","expiration_date":1898582400}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":150,"account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1554170401,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1898582400,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":1898582400,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE'],
                true,
                ['CURRENT'],
            ],
            // user's license Type is CURRENT, product is SERVE
            [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":[],"emails":[],"status":"enabled","audited":1,"domains":[],"product":"","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":10,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1556597789,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"aa8834fa-6ac0-11e9-b588-02c10f456dba","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['CURRENT'],
                false,
                ['SUGAR_SERVE'],
            ],
            // invalid format
            [
                '{"no subscription section": {"quantity" : "100"}}',
                ['CURRENT'],
                false,
                [],
            ],
            // user's license Type is SELL, product is SERVE
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SELL'],
                false,
                [],
            ],
            // user is admin, license Type is SELL, product is SERVE
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SELL'],
                true,
                ['SUGAR_SERVE'],
            ],
            // user's license Type is SELL, product is SERVE + ENT
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":1010,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SELL'],
                false,
                [],
            ],
            // user's license Type is SERVE + ENT, product is SERVE + ENT
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":1010,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE', 'CURRENT'],
                false,
                ['SUGAR_SERVE', 'CURRENT'],
            ],
            // user's license Type is empty, product is ENT only
            [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","expiration_date":1898582400},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","expiration_date":1898582400},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","expiration_date":1898582400},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","expiration_date":1898582400}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":150,"account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1554170401,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1898582400,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":1898582400,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['CURRENT'],
                false,
                ['CURRENT'],
            ],
            // user's license Type is empty, product is SERVE
            [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":[],"emails":[],"status":"enabled","audited":1,"domains":[],"product":"","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":10,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1556597789,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"aa8834fa-6ac0-11e9-b588-02c10f456dba","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                false,
                ['SUGAR_SERVE'],
            ],
            // user's license Type is empty, product is SERVE + ENT
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":1010,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                false,
                ['CURRENT'],
            ],
            // user's license Type is empty, product is SERVE + SELL + ENT
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}, "181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":1010,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                true,
                ['CURRENT'],
            ],
            // user's license Type is empty, product is SERVE + SELL
            [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}, "181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1587798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1587798000,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1587798000,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                true,
                ['SUGAR_SERVE'],
            ],
            // not found
            [
                '{"success":false,"error":"Subscription Not Found","subscription":null}',
                ['CURRENT'],
                false,
                [],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
}