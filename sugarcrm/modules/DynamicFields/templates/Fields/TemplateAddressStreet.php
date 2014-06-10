<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */
require_once('modules/DynamicFields/templates/Fields/TemplateField.php');

class TemplateAddressStreet extends TemplateField
{
    //Set display type to text, but stored in db as a varchar
    var $type = 'text';
    var $ext3 = 'varchar';
    var $len = 150;

    function get_field_def(){
        $vardef = parent::get_field_def();
        $vardef['dbType'] = $this->ext3;
        return $vardef;
    }
}
