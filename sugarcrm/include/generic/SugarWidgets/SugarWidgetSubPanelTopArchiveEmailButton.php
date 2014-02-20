<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * SugarWidgetSubPanelTopCreateNoteButton
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

class SugarWidgetSubPanelTopArchiveEmailButton extends SugarWidgetSubPanelTopButton
{
    function display($defines)
    {
        global $app_strings;

        if((ACLController::moduleSupportsACL($defines['module']) && !ACLController::checkAccess($defines['module'], 'edit', true) ||
            $defines['module'] == "History" & !ACLController::checkAccess("Emails", 'edit', true))){
            $temp = '';
            return $temp;
        }

        // if module is hidden or subpanel for the module is hidden - doesn't show quick create button
        if (SugarWidget::isModuleHidden('Emails')) {
            return '';
        }

        $title = $app_strings['LBL_TRACK_EMAIL_BUTTON_TITLE'];
        $value = $app_strings['LBL_TRACK_EMAIL_BUTTON_LABEL'];
        $this->module = 'Emails';

        if (ACLController::moduleSupportsACL($defines['module'])  && !ACLController::checkAccess($defines['module'], 'edit', true)){
            $button = "<input id='".preg_replace('[ ]', '', $value)."_button'  title='$title' class='button' type='button' name='".preg_replace('[ ]', '', strtolower($value))."_button' value='$value' disabled/>\n";
        } else {
            $button = "<input id='".preg_replace('[ ]', '', $value)."_button' title='$title' class='button' type='button' onClick=\"javascript:subp_archive_email();\" name='".preg_replace('[ ]', '', strtolower($value))."_button' value='$value'/>\n";
        }
        return $button;
    }
}
