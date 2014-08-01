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

        // must be upgrading from between 710 to 722
        if (version_compare($this->from_version, '7.1.0', '>') && version_compare($this->from_version, '7.2.2', '<')) {
            // can be files or directories
            $this->fileToDelete('modules/WebLogicHooks/clients/base/layouts/record/record.php');
            $this->fileToDelete('modules/WebLogicHooks/clients/base/layouts/records/records.php');
            $this->fileToDelete('modules/WebLogicHooks/clients/base/views/list-headerpane/headerpane.php');
        }

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
            $files[] = 'modules/ModuleBuilder/tpls/portalpreview.tpl';
            $files[] = 'modules/ModuleBuilder/views/view.portalpreview.php';
            $files[] = 'LICENSE.txt';
        }

        $this->fileToDelete($files);
    }
}
