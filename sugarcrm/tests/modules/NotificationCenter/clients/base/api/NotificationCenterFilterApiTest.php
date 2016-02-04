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

namespace Sugarcrm\SugarcrmTests\modules\NotificationCenter\clients\base\api;

require_once 'modules/NotificationCenter/clients/base/api/NotificationCenterFilterApi.php';

use NotificationCenterFilterApi;
use SugarTestRestServiceMock;

/**
 * Class NotificationCenterFilterApiTest
 *
 * @coversDefaultClass NotificationCenterFilterApi
 */
class NotificationCenterFilterApiTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var NotificationCenterFilterApi */
    protected $notificationCenterFilterApi = null;

    /** @var SugarTestRestServiceMock */
    protected $service = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->notificationCenterFilterApi = new NotificationCenterFilterApi();
        $this->service = new SugarTestRestServiceMock();
    }

    /**
     * Checks whether the function always returns an empty array.
     *
     * @covers NotificationCenterFilterApi::filterList
     * @dataProvider filterListProvider
     * @param array $args
     */
    public function testFilterList($args)
    {
        $this->assertEquals(array(), $this->notificationCenterFilterApi->filterList($this->service, $args));
    }

    /**
     * Data provider for testFilterList.
     *
     * @see NotificationCenterFilterApiTest::testFilterList
     * @return array
     */
    public static function filterListProvider()
    {
        return array(
            'emptyArgs' => array(
                'args' => array(),
            ),
            'notEmptyArgs' => array(
                'args' => array(1, 2, 3),
            ),
        );
    }
}
