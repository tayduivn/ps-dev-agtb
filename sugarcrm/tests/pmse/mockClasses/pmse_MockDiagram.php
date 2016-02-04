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
class pmse_MockDiagram
{
    public $id;
    public $object_name;
    public $dia_uid;
    
    public function __construct($name = 'pmse_BpmnDiagram')
    {
        $this->object_name = $name;
    }

    public function getIndices()
    {
    }
    
    public function get_where()
    {
    }

    public function retrieve_by_string_fields($array) {
        foreach ($array as $key => $field) {
            $this->$key = $field;
        }
        return $this;
    }
    
    public function save()
    {
        return true;
    }
}
