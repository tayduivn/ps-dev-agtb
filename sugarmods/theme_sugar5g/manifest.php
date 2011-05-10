<?PHP

// manifest file for information regarding application of new code
$manifest = array(
	'acceptable_sugar_flavors' => array( 'CE', 'PRO', 'ENT' ),

    // only install on the following regex sugar versions (if empty, no check)
    'acceptable_sugar_versions' => array(
    	'regex_matches' => array( '6\.1\.\d\w*' ),
    ),

    'is_uninstallable'=>true,

    // name of new code
    'name' => 'Classic with Group Tabs',

    // description of new code
    'description' => 'Default theme from Sugar 5 with Group Tabs enabled',

    // author of new code
    'author' => 'John Mertic, SugarCRM',

    // date published
    'published_date' => '2010/12/02',

    // version of code
    'version' => '6.1.0',

    // type of code (valid choices are: full, langpack, module, patch, theme )
    'type' => 'theme',

    // icon for displaying in UI (path to graphic contained within zip package)
    'icon' => '',

    // flag to specify whether to remove database tables on uninstallation
    'remove_tables' => 'prompt',
);


$installdefs = array(
	'id' => 'sugar5g',
	'copy' => array(
		array(
		    'from' => '<basepath>/themes/Sugar5G',
		    'to' => 'themes/Sugar5G',
		),	
	),
);
