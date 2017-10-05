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

/**
 * The old Sugar oauth2 flow used storage object
 * We cannot create SugarOAuth2ServerOIDC without storage
 * This class provides required interface but all methods are not allowed
 */
class SugarOAuth2StorageOIDC implements IOAuth2GrantUser
{
    /**
     * @inheritdoc
     */
    public function checkUserCredentials($client_id, $username, $password)
    {
        throw new BadMethodCallException('Unsupported method for OIDC');
    }

    /**
     * @inheritdoc
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        throw new BadMethodCallException('Unsupported method for OIDC');
    }

    /**
     * @inheritdoc
     */
    public function getClientDetails($client_id)
    {
        throw new BadMethodCallException('Unsupported method for OIDC');
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken($oauth_token)
    {
        throw new BadMethodCallException('Unsupported method for OIDC');
    }

    /**
     * @inheritdoc
     */
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null)
    {
        throw new BadMethodCallException('Unsupported method for OIDC');
    }

    /**
     * @inheritdoc
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        throw new BadMethodCallException('Unsupported method for OIDC');
    }
}
