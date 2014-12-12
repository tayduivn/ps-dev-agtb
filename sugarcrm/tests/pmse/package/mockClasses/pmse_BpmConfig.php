<?php

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
