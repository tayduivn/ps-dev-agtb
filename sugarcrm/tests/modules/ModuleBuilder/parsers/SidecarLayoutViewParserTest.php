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

require_once "modules/ModuleBuilder/parsers/parser.sidecarlayoutview.php";


class SidecarLayoutViewParserTest extends Sugar_PHPUnit_Framework_TestCase
{


    public function setUp()
    {
        //echo "Setup";
    }

    public function tearDown()
    {
        //echo "TearDown";
    }


//
//        // We use a derived class to aid in stubbing out test properties and functions
//        $parser = new SearchViewMetaDataParserTestDerivative("basic_search");
//
//        // Creating a mock object for the DeployedMetaDataImplementation
//
//        $impl = $this->getMock('DeployedMetaDataImplementation',
//                               array('getOriginalViewdefs'),
//                               array(),
//                               'DeployedMetaDataImplementation_Mock',
//                               FALSE);
//
//        // Making the getOriginalViewdefs function return the test viewdefs and verify that it is being called once
//        $impl->expects($this->once())
//                ->method('getOriginalViewdefs')
//                ->will($this->returnValue($orgViewDefs));
//
//        // Replace the protected implementation with our Mock object
//        $parser->setImpl($impl);
//
//        // Execute the method under test
//        $result = $parser->getOriginalViewDefs();
//
//        // Verify that the returned result matches our expectations
//        $this->assertEquals($result, $expectedResult);
//
//        //echo "Done";
//    }

    public function testdoWeHasAParser() {
        $parser = new ParserSidecarLayoutView();
        $this->assertInstanceOf('ParserSidecarLayoutView',$parser);
    }
}

///**
// * Using derived helper class from SearchViewMetaDataParser to avoid having to fully
// * initialize the whole class and to give us the flexibility to replace the
// * Deploy/Undeploy MetaDataImplementation
// */
//class SearchViewMetaDataParserTestDerivative extends SearchViewMetaDataParser
//{
//    function __construct ($layout){
//        $this->_searchLayout = $layout;
//    }
//
//    function setImpl($impl) {
//        $this->implementation = $impl;
//    }
//}