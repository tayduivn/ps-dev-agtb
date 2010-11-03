    <?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created

 ********************************************************************************/

    $manifest = array (
         'acceptable_sugar_versions' => 
          array (
            
          ),
          'acceptable_sugar_flavors' =>
          array(
            'ENT'
          ),
          'readme'=>'',
          'key'=>'Spec',
          'author' => 'Lila',
          'description' => 'Product requirement specifications
',
          'icon' => '',
          'is_uninstallable' => true,
          'name' => 'Specifications',
          'published_date' => '2009-12-17 16:35:18',
          'type' => 'module',
          'version' => '1261067718',
          'remove_tables' => 'prompt',
          );
$installdefs = array (
  'id' => 'Specifications',
  'beans' => 
  array (
    0 => 
    array (
      'module' => 'Spec_UseCases',
      'class' => 'Spec_UseCases',
      'path' => 'modules/Spec_UseCases/Spec_UseCases.php',
      'tab' => true,
    ),
  ),
  'layoutdefs' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/layoutdefs/Bugs.php',
      'to_module' => 'Bugs',
    ),
    1 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/layoutdefs/Spec_UseCases.php',
      'to_module' => 'Spec_UseCases',
    ),
  ),
  'relationships' => 
  array (
    0 => 
    array (
      'meta_data' => '<basepath>/SugarModules/relationships/relationships/spec_usecases_bugsMetaData.php',
    ),
  ),
  'image_dir' => '<basepath>/icons',
  'copy' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/SugarModules/modules/Spec_UseCases',
      'to' => 'modules/Spec_UseCases',
    ),
  ),
  'language' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Bugs.php',
      'to_module' => 'Bugs',
      'language' => 'en_us',
    ),
    1 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Bugs.php',
      'to_module' => 'Bugs',
      'language' => 'ja',
    ),
    2 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Bugs.php',
      'to_module' => 'Bugs',
      'language' => 'fr_fr',
    ),
    3 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Bugs.php',
      'to_module' => 'Bugs',
      'language' => 'zh_cn',
    ),
    4 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Spec_UseCases.php',
      'to_module' => 'Spec_UseCases',
      'language' => 'en_us',
    ),
    5 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Spec_UseCases.php',
      'to_module' => 'Spec_UseCases',
      'language' => 'ja',
    ),
    6 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Spec_UseCases.php',
      'to_module' => 'Spec_UseCases',
      'language' => 'fr_fr',
    ),
    7 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Spec_UseCases.php',
      'to_module' => 'Spec_UseCases',
      'language' => 'zh_cn',
    ),
    8 => 
    array (
      'from' => '<basepath>/SugarModules/language/application/en_us.lang.php',
      'to_module' => 'application',
      'language' => 'en_us',
    ),
  ),
  'vardefs' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/vardefs/Bugs.php',
      'to_module' => 'Bugs',
    ),
    1 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/vardefs/Spec_UseCases.php',
      'to_module' => 'Spec_UseCases',
    ),
  ),
);