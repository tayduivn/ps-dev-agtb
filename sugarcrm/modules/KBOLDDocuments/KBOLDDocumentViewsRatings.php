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


class KBOLDDocumentViewsRating extends SugarBean {

    var $id;
    var $kbolddocument_id;
    var $views_number;
    var $ratings_number;
    var $date_entered;
    var $date_modified;
    var $modified_user_id;
    //BEGIN SUGARCRM flav=pro ONLY
    var $team_id;
    //END SUGARCRM flav=pro ONLY


    var $table_name = "kbolddocuments_views_ratings";
    var $object_name = "KBOLDDocumentViewsRating";
    var $module_name = 'KBOLDDocumentsViewsRating';
    var $disable_custom_fields = true;
    var $user_preferences;

    var $encodeFields = Array ();

    var $new_schema = true;
    var $module_dir = 'KBOLDDocuments';

    /**
     * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @deprecated
     */
    public function KBOLDDocumentViewsRatings()
    {
        self::__construct();
    }

    public function __construct() {
        parent::__construct();
        $this->setupCustomFields('KBOLDDocumentViewsRating'); //parameter is module name
        $this->disable_row_level_security = false;
    }

    function save($check_notify = false) {
        return parent::save($check_notify);
    }

    function retrieve($id, $encode = false) {
        $ret = parent::retrieve($id, $encode);
        return $ret;
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

    function bean_implements($interface) {
        switch ($interface) {
            case 'ACL' :
                return true;
        }
        return false;
    }
}
?>