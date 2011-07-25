<?php
/* example data creation config, copy to createdefs.php */
$createdef['example@example.com']['Contacts'] = array(
        'fields' => array(
                'email1' => '{from_addr}',
                'last_name' => '{from_name}',
                'description' => 'created from {subject}',
                'lead_source' => 'Email',
        ),
);
