<?php

class pmse_MockActivity
{
    public $id;
    public $act_uid;
    public $act_default_flow;
    public $object_name;
    
    public function __construct($name = '')
    {
        $this->object_name = $name;
        $this->act_default_flow = 'some flow';
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
