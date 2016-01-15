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
class User
{
    public function retrieve($param){
        
    }
    //put your code here
    
    public function get_full_list($data1, $dat2)
    {
        $user1 = new stdClass();
        $user1->id = 1;
        $user1->first_name = 'prueba';
        $user1->last_name = 'uno';
        $user1->full_name = 'prueba uno';
        return array(
            $user1
        );
    }
}

