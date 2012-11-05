<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
$viewdefs['Forecasts']['base']['view']['forecastsConfigWizardButtons'] = array(
    'panels' => array(
        array(
            'buttons' => array(
                array(
                    'name' => 'start_button',
                    'type' => 'button',
                    'css_class' => 'btn-primary pull-right',
                    'label' => 'LBL_START_BUTTON_LABEL',
                    'primary' => true,
                ),
                array(
                    'name' => 'done_button',
                    'type' => 'button',
                    'css_class' => 'btn-primary pull-right hide',
                    'label' => 'LBL_FINISH_BUTTON_LABEL',
                    'primary' => true,
                ),
                array(
                    'name' => 'next_button',
                    'type' => 'button',
                    'css_class' => 'btn-primary pull-right hide',
                    'label' => 'LNK_LIST_NEXT',
                    'primary' => false,
                ),
                array(
                    'name' => 'previous_button',
                    'type' => 'button',
                    'css_class' => 'disabled pull-right hide',
                    'label' => 'LNK_LIST_PREVIOUS',
                    'primary' => false,
                ),
                array(
                    'name' => 'close_button',
                    'type' => 'button',
                    'css_class' => 'btn-invisible btn-link pull-right hide',
                    'label' => 'LBL_EMAIL_CANCEL',
                    'primary' => false,
                ),
            ),
        ),
    ),
);