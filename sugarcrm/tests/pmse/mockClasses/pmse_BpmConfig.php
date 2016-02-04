<?php
//FILE SUGARCRM flav=ent ONLY
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
class pmse_BpmConfig {

    public $cas_status = 'TODO';
    
    public function get_full_list()
    {
        return array(
            (object)array('name' => 'error_number_of_cycles', 'cfg_value' => '10'),
            (object)array('name' => 'error_timeout', 'cfg_value' => '30'),
            (object)array('name' => 'logger_level', 'cfg_value' => 'INFO')
        );
    }
}
