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
use PHPUnit\Framework\TestCase;
use Contact;
use Lead;
use Note;
use SugarBean;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use SugarQuery;
use SugarTestContactUtilities;
use SugarTestHelper;
use SugarTestLeadUtilities;
use SugarTestNoteUtilities;
use SugarTestRestUtilities;
use BeanFactory;

class ErasedFieldsApiTest extends TestCase
{
    /**#@+
     * @var Contact
     */
    private static $contact1;
    private static $contact2;
    private static $contact3;
    /**#@-*/

    /**
     * @var Lead
     */
    private static $lead;

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

        self::$contact2 = SugarTestContactUtilities::createContact(null, [
            'reports_to_id' => self::$contact1->id,
        ]);
        self::$contact2->erase(FieldList::fromArray(['last_name']), false);

        self::$contact3 = SugarTestContactUtilities::createContact();
        self::$contact3->erase(FieldList::fromArray(['first_name', 'last_name']), false);

        self::$note = SugarTestNoteUtilities::createNote(null, [
            'contact_id' => self::$contact1->id,
            'parent_type' => self::$contact2->module_name,
            'parent_id' => self::$contact2->id,
        ]);

        self::$lead = SugarTestLeadUtilities::createLead(null, [
            'reports_to_id' => self::$contact3->id,
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
    public function erasedFieldsAreOnlyDisplayedWnehPiiIsSelected()
    {
        $query = new SugarQuery();
        $query->from(self::$contact1, [
            'erased_fields' => true,
        ]);
        $query->select('id');
        $query->where()->equals('id', self::$contact1->id);

        $data = $query->execute();
        $this->assertCount(1, $data);

        $row = array_shift($data);
        $this->assertEquals(self::$contact1->id, $row['id']);

        $this->assertArrayNotHasKey('erased_fields', $row);
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

    /**
     * @test
     */
    public function retrieveWithParentErasedFields()
    {
        $note = \BeanFactory::retrieveBean('Notes', self::$note->id, [
            'erased_fields' => true,
            'encode' => false,
            'use_cache' => false,
        ]);
        $data = $this->format($note);

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

    /**
     * @test
     */
    public function ownAndRelateFields()
    {
        $query = new SugarQuery();
        $query->from(self::$contact2, [
            'erased_fields' => true,
        ]);
        $query->select('report_to_name');
        $query->where()->equals('id', self::$contact2->id);

        $contacts = self::$contact2->fetchFromQuery($query);
        $this->assertCount(1, $contacts);

        $data = $this->format(array_shift($contacts));

        $this->assertArraySubset([
            '_erased_fields' => [
                'last_name',
            ],
            'reports_to_link' => [
                '_erased_fields' => [
                    'first_name',
                ],
            ],
        ], $data);
    }

    /**
     * @test
     */
    public function ownAndRelateFieldsViaRetrieve()
    {
        BeanFactory::clearCache();

        $contact = BeanFactory::retrieveBean(self::$contact2->module_name, self::$contact2->id, [
            'erased_fields' => true,
        ]);

        $this->assertArraySubset([
            '_erased_fields' => [
                'last_name',
            ],
            'reports_to_link' => [
                '_erased_fields' => [
                    'first_name',
                ],
            ],
        ], $this->format($contact));
    }

    /**
     * @test
     */
    public function relateFieldWithoutLink()
    {
        $query = new SugarQuery();
        $query->from(self::$lead, [
            'erased_fields' => true,
        ]);
        $query->select('report_to_name');
        $query->where()->equals('id', self::$lead->id);

        $leads = self::$lead->fetchFromQuery($query);
        $this->assertCount(1, $leads);

        $data = $this->format(array_shift($leads));

        $this->assertArraySubset([
            '_erased_fields' => [
                'report_to_name',
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
