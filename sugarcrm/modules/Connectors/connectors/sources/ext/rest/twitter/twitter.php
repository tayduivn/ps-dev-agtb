<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/*********************************************************************************
* Description:
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
* Reserved. Contributor(s): contact@synolia.com - www.synolia.com
* *******************************************************************************/

require_once('include/connectors/sources/ext/rest/rest.php');

class ext_rest_twitter extends ext_rest {

    protected $_has_testing_enabled = true;

    public function __construct(){
        parent::__construct();
        $this->_enable_in_wizard = false;
        $this->_enable_in_hover = true;
    }

    /**
     * test
     * This method is called from the administration interface to run a test of the service
     * It is up to subclasses to implement a test and set _has_testing_enabled to true so that
     * a test button is rendered in the administration interface
     *
     * @return result boolean result of the test function
     */
    public function test() {
        require_once 'vendor/Zend/Oauth/Consumer.php';

        $api = ExternalAPIFactory::loadAPI('Twitter', true);

        if ($api) {
            $properties = $this->getProperties();
            $config = array(
                'callbackUrl' => 'http://www.sugarcrm.com',
                'siteUrl' => $api->getOauthRequestURL(),
                'consumerKey' => $properties['oauth_consumer_key'],
                'consumerSecret' => $properties['oauth_consumer_secret']
            );

            $consumer = new Zend_Oauth_Consumer($config);
            $consumer->getRequestToken();
            return true;
        }
        
        return false;
    }

    /*
     * getItem
     *
     * As the twitter connector does not have a true API call, we simply
     * override this abstract
     */
    public function getItem($args=array(), $module=null){}


    /*
     * getList
     *
     * As the twitter connector does not have a true API call, we simply
     * override this abstract method
     */
    public function getList($args=array(), $module=null){}
}

?>
