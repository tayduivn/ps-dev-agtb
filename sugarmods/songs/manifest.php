<?PHP

// manifest file for information regarding application of new code
$manifest = array(
    // only install on the following regex sugar versions (if empty, no check)
    'acceptable_sugar_versions' => array(),

    //makes module removable.
    'is_uninstallable' => true,

    // name of new code
    'name' => 'Song Module',

    // description of new code
    'description' => 'A Module for all your song needs',

    // author of new code
    'author' => 'Ajay',

    // date published
    'published_date' => '2005/08/11',

    // version of code
    'version' => '1.1',

    // type of code (valid choices are: full, langpack, module, patch, theme)
    'type' => 'module',

    // icon for displaying in UI (path to graphic contained within zip package)
    'icon' => '',
);



$installdefs = array(
	'id'=> 'songs',
	'image_dir'=>'<basepath>/images',
	'copy' => array(
						array('from'=> '<basepath>/module/Songs',
							  'to'=> 'modules/Songs',
							  ),
					),

	
	'language'=> array(array('from'=> '<basepath>/application/app_strings.php', 
					'to_module'=> 'application',
					'language'=>'en_us'
					),
					array('from'=> '<basepath>/administration/en_us.songsadmin.php', 
					'to_module'=> 'Administration',
					'language'=>'en_us'
					)
					),
	'administration'=> array(
				array('from'=>'<basepath>/administration/songsadminoption.php',
					  ),
					  ),
	'beans'=> array(
				array('module'=> 'Songs',
					  'class'=> 'Song',
					  'path'=> 'modules/Songs/Song.php',
					  'tab'=> true,
					  )
					  ),
	'relationships'=>array(
					array(
						'module'=> 'Contacts',
						'meta_data'=>'<basepath>/relationships/contacts_songsMetaData.php',
						'module_vardefs'=>'<basepath>/vardefs/contacts_vardefs.php',
						'module_layoutdefs'=>'<basepath>/layoutdefs/contactslayout_defs.php'
						
					),
					array(
						'module'=> 'Products',
						'meta_data'=>'<basepath>/relationships/products_songsMetaData.php',
						'module_vardefs'=>'<basepath>/vardefs/products_vardefs.php',
						'module_layoutdefs'=>'<basepath>/layoutdefs/productslayout_defs.php'
					)
					),
	'custom_fields'=>array(
						//will be referenced as sudo_name_c   - _c indicates a custom field
						//current types are varchar,textarea,double,float,int,date,bool,enum (select), relate
						array('name'=>'music_name',
								'label'=>'Music Name',
								'type'=>'varchar',
								'max_size'=>255,
								'require_option'=>'optional',
								'default_value'=>'',
								'ext1'=>'',
								'ext2'=>'',
								'ext3'=>'',
								'audited'=>0,
								'module'=>'Contacts',
					),
						array('name'=>'label_company',
								'label'=>'Label',
								'type'=>'relate',
								'max_size'=>36,
								'require_option'=>'optional',
								'default_value'=>'',
								'ext1'=>'name',//Field to get from To Module (Bugs)
								'ext2'=>'Accounts',//Relate To Module
								'ext3'=>'',
								'audited'=>0,
								'module'=>'Songs', //Relate From Module
					)
				
					),
					  
);
?>
