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
/**
 * Select files to delete during install
 */
class SugarUpgradeFilesForDelete extends UpgradeScript
{
    public $order = 7000;
    public $version = '7.1.5';
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        $files = array('themes/Sugar/js',
            //Remove the themes/Sugar/tpls directory
            'themes/Sugar/tpls',
            'themes/Sugar5',
            // remove the files moved to vendor
            'include/HTMLPurifier',
            'include/HTTP_WebDAV_Server',
            'include/Pear',
            'include/Smarty',
            'XTemplate',
            'Zend',
            'include/lessphp',
            'log4php',
            'include/nusoap',
            'include/oauth2-php',
            'include/pclzip',
            'include/reCaptcha',
            'include/tcpdf',
            'include/ytree',
            'include/SugarSearchEngine/Elastic/Elastica',
            //Remove the SugarFeed files
            'modules/Cases/SugarFeeds',
            'modules/Contacts/SugarFeeds',
            'modules/Leads/SugarFeeds',
            'modules/Opportunities/SugarFeeds/OppFeed.php',
            'modules/SugarFeed',
            // remove the old FTS Logic Hook
            'custom/Extension/application/Ext/LogicHooks/SugarFTSHooks.php',
            // remove old popup picker files from RLI
            'modules/RevenueLineItems/Popup_picker.html',
            'modules/RevenueLineItems/Popup_picker.php',
            // remove phpunit from vendor
            'vendor/phpunit',
            // remove the old base metadata file template for the dashablelist view
            'include/SugarObjects/templates/basic/clients/base/views/dashablelist/dashablelist.php',
            // old phpmailer in thr include directory is no longer needed or referenced as of 7.0
            'include/phpmailer',
            //remove old connectors
            'modules/Connectors/connectors/sources/ext/rest/zoominfocompany',
            'modules/Connectors/connectors/sources/ext/rest/zoominfoperson',
            'modules/Connectors/connectors/sources/ext/rest/linkedin',
            'modules/Connectors/connectors/sources/ext/rest/insideview',
            'modules/Connectors/connectors/sources/ext/eapm/facebook',
            'modules/Connectors/connectors/sources/ext/soap/hoovers',
            //remove old sidecar files
            'sidecar/lib/chosen',
            'sidecar/lib/handlebars/handlebars-1.0.rc.1.js',
            'sidecar/lib/handlebars/handlebars.runtime-1.0.rc.1.js',
            'sidecar/lib/jquery-timepicker',
            'sidecar/lib/twitterbootstrap',
            'sidecar/src/view/hbt-helpers.js',
            // Remove old less files from styleguide
            'styleguide/less/clients/mobile/fixed_variables.less',
            'styleguide/less/clients/mobile/font-awesome.less',
            'styleguide/less/clients/mobile/forms.less',
            'styleguide/less/clients/mobile/labels-badges.less',
            'styleguide/less/clients/mobile/navbar.less',
            'styleguide/less/clients/mobile/navs.less',
            'styleguide/less/clients/mobile/nomad.less',
            'styleguide/less/clients/mobile/sugarmobile.less',
            'styleguide/less/clients/portal/config.less',
            'styleguide/less/modules/nv.d3.less',
            'styleguide/less/sugar-bootstrap',
            'styleguide/less/sugar-specific/actions.less',
            'styleguide/less/sugar-specific/activitystreams.less',
            'styleguide/less/sugar-specific/chosen.less',
            'styleguide/less/sugar-specific/clickmenu.less',
            'styleguide/less/sugar-specific/dcmenu.less',
            'styleguide/less/sugar-specific/modulelist.less',
            'styleguide/less/sugar-specific/position.less',
            'styleguide/less/sugar-specific/progress.less',
            'styleguide/less/sugar-specific/quickcreate.less',
            'styleguide/less/sugar-specific/responsive-forecast.less',
            'styleguide/less/sugar-specific/responsive.less',
            'styleguide/less/sugar-specific/topline-forecast.less',
            'styleguide/less/sugar-specific/vcard.less',
            'styleguide/less/sugar-specific/yui.less',
            'styleguide/less/twitter-bootstrap/carousel.less',
            'styleguide/less/twitter-bootstrap/charts.less',
            'styleguide/less/twitter-bootstrap/chosen.less',
            'styleguide/less/twitter-bootstrap/datatables.less',
            'styleguide/less/twitter-bootstrap/pager.less',
            'styleguide/less/twitter-bootstrap/pagination.less',
            'styleguide/less/twitter-bootstrap/responsive.less',
            'styleguide/less/twitter-bootstrap/sprites.less',
            'styleguide/less/twitter-bootstrap/tiptip.less',
            'styleguide/less/twitter-bootstrap/toggle.less',
            // BR 796 api files
            'clients/mobile/api/CurrentUserMobileApi.php',
            'clients/mobile/api/MetadataMobileApi.php',
            'clients/portal/api/MetadataPortalApi.php',
            'clients/base/views/activitystream-bottom/activitystream-bottom.php',
            // NOMAD-1179 mobile search definitions
            'modules/Accounts/clients/mobile/views/search/search.php',
            'modules/Bugs/clients/mobile/views/search/search.php',
            'modules/Calls/clients/mobile/views/search/search.php',
            'modules/Cases/clients/mobile/views/search/search.php',
            'modules/Contacts/clients/mobile/views/search/search.php',
            'modules/Employees/clients/mobile/views/search/search.php',
            'modules/Leads/clients/mobile/views/search/search.php',
            'modules/Meetings/clients/mobile/views/search/search.php',
            'modules/Opportunities/clients/mobile/views/search/search.php',
            'modules/ProductTemplates/clients/mobile/views/search/search.php',
            'modules/Tasks/clients/mobile/views/search/search.php',
            'modules/Users/clients/mobile/views/search/search.php',
            'include/SugarObjects/templates/basic/clients/mobile/views/search/search.php',
            'include/SugarObjects/templates/company/clients/mobile/views/search/search.php',
            'include/SugarObjects/templates/file/clients/mobile/views/search/search.php',
            'include/SugarObjects/templates/issue/clients/mobile/views/search/search.php',
            'include/SugarObjects/templates/person/clients/mobile/views/search/search.php',
            'include/SugarObjects/templates/sale/clients/mobile/views/search/search.php',
            // NOMAD-1384 remove mobile views from modules which are not supported by mobile app
            'modules/Bugs/clients/mobile/',
            // NOMAD-1295
            'modules/Accounts/metadata/wireless.subpaneldefs.php',
            'modules/Bugs/metadata/wireless.listviewdefs.php',
            'modules/Bugs/metadata/wireless.listviewdefs.php',
            'modules/Calls/metadata/wireless.listviewdefs.php',
            'modules/Calls/metadata/wireless.subpaneldefs.php',
            'modules/Cases/metadata/wireless.subpaneldefs.php',
            'modules/Contacts/metadata/wireless.subpaneldefs.php',
            'modules/Documents/metadata/wireless.editviewdefs.php',
            'modules/Documents/metadata/wireless.subpaneldefs.php',
            'modules/Leads/metadata/wireless.subpaneldefs.php',
            'modules/Meetings/metadata/wireless.listviewdefs.php',
            'modules/Meetings/metadata/wireless.subpaneldefs.php',
            'modules/Notes/metadata/wireless.editviewdefs.php',
            'modules/Notes/metadata/wireless.listviewdefs.php',
            'modules/Opportunities/metadata/wireless.subpaneldefs.php',
            'modules/ProductTemplates/metadata/wireless.detailviewdefs.php',
            'modules/ProductTemplates/metadata/wireless.editviewdefs.php',
            'modules/Products/metadata/wireless.detailviewdefs.php',
            'modules/Products/metadata/wireless.editviewdefs.php',
            'modules/Quotes/metadata/wireless.subpaneldefs.php',
            'modules/Tasks/metadata/wireless.listviewdefs.php',
            'modules/Tasks/metadata/wireless.subpaneldefs.php',
            'modules/Users/metadata/wireless.detailviewdefs.php',
            'modules/Users/metadata/wireless.editviewdefs.php',
            'modules/Users/metadata/wireless.listviewdefs.php',
            'modules/Users/metadata/wireless.searchdefs.php',
            'modules/Employees/views/view.wirelessedit.php',
            'modules/Opportunities/views/view.wirelessedit.php',
            'modules/Users/views/view.wirelesslogin.php',
            'modules/Users/views/view.wirelessmain.php',
            'modules/Calls/views/view.wirelesssave.php',
            'modules/Meetings/views/view.wirelesssave.php',
            'tests/include/SubPanel/Bug63486Test.php',
            'modules/Meetings/api/MeetingsApi.php',
            // MAR-1736 / SC-2611
            'modules/Emails/clients/base/views/panel-top/panel-top.js',
            // NOMAD-1799
            'modules/Meetings/clients/mobile/api/MobileMeetingsApi.php',
        );

        if (version_compare($this->from_version, '7.2', '<')) {
            // SC-2664
            $files[] = 'modules/Notifications/clients/base/layouts/records/records.php';
            $files[] = 'modules/Notifications/clients/base/views/raw/raw.hbs';
            $files[] = 'modules/Notifications/clients/base/views/raw/raw.js';
            $files[] = 'modules/Notifications/clients/base/views/raw/raw.php';
        }

        if (version_compare($this->from_version, '7.2.2', '<')) {
            $files[] = 'clients/base/layouts/search';
            $files[] = 'clients/base/views/results';
            $files[] = 'clients/base/views/subdetail';
            $files[] = 'clients/base/views/subnav';
            $files[] = 'clients/portal/views/detail';
            $files[] = 'clients/portal/views/subdetail';
            $files[] = 'clients/portal/views/subnav';
        }

        $this->fileToDelete($files);
    }
}
