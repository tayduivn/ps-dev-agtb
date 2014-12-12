<?php


class SugarBean
{
    public $id;
    public $bou_uid;
    public $object_name;
    public $column_fields;

    public function __construct($name = '')
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
    
    public function toArray()
    {
        return get_object_vars($this);
    }
    
}