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

    // only install on the following regex sugar versions (if empty, no check)
    'acceptable_sugar_versions' => array(
    	'regex_matches' => array( '5\.0\.0\w*' ),
    ),

    'is_uninstallable'=>true,

    // name of new code
    'name' => 'Knowledge Base',

    // description of new code
    'description' => 'Knowledge Base 5.0.0',

    // author of new code
    'author' => 'Vineet Dhyani, Eddy Ramirez, Collin Lee, SugarCRM',

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
	'id' => 'knowledgebase',
	'image_dir'=>'<basepath>/images',
	'copy' => array(

		array(
			'from' => '<basepath>/modules/KBDocuments',
			'to'   => 'modules/KBDocuments',
		),
		array(
			'from' => '<basepath>/modules/KBDocumentRevisions',
			'to'   => 'modules/KBDocumentRevisions',
		),
		array(
			'from' => '<basepath>/modules/KBTags',
			'to'   => 'modules/KBTags',
		),
		array(
			'from' => '<basepath>/modules/KBDocumentKBTags',
			'to'   => 'modules/KBDocumentKBTags',
		),
		array(
			'from' => '<basepath>/modules/KBContents',
			'to'   => 'modules/KBContents',
		),
        array(
			'from' => '<basepath>/Portal/modules/KBDocuments',
			'to'   => 'portal/modules/KBDocuments',
		),
        array(
			'from' => '<basepath>/Portal/PortalSync',
            'to'   => 'portal/PortalSync',
        ),
        array(
			'from' => '<basepath>/Portal/index.php',
            'to'   => 'portal/index.php',
        ),

	  //Adding images

		array(
			'from' => '<basepath>/include/language/en_us.notify_template.html',
			'to'   => 'include/language/en_us.notify_template.html',

		),
		//portal files
		array(
			'from' => '<basepath>/Portal/custom',
			'to'   => 'portal/custom',

		),
       array(
			'from' => '<basepath>/Portal/include',
			'to'   => 'portal/include',

		),
        array(
			'from' => '<basepath>/Portal/modules/DocumentRevisions',
			'to'   => 'portal/modules/DocumentRevisions',
		),
		array(
			'from' => '<basepath>/Portal/modules/FAQ',
			'to'   => 'portal/modules/FAQ',
		),
        array(
			'from' => '<basepath>/Portal/modules/KBDocuments',
			'to'   => 'portal/modules/KBDocuments',
		),
  	    array(
		    'from' => '<basepath>/Portal/custom/Extension/application/Ext/Include/KBDocuments/SoapPortalUsers.php',
		    'to'   => 'soap/SoapPortalUsers.php',
		),
		// Copy md5 file
		array(
			'from' => '<basepath>/knowledgebase_files.md5',
			'to'   => 'knowledgebase_files.md5',
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
			'module' => 'KBDocuments',
			'class'  => 'KBDocument',
			'path'   => 'modules/KBDocuments/KBDocument.php',
			'tab'    => true,
		),
		array(
			'module' => 'KBDocumentRevisions',
			'class'  => 'KBDocumentRevision',
			'path'   => 'modules/KBDocumentRevisions/KBDocumentRevision.php',
			'tab'    => false,
		),
		array(
			'module' => 'KBTags',
			'class'  => 'KBTag',
			'path'   => 'modules/KBTags/KBTag.php',
			'tab'    => false,
		),
	    array(
			'module' => 'KBContents',
			'class'  => 'KBContent',
			'path'   => 'modules/KBContents/KBContent.php',
			'tab'    => false,
		),
		array(
			'module' => 'KBDocumentKBTags',
			'class'  => 'KBDocumentKBTag',
			'path'   => 'modules/KBDocumentKBTags/KBDocumentKBTag.php',
			'tab'    => false,
		),
	),



   'relationships'=>array(
		array(
			'module'=> 'KBDocuments',
			'meta_data' => '<basepath>/relationships/kbdocuments_views_ratingsMetaData.php',
			'module_vardefs'=>'',
			'module_layoutdefs'=>''
		),
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
