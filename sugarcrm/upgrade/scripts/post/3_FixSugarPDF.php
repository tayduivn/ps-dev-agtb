<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/
/**
 * Fix sugarPDF configs that could be broken by move to vendor/
 * @see BR-1557
 */
class SugarUpgradeFixSugarPDF extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    protected $config_keys = array("K_PATH_MAIN", "K_PATH_URL", "K_PATH_FONTS");

    public function run()
    {
        if (!version_compare($this->from_version, '7.2.1', '<')) {
            // only needed for upgrades from pre-7.2.1
            return;
        }

        if(!file_exists("custom/include/Sugarpdf/sugarpdf_default.php")) {
            return;
        }
        require "custom/include/Sugarpdf/sugarpdf_default.php";
        $rewrite = false;
        foreach($this->config_keys as $key) {
            if(empty($sugarpdf_default[$key])) continue;

            if(strncmp($sugarpdf_default[$key], "include/tcpdf/", 14) === 0) {
                $sugarpdf_default[$key] = str_replace("include/tcpdf/", "vendor/tcpdf/", $sugarpdf_default[$key]);
                $rewrite = true;
            }
        }
        if($rewrite) {
            $this->log("Writing fixed custom/include/Sugarpdf/sugarpdf_default.php");
            write_array_to_file("sugarpdf_default", $sugarpdf_default, "custom/include/Sugarpdf/sugarpdf_default.php");
        }
    }
}
