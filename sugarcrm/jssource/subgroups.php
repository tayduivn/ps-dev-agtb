<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Helper to allow for getting sub groups of combinations of includes that are likely to be required by
 * many clients (so that we don't end up with duplication from client to client).
 * @param  string $subGroup The sub-group
 * @param  string $target The target file to point to e.g. '<app>/<app>.min.js',
 * @return array array of key vals where the keys are source files and values are the $target passed in. 
 */
function getSubgroupForTarget ($subGroup, $target) {

    // TODO: Add more sub-groups as needed here if client include duplication in $js_groupings
    switch ($subGroup) {
        case 'bootstrap':
                return array(
                    'styleguide/assets/js/bootstrap-button.js'  => $target,
                    'styleguide/assets/js/bootstrap-tooltip.js' => $target,
                    'styleguide/assets/js/bootstrap-dropdown.js'=> $target,
                    'styleguide/assets/js/bootstrap-popover.js' => $target,
                    'styleguide/assets/js/bootstrap-modal.js'   => $target,
                    'styleguide/assets/js/bootstrap-alert.js'   => $target,
                    'styleguide/assets/js/bootstrap-datepicker.js'   => $target,
                );
            break;
        default:
            // Not sure what would be useful within config file?
            break;
    }
}