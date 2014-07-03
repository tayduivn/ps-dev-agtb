<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class SugarWidgetSubPanelTopScheduleMeetingButton extends SugarWidgetSubPanelTopButton
{
    function display($defines)
    {
        global $app_strings;

        $label = $app_strings['LBL_SCHEDULE_MEETING_BUTTON_TITLE'];
        $this->module = 'Meetings';

        $parentId = $defines['focus']->id;
        $wid = $this->getWidgetId();
        $id = $wid."_create";
        $link = 'meetings';

        $button = "<a href='#' onClick=\"javascript:subp_nav_sidecar('" . $this->module . "','" . $parentId . "',
            'c', '" . $link . "');\" class='create_from_bwc_to_sidecar' id=\"{$id}\">{$label}</a>";

        return $button;
    }
}
