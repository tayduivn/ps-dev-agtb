<?php
/*
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

class WebLogicHook extends SugarBean
{
    public $id;
    public $name;
    public $module_name;
    public $request_method;
    public $url;
    public $trigger_event;

    public $table_name = 'web_logic_hooks';
    public $object_name = 'WebLogicHook';
    public $module_dir = 'WebLogicHooks';
    public $new_schema = true;
    public $importable = true;

    /**
     * Default Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

}
