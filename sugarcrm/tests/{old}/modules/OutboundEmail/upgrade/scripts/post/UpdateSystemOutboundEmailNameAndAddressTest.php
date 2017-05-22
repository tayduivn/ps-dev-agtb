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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'modules/OutboundEmail/upgrade/scripts/post/2_UpdateSystemOutboundEmailNameAndAddress.php';

/**
 * @coversDefaultClass SugarUpgradeUpdateSystemOutboundEmailNameAndAddress
 */
class UpdateSystemOutboundEmailNameAndAddressTest extends UpgradeTestCase
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

        $userInfo = array(
            'first_name' => 'foo',
            'last_name' => 'bar',
            'email' => 'foo@bar.com',
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $userInfo);
        $oeOverride = $this->createSystemOverrideOutboundEmail($user);

        $oeSystem = $this->createSystemOutboundEmail();

        $script = $this->upgrader->getScript('post', '2_UpdateSystemOutboundEmailNameAndAddress');
        $script->db = $GLOBALS['db'];
        $script->from_version = '7.9.0.0';
        $script->run();

        $sea = BeanFactory::newBean('EmailAddresses');
        $adminSettings = Administration::getSettings('notify');
        $systemAddress = $adminSettings->settings['notify_fromaddress'];
        $systemName = $adminSettings->settings['notify_fromname'];
        $systemAddressId = $sea->getEmailGUID($systemAddress);

        $sData = $this->getOutboundNameAndEmailAddressId($oeSystem->id);
        $this->assertSame($systemName, $sData['name'], 'Unexpected System OutboundEmail Name');
        $this->assertSame($systemAddressId, $sData['email_address_id'], 'Unexpected System OutboundEmail Address ID');

        $userData = $user->getUsersNameAndEmail();
        $userName = $userData['name'];
        $addressId = $user->emailAddress->getEmailGUID($userData['email']);

        $sData = $this->getOutboundNameAndEmailAddressId($oeOverride->id);
        $this->assertSame($userName, $sData['name'], 'Unexpected Override OutboundEmail Name');
        $this->assertSame($addressId, $sData['email_address_id'], 'Unexpected Override OutboundEmail Address ID');

        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();
    }

    protected function createSystemOutboundEmail()
    {
        $sql = "SELECT id, name, email_address_id FROM outbound_email WHERE type='system' AND deleted=0";
        $stmt = DBManagerFactory::getConnection()->executeQuery($sql);
        if ($row = $stmt->fetch()) {
            $oe = BeanFactory::retrieveBean(
                'OutboundEmail',
                $row['id'],
                array('disable_row_level_security' => true, 'use_cache' => false)
            );
        } else {
            $oe = OutboundEmailConfigurationTestHelper::createSystemOutboundEmailConfiguration();
        }
        $sql = "UPDATE outbound_email SET email_address_id = ?, name = ? WHERE id = ?";
        $result = DBManagerFactory::getConnection()->executeUpdate($sql, ['', 'sugar-test', $oe->id]);
        $oe = BeanFactory::retrieveBean(
            'OutboundEmail',
            $oe->id,
            array('disable_row_level_security' => true, 'use_cache' => false)
        );
        return $oe;
    }

    protected function createSystemOverrideOutboundEmail(User $user)
    {
        $oe = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration($user->id);
        $sql = "UPDATE outbound_email SET email_address_id = ?, name = ? WHERE id = ?";
        $result = DBManagerFactory::getConnection()->executeUpdate($sql, ['', 'sugar-test', $oe->id]);
        $oe = BeanFactory::retrieveBean(
            'OutboundEmail',
            $oe->id,
            array('disable_row_level_security' => true, 'use_cache' => false)
        );
        return $oe;
    }

    protected function getOutboundNameAndEmailAddressId($outboundEmailId)
    {
        $sql = "SELECT id, name, email_address_id FROM outbound_email WHERE id = ? AND deleted=0";
        $stmt = DBManagerFactory::getConnection()->executeQuery($sql, [$outboundEmailId]);
        if ($row = $stmt->fetch()) {
            return $row;
        }
        return array();
    }
}
