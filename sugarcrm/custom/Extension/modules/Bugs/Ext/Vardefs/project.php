<?php
//BEGIN BUGS VARDEFS 
// adding project field
$dictionary['Bug']['fields']['projects'] = array (
    'name' => 'projects',
    'type' => 'link',
    'relationship' => 'projects_bugs',
    'source'=>'non-db',
    'vname'=>'LBL_PROJECTS',
);
//END BUGS VARDEFS
?>