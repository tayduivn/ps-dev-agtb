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

/**
 * @coversDefaultClass \SugarACLDpoErasure
 */
class SugarACLDpoErasureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * If the module is DPR, grant access to it.
     *
     * @covers ::checkAccess
     */
    public function testCheckAccess_IsDPRModule()
    {
        $currentUserMock = $this->getCurrentUserMock(array('isAdminForModule'));

        $currentUserMock->method('isAdminForModule')
            ->willReturn(true);

        $aclClass = $this->getMockBuilder('SugarACLDpoErasure')
            ->setMethods(array('getCurrentUser'))
            ->getMock();

        $aclClass->method('getCurrentUser')
            ->willReturn($currentUserMock);

        $result = $aclClass->checkAccess('DataPrivacy', '', array('action' => 'erase'));
        $this->assertEquals(true, $result);

        $result = $aclClass->checkAccess('DataPrivacy', '', array('action' => 'edit'));
        $this->assertEquals(true, $result);

        $result = $aclClass->checkAccess('DataPrivacy', array('view' => 'team_security'), array());
        $this->assertEquals(true, $result);
    }

    /**
     * If the module is not DPR, deny the access if the action is 'erase'.
     *
     * @covers ::checkAccess
     */
    public function testCheckAccess_IsNonDPRModule()
    {
        $currentUserMock = $this->getCurrentUserMock(array('isAdminForModule'));

        $currentUserMock->method('isAdminForModule')
            ->willReturn(true);

        $aclClass = $this->getMockBuilder('SugarACLDpoErasure')
            ->setMethods(array('getCurrentUser'))
            ->getMock();

        $aclClass->method('getCurrentUser')
            ->willReturn($currentUserMock);

        $result = $aclClass->checkAccess('Accounts', '', array('action' => 'erase'));
        $this->assertEquals(false, $result);

        $result = $aclClass->checkAccess('Accounts', '', array('action' => 'edit'));
        $this->assertEquals(true, $result);

        $result = $aclClass->checkAccess('Accounts', array('view' => 'team_security'), array());
        $this->assertEquals(true, $result);

        $result = $aclClass->checkAccess(null, '', array('action' => 'erase'));
        $this->assertEquals(false, $result);

        $result = $aclClass->checkAccess(null, '', array('action' => 'edit'));
        $this->assertEquals(true, $result);

        $result = $aclClass->checkAccess(null, array('view' => 'team_security'), array());
        $this->assertEquals(true, $result);
    }


    /**
     * If admin, grant access.
     *
     * @covers ::checkAccess
     */
    public function test_IsNotAdmin()
    {
        $currentUserMock = $this->getCurrentUserMock(array('isAdminForModule'));

        $currentUserMock->method('isAdminForModule')
            ->willReturn(false);

        $aclClass = $this->getMockBuilder('SugarACLDpoErasure')
            ->setMethods(array('getCurrentUser'))
            ->getMock();

        $aclClass->method('getCurrentUser')
            ->willReturn($currentUserMock);

        $result = $aclClass->checkAccess(null, '', array());
        $this->assertEquals(true, $result);

        $result = $aclClass->checkAccess('DataPrivacy', '', array('action' => 'erase'));
        $this->assertEquals(false, $result);

        $result = $aclClass->checkAccess('Accounts', '', array('action' => 'erase'));
        $this->assertEquals(false, $result);
    }

    /**
     * Returns a mock for the current user. The method isAdminForModule is
     * stubbed.
     * @param array $stubbedMethods The methods that are intended to be stubbed.
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    private function getCurrentUserMock($stubbedMethods)
    {
        $currentUserMock = $this->getMockBuilder(\stdClass::class)
            ->setMethods($stubbedMethods)
            ->getMock();

        return $currentUserMock;
    }
}
