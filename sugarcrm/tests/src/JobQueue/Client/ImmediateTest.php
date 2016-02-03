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

namespace Sugarcrm\SugarcrmTests\JobQueue\Client;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Client\Immediate;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;

class ImmediateTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Workload
     */
    protected $workload;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->workload = new Workload('route', array());
    }

    public function tearDown()
    {
        \SugarTestHelper::tearDown();
    }

    /**
     * Should execute passed callable on add.
     */
    public function testAddJob()
    {
        $expected = 'expectedValue';
        $callable = function ($workload) use ($expected) {
            $workload->setAttribute('actual', $expected);
        };
        $client = new Immediate($callable, new NullLogger());
        $client->addJob($this->workload);

        $this->assertEquals($expected, $this->workload->getAttribute('actual'));
    }
}
