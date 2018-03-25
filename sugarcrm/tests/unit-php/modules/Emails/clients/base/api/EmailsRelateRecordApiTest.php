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

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

require_once 'include/utils.php';

/**
 * @coversDefaultClass \EmailsRelateRecordApi
 */
class EmailsRelateRecordApiTest extends TestCase
{
    public function linkProvider()
    {
        return [
            [
                'from',
            ],
            [
                'to',
            ],
            [
                'cc',
            ],
            [
                'bcc',
            ],
            [
                'attachments',
            ],
        ];
    }

    /**
     * Existing sender, recipients, and attachments cannot be linked to an email.
     *
     * @dataProvider linkProvider
     * @covers ::createRelatedLinks
     * @expectedException \SugarApiExceptionNotAuthorized
     */
    public function testCreateRelatedLinks($linkName)
    {
        $args = [
            'module' => 'Emails',
            'record' => Uuid::uuid1(),
            'link_name' => $linkName,
            'remote_id' => Uuid::uuid1(),
        ];
        $service = $this->createPartialMock('\\RestService', []);
        $api = new \EmailsRelateRecordApi();
        $api->createRelatedLinks($service, $args);
    }

    /**
     * Existing sender, recipients, and attachments cannot be linked to an email.
     *
     * @dataProvider linkProvider
     * @covers ::createRelatedLinksFromRecordList
     * @expectedException \SugarApiExceptionNotAuthorized
     */
    public function testCreateRelatedLinksFromRecordList($linkName)
    {
        $args = [
            'module' => 'Emails',
            'record' => Uuid::uuid1(),
            'link_name' => $linkName,
            'remote_id' => Uuid::uuid1(),
        ];
        $service = $this->createPartialMock('\\RestService', []);
        $api = new \EmailsRelateRecordApi();
        $api->createRelatedLinksFromRecordList($service, $args);
    }

    /**
     * Cannot break the link between an email and its sender.
     *
     * @covers ::deleteRelatedLink
     * @expectedException \SugarApiExceptionNotAuthorized
     */
    public function testDeleteRelatedLink()
    {
        $args = [
            'module' => 'Emails',
            'record' => Uuid::uuid1(),
            'link_name' => 'from',
            'remote_id' => Uuid::uuid1(),
        ];
        $service = $this->createPartialMock('\\RestService', []);
        $api = new \EmailsRelateRecordApi();
        $api->deleteRelatedLink($service, $args);
    }
}
