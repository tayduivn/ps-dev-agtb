<?php


namespace Sugarcrm\Sugarcrm\Session;

Interface SessionStorageInterface extends \ArrayAccess, \Serializable {

    /**
     * Get the current SessionStorage object.
     * @return SessionStorageInterface
     */
    public static function getInstance();

    /**
     * Start a new session or resume the session with this id.
     *
     * @param bool $lock when true, addional session starts with this session id in other threads will be blocked
     * until this thread ends or unlock is called
     *
     * @return mixed
     */
    public function start($lock = false);

    /**
     * Destroys this session and removes all data from storage and memory
     * @return null
     */
    public function destroy();

    /**
     * Set the id of this session.
     * @param string $id
     *
     * @return null
     */
    public function setId($id);

    /**
     * returns the ID of this session.
     * @return string|null
     */
    public function getId();

    /**
     * Commits current session data and unlocks any blocking sesions_starts if applicable
     *
     * similar to session_write_close or session_commit but does not destroy the session data currently in memory.
     *
     * @return null
     */
    public function unlock();

    /**
     * Checks if a session has an id yet
     * @return boolean
     */
    public function sessionHasId();

}