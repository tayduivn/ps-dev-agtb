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

require_once 'include/SugarObjects/templates/person/Person.php';

class Addressee extends Person {
    public $new_schema = true;
    public $module_dir = 'Addressees';
    public $object_name = 'Addressee';
    public $object_names = 'Addressees';
    public $table_name = 'addressees';

    /**
     * @inheritdoc
     */
    public function save($check_notify = false)
    {
        if (!$this->assigned_user_id) {
            $this->assigned_user_id = $GLOBALS['current_user']->id;
        }

        return parent::save($check_notify);
    }
}
