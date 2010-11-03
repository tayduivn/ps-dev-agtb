<?php

$listViewDefs['Leads']['LEAD_RELATION_C'] = array(
	'width' => '10',
	'label' => 'lead_relation_c',
	'default' => true,
);

// SADEK BEGIN TEMPORARY CUSTOMIZATION FOR AMIE TO SEE LEAD GROUP IN THE LISTVIEW
if($GLOBALS['current_user']->user_name == 'amie'){
    $listViewDefs['Leads']['LEAD_GROUP_C'] = array(
        'width' => '10',
        'label' => 'Lead Group',
        'default' => true,
    );
}
// SADEK END TEMPORARY CUSTOMIZATION FOR AMIE TO SEE LEAD GROUP IN THE LISTVIEW

?>
