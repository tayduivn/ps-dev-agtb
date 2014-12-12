<?php

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

?>
