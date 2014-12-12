<?php
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

class Tag extends Basic
{
    public $module_dir = 'Tags';
    public $object_name = 'Tag';
    public $table_name = 'tags';
    public $new_schema = true;
    public $importable = false;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function save($check_notify = false)
    {
        // We need a tag name or really what's the point?
        if (empty($this->name)) {
            return false;
        }

        // For searching making sure we lowercase the name to name_lower
        $this->name_lower = strtolower($this->name);
        return parent::save($check_notify);
    }
}
