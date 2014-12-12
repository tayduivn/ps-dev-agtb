<?php

class pmse_MockFlow
{
    public $id;
    public $flo_uid;
    public $flo_id;
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
    
    public function toArray()
    {
        return get_object_vars($this);
    }
    
}
