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
* Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
********************************************************************************/
require_once 'CliUpgrader.php';

class ShadowUpgrader extends CliUpgrader
{
    protected function commit()
    {
        // commit doesn't do anything
        return true;
    }

    protected static function usage()
    {
    	$usage =<<<eoq2
Usage: php -f ShadowUpgrader.php oldTemplate newTemplate pathToSugarInstance zip logFile admin-user

Example:
    [path-to-PHP/]php -f ShadowUpgrader.php /sugar/templates/6.6.2 /sugar/templates/6.7.0 path-to-sugar-instance/ SugarEnt-Upgrade-6.6.x-to-6.7.0.zip silentupgrade.log admin

Arguments:
    oldTemplate                          : Pre-upgrade template
    newTemplate                          : Target template
    pathToSugarInstance                  : Sugar instance being upgraded
    zip                                  : ZIP file with manifest and DB scripts
    logFile                              : Upgrade log
    admin-user                           : admin user performing the upgrade

eoq2;
    	echo $usage;
    }

    protected function verifyArguments($argv)
    {
        if(!function_exists("shadow")) {
            self::argError("Shadow module should be installed to run this script.");
        }

        if(empty($this->context['source_dir']) || !is_dir($this->context['source_dir'])) {
            self::argError("3rd parameter must be a valid directory: {$argv[3]}.");
        }

        if(empty($this->context['pre_template']) || empty($this->context['post_template'])) {
            self::argError("Templates should be specified");
        }

        if(!is_file("{$this->context['pre_template']}/include/entryPoint.php")) {
            self::argError("{$context['pre_template']} is not a SugarCRM template.");
        }

        if(!is_file("{$this->context['post_template']}/include/entryPoint.php")) {
            self::argError("{$context['post_template']} is not a SugarCRM template.");
        }

        if(!is_file("{$this->context['source_dir']}/config.php")) {
            self::argError("{$context['source_dir']} is not a SugarCRM directory.");
        }

    	return true;
    }

    /**
     * Map CLI arguments into context entries
     * @param array $argv
     * @return array
     */
    public static function mapArgs($argv)
    {
        if(count($argv) < 7) {
            $cnt = count($argv);
            self::argError("Upgrader requires 6 argumens, $cnt given");
            return array(); // never happens
        }

        $context = array(
                'pre_template' => realpath($argv[1]),
                'post_template' => realpath($argv[2]),
                'source_dir' => realpath($argv[3]),
                'zip' => realpath($argv[4]),
                'log' => $argv[5],
                'admin' => $argv[6],
        );
        if(isset($argv[7])) {
            $context['stage'] = $argv[7];
        }
        return $context;
    }

    protected function getScripts($dir, $stage)
    {
        $scripts = parent::getScripts($dir, $stage);
        foreach($scripts as $name => $script) {
            // shadow will allow only DB and custom scripts
            if(($script->type & (UpgradeScript::UPGRADE_CUSTOM|UpgradeScript::UPGRADE_DB)) == 0) {
                unset($scripts[$name]);
            }
        }
        return $scripts;
    }

    protected function initSugar()
    {
        if($this->context['stage'] == 'pre' || $this->context['stage'] == 'unpack') {
            $templ_dir = $this->context['pre_template'];
        } else {
            $templ_dir = $this->context['post_template'];
        }
        chdir($templ_dir);
        $this->log("Shadow configuration: $templ_dir -> {$this->context['source_dir']}");
        shadow($templ_dir, $this->context['source_dir'], array("cache", "upload", "config.php"));
        $this->context['source_dir'] = $templ_dir;
        return parent::initSugar();
    }
}

if(empty($argv[0]) || basename($argv[0]) != basename(__FILE__)) return;

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is command-line only script");
}
ShadowUpgrader::start();


