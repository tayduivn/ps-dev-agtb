<?php
if(!defined('sugarEntry') || !sugarEntry)
	die('Not A Valid Entry Point');
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

require_once ('include/upload_file.php');


// User is used to store Forecast information.
class KBOLDDocumentKBOLDTag extends SugarBean {

	var $id;
	var $kbolddocument_id;
	var $kboldtag_id;
	var $created_by;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $kbolddocument_name;
	//BEGIN SUGARCRM flav=pro ONLY
	var $team_id;
	//END SUGARCRM flav=pro ONLY


	var $table_name = "kbolddocuments_kboldtags";
	var $object_name = "KBOLDDocumentKBOLDTag";
	var $user_preferences;

	var $encodeFields = Array ();

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array ('revision');



	var $new_schema = true;
	var $module_dir = 'KBOLDDocumentKBOLDTags';

//todo remove leads relationship.
	var $relationship_fields = Array('contract_id'=>'contracts',

		'lead_id' => 'leads'
	 );


    /**
     * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @deprecated
     */
    public function KBOLDDocumentKBOLDTag()
    {
        self::__construct();
    }

	public function __construct() {
		parent::__construct();
		$this->setupCustomFields('KBOLDDocumentKBOLDTags'); //parameter is module name
		$this->disable_row_level_security = false;
	}

	function save($check_notify = false) {
		return parent::save($check_notify);
	}

	function fill_in_additional_detail_fields()
	{
	    $kbdoc = BeanFactory::getBean('KBOLDDocuments', $this->kbolddocument_id);
	    if ( !empty($kbdoc->id) ) {
	        $this->kbolddocument_name = $kbdoc->kbolddocument_name;
	    }
	}
	function get_summary_text() {
		return "$this->kbolddocument_name";
	}

	function is_authenticated() {
		return $this->authenticated;
	}

	function fill_in_additional_list_fields() {
		$this->fill_in_additional_detail_fields();
	}

	function mark_relationships_deleted($id) {
		//do nothing, this call is here to avoid default delete processing since
		//delete.php handles deletion of document revisions.
	}


}
?>