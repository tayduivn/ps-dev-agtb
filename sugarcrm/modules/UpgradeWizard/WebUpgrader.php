<?php
require_once dirname(__FILE__).'/UpgradeDriver.php';

/**
 * Web driver
 *
 */
class WebUpgrader extends UpgradeDriver
{
    public function runStage($stage)
    {
        return $this->run($stage);
    }

    public function __construct($dir)
    {
        $this->context['source_dir'] = $dir;
        $this->context['log'] = "UpgradeWizard.log";
        $this->context['zip'] = ''; // temporary
        parent::__construct();
    }

    protected function initSession()
    {
        if (!isset($_SESSION)) {
            // Oauth token support
            if(isset($_SERVER['HTTP_OAUTH_TOKEN'])) {
                session_id($_SERVER['HTTP_OAUTH_TOKEN']);
            }
            session_start();
        }
    }

    /**
     * Check if we've started upgrade process and have correct token
     * If yes, setup current request
     * @param string $token
     * @return boolean
     */
    public function startRequest($token)
    {
        if(empty($token) || empty($this->state['webToken']) || $token != $this->state['webToken']) {
            return false;
        }
        if(empty($this->state['admin'])) {
            return false;
        }
        if(!empty($this->state['zip'])) {
            $this->context['zip'] = $this->state['zip'];
            $this->context['backup_dir'] = $this->config['upload_dir']."/upgrades/backup/".pathinfo($this->context['zip'], PATHINFO_FILENAME) . "-restore";
        }
        return true;
    }

    protected function getUser()
    {
        $user = BeanFactory::getBean('Users', $this->state['admin']);
        if($user) {
            $this->context['admin'] = $user->user_name;
        }
        return $user;
    }

    /**
     * Files that are used by the upgrade driver
     * @var array
     */
    protected $upgradeFiles = array('WebUpgrader.php', 'UpgradeDriver.php', 'upgrade_screen.php');

    /**
     * Start upgrade process
     * @return boolean
     */
    public function startUpgrade()
    {
        // Load admin user name
         $this->initSession();
         if(empty($_SESSION['authenticated_user_id'])) {
             return false;
         }
         $this->cleanState();
         $this->state['admin'] = $_SESSION['authenticated_user_id'];
         $this->initSugar();
         if(empty($GLOBALS['current_user']) || !$GLOBALS['current_user']->isAdmin()) {
             return false;
         }
         $this->state['webToken'] = create_guid();
         $this->saveState();
         // copy upgrader files
         $upg_dir = $this->cacheDir("upgrades/driver/");
         $this->ensureDir($upg_dir);
         $_SESSION['upgrade_dir'] = $upg_dir;
         foreach($this->upgradeFiles as $ufile) {
             copy("modules/UpgradeWizard/$ufile", "{$upg_dir}{$ufile}");
         }
         return $this->state['webToken'];
    }

    /**
     * Get upgrade status
     * @return multitype:multitype:
     */
    protected function getStatus()
    {
        $state = array();
        if(isset($this->state['stage'])) {
            $state['stage'] = $this->state['stage'];
        }
        if(isset($this->state['scripts'])) {
            $state['scripts'] = $this->state['scripts'];
        }
            if(isset($this->state['script_count'])) {
            $state['script_count'] = $this->state['script_count'];
        }
        return $state;
    }

    /**
     * Process upgrade action
     * @param string $action
     * @return next stage name or false on error
     */
    public function process($action)
    {
        if($action == "status") {
            return $this->getStatus();
        }
        if(!in_array($action, $this->stages)) {
            return $this->error("Unknown stage $action", true);
        }
        if($action == 'unpack') {
            // accept file upload
            if(!$this->handleUpload()) {
                return false;
            }
        }
        return $this->runStep($action);
    }

    protected $upload_errors = array(
        0=>"There is no error, the file uploaded with success",
        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3=>"The uploaded file was only partially uploaded",
        4=>"No file was uploaded",
        6=>"Missing a temporary folder",
        7=>"Failed to write file to disk",
        8=>"A PHP extension stopped the file upload",

    );

    /**
     * Handle zip file upload
     * @return boolean
     */
    protected function handleUpload()
    {
        if(empty($_FILES['zip'])) {
            return $this->error("Expected file upload", true);
        }
        if($_FILES['zip']['error'] != UPLOAD_ERR_OK) {
            return $this->error("File upload error: {$this->upload_errors[$_FILES['zip']['error']]} ({$_FILES['zip']['error']})", true);
        }
        if(!is_uploaded_file($_FILES['zip']['tmp_name'])) {
            return $this->error("Upload failed", true);
        }
        $this->ensureDir($this->config['upload_dir']."/upgrades");
        $this->context['zip'] = $this->config['upload_dir']."/upgrades/".basename($_FILES['zip']['name']);
        if (move_uploaded_file($_FILES['zip']['tmp_name'], $this->context['zip'])) {
            $this->state['zip'] = $this->context['zip'];
            $this->context['backup_dir'] = $this->config['upload_dir']."/upgrades/backup/".pathinfo($this->context['zip'], PATHINFO_FILENAME) . "-restore";
            $this->saveState();
            return true;
        } else {
            return $this->error("Failed to move uploaded file to {$this->context['zip']}", true);
        }

    }

    /**
     * Display upgrade screen page
     */
    public function displayUpgradePage()
    {
        global $token;
        include dirname(__FILE__).'/upgrade_screen.php';
    }

    /**
     * Remove temp files for upgrader
     */
    public function removeTempFiles()
    {
        parent::removeTempFiles();
        $this->removeDir($this->cacheDir("upgrades/driver/"));
    }
}