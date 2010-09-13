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
    'name' => 'RSS',

    // description of new code
    'description' => 'RSS',

    // author of new code
    'author' => 'Collin Lee, SugarCRM',

    // date published
    'published_date' => '2009/10/10',

    // version of code
    'version' => '@_SUGAR_VERSION',

    // db_version of code
    'db_version' => '@_SUGAR_VERSION',

    // type of code (valid choices are: full, langpack, module, patch, theme )
    'type' => 'module',

    // icon for displaying in UI (path to graphic contained within zip package)
    'icon' => '',

    // flag to specify whether to remove database tables on uninstallation
    'remove_tables' => 'prompt',
);


$installdefs = array(
	'id' => 'feeds',

	//'image_dir'=>'<basepath>/images',

	'copy' => array(
		array(
			'from' => '<basepath>/modules/Feeds',
			'to'   => 'modules/Feeds',
		),	
	),

	'language'=> array(
		array(
			'from'=> '<basepath>/application/app_strings.php',
			'to_module'=> 'application',
			'language'=>'en_us'
		),
	),
	
	'beans'=> array(
		array(
			'module' => 'Feeds',
			'class'  => 'Feed',
			'path'   => 'modules/Feeds/Feed.php',
			'tab'    => true,
		),
	),
	
	'post_execute'=>array(
		0 => '<basepath>/post_install/install_actions.php',
	),	

);

?>