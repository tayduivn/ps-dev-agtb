<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Popup Picker
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */
require_once('include/MVC/View/views/view.popup.php');
require_once('vendors/ytree/Tree.php');
require_once('vendors/ytree/Node.php');
require_once('modules/ProductTemplates/TreeData.php');

class ProductTemplatesViewPopup extends ViewPopup {

 	function display() {
         $catalogtree = new Tree('productcatalog');
         $catalogtree->set_param('module', 'ProductTemplates');

         $nodes = get_categories_and_products(null);
         foreach($nodes as $node)
         {
             $catalogtree->add_node($node);
         }
         $this->override_popup['template_data']['treeheader'] = $catalogtree->generate_header();
         $this->override_popup['template_data']['treeinstance'] = '{literal}' . $catalogtree->generate_nodes_array() . '{/literal}';

         parent::display();
 	}
}
