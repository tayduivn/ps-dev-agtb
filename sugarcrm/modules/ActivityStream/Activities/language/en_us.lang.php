<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$mod_strings = array(
    'TPL_ACTIVITY_CREATE' => 'Created {{{str "TPL_ACTIVITY_RECORD" "Activities" object}}} {{str "LBL_MODULE_NAME_SINGULAR" object.module}}.',
    'TPL_ACTIVITY_POST' => '{{{value}}}{{{str "TPL_ACTIVITY_ON" "Activities" this}}}',
    'TPL_ACTIVITY_UPDATE' => 'Updated {{#if updateStr}}{{{updateStr}}} on {{/if}}{{{str "TPL_ACTIVITY_RECORD" "Activities" object}}}.',
    'TPL_ACTIVITY_UPDATE_FIELD' => '<a rel="tooltip" title="Changed: {{before}} To: {{after}}">{{field_label}}</a>',
    'TPL_ACTIVITY_LINK' => 'Linked {{{str "TPL_ACTIVITY_RECORD" "Activities" subject}}} to {{{str "TPL_ACTIVITY_RECORD" "Activities" object}}}.',
    'TPL_ACTIVITY_UNLINK' => 'Unlinked {{{str "TPL_ACTIVITY_RECORD" "Activities" subject}}} to {{{str "TPL_ACTIVITY_RECORD" "Activities" object}}}.',
    'TPL_ACTIVITY_ATTACH' => 'Added file <a class="dragoff" target="sugar_attach" href="{{url}}">{{filename}}</a>{{{str "TPL_ACTIVITY_ON" "Activities" this}}}',
    'TPL_ACTIVITY_DELETE' => 'Deleted {{{str "TPL_ACTIVITY_RECORD" "Activities" object}}} {{str "LBL_MODULE_NAME_SINGULAR" object.module}}.',
    'TPL_ACTIVITY_UNDELETE' => 'Restored {{{str "TPL_ACTIVITY_RECORD" "Activities" object}}} {{str "LBL_MODULE_NAME_SINGULAR" object.module}}.',
    'TPL_ACTIVITY_RECORD' => '<a href="#{{buildRoute module=module id=id}}">{{name}}</a>',
    // We need the trailing space at the end of the next line so that the str
    // handlebars helper isn't confused by a template that returns no text.
    'TPL_ACTIVITY_ON' => '{{#if object}} on {{{str "TPL_ACTIVITY_RECORD" "Activities" object}}}.{{/if}}{{#if module}} on {{str "LBL_MODULE_NAME_SINGULAR" module}}.{{/if}} ',
    'TPL_COMMENT' => '{{{value}}}',
    'TPL_MORE_COMMENT' => '{{this}} more comment&hellip;',
    'TPL_MORE_COMMENTS' => '{{this}} more comments&hellip;',
);
