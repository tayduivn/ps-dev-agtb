<?php
//BEGIN PROJECT VARDEFS 
$dictionary['Project']['fields']['cases'] = array (
    'name' => 'cases',
    'type' => 'link',
    'relationship' => 'projects_cases',
    'side' => 'right',
    'source'=>'non-db',
    'vname'=>'LBL_CASES',
);

$dictionary['Project']['fields']['bugs'] = array (
    'name' => 'bugs',
    'type' => 'link',
    'relationship' => 'projects_bugs',
    'side' => 'right',
    'source'=>'non-db',
    'vname'=>'LBL_BUGS',
);
$dictionary['Project']['fields']['products'] = array (
    'name' => 'products',
    'type' => 'link',
    'relationship' => 'projects_products',
    'side' => 'right',
    'source'=>'non-db',
    'vname'=>'LBL_PRODUCTS',
);
//END PROJECT VARDEFS  
?>