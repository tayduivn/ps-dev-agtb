<?php

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'include/database/DBManagerFactory.php';

class Bug43466 extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;


/*
	public static function setUpBeforeClass()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}
*/
	public function setUp()
    {
	    $this->_db = DBManagerFactory::getInstance();
        $this->useOutputBuffering = false;
	}

	public function tearDown()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestContactUtilities::removeAllCreatedContacts();
	}

    /*
     * @group bug43466
     */

	public function providerRepairTableParams()
	{
		$fieldDefs = array (
			'name' =>
				array (
					'name' => 'name',
					'vname' => 'LBL_SUBJECT',
					'dbType' => 'varchar',
					'type' => 'name',
					'len' => '50',
					'comment' => 'Brief description of the call',
					'unified_search' => true,
					'required'=>true,
					'importable' => 'required',
				  ),

			'duration_hours' =>
				array (
					'name' => 'duration_hours',
					'vname' => 'LBL_DURATION_HOURS',
					'type' => 'int',
					'len' => '2',
					'comment' => 'Call duration, hours portion',
					'required' => true,
				),
			'duration_minutes' =>
				array (
					'name' => 'duration_minutes',
					'vname' => 'LBL_DURATION_MINUTES',
					'type' => 'int',
					'function' => array('name'=>'getDurationMinutesOptions', 'returns'=>'html', 'include'=>'modules/Calls/CallHelper.php'),
					'len' => '2',
					'group'=>'duration_hours',
					'importable' => 'required',
					'comment' => 'Call duration, minutes portion'
				),

			'date_start' =>
				array (
					'name' => 'date_start',
					'vname' => 'LBL_DATE',
					'type' => 'datetimecombo',
					'dbType' => 'datetime',
					'comment' => 'Date in which call is schedule to (or did) start',
					'importable' => 'required',
					'required' => true,
					'enable_range_search' => true,
					'options' => 'date_range_search_dom',
				  ),

			'date_end' =>
				array (
					'name' => 'date_end',
					'vname' => 'LBL_DATE_END',
					'type' => 'datetimecombo',
					'massupdate'=>false,
					'comment' => 'Date is which call is scheduled to (or did) end',
					'enable_range_search' => true,
					'options' => 'date_range_search_dom',
				),

			'parent_type'=>
				array(
					'name'=>'parent_type',
					'vname'=>'LBL_PARENT_TYPE',
					'type' => 'parent_type',
					'dbType'=>'varchar',
					'required'=>false,
					'group'=>'parent_name',
					'options'=> 'parent_type_display',
					'len'=>255,
					  'comment' => 'The Sugar object to which the call is related'
				),

			'parent_name'=>
				array(
					'name'=> 'parent_name',
					'parent_type'=>'record_type_display' ,
					'type_name'=>'parent_type',
					'id_name'=>'parent_id',
					'vname'=>'LBL_LIST_RELATED_TO',
					'type'=>'parent',
					'group'=>'parent_name',
					'source'=>'non-db',
					'options'=> 'parent_type_display',
				),
			'status' =>
				array (
					'name' => 'status',
					'vname' => 'LBL_STATUS',
					'type' => 'enum',
					'len' => 100,
					'options' => 'call_status_dom',
					'comment' => 'The status of the call (Held, Not Held, etc.)',
					'required' => true,
					'importable' => 'required',
					'default' => 'Planned',
				),
			'direction' =>
				array (
					'name' => 'direction',
					'vname' => 'LBL_DIRECTION',
					'type' => 'enum',
					'len' => 100,
					'options' => 'call_direction_dom',
					'comment' => 'Indicates whether call is inbound or outbound'
				),
			'parent_id'=>
				array(
					'name'=>'parent_id',
					'vname'=>'LBL_LIST_RELATED_TO_ID',
					'type'=>'id',
					'group'=>'parent_name',
						'reportable'=>false,
					  'comment' => 'The ID of the parent Sugar object identified by parent_type'
				),
			'reminder_checked'=>
				array(
					'name' => 'reminder_checked',
					'vname' => 'LBL_REMINDER',
					'type' => 'bool',
					'source' => 'non-db',
					'comment' => 'checkbox indicating whether or not the reminder value is set (Meta-data only)',
					'massupdate'=>false,
				),
            'reminder_time' =>
                array (
                    'name' => 'reminder_time',
                    'vname' => 'LBL_REMINDER_TIME',
                    'type' => 'enum',
                    'dbType' => 'int',
                    'options' => 'reminder_time_options',
                    'reportable' => false,
                    'massupdate' => false,
                    'default'=> -1,
                    'comment' => 'Specifies when a reminder alert should be issued; -1 means no alert; otherwise the number of seconds prior to the start'
                ),
            'email_reminder_checked' => array(
                    'name' => 'email_reminder_checked',
                    'vname' => 'LBL_EMAIL_REMINDER',
                    'type' => 'bool',
                    'source' => 'non-db',
                    'comment' => 'checkbox indicating whether or not the email reminder value is set (Meta-data only)',
                    'massupdate' => false,
                ),
            'email_reminder_time' =>
            array (
                    'name' => 'email_reminder_time',
                    'vname' => 'LBL_EMAIL_REMINDER_TIME',
                    'type' => 'enum',
                    'dbType' => 'int',
                    'options' => 'reminder_time_options',
                    'reportable' => false,
                    'massupdate' => false,
                    'default'=> -1,
                    'comment' => 'Specifies when a email reminder alert should be issued; -1 means no alert; otherwise the number of seconds prior to the start'
                ),
            'email_reminder_sent' => array(
                    'name' => 'email_reminder_sent',
                    'vname' => 'LBL_EMAIL_REMINDER_SENT',
                    'default' => 0,
                    'type' => 'bool',
                    'comment' => 'Whether email reminder is already sent',
                    'studio' => false,
                    'massupdate'=> false,
                ),

			'outlook_id' =>
				array (
					'name' => 'outlook_id',
					'vname' => 'LBL_OUTLOOK_ID',
					'type' => 'varchar',
					'len' => '255',
					'reportable' => false,
					'comment' => 'When the Sugar Plug-in for Microsoft Outlook syncs an Outlook appointment, this is the Outlook appointment item ID'
				),
			'accept_status' =>
				array (
					'name' => 'accept_status',
					'vname' => 'LBL_SUBJECT',
					'dbType' => 'varchar',
					'type' => 'varchar',
					'len' => '20',
					'source'=>'non-db',
				 ),

			'set_accept_links' =>
				array (
					'name' => 'accept_status',
					'vname' => 'LBL_SUBJECT',
					'dbType' => 'varchar',
					'type' => 'varchar',
					'len' => '20',
					'source'=>'non-db',
				),
			'contact_name' =>
				array (
					'name' => 'contact_name',
					'rname' => 'last_name',
					'db_concat_fields'=> array(0=>'first_name', 1=>'last_name'),
					'id_name' => 'contact_id',
					'massupdate' => false,
					'vname' => 'LBL_CONTACT_NAME',
					'type' => 'relate',
					'link'=>'contacts',
					'table' => 'contacts',
					'isnull' => 'true',
					'module' => 'Contacts',
					'join_name' => 'contacts',
					'dbType' => 'varchar',
					'source'=>'non-db',
					'len' => 36,
					'importable' => 'false',
					'studio' => array('required' => false, 'listview'=>true, 'visible' => false),
				),
			'account' =>
				array (
					'name' => 'account',
					'type' => 'link',
					'relationship' => 'account_calls',
						'link_type'=>'one',
					'source'=>'non-db',
						'vname'=>'LBL_OLD_ACCOUNT_LINK',
				),
			'opportunity' =>
				array (
					'name' => 'opportunity',
					'type' => 'link',
					'relationship' => 'opportunity_calls',
					'source'=>'non-db',
						'link_type'=>'one',
						'vname'=>'LBL_OPPORTUNITY',
				),
			'leads' =>
				 array (
					'name' => 'leads',
					'type' => 'link',
					'relationship' => 'calls_leads',
					'source'=>'non-db',
						'vname'=>'LBL_LEADS',
				),
			'case' =>
				array (
					'name' => 'case',
					'type' => 'link',
					'relationship' => 'case_calls',
					'source'=>'non-db',
						'link_type'=>'one',
						'vname'=>'LBL_CASE',
				),
			'accounts' =>
				array (
					'name' => 'accounts',
					'type' => 'link',
					'relationship' => 'account_calls',
					'module'=>'Accounts',
					'bean_name'=>'Account',
					'source'=>'non-db',
					'vname'=>'LBL_ACCOUNT',
				),
			'contacts' =>
				array (
					'name' => 'contacts',
					'type' => 'link',
					'relationship' => 'calls_contacts',
					'source'=>'non-db',
						'vname'=>'LBL_CONTACTS',
				),
			'users' =>
				array (
					'name' => 'users',
					'type' => 'link',
					'relationship' => 'calls_users',
					'source'=>'non-db',
						'vname'=>'LBL_USERS',
				),
			'notes' =>
				array (
					'name' => 'notes',
					'type' => 'link',
					'relationship' => 'calls_notes',
					'module'=>'Notes',
					'bean_name'=>'Note',
					'source'=>'non-db',
						'vname'=>'LBL_NOTES',
				),
			'created_by_link' =>
				array (
						'name' => 'created_by_link',
					'type' => 'link',
					'relationship' => 'calls_created_by',
					'vname' => 'LBL_CREATED_BY_USER',
					'link_type' => 'one',
					'module'=>'Users',
					'bean_name'=>'User',
					'source'=>'non-db',
				),
			'modified_user_link' =>
				array (
						'name' => 'modified_user_link',
					'type' => 'link',
					'relationship' => 'calls_modified_user',
					'vname' => 'LBL_MODIFIED_BY_USER',
					'link_type' => 'one',
					'module'=>'Users',
					'bean_name'=>'User',
					'source'=>'non-db',
				),
			'assigned_user_link' =>
				array (
					'name' => 'assigned_user_link',
					'type' => 'link',
					'relationship' => 'calls_assigned_user',
					'vname' => 'LBL_ASSIGNED_TO_USER',
					'link_type' => 'one',
					'module'=>'Users',
					'bean_name'=>'User',
					'source'=>'non-db',
				),
			'contact_id' =>
				array(
					'name' => 'contact_id',
					'type' => 'id',
					'source' => 'non-db',
					'importable' => false,
				)
			);


		$returnArray = array (
			array(
				"calls",
				$fieldDefs,
				array(
					array(
						'name' => 'idx_call_name',
						'type' => 'index',
						'fields'=> array('name'),
					),
					array(
						'name' => 'idx_status',
						'type' => 'index',
						'fields'=> array('status'),
					),
					array(
						'name' => 'idx_CALLS_date_Start',
						'type' => 'index',
						'fields' => array('date_start'),
					)
				),
				true
			),
			array(
				"calls",
				$fieldDefs,
				array(
					array(
						'name' => 'idx_call_name2',
						'type' => 'index',
						'fields'=> array('name'),
					),
					array(
						'name' => 'idx_status',
						'type' => 'index',
						'fields'=> array('status'),
					),
					array(
						'name' => 'idx_CALLS_date_Start',
						'type' => 'index',
						'fields' => array('date_start'),
					)
				),
				false
			),
			array(
				"calls",
				$fieldDefs,
				array(
					array(
						'name' => 'iDX_cAll_NAMe',
						'type' => 'index',
						'fields'=> array('name'),
					),
					array(
						'name' => 'idx_STAtus',
						'type' => 'index',
						'fields'=> array('status'),
					),
					array(
						'name' => 'idx_CALLS_date_Start',
						'type' => 'index',
						'fields' => array('date_start'),
					)
				),
				true
			),
			array(
				"calls",
				$fieldDefs,
				array(
					array(
						'name' => 'idx_call_name',
						'type' => 'index',
						'fields'=> array('name'),
					),
					array(
						'name' => 'idx_status',
						'type' => 'index',
						'fields'=> array('status'),
					),
					array(
						'name' => 'idx_calls_date_start2',
						'type' => 'index',
						'fields' => array('date_start'),
					)
				),
				false
			)

		);
        return $returnArray;
	}

	static function isEmpty($result){
		if(trim($result) == ""){
			return true;
		}
		return false;
	}

	/**
     * @dataProvider providerRepairTableParams
     */


    public function testRepairTableParams(
	    $tablename,
        $fielddefs,
        $indices,
		$expectedResult
		)
    {

		if ( $expectedResult ) {
            $this->assertTrue($this->isEmpty($this->_db->repairTableParams($tablename, $fielddefs, $indices, false)));
        }
        else {
            $this->assertFalse($this->isEmpty($this->_db->repairTableParams($tablename, $fielddefs, $indices, false)));
        }
    }


	public function providerCompareVardefs()
    {
        $returnArray = array(
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'Foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo2',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                false),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '123',
                    ),
                true),
			array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '123',
                    ),
                array(
                    'name' => 'Foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                false)
           );

        return $returnArray;
    }

    /**
     * @dataProvider providerCompareVarDefs
     */

    public function testCompareVarDefs($fieldDef1,$fieldDef2,$expectedResult)
    {
        if ( $expectedResult ) {
            $this->assertTrue($this->_db->compareVarDefs($fieldDef1,$fieldDef2));
        }
        else {
            $this->assertFalse($this->_db->compareVarDefs($fieldDef1,$fieldDef2));
        }
    }
}
