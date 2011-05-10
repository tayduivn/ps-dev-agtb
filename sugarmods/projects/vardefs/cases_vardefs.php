<?php
//BEGIN CASES VARDEFS 
// adding project field
$dictionary['Case']['fields']['projects'] =   array (
    'name' => 'projects',
    'type' => 'link',
    'relationship' => 'projects_cases',
    'source'=>'non-db',
    'vname'=>'LBL_PROJECTS',
);
//END CASES VARDEFS
?>