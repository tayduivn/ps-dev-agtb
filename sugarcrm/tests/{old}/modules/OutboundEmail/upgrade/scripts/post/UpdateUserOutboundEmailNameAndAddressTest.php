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

require_once 'modules/OutboundEmail/upgrade/scripts/post/2_UpdateUserOutboundEmailNameAndAddress.php';

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass SugarUpgradeUpdateOutboundEmailNameAndAddress
 *
 * Test Update of Outbound Emails with type = 'user'
 */
class UpdateUserOutboundEmailNameAndAddressTest extends UpgradeTestCase
{
    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        OutboundEmailConfigurationTestHelper::backupExistingConfigurations();
        $sea = BeanFactory::newBean('EmailAddresses');
        $oeIds = array();

        /*--- User 1 ---*/
        $userInfo1 = array(
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe1@abc.xyz',
        );
        $user1 = SugarTestUserUtilities::createAnonymousUser(true, 0, $userInfo1);

        /*--- User 2 ---*/
        $userInfo2 = array(
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'janedoe1@abc.xyz',
        );
        $user2 = SugarTestUserUtilities::createAnonymousUser(true, 0, $userInfo2);
        $userData2 = $user2->getUsersNameAndEmail();

        /*-- Outbound Email - referenced by inbound email with From Name: John Doe One --*/
        $oe1 = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfiguration($user1->id);
        $oeIds[] = $oe1->id;

        $storedOptions1 = array(
            'from_addr' => 'johndoe1@abc.xyz',
            'from_name' => 'John Doe One',
            'outbound_email' => $oe1->id,
        );
        $ie1 = OutboundEmailConfigurationTestHelper::createInboundEmail($user1->id, $storedOptions1);

        /*-- Same Outbound Email with a second reference - Name and Email Also the Same - Shouldn't Duplicate OE --*/
        $ieIgnore = OutboundEmailConfigurationTestHelper::createInboundEmail($user1->id, $storedOptions1);

        /*-- Same Outbound Email - but this one has a Different From Name: John Doe Two - Should Duplicate OE --*/
        $storedOptions2 = array(
            'from_addr' => 'johndoe2@abc.xyz',
            'from_name' => 'John Doe 2',
            'outbound_email' => $oe1->id,
        );
        $ie2 = OutboundEmailConfigurationTestHelper::createInboundEmail($user1->id, $storedOptions2);

        /*-- Outbound Email - Not referenced by an inbound email - User 2 --*/
        $oe2 = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfiguration($user2->id);
        $oeIds[] = $oe2->id;

        /*--- Run Upgrader Script ---*/
        $script = $this->upgrader->getScript('post', '2_UpdateUserOutboundEmailNameAndAddress');
        $script->db = $GLOBALS['db'];
        $script->from_version = '7.9.0.0';
        $script->run();

        /*-- Outbound Email 1 --*/
        $oe = BeanFactory::retrieveBean(
            'OutboundEmail',
            $oe1->id,
            array('disable_row_level_security' => true, 'use_cache' => false)
        );
        $expectedStoredOptions = ($ie2->id < $ie1->id) ? $storedOptions2 : $storedOptions1;
        $expectedName = $expectedStoredOptions['from_name'];
        $expectedAddressId = $user1->emailAddress->getEmailGUID($expectedStoredOptions['from_addr']);
        $this->assertSame($expectedName, $oe->name, 'Unexpected OutboundEmail1 Name');
        $this->assertSame($expectedAddressId, $oe->email_address_id, 'Unexpected OutboundEmail1 Address ID');

        /*-- Outbound Email 2 --*/
        $oe = BeanFactory::retrieveBean(
            'OutboundEmail',
            $oe2->id,
            array('disable_row_level_security' => true, 'use_cache' => false)
        );
        $expectedName = $oe2->name;  // Not blank so should not have changed
        $expectedAddressId = $user2->emailAddress->getEmailGUID($userData2['email']);
        $this->assertSame($expectedName, $oe->name, 'Unexpected OutboundEmail2 Name');
        $this->assertSame($expectedAddressId, $oe->email_address_id, 'Unexpected OutboundEmail2 Address ID');

        /*-- Expecting one Outbound Email to have been Created --*/
        $oeResults = $this->getUserOutboundNamesAndEmailAddressIds();
        $this->assertSame(3, count($oeResults), 'Unexpected Numnber of User OutboundEmails');
        foreach ($oeResults as $id => $data) {
            if (!in_array($id, $oeIds)) {
                $oe = BeanFactory::retrieveBean(
                    'OutboundEmail',
                    $id,
                    array('disable_row_level_security' => true, 'use_cache' => false)
                );
                $this->assertSame($oe->user_id, $user1->id, 'Unexpected User');
                $expectedName = $storedOptions2['from_name'];
                $expectedAddressId = $user1->emailAddress->getEmailGUID($storedOptions2['from_addr']);
                $this->assertSame($expectedName, $oe->name, 'Unexpected OutboundEmail3 Name');
                $this->assertSame($expectedAddressId, $oe->email_address_id, 'Unexpected OutboundEmail3 Address ID');
            }
        }

        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();
    }

    protected function getUserOutboundNamesAndEmailAddressIds()
    {
        $rows = array();
        $sql = "SELECT id, name, email_address_id FROM outbound_email WHERE type='user' AND deleted=0";
        $stmt = DBManagerFactory::getConnection()->executeQuery($sql);
        while ($row = $stmt->fetch()) {
            $rows[$row['id']] = $row;
        }
        return $rows;
    }
}
