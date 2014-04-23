<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'include/SugarFields/Fields/Tags/SugarFieldTags.php';

class SugarFieldTagsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Fixtures
     * @var SugarBean
     */
    protected $contact;
    protected $tag1;
    protected $tag2;

    public static function setUpBeforeClass()
    {
        VardefManager::loadVardef('Tags', 'Tag', true);
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        // create fixtures
        $this->contact = SugarTestContactUtilities::createContact();
        $this->tag1 = SugarTestTagUtilities::createTag();
        $this->tag2 = SugarTestTagUtilities::createTag();
    }

    public function tearDown()
    {
        SugarTestTagUtilities::deleteM2MRelationships('Contacts', $this->contact->id);
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTagUtilities::removeAllCreatedTags();

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Test property parsing. Tags field enforces the values
     * for collection_Create and collection_fields
     *
     * @dataProvider providerTestParseProperties
     */
    public function testParseProperties($properties, $expected, $message)
    {
        $relateCollection = new SugarFieldTagsTestMock('tags');

        $actual = $relateCollection->parsePropertiesTest($properties);

        $this->assertEquals($expected, $actual, $message);
    }

    public function providerTestParseProperties()
    {
        $defaultResult = array(
            false,
            array('id', 'name'), // enforced
            -1,
            true,				 // enforced
        );

        return array(
            // empty input data
            array(
                array(),
                $defaultResult,
                'Tag default properties invalid',
            ),
            // try alter collection_create
            array(
                array(
                    'collection_create' => false,
                ),
                $defaultResult,
                'Property "collection_create" is not enforced',
            ),
            // try to alter collection_fields
            array(
                array(
                    'collection_fields' => array('xxx', 'yyy'),
                ),
                $defaultResult,
                'Property "collection_fields" is not enforced',
            ),
            // mix enforced and non-enforced properties
            array(
                array(
                    'link' => 'testLink',
                    'collection_limit' => 23,
                    'collection_create' => false,
                    'collection_fields' => array('xxx', 'yyy'),
                ),
                array(
                    'testLink',
                    array(
                        'id',
                        'name',
                    ),
                    23,
                    true,
                ),
                'Failure testing enforced and non-enforced field properties',
            ),
        );
    }

    /**
     * Api format integration test.
     *
     * Create contact and several tags for that contact.
     * Call apiFormatField for tags_link.
     * Expect tags data will be present after apiFormatField
     */
    public function testApiFormatFieldIntegration()
    {

        // FIXME: temp disabled - needs to be retested when doing MT-909
        $this->markTestSkipped('Awaiting MT-909');

        // Link tag1 and tag2 to contact
        $relName = 'tags_link';
        $this->contact->load_relationship($relName);
        $this->contact->$relName->add($this->tag1);
        $this->contact->$relName->add($this->tag2);

        //Clean up any hanging related records.
        SugarRelationship::resaveRelatedBeans();

        // This forces a re-retrieval of the bean from the database
        BeanFactory::unregisterBean($this->contact);

        // call api format for tags field
        $tags = new SugarFieldTags('tag');

        $data = array();

        $tags->apiFormatField($data, $this->contact, array(), $relName, array('link' => $relName));

        // expect tags data
        $this->assertArrayHasKey($relName, $data);
        $this->assertCount(2, $data[$relName]);
    }

    /**
     * Integration test on field save
     */
    public function testApiSaveIntegration()
    {
        // FIXME: temp disabled - needs to be retested when doing MT-909
        $this->markTestSkipped('Awaiting MT-909');

        // Link tag1 to contact
        $relName = 'tags_link';
        $this->contact->load_relationship($relName);
        $this->contact->$relName->add($this->tag1);

        //Clean up any hanging related records.
        SugarRelationship::resaveRelatedBeans();

        // This forces a re-retrieval of the bean from the database
        BeanFactory::unregisterBean($this->contact);

        // call api format for tags field
        $tags = new SugarFieldTags('tag');

        $params['tags'] = array(
            // remove existing link
            array(
                'id' => $this->tag1->id,
                'name' => 'foo',
                'removed' => true,
            ),
            // add link
            array(
                'id' => $this->tag2->id,
                'name' => 'bar',
            ),
            // add link to new object
            array(
                'id' => false,
                'name' => 'new',
            ),
            // remove non-existing link
            array(
                'id' => false,
                'name' => 'newbutremoved',
                'removed' => true,
            ),
            // remove non-exiting link
            array(
                'id' => 'foo',
                'name' => 'bar',
                'removed' => true,
            ),
        );

        $tags->apiSave($this->contact, $params, 'tags', array('link' => $relName));

        // refresh contact bean
        BeanFactory::unregisterBean($this->contact);
        $contact = BeanFactory::getBean('Contacts', $this->contact->id);
        $contact->load_relationship($relName);
        $tags = $contact->$relName->getBeans();

        // check results
        $this->assertCount(2, $tags);
        $this->assertArrayHasKey($this->tag2->id, $tags);
        $this->assertArrayNotHasKey($this->tag1->id, $tags);

        // verify we have our "new" tag
        unset($tags[$this->tag2->id]);
        $new = array_pop($tags);
        $this->assertEquals('new', $new->name);
    }
}

class SugarFieldTagsTestMock extends SugarFieldTags
{
    public function parsePropertiesTest($properties)
    {
        return parent::parseProperties($properties);
    }
}
