<?php
// FILE SUGARCRM flav=pro ONLY 
//BEGIN PRODUCTS VARDEFS 
// adding project field
$dictionary['Product']['fields']['projects'] = array (
    'name' => 'projects',
    'type' => 'link',
    'relationship' => 'projects_products',
    'source'=>'non-db',
    'vname'=>'LBL_PROJECTS',
);
//END PRODUCTS VARDEFS
?>