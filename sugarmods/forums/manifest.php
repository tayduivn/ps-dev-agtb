<?PHP
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
// manifest file for information regarding application of new code
$manifest = array(
    // only install on the following regex sugar versions (if empty, no check)
	//BEGIN SUGARCRM flav=pro ONLY 
    'acceptable_sugar_flavors' => array('PRO'),
    //END SUGARCRM flav=pro ONLY 
    //BEGIN SUGARCRM flav=ent ONLY 
	'acceptable_sugar_flavors' => array('ENT'),
	//END SUGARCRM flav=ent ONLY 
	//BEGIN SUGARCRM flav=com ONLY 
	'acceptable_sugar_flavors' => array('CE','OS'),
	//END SUGARCRM flav=com ONLY 
    'acceptable_sugar_versions' => array(),

    'is_uninstallable'=>true,

    // name of new code
    'name' => 'Forums, Threads, Posts Modules',

    // description of new code
    'description' => 'This module is to allow creating Forums for general discussion, as well as creating threads to link to Accounts, Bugs, Cases, Opportunities, and Projects.',

    // author of new code
    'author' => 'Sadek Baroudi',

    // date published
    'published_date' => '2008/07/08',

    // version of code
    'version' => '5.1.0',

    // type of code (valid choices are: full, langpack, module, patch, theme )
    'type' => 'module',

    // icon for displaying in UI (path to graphic contained within zip package)
    'icon' => '',
);


$installdefs = array(
	'id' => 'forums',
	'copy' => array(
				array(
					'from' => '<basepath>/modules/Forums',
					'to'   => 'modules/Forums',
				),
				array(
					'from' => '<basepath>/modules/Threads',
                	'to'   => 'modules/Threads',
				),
				array(
					'from' => '<basepath>/modules/Posts',
					'to'   => 'modules/Posts',
				),
				array(
					'from' => '<basepath>/modules/ForumTopics',
					'to'   => 'modules/ForumTopics',
				),
				// Copy md5 file
				array(
					'from' => '<basepath>/forums_files.md5',
					'to'   => 'forums_files.md5',
				),								
			),

	'language'=> array(
		array(
			'from'=> '<basepath>/application/app_strings.php', 
			'to_module'=> 'application',
			'language'=>'en_us'
		),
		array(
			'from'=> '<basepath>/administration/en_us.forumsadmin.php', 
			'to_module'=> 'Administration',
			'language'=>'en_us'
		),
		array(
			'from' => '<basepath>/modules/Accounts/en_us.lang.php',
			'to_module' => 'Accounts',
			'language' =>'en_us'
		),
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
			'from' => '<basepath>/modules/Opportunities/en_us.lang.php',
			'to_module' => 'Opportunities',
			'language' =>'en_us'
		),
		array(
			'from' => '<basepath>/modules/Project/en_us.lang.php',
			'to_module' => 'Project',
			'language' =>'en_us'
		),
	),
	'administration'=> array(
				array(
					'from'=>'<basepath>/administration/forumsadminoption.php',
					'to' => 'modules/Administration/forumsadminoption.php',
				),
	),
	'beans'=> array(
				array(
					'module' => 'Forums',
					'class'  => 'Forum',
					'path'   => 'modules/Forums/Forum.php',
					'tab'    => true,
				),
				array(
					'module' => 'Threads',
					'class'   => 'Thread',
					'path'    => 'modules/Threads/Thread.php',
					'tab'     => false,
				),
				array(
					'module' => 'Posts',
					'class'  => 'Post',
					'path'   => 'modules/Posts/Post.php',
					'tab'    => false,
				),
				array(
					'module' => 'ForumTopics',
					'class'  => 'ForumTopic',
					'path'   => 'modules/ForumTopics/ForumTopic.php',
					'tab'    => false,
				),
	),

    'layoutdefs'=> array(
		/* may need to use this
		array(
			'from' => '<basepath>/layoutdefs/forumslayout_defs.php', 
			'to_module' => 'Forums',
		),
		array(
			'from' => '<basepath>/layoutdefs/threadslayout_defs.php', 
			'to_module' => 'Threads',
		),		
		*/
    ),

	'relationships'=>array(
		array(
			'module'=> 'Accounts',
			'meta_data'=>'<basepath>/relationships/accounts_threadsMetaData.php',
			'module_vardefs'=>'<basepath>/vardefs/accounts_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/accounts_layoutdefs.php'
		),
		array(
			'module'=> 'Bugs',
			'meta_data'=>'<basepath>/relationships/bugs_threadsMetaData.php',
			'module_vardefs'=>'<basepath>/vardefs/bugs_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/bugs_layoutdefs.php'
		),
		array(
			'module'=> 'Cases',
			'meta_data'=>'<basepath>/relationships/cases_threadsMetaData.php',
			'module_vardefs'=>'<basepath>/vardefs/cases_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/cases_layoutdefs.php'
		),
		array(
			'module'=> 'Opportunities',
			'meta_data'=>'<basepath>/relationships/opportunities_threadsMetaData.php',
			'module_vardefs'=>'<basepath>/vardefs/opportunities_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/opportunities_layoutdefs.php'
		),
		array(
			'module'=> 'Project',
			'meta_data'=>'<basepath>/relationships/project_threadsMetaData.php',
			'module_vardefs'=>'<basepath>/vardefs/project_vardefs.php',
			'module_layoutdefs'=>'<basepath>/layoutdefs/project_layoutdefs.php'
		),
	),
	
	'post_execute'=>array(
		0 => '<basepath>/post_install/install_actions.php',
	),
);

?>
