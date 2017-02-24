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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Emails\clients\base\api;

use Sugarcrm\Sugarcrm\Util\Uuid;

require_once 'include/utils.php';

/**
 * @coversDefaultClass \EmailsRelateRecordApi
 */
class EmailsRelateRecordApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Existing attachments cannot be linked to an email.
     *
     * @covers ::createRelatedLinks
     * @expectedException \SugarApiExceptionNotAuthorized
     */
    public function testCreateRelatedLinks()
    {
        $args = [
            'module' => 'Emails',
            'record' => Uuid::uuid1(),
            'link_name' => 'attachments',
            'remote_id' => Uuid::uuid1(),
        ];
        $service = $this->createPartialMock('\\RestService', []);
        $api = new \EmailsRelateRecordApi();
        $api->createRelatedLinks($service, $args);
    }

    /**
     * Existing attachments cannot be linked to an email.
     *
     * @covers ::createRelatedLinksFromRecordList
     * @expectedException \SugarApiExceptionNotAuthorized
     */
    public function testCreateRelatedLinksFromRecordList()
    {
        $args = [
            'module' => 'Emails',
            'record' => Uuid::uuid1(),
            'link_name' => 'attachments',
            'remote_id' => Uuid::uuid1(),
        ];
        $service = $this->createPartialMock('\\RestService', []);
        $api = new \EmailsRelateRecordApi();
        $api->createRelatedLinksFromRecordList($service, $args);
    }
}
