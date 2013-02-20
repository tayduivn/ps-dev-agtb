<?php
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

/**
 * Entry points handler class
 *
 * Generate transaction names for Sugar Metrics
 * Called from SugarMetric_Manager. To customize entry point transaction name
 * - create class method that will be named same as entry point
 * in /include/MVC/Controller/entry_point_registry.php in lowercase
 */
class SugarMetric_EntryPointHandler
{
    /**
     * Generate transaction name for 'vCard' entry point
     *
     * @return string
     */
    public function vcard()
    {
        $module = (isset($_REQUEST['module'])) ? clean_string($_REQUEST['module']) : 'Contacts';
        return 'vcard_' . $module;
    }

    /**
     * Generate transaction name for 'TreeData' entry point
     *
     * @return string
     */
    public function treedata()
    {
        $module = '';

        if (isset($_REQUEST['PARAMT_module'])) {
            $module = $_REQUEST['PARAMT_module'];
        } elseif (isset($_REQUEST['module'])) {
            $module = $_REQUEST['module'];
        }

        return 'treedata_' . clean_string($module);
    }

    /**
     * Generate transaction name for 'download' entry point
     *
     * @return string
     */
    public function download()
    {
        return 'download_' . clean_string($_REQUEST['type']);
    }

    /**
     * Generate transaction name for 'export' entry point
     *
     * @return string
     */
    public function export()
    {
        return 'export_' . clean_string($_REQUEST['module']);
    }

    /**
     * Generate transaction name for 'export_dataset' entry point
     *
     * @return string
     */
    public function export_dataset()
    {
        return 'dataset_' . clean_string($_REQUEST['module']);
    }

    /**
     * Generate transaction name for 'pdf' entry point
     *
     * @return string
     */
    public function pdf()
    {
        return 'pdf_' . clean_string($_REQUEST['module']) . '_' . clean_string($_REQUEST['action']);
    }

    /**
     * Generate transaction name for 'get_url' entry point
     *
     * @return string
     */
    public function get_url()
    {
        return 'get_url_' . (isset($_GET['type']) ? $_GET['type'] : '');
    }

    /**
     * Generate transaction name for 'HandleAjaxCall' entry point
     *
     * @return string
     */
    public function handleajaxcall()
    {
        return 'handle_ajax_call_' . clean_string($_REQUEST['method']);
    }
}
