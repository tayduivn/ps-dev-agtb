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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\Mapping;

use Sugarcrm\IdentityProvider\Authentication\UserMapping\MappingInterface;
use Sugarcrm\IdentityProvider\Authentication\User as IdmUser;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Srn\Converter;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class SugarOidcUserMapping implements MappingInterface
{
    const OIDC_USER_STATUS_ACTIVE = 0;
    const OIDC_USER_STATUS_INACTIVE = 1;

    /**
     * Map OIDC response to sugar user fields
     * @param array $response
     * @return array
     */
    public function map($response)
    {
        if (empty($response) || !is_array($response)) {
            return [];
        }

        return [
            'user_name' => $this->getAttribute($response, 'preferred_username'),
            'status' => $this->getUserStatus($response),
            'date_entered' => $this->getAttribute($response, 'created_at'),
            'date_modified' => $this->getAttribute($response, 'updated_at'),
            'first_name' => $this->getAttribute($response, 'given_name'),
            'last_name' => $this->getAttribute($response, 'family_name'),
            'phone_work' => $this->getAttribute($response, 'phone_number'),
            'email' => $this->getAttribute($response, 'email'),
            'address_street' => $this->getAddressAttribute($response, 'street_address'),
            'address_city' => $this->getAddressAttribute($response, 'locality'),
            'address_state' => $this->getAddressAttribute($response, 'region'),
            'address_country' => $this->getAddressAttribute($response, 'country'),
            'address_postalcode' => $this->getAddressAttribute($response, 'postal_code'),
        ];
    }

    /**
     * @inheritDoc
     * @throws UsernameNotFoundException
     */
    public function mapIdentity($response)
    {
        if (!is_array($response) || empty($response['sub'])) {
            throw new UsernameNotFoundException('User not found in SRN');
        }

        return [
            'field' => 'id',
            'value' => $this->getUserIdFromSrn($response['sub']),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getIdentityValue(IdmUser $user)
    {
        return $this->getUserIdFromSrn($user->getSrn());
    }

    /**
     * get user id from srn
     * @param string $srn
     * @return string
     * @throws UsernameNotFoundException
     */
    protected function getUserIdFromSrn($srn)
    {
        $userSrn = Converter::fromString($srn);
        $userResource = $userSrn->getResource();
        if (empty($userResource) || $userResource[0] != 'user' || empty($userResource[1])) {
            throw new UsernameNotFoundException('User not found in SRN');
        }
        return $userResource[1];
    }

    /**
     * get user attribute
     * @param array $response
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    protected function getAttribute(array $response, $name, $default = null)
    {
        return isset($response[$name]) ? $response[$name] : $default;
    }

    /**
     * get address value from token ID extension
     * @param array $response
     * @param string $name
     * @param null|mixed $default
     * @return null
     */
    protected function getAddressAttribute(array $response, $name, $default = null)
    {
        return !empty($response['address'][$name]) ? $response['address'][$name] : $default;
    }

    /**
     * return user status
     * @param array $response
     * @return int
     */
    protected function getUserStatus(array $response)
    {
        $status = $this->getAttribute($response, 'status');
        return (int) $status == self::OIDC_USER_STATUS_ACTIVE
            ? User::USER_STATUS_ACTIVE
            : User::USER_STATUS_INACTIVE;
    }
}
