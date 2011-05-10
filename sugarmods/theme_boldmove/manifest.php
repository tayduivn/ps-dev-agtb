<?PHP

// manifest file for information regarding application of new code
$manifest = array(
	'acceptable_sugar_flavors' => array( 'CE', 'PRO', 'ENT' ),

    // only install on the following regex sugar versions (if empty, no check)
    'acceptable_sugar_versions' => array(
    	'regex_matches' => array( '6\.0\.\d\w*' ),
    ),

    'is_uninstallable'=>true,

    // name of new code
    'name' => 'Bold Move Theme from Sugar 5.5.1',

    // description of new code
    'description' => 'Bold Move Theme from Sugar 5.5.1',

    // author of new code
    'author' => 'John Mertic, SugarCRM',

    // date published
    'published_date' => '2010/03/01',

    // version of code
    'version' => '@_SUGAR_VERSION',

    // db_version of code
    'db_version' => '@_SUGAR_VERSION',

    // type of code (valid choices are: full, langpack, module, patch, theme )
    'type' => 'theme',

    // icon for displaying in UI (path to graphic contained within zip package)
    'icon' => '',

    // flag to specify whether to remove database tables on uninstallation
    'remove_tables' => 'prompt',
);


$installdefs = array(
	'id' => 'boldmove',
	'copy' => array(
		array(
		    'from' => '<basepath>/themes/BoldMove',
		    'to' => 'themes/BoldMove',
		),	
	),
);
