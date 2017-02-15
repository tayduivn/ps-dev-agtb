<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication;

use Sugarcrm\IdentityProvider\Authentication\User as IdmUser;

class User extends IdmUser
{
    // User password generator types
    CONST PASSWORD_TYPE_SYSTEM = 'syst';
    CONST PASSWORD_TYPE_USER = 'user';

    // sugar config expiration types
    CONST PASSWORD_EXPIRATION_TYPE_TIME = 1;
    CONST PASSWORD_EXPIRATION_TYPE_LOGIN = 2;

    /**
     * @var bool
     */
    protected $isPasswordExpired = false;

    /**
     * @var \User
     */
    protected $sugarUser;

    /**
     * setter for mango base user
     * @param \User $user
     */
    public function setSugarUser(\User $user)
    {
        $this->sugarUser = $user;
    }

    /**
     * getter for mango base user
     * @return \User
     */
    public function getSugarUser()
    {
        return $this->sugarUser;
    }

    /**
     * set password expired
     * @param $isPasswordExpired
     */
    public function setPasswordExpired($isPasswordExpired)
    {
        $this->isPasswordExpired = $isPasswordExpired;
    }

    /**
     * Is credentials non expired?
     * @return boolean
     */
    public function isCredentialsNonExpired()
    {
        return !$this->isPasswordExpired;
    }

    /**
     * return sugar user password's type.
     * @return string
     */
    public function getPasswordType()
    {
        if ($this->sugarUser instanceof \User && !empty($this->sugarUser->system_generated_password)) {
            return self::PASSWORD_TYPE_SYSTEM;
        }
        return self::PASSWORD_TYPE_USER;
    }

    /**
     * return password last change date
     * @return string
     */
    public function getPasswordLastChangeDate()
    {
        return $this->getSugarUser()->pwd_last_changed;
    }

    /**
     * set password last change date
     * @param $date
     */
    public function setPasswordLastChangeDate($date)
    {
        $this->getSugarUser()->pwd_last_changed = $date;
    }

    /**
     * allows to update date_modified property
     * @param boolean $flag
     */
    public function allowUpdateDateModified($flag)
    {
        $this->getSugarUser()->update_date_modified = $flag;
    }
}
