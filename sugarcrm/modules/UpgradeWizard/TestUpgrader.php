<?php
require_once 'modules/UpgradeWizard/UpgradeDriver.php';

/**
 * Test upgrader class
 *
 * Used for unit tests on upgrader
 */
class TestUpgrader extends UpgradeDriver
{
    /**
     * List of upgrade scripts
     * @var string
     */
    protected $scripts = array();

    public function __construct($admin)
    {
        $context = array(
            "admin" => $admin->user_name,
            "log" => "cache/upgrade.log",
            "source_dir" => realpath(dirname(__FILE__)."/../../"),
        );
        parent::__construct($context);
    }

    public function cleanState()
    {
        $statefile = $this->cacheDir('upgrades/').self::STATE_FILE;
        if(file_exists($statefile)) {
            unlink($statefile);
        }
    }

    public function runStage($stage)
    {
        return $this->run($stage);
    }

    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * Get script object for certain script
     * @param string $stage
     * @param string $script
     * @return UpgradeScript
     */
    public function getScript($stage, $script)
    {
        if(empty($this->scripts[$stage])) {
            $this->scripts[$stage] = $this->getScripts($stage);
        }
        return $this->scripts[$stage][$script];
    }

    public function getTempDir()
    {
        return $this->context['temp_dir'];
    }

    public function setVersions($from, $flav_from, $to, $flav_to)
    {
        $this->from_version = $from;
        $this->from_flavor = $flav_from;
        $this->to_version = $to;
        $this->to_flavor = $flav_to;
    }
}
