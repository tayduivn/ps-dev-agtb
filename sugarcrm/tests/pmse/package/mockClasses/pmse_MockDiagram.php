<?php


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
