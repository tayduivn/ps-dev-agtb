<?php
class TestSugarBean extends SugarBean
{
    public $object_name = "TestSugarBean";
	var $table_name = "test";
	var $module_dir = 'Tests';
	public $disable_row_security = true;

    public function __construct($name, $vardefs)
    {
        global $dictionary;
        $this->object_name = $name;
        $this->table_name = $name;
        $dictionary[$this->object_name] = $vardefs;
        parent::SugarBean();
    }
}
