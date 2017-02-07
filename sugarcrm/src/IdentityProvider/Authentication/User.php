<?php
namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication;

use Sugarcrm\IdentityProvider\Authentication\User as IdmUser;

class User extends IdmUser
{
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

}
