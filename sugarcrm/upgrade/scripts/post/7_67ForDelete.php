<?php
/**
 * Files to delete for 6.7 install
 */
class SugarUpgrade67ForDelete extends UpgradeScript
{
    public $order = 7000;
    public $version = '6.7.0';
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
            // remove the old base metadata file template for the dashablelist view
            'include/SugarObjects/templates/basic/clients/base/views/dashablelist/dashablelist.php',
        );
        $this->fileToDelete($files);
    }
}
