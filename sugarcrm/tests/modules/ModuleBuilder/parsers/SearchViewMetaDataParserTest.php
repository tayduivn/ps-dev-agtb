<?php
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

require_once("modules/ModuleBuilder/parsers/views/SearchViewMetaDataParser.php");
require_once("modules/ModuleBuilder/parsers/views/DeployedMetaDataImplementation.php");

class SearchViewMetaDataParserTest extends Sugar_PHPUnit_Framework_TestCase
{


    public function setUp()
    {
        //echo "Setup";
    }

    public function tearDown()
    {
        //echo "TearDown";
    }

    /**
     * Bug 40530 returned faulty search results when a assigned_to relate field was used on the basic search
     * The fix is making the widgets consistent between Basic and Advanced search when there is no definition
     * for basic search. This was implemented in getOriginalViewDefs and that is what is being tested here.
     */
    public function test_Bug40530_getOriginalViewDefs()
    {
        //echo "begin test";
        // NOTE This is sample data from the live application used for test verification purposes
        $orgViewDefs = array(
            'templateMeta' =>
            array(
                'maxColumns' => '3',
                'widths' =>
                array(
                    'label' => '10',
                    'field' => '30',
                ),
            ),
            'layout' =>
            array(
                'basic_search' =>
                array(
                    'name' =>
                    array(
                        'name' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'current_user_only' =>
                    array(
                        'name' => 'current_user_only',
                        'label' => 'LBL_CURRENT_USER_FILTER',
                        'type' => 'bool',
                        'default' => true,
                        'width' => '10%',
                    ),
                    0 =>
                    array(
                        'name' => 'favorites_only',
                        'label' => 'LBL_FAVORITES_FILTER',
                        'type' => 'bool',
                    ),
                ),
                'advanced_search' =>
                array(
                    'name' =>
                    array(
                        'name' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'website' =>
                    array(
                        'name' => 'website',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'phone' =>
                    array(
                        'name' => 'phone',
                        'label' => 'LBL_ANY_PHONE',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'email' =>
                    array(
                        'name' => 'email',
                        'label' => 'LBL_ANY_EMAIL',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'address_street' =>
                    array(
                        'name' => 'address_street',
                        'label' => 'LBL_ANY_ADDRESS',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'address_city' =>
                    array(
                        'name' => 'address_city',
                        'label' => 'LBL_CITY',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'address_state' =>
                    array(
                        'name' => 'address_state',
                        'label' => 'LBL_STATE',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'address_postalcode' =>
                    array(
                        'name' => 'address_postalcode',
                        'label' => 'LBL_POSTAL_CODE',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'billing_address_country' =>
                    array(
                        'name' => 'billing_address_country',
                        'label' => 'LBL_COUNTRY',
                        'type' => 'name',
                        'options' => 'countries_dom',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'account_type' =>
                    array(
                        'name' => 'account_type',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'industry' =>
                    array(
                        'name' => 'industry',
                        'default' => true,
                        'width' => '10%',
                    ),
                    'assigned_user_id' =>
                    array(
                        'name' => 'assigned_user_id',
                        'type' => 'enum',
                        'label' => 'LBL_ASSIGNED_TO',
                        'function' =>
                        array(
                            'name' => 'get_user_array',
                            'params' =>
                            array(
                                0 => false,
                            ),
                        ),
                        'default' => true,
                        'width' => '10%',
                    ),
                    0 =>
                    array(
                        'name' => 'favorites_only',
                        'label' => 'LBL_FAVORITES_FILTER',
                        'type' => 'bool',
                    ),
                ),
            ),
        );

        $expectedResult = array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'website' =>
            array(
                'name' => 'website',
                'default' => true,
                'width' => '10%',
            ),
            'phone' =>
            array(
                'name' => 'phone',
                'label' => 'LBL_ANY_PHONE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'email' =>
            array(
                'name' => 'email',
                'label' => 'LBL_ANY_EMAIL',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'address_street' =>
            array(
                'name' => 'address_street',
                'label' => 'LBL_ANY_ADDRESS',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'address_city' =>
            array(
                'name' => 'address_city',
                'label' => 'LBL_CITY',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'address_state' =>
            array(
                'name' => 'address_state',
                'label' => 'LBL_STATE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'address_postalcode' =>
            array(
                'name' => 'address_postalcode',
                'label' => 'LBL_POSTAL_CODE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'billing_address_country' =>
            array(
                'name' => 'billing_address_country',
                'label' => 'LBL_COUNTRY',
                'type' => 'name',
                'options' => 'countries_dom',
                'default' => true,
                'width' => '10%',
            ),
            'account_type' =>
            array(
                'name' => 'account_type',
                'default' => true,
                'width' => '10%',
            ),
            'industry' =>
            array(
                'name' => 'industry',
                'default' => true,
                'width' => '10%',
            ),
            'assigned_user_id' =>
            array(
                'name' => 'assigned_user_id',
                'type' => 'enum',
                'label' => 'LBL_ASSIGNED_TO',
                'function' =>
                array(
                    'name' => 'get_user_array',
                    'params' =>
                    array(
                        0 => false,
                    ),
                ),
                'default' => true,
                'width' => '10%',
            ),
            'favorites_only' =>
            array(
                'name' => 'favorites_only',
                'label' => 'LBL_FAVORITES_FILTER',
                'type' => 'bool',
            ),
            'current_user_only' =>
            array(
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
                'default' => true,
                'width' => '10%',
            ),
        );

        // We use a derived class to aid in stubbing out test properties and functions
        $parser = new SearchViewMetaDataParserTestDerivative("basic_search");

        // Creating a mock object for the DeployedMetaDataImplementation

        $impl = $this->getMock('DeployedMetaDataImplementation',
                               array('getOriginalViewdefs'),
                               array(),
                               'DeployedMetaDataImplementation_Mock',
                               FALSE);

        // Making the getOriginalViewdefs function return the test viewdefs and verify that it is being called once
        $impl->expects($this->once())
                ->method('getOriginalViewdefs')
                ->will($this->returnValue($orgViewDefs));

        // Replace the protected implementation with our Mock object
        $parser->setImpl($impl);

        // Execute the method under test
        $result = $parser->getOriginalViewDefs();

        // Verify that the returned result matches our expectations
        $this->assertEquals($result, $expectedResult);

        //echo "Done";
    }

}

/**
 * Using derived helper class from SearchViewMetaDataParser to avoid having to fully
 * initialize the whole class and to give us the flexibility to replace the
 * Deploy/Undeploy MetaDataImplementation
 */
class SearchViewMetaDataParserTestDerivative extends SearchViewMetaDataParser
{
    function __construct ($layout){
        $this->_searchLayout = $layout;
    }

    function setImpl($impl) {
        $this->implementation = $impl;
    }
}