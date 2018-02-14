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

namespace Sugarcrm\SugarcrmTests\DataPrivacy\Erasure;

use ApiHelper;
use Contact;
use Note;
use SugarBean;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use SugarQuery;
use SugarTestContactUtilities;
use SugarTestHelper;
use SugarTestNoteUtilities;
use SugarTestRestUtilities;

class ErasedFieldsApiTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * @var Contact
     */
    private static $contact1;
    private static $contact2;
    /**#@-*/

    /**
     * @var Note
     */
    private static $note;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user', [true, true]);

        self::$contact1 = SugarTestContactUtilities::createContact();
        self::$contact1->erase(FieldList::fromArray(['first_name']), false);

        self::$contact2 = SugarTestContactUtilities::createContact();
        self::$contact2->erase(FieldList::fromArray(['last_name']), false);

        self::$note = SugarTestNoteUtilities::createNote(null, [
            'contact_id' => self::$contact1->id,
            'parent_type' => self::$contact2->module_name,
            'parent_id' => self::$contact2->id,
        ]);
    }

    /**
     * @test
     */
    public function ownFields()
    {
        $query = new SugarQuery();
        $query->from(self::$contact1, [
            'erased_fields' => true,
        ]);
        $query->where()->in('id', [
            self::$contact1->id,
            self::$contact2->id,
        ]);

        $contacts = self::$contact1->fetchFromQuery($query);
        $this->assertCount(2, $contacts);

        $data = array_map(function (SugarBean $bean) {
            return $this->format($bean);
        }, $contacts);

        $this->assertArraySubset([
            self::$contact1->id => [
                '_erased_fields' => [
                    'first_name',
                ],
            ],
            self::$contact2->id => [
                '_erased_fields' => [
                    'last_name',
                ],
            ],
        ], $data);
    }

    /**
     * @test
     */
    public function relateAndParentFields()
    {
        $query = new SugarQuery();
        $query->from(self::$note, [
            'erased_fields' => true,
        ]);
        $query->where()->equals('id', self::$note->id);

        $notes = self::$note->fetchFromQuery($query);
        $this->assertCount(1, $notes);

        $data = $this->format(array_shift($notes));

        $this->assertArraySubset([
            'contact' => [
                '_erased_fields' => [
                    'first_name',
                ],
            ],
            'parent' => [
                '_erased_fields' => [
                    'last_name',
                ],
            ],
        ], $data);
    }

    private function format(SugarBean $bean)
    {
        $api = SugarTestRestUtilities::getRestServiceMock();

        return ApiHelper::getHelper($api, $bean)->formatForApi($bean, [], [
            'args' => [
                'erased_fields' => true,
            ],
        ]);
    }

    public static function tearDownAfterClass()
    {
        SugarTestNoteUtilities::removeAllCreatedNotes();
        SugarTestContactUtilities::removeAllCreatedContacts();

        parent::tearDownAfterClass();
    }
}
