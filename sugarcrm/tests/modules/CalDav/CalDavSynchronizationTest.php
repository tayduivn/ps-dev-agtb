<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Class CalDavSynchronizationTest
 *
 * @coversDefaultClass \CalDavSynchronization
 */
class CalDavSynchronizationTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * test the Bean Sync Counter
     *
     * @group  caldav
     * @covers CalDavSynchronization::setSaveCounter
     * @covers CalDavSynchronization::getSaveCounter
     */
    public function testSaveCounter()
    {
        $rand = rand(0, 999);
        $syncBean = $this->getMockBuilder('\CalDavSynchronization')
                         ->disableOriginalConstructor()
                         ->setMethods(array('save'))
                         ->getMock();
        $syncBean->save_counter = $rand;

        $this->assertEquals(++ $rand, $syncBean->setSaveCounter());
        $this->assertEquals($rand, $syncBean->getSaveCounter());
    }

    /**
     * test the Dav Sync Counter
     *
     * @group  caldav
     * @covers CalDavSynchronization::setJobCounter
     * @covers CalDavSynchronization::getJobCounter
     */
    public function testJobCounter()
    {
        $rand = rand(0, 999);

        $syncBean = $this->getMockBuilder('\CalDavSynchronization')
                         ->disableOriginalConstructor()
                         ->setMethods(array('save'))
                         ->getMock();

        $syncBean->job_counter = $rand;

        $this->assertEquals(++ $rand, $syncBean->setJobCounter());
        $this->assertEquals($rand, $syncBean->getJobCounter());
    }
}
