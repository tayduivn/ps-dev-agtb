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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
require_once ('include/api/RestService.php');
require_once ("clients/base/api/FilterApi.php");

/**
 * @group ApiTests
 */
class RestFilterTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static $notes, $opps, $accounts;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');

        // Need at least 20 records so we can test pagination
        for ( $i = 0 ; $i < 20 ; $i++ ) {
            $account = BeanFactory::newBean('Accounts');
            $account->id = 'UNIT-TEST-' . create_guid_section(10);
            $account->new_with_id = true;
            $account->name = "TEST $i Account";
            $account->billing_address_postalcode = ($i%10)."0210";
            $account->save();
            self::$accounts[] = $account;
            for ( $ii = 0; $ii < 2 ; $ii++ ) {
                $opp = BeanFactory::newBean('Opportunities');
                $opp->id = 'UNIT-TEST-' . create_guid_section(10);
                $opp->new_with_id = true;
                $opp->name = "TEST $ii Opportunity FOR $i Account";
                $opp->amount = $ii * 10000;
                $opp->expected_close_date = '12-1'.$ii.'-2012';
                $opp->save();
                self::$opps[] = $opp;
                $account->load_relationship('opportunities');
                $account->opportunities->add(array($opp));
            }
            if ( $i < 5 ) {
                // Only need a few notes
                $note = BeanFactory::newBean('Notes');
                $note->id = 'UNIT-TEST-' . create_guid_section(10);
                $note->new_with_id = true;
                $note->name = "Test $i Note";
                $note->description = "This is a note for account $i";
                $note->save();
                $account->load_relationship('notes');
                $account->notes->add(array($note));
                $note->save();
                self::$notes[] = $note;
            }
        }

        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();
    }

    public function setUp()
    {
        $this->filterApi = new FilterApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        //BEGIN SUGARCRM flav=pro ONLY
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");
        //END SUGARCRM flav=pro ONLY
    }

    public static function tearDownAfterClass()
    {
        if (count(self::$accounts)) {
            $accountIds = array();
            foreach ( self::$accounts as $account ) {
                $accountIds[] = $account->id;
            }
            $accountIds = "('".implode("','",$accountIds)."')";
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
        }

        // Opportunities clean up
        if (count(self::$opps)) {
            $oppIds = array();
            foreach ( self::$opps as $opp ) {
                $oppIds[] = $opp->id;
            }
            $oppIds = "('".implode("','",$oppIds)."')";
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
        }
        // Notes cleanup
        if (count(self::$notes)) {
            $noteIds = array();
            foreach ( self::$notes as $note ) {
                $noteIds[] = $note->id;
            }
            $noteIds = "('".implode("','",$noteIds)."')";

            $GLOBALS['db']->query("DELETE FROM notes WHERE id IN {$noteIds}");
        }
        SugarTestFilterUtilities::removeAllCreatedFilters();
        SugarTestHelper::tearDown();
    }

    public function testSimpleFilter()
    {
        $reply = $this->filterApi->filterList($this->serviceMock,
            array('module' => 'Accounts',
                'filter' => array(array("name" => "TEST 7 Account")),
                'fields' => 'id,name'));
        $this->assertEquals('TEST 7 Account',$reply['records'][0]['name'],'Simple: The name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'Simple: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['records']),'Simple: Returned too many results');
    }

    public function testSimpleJoinFilter()
    {
        $reply = $this->filterApi->filterList($this->serviceMock,
            array('module' => 'Accounts',
                'filter' => array(array("notes.name" => "Test 3 Note")),
                'fields' => 'id,name'));
        $this->assertEquals('TEST 3 Account',$reply['records'][0]['name'],'SimpleJoin: The account name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'SimpleJoin: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['records']),'SimpleJoin: Returned too many results');
    }

    public function testSimpleFilterWithOffset()
    {
        $reply = $this->filterApi->filterList($this->serviceMock,
            array('module' => 'Accounts',
                'filter' => array(array("name" => array('$starts' => "TEST 1"))),
                'fields' => 'id,name', 'max_num' => '5'));
        $this->assertEquals(5,$reply['next_offset'],'Offset-1: Next offset is not set correctly');
        $this->assertEquals(5,count($reply['records']),'Offset-1: Returned too many results');

        $reply = $this->filterApi->filterList($this->serviceMock,
            array('module' => 'Accounts',
                'filter' => array(array("name" => array('$starts' => "TEST 1"))),
                'fields' => 'id,name', 'max_num' => '5', 'offset' => '5'));
        $this->assertEquals(10,$reply['next_offset'],'Offset-2: Next offset is not set correctly');
        $this->assertEquals(5,count($reply['records']),'Offset-2: Returned too many results');

        $reply = $this->filterApi->filterList($this->serviceMock,
            array('module' => 'Accounts',
                'filter' => array(array("name" => array('$starts' => "TEST 1"))),
                'fields' => 'id,name', 'max_num' => '5', 'offset' => '10'));
        $this->assertEquals(-1,$reply['next_offset'],'Offset-3: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['records']),'Offset-3: Returned too many results');
    }

    public function testOrFilter()
    {
        $reply = $this->filterApi->filterList($this->serviceMock,
                array('module' => 'Accounts',
                        'filter' => array(array('$or' => array(
                                                            array('name' => "TEST 7 Account"),
                                                            array('name' => "TEST 17 Account"),
                                                            )
                                        )),
                        'fields' => 'id,name', 'order_by' => 'name:ASC'));

        $this->assertEquals('TEST 17 Account',$reply['records'][0]['name'],'Or-1: The name is not set correctly');
        $this->assertEquals('TEST 7 Account',$reply['records'][1]['name'],'Or-2: The name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'Or: Next offset is not set correctly');
        $this->assertEquals(2,count($reply['records']),'Or: Returned too many results');
    }

    public function testAndFilter()
    {
        $reply = $this->filterApi->filterList($this->serviceMock,
                array('module' => 'Accounts',
                        'filter' => array(array('$and' => array(
                                                            array('name' => array('$starts' => "TEST 1")),
                                                            array('billing_address_postalcode' => "70210"),
                                                            )
                                        )),
                        'fields' => 'id,name', 'order_by' => 'name:ASC'));

        $this->assertEquals('TEST 17 Account',$reply['records'][0]['name'],'And: The name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'And: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['records']),'And: Returned too many results');
    }

    public function testNoFilter()
    {
        $reply = $this->filterApi->filterList($this->serviceMock,
                array('module' => 'Accounts', 'filter' => array(), 'max_num' => '10'));

        $this->assertNotEmpty($reply, "Empty filter returned no results.");
        $this->assertEquals(10,$reply['next_offset'], "Empty filter did not return at least 10 results.");

    }

    //BEGIN SUGARCRM flav=pro ONLY
    public function testFavoriteFilter()
    {
        $this->assertEquals('TEST 4 Account',self::$accounts[4]->name,'Favorites: Making sure the name is correct before favoriting.');

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Accounts',self::$accounts[4]->id);
        $fav->new_with_id = true;
        $fav->module = 'Accounts';
        $fav->record_id = self::$accounts[4]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->filterApi->filterList($this->serviceMock,
                array('module' => 'Accounts',
                        'filter' => array(array('$favorite' => '')),
                        'fields' => 'id,name', 'order_by' => 'name:ASC'));

        $this->assertEquals('TEST 4 Account',$reply['records'][0]['name'],'Favorites: The name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'Favorites: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['records']),'Favorites: Returned too many results');
    }

    public function testRelatedFavoriteFilter()
    {
        $this->assertEquals('TEST 0 Opportunity FOR 3 Account', self::$opps[6]->name,'FavRelated: Making sure the name is correct before favoriting.');

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Opportunities', self::$opps[6]->id);
        $fav->new_with_id = true;
        $fav->module = 'Opportunities';
        $fav->record_id = self::$opps[6]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();
        $reply = $this->filterApi->filterList($this->serviceMock,
                array('module' => 'Accounts',
                        'filter' => array(array('$favorite' => 'opportunities')),
                        'fields' => 'id,name', 'order_by' => 'name:ASC'));

        $this->assertEquals('TEST 3 Account',$reply['records'][0]['name'],'FavRelated: The name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'FavRelated: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['records']),'FavRelated: Returned too many results');
    }

    public function testMultipleRelatedFavoriteFilter()
    {
        $this->assertEquals('TEST 0 Opportunity FOR 0 Account', self::$opps[0]->name,'FavMulRelated: Making sure the opp name is correct before favoriting.');

        $this->assertEquals('Test 4 Note', self::$notes[4]->name,'FavMulRelated: Making sure the note name is correct before favoriting.');

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Opportunities', self::$opps[0]->id);
        $fav->new_with_id = true;
        $fav->module = 'Opportunities';
        $fav->record_id = self::$opps[0]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Notes', self::$notes[4]->id);
        $fav->new_with_id = true;
        $fav->module = 'Notes';
        $fav->record_id = self::$notes[4]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->filterApi->filterList($this->serviceMock,
                array('module' => 'Accounts',
                        'filter' => array(array('$or' => array(
                                                          array('$favorite' => 'opportunities'),
                                                          array('$favorite' => 'notes'),
                                    ))),
                        'fields' => 'id,name', 'order_by' => 'name:ASC'));

        $this->assertEquals('TEST 0 Account',$reply['records'][0]['name'],'FavMulRelated: The first name is not set correctly');
        $this->assertEquals('TEST 4 Account',$reply['records'][1]['name'],'FavMulRelated: The second name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'FavMulRelated: Next offset is not set correctly');
        $this->assertEquals(2,count($reply['records']),'FavMulRelated: Returned too many results');

    }
    //END SUGARCRM flav=pro ONLY

    public function testOwnerFilter()
    {
        $this->assertEquals('TEST 7 Account',self::$accounts[7]->name,'Owner: Making sure the name is correct before ownering.');

        self::$accounts[7]->assigned_user_id = $GLOBALS['current_user']->id;
        self::$accounts[7]->save();

        $reply = $this->filterApi->filterList($this->serviceMock,
                array('module' => 'Accounts',
                        'filter' => array(array('$owner' => '')),
                        'fields' => 'id,name', 'order_by' => 'name:ASC'));

        $this->assertEquals('TEST 7 Account',$reply['records'][0]['name'],'Owner: The name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'Owner: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['records']),'Owner: Returned too many results');
    }

    public function testRelatedOwnerFilter()
    {
        $this->assertEquals('TEST 1 Opportunity FOR 3 Account',self::$opps[7]->name,'OwnerRelated: Making sure the name is correct before ownering.');

        self::$opps[7]->assigned_user_id = $GLOBALS['current_user']->id;
        self::$opps[7]->save();

        $reply = $this->filterApi->filterList($this->serviceMock,
                array('module' => 'Accounts',
                        'filter' => array(array('$owner' => 'opportunities')),
                        'fields' => 'id,name', 'order_by' => 'name:ASC'));

        $this->assertEquals('TEST 3 Account',$reply['records'][0]['name'],'OwnerRelated: The name is not set correctly');
        $this->assertEquals(-1,$reply['next_offset'],'OwnerRelated: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['records']),'OwnerRelated: Returned too many results');
    }

    public function testFilteringOnARelationship()
    {
        $account_id = self::$accounts[0]->id;
        $oppty_id = self::$opps[0]->id;
        $reply = $this->filterApi->filterRelated($this->serviceMock,
                array('module' => 'Accounts', 'record' => $account_id,
                        'link_name' => 'opportunities',
                        'filter' => array(array('name' => array('$starts' => "TEST 0 Opportunity"))),
                        'fields' => 'id,name', 'order_by' => 'name:ASC'));

        $this->assertEquals(1, count($reply['records']));
        $this->assertEquals($oppty_id, $reply['records'][0]['id']);
    }
}
