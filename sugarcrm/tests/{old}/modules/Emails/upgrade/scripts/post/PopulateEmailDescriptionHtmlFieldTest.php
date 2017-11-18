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
require_once 'modules/Emails/upgrade/scripts/post/2_PopulateEmailDescriptionHtmlField.php';

/**
 * @coversDefaultClass SugarUpgradePopulateEmailDescriptionHtmlField
 */
class PopulateEmailDescriptionHtmlFieldTest extends UpgradeTestCase
{
    public function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        parent::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $emails = [
            // Archived
            'archived_1' => [
                'state' => 'Archived',
                'description' => "Now\r\nis\nthe\r\ntime\n",
                'description_html' => '',
            ],
            'archived_2' => [
                'state' => 'Archived',
                'description' => "Now\r\nis\nthe\r\ntime\n",
                'description_html' => '',
            ],
            'archived_3' => [
                'state' => 'Archived',
                'description' => "Now\r\nis\nthe\r\ntime\n",
                'description_html' => 'Not Empty',
            ],
            'archived_4' => [
                'state' => 'Archived',
                'description' => '',
                'description_html' => 'Not Empty',
            ],
            'archived_5' => [
                'state' => 'Archived',
                'description' => null,
                'description_html' => 'Not Empty',
            ],
            // Draft
            'draft_1' => [
                'state' => 'Draft',
                'description' => "Now\r\nis\nthe\r\ntime\n",
                'description_html' => '',
            ],
            'draft_2' => [
                'state' => 'Draft',
                'description' => "Now\r\nis\nthe\r\ntime\n",
                'description_html' => '',
            ],
            'draft_3' => [
                'state' => 'Draft',
                'description' => "Now\r\nis\nthe\r\ntime\n",
                'description_html' => 'Not Empty',
            ],
            'draft_4' => [
                'state' => 'Draft',
                'description' => '',
                'description_html' => 'Not Empty',
            ],
            'draft_5' => [
                'state' => 'Draft',
                'description' => null,
                'description_html' => 'Not Empty',
            ],
        ];

        $emailIds = array_keys($emails);
        $idString = '(\'' . implode("', '", $emailIds) . '\')';

        foreach ($emails as $emailId => $email) {
            SugarTestEmailUtilities::createEmail(
                $emailId,
                [
                    'state' => $email['state'],
                    'description' => $email['description'],
                    'description_html' => $email['description_html'],
                ]
            );
        }

        $script = $this->upgrader->getScript('post', '2_PopulateEmailDescriptionHtmlField');
        $script->db = $GLOBALS['db'];
        $script->from_version = '7.10.0.0';
        $script->run();

        $sql = "SELECT email_id, description, description_html FROM emails_text WHERE email_id in {$idString}";
        $stmt = DBManagerFactory::getConnection()->executeQuery($sql);
        $results = array();
        while ($row = $stmt->fetch()) {
            $results[$row['email_id']] = $row;
        }

        /**
         * Description should been copied and CRLF characters converted on All Archived and Draft Emails whose
         * description_html is Empty/NULL  and whose description field is Not Empty/Null
         */
        $this->assertSame(
            "Now<br />is<br />the<br />time<br />",
            $results['archived_1']['description_html'],
            'Archived Email description_html Was Not Converted : archived_1'
        );

        $this->assertSame(
            "Now<br />is<br />the<br />time<br />",
            $results['archived_2']['description_html'],
            'Archived Email description_html Was Not Converted : archived_2'
        );

        $this->assertSame(
            $emails['archived_3']['description_html'],
            $results['archived_3']['description_html'],
            'Archived Email description_html Should Not have been Modified : archived_3'
        );

        $this->assertSame(
            $emails['archived_4']['description_html'],
            $results['archived_4']['description_html'],
            'Archived Email description_html Should Not have been Modified : archived_4'
        );

        $this->assertSame(
            $emails['archived_5']['description_html'],
            $results['archived_5']['description_html'],
            'Archived Email description_html Should Not have been Modified : archived_5'
        );

        // Draft
        $this->assertSame(
            "Now<br />is<br />the<br />time<br />",
            $results['draft_1']['description_html'],
            'Draft Email description_html Was Not Converted : draft_1'
        );

        $this->assertSame(
            "Now<br />is<br />the<br />time<br />",
            $results['draft_2']['description_html'],
            'Draft Email description_html Was Not Converted : draft_2'
        );

        $this->assertSame(
            $emails['draft_3']['description_html'],
            $results['draft_3']['description_html'],
            'Draft Email description_html Should Not have been Modified : draft_3'
        );

        $this->assertSame(
            $emails['draft_4']['description_html'],
            $results['draft_4']['description_html'],
            'Draft Email description_html Should Not have been Modified : draft_4'
        );

        $this->assertSame(
            $emails['draft_5']['description_html'],
            $results['draft_5']['description_html'],
            'Draft Email description_html Should Not have been Modified : draft_5'
        );

        /**
         * Description should be Empty for All Draft Emails And Not Modified For Archived
         */
        foreach ($emails as $emailId => $email) {
            if ($email['state'] === 'Draft') {
                $this->assertEmpty(
                    $results[$emailId]['description'],
                    'Draft Email Description Not Empty'
                );
            }
        }
    }
}
