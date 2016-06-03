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

namespace Sugarcrm\SugarcrmTests\JobQueue\Helper;

use Sugarcrm\Sugarcrm\JobQueue\Helper\ProcessControl;

class SugarUpgradeTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessControl
     */
    protected $helper;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->helper = new ProcessControl('testService');
    }

    public function tearDown()
    {
        \SugarTestHelper::tearDown();
    }

    /**
     * Test the helper's lock feature.
     */
    public function testLockFeature()
    {
        $this->assertFalse($this->helper->isServiceLocked());

        $this->helper->lockService();

        $this->assertFileExists(sugar_cached($this->helper->getLockFileKey()));
        $this->assertTrue($this->helper->isServiceLocked());

        $this->helper->unlockService();

        $this->assertFileNotExists(sugar_cached($this->helper->getLockFileKey()));
        $this->assertFalse($this->helper->isServiceLocked());
    }
}
