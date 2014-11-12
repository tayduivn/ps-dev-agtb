<?php

require_once 'PMSEActivity.php';

class PMSEScriptTask extends PMSEActivity
{
    protected $currentUser;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(){
        global $current_user;
        $this->currentUser = $current_user;
        parent::__construct();
    }

    /**
     * @param mixed $currentUser
     * @codeCoverageIgnore
     */
    public function setCurrentUser($currentUser)
    {
        $this->currentUser = $currentUser;
    }

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }
}
