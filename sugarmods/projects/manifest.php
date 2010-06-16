<?PHP

// manifest file for information regarding application of new code
$manifest = array(
	//BEGIN SUGARCRM flav=ent ONLY 
	'acceptable_sugar_flavors' => array( 'ENT' ),
	/*
	//END SUGARCRM flav=ent ONLY 
	//BEGIN SUGARCRM flav=pro ONLY 
	'acceptable_sugar_flavors' => array( 'PRO' ),
	//END SUGARCRM flav=pro ONLY 
	//BEGIN SUGARCRM flav=ent ONLY 
	 */
	//END SUGARCRM flav=ent ONLY 
	//BEGIN SUGARCRM flav=pro ONLY 
	/*
	//END SUGARCRM flav=pro ONLY 
	'acceptable_sugar_flavors' => array( 'OS' ),
	//BEGIN SUGARCRM flav=pro ONLY 
	*/
	//END SUGARCRM flav=pro ONLY 

    // only install on the following regex sugar versions (if empty, no check)
    'acceptable_sugar_versions' => array(
    	'regex_matches' => array( '5\.0\.0\w*' ),
    ),

    'is_uninstallable'=>true,

    // name of new code
    'name' => 'Advanced Project Management',

    // description of new code
    'description' => 'Advanced Project Management 5.0.0',

    // author of new code
    'author' => 'Jenny Gonsalves, Andrew Wu, SugarCRM',

    // date published
    'published_date' => '2007/09/30',

    // version of code
    'version' => '5.0.0',

    // db_version of code
    'db_version' => '5.0.0',

    // type of code (valid choices are: full, langpack, module, patch, theme )
    'type' => 'module',

    // icon for displaying in UI (path to graphic contained within zip package)
    'icon' => '',

    // flag to specify whether to remove database tables on uninstallation
    'remove_tables' => 'prompt',
);


$installdefs = array(
	'id' => 'project',
	'image_dir'=>'<basepath>/images',
	'copy' => array(
	//BEGIN SUGARCRM flav=pro ONLY 
		array(
			'from' => '<basepath>/modules/Holidays',
			'to'   => 'modules/Holidays',
		),
		array(
			'from' => '<basepath>/modules/ProjectResources',
			'to'   => 'modules/ProjectResources',
		),
		array(
			'from' => '<basepath>/include/generic/SugarWidgets/SugarWidgetSubPanelTopSelectContactsButton.php',
			'to'   => 'include/generic/SugarWidgets/SugarWidgetSubPanelTopSelectContactsButton.php',
		),
		array(
			'from' => '<basepath>/include/generic/SugarWidgets/SugarWidgetSubPanelTopSelectUsersButton.php',
			'to'   => 'include/generic/SugarWidgets/SugarWidgetSubPanelTopSelectUsersButton.php',
		),
		array(
			'from' => '<basepath>/include/generic/SugarWidgets/SugarWidgetSubPanelEditProjectTasksButton.php',
			'to'   => 'include/generic/SugarWidgets/SugarWidgetSubPanelEditProjectTasksButton.php',
		),
		array(
			'from' => '<basepath>/include/generic/SugarWidgets/SugarWidgetSubPanelRemoveButtonProjects.php',
			'to'   => 'include/generic/SugarWidgets/SugarWidgetSubPanelRemoveButtonProjects.php',
		),

		array(
			'from' => '<basepath>/include/javascript/yui/ext/Element.js',
			'to'   => 'include/javascript/yui/ext/Element.js',
		),
		array(
			'from' => '<basepath>/include/javascript/yui/ext/SplitBar.js',
			'to'   => 'include/javascript/yui/ext/SplitBar.js',
		),
		array(
			'from' => '<basepath>/include/javascript/yui/container_core.js',
			'to'   => 'include/javascript/yui/container_core.js',
		),
		// Users subpanel for Projects
		array(
			'from' => '<basepath>/modules/Users/subpanels/ForProject.php',
			'to'   => 'modules/Users/subpanels/ForProject.php',
		),

		// Contacts subpanel for Projects
		array(
			'from' => '<basepath>/modules/Contacts/subpanels/ForProject.php',
			'to'   => 'modules/Contacts/subpanels/ForProject.php',
		),

		array(
			'from' => '<basepath>/modules/Project',
			'to'   => 'modules/Project',
		),
		array(
			'from' => '<basepath>/modules/ProjectTask',
        	'to'   => 'modules/ProjectTask',
		),

		// Copy md5 file
		array(
			'from' => '<basepath>/projects_files.md5',
			'to'   => 'projects_files.md5',
		),
	//END SUGARCRM flav=pro ONLY 
	),

	'user_page' => array (
	//BEGIN SUGARCRM flav=pro ONLY 
		array(
			'from' => '<basepath>/modules/Users/holiday_subpanel.php',
		),
	//END SUGARCRM flav=pro ONLY 
	),

	'language'=> array(
	//BEGIN SUGARCRM flav=pro ONLY 
		array(
			'from'=> '<basepath>/application/app_strings.php',
			'to_module'=> 'application',
			'language'=>'en_us'
		),
		array(
			'from' => '<basepath>/modules/Contacts/en_us.lang.php',
			'to_module' => 'Contacts',
			'language' =>'en_us'
		),
		array(
			'from' => '<basepath>/modules/Users/en_us.lang.php',
			'to_module' => 'Users',
			'language' =>'en_us'
		),
	//END SUGARCRM flav=pro ONLY 
		array(
			'from' => '<basepath>/modules/Bugs/en_us.lang.php',
			'to_module' => 'Bugs',
			'language' =>'en_us'
		),
		array(
			'from' => '<basepath>/modules/Cases/en_us.lang.php',
			'to_module' => 'Cases',
			'language' =>'en_us'
		),
		array(
			'from' => '<basepath>/modules/Products/en_us.lang.php',
			'to_module' => 'Products',
			'language' =>'en_us'
		),
	//BEGIN SUGARCRM flav=pro ONLY 
	/*
	//END SUGARCRM flav=pro ONLY 
		array(
			'from' => '<basepath>/language/project_en_us.lang.php',
			'to_module' => 'Project',
			'language' =>'en_us'
		),
	//BEGIN SUGARCRM flav=pro ONLY 
	 */
	//END SUGARCRM flav=pro ONLY 

	),
	'beans'=> array(
	//BEGIN SUGARCRM flav=pro ONLY 
		array(
			'module' => 'ProjectResources',
			'class'  => 'ProjectResource',
			'path'   => 'modules/ProjectResources/ProjectResource.php',
			'tab'    => false,
		),
		array(
			'module' => 'Holidays',
			'class'  => 'Holiday',
			'path'   => 'modules/Holidays/Holiday.php',
			'tab'    => false,
		),
	//END SUGARCRM flav=pro ONLY 
	),

    'layoutdefs'=> array(
    //BEGIN SUGARCRM flav=pro ONLY 
		array(
			'from' => '<basepath>/layoutdefs/emails_layoutdefs.php',
			'to_module' => 'Emails',
		),
		array(
			'from' => '<basepath>/layoutdefs/users_layoutdefs.php',
			'to_module' => 'Users',
		),
	//END SUGARCRM flav=pro ONLY 

	//BEGIN SUGARCRM flav=pro ONLY 
	/*
	//END SUGARCRM flav=pro ONLY 
		array(
			'from'=>'<basepath>/layoutdefs/project_layoutdefs.php',
			'to_module' => 'Project',
		),
	//BEGIN SUGARCRM flav=pro ONLY 
	 */
	//END SUGARCRM flav=pro ONLY 
    ),
	//BEGIN SUGARCRM flav=pro ONLY 
	/*
	//END SUGARCRM flav=pro ONLY 
    'vardefs'=> array(
	    array(
			'from' => '<basepath>/vardefs/project_vardefs.php',
			'to_module' => 'Project',
		),
    ),
	//BEGIN SUGARCRM flav=pro ONLY 
	 */
	//END SUGARCRM flav=pro ONLY 
	'relationships'=>array(
	//BEGIN SUGARCRM flav=pro ONLY 
		array(
			'module'=> 'Users',
			'meta_data' => '<basepath>/relationships/users_holidaysMetaData.php',
			'module_vardefs'=>'<basepath>/vardefs/users_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/users_layoutdefs.php'
		),
	//END SUGARCRM flav=pro ONLY 
		array(
			'module'=> 'Bugs',
			'meta_data' => '<basepath>/relationships/project_bugsMetaData.php',
			'module_vardefs' => '<basepath>/vardefs/bugs_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/bugs_layoutdefs.php'
		),
		array(
			'module'=> 'Cases',
			'meta_data' => '<basepath>/relationships/project_casesMetaData.php',
			'module_vardefs' => '<basepath>/vardefs/cases_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/cases_layoutdefs.php'
		),
	//BEGIN SUGARCRM flav=pro ONLY 
		array(
			'module'=> 'Products',
			'meta_data' => '<basepath>/relationships/project_productsMetaData.php',
			'module_vardefs' => '<basepath>/vardefs/products_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/products_layoutdefs.php'
		),
	//END SUGARCRM flav=pro ONLY 
		array(
			'module'=> 'Accounts',
			'meta_data' => '<basepath>/relationships/projects_accountsMetaData.php',
			'module_vardefs' => '',
			'module_layoutdefs' => ''
		),
		array(
			'module'=> 'Contacts',
			'meta_data' => '<basepath>/relationships/projects_contactsMetaData.php',
			'module_vardefs' => '',
			'module_layoutdefs'=>''
		),
		array(
			'module'=> 'Opportunities',
			'meta_data' => '<basepath>/relationships/projects_opportunitiesMetaData.php',
			'module_vardefs' => '',
			'module_layoutdefs'=>''
		),
	//BEGIN SUGARCRM flav=pro ONLY 
		array(
			'module'=> 'Quotes',
			'meta_data' => '<basepath>/relationships/projects_quotesMetaData.php',
			'module_vardefs' => '',
			'module_layoutdefs'=>''
		),
	//END SUGARCRM flav=pro ONLY 

	),

	'pre_execute'=>array(
		0 => '<basepath>/pre_install/pre_install.php',
	),

	'post_execute'=>array(
		0 => '<basepath>/post_install/post_install.php',
	),

	'pre_uninstall'=>array(
		0 => '<basepath>/pre_uninstall/pre_uninstall.php',
	),

	'post_uninstall'=>array(
		0 => '<basepath>/post_uninstall/post_uninstall.php',
	),
);

?>
