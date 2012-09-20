<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
interface SugarOAuth2SugarInterface {
    /**
     * Get the user type for this user
     * 
     * @return string
     */
    public function getUserType();
    
    /**
     * Gets a user bean 
     * 
     * @param  string $user_id The ID of the User to get
     * @return User
     */
    public function getUserBean($user_id);

    /**
     * Small validator for child classes to use to determine whether a session can
     * be written to
     * 
     * @return boolean
     */
    public function canStartSession();

    /**
     * Fills in any added session data needed by this client type
     * 
     * This method is used by child classes like portal
     */
    public function fillInAddedSessionData();

    /**
     * Gets the authentication bean for a given client
     * 
     * @param OAuthToken
     * @return mixed
     */
    public function getAuthBean(OAuthToken $token);

    /**
     * Gets contact and user ids for a user id. Most commonly different for clients
     * like portal
     * 
     * @param string $user_id The ID of the user this is for
     * @param string $client_id The client id for this check
     * @return array An array of contact_id and user_id
     */
    public function getIdsForUser($user_id, $client_id);
}