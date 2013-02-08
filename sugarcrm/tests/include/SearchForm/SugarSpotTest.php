<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once 'include/SearchForm/SugarSpot.php';

class SugarSpotTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }
    
    public function tearDown()
    {
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 41236
     */
    public function testSearchGrabsModuleDisplayName() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('moduleList',array('Foo'=>'Bar'));
        $langpack->save();
        
        $result = array(
            'Foo' => array(
                'data' => array(
                    array(
                        'ID' => '1',
                        'NAME' => 'recordname',
                        ),
                    ),
                'pageData' => array(
                    'offsets' => array(
                        'total' => 1,
                        'next' => 0,
                        ),
                    'bean' => array(
                        'moduleDir' => 'Foo',
                        ),
                    ),
                ),
                'readAccess' => true,
            );
        
        $sugarSpot = $this->getMock('SugarSpot', array('_performSearch'));
        $sugarSpot->expects($this->any())
            ->method('_performSearch')
            ->will($this->returnValue($result));
            
        $returnValue = $sugarSpot->searchAndDisplay('','');

        $this->assertRegExp('/Bar/',$returnValue);
    }

    /**
     * @ticket 43080
     */
    public function testSearchGrabsMore() 
    {
        $app_strings = return_application_language($GLOBALS['current_language']); 
        $this->assertTrue(array_key_exists('LBL_SEARCH_MORE', $app_strings));

        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppString('LBL_SEARCH_MORE', 'XXmoreXX');
        $langpack->save();
        
        $result = array(
            'Foo' => array(
                'data' => array(
                    array(
                        'ID' => '1',
                        'NAME' => 'recordname',
                        ),
                    ),
                'pageData' => array(
                    'offsets' => array(
                        'total' => 100,
                        'next' => 0,
                        ),
                    'bean' => array(
                        'moduleDir' => 'Foo',
                        ),
                    ),
                ),
                'readAccess' => true,
            );
        
        $sugarSpot = $this->getMock('SugarSpot', array('_performSearch'));
        $sugarSpot->expects($this->any())
            ->method('_performSearch')
            ->will($this->returnValue($result));
            
        $returnValue = $sugarSpot->searchAndDisplay('','');

        $this->assertNotContains('(99 more)',$returnValue);
        $this->assertContains('(99 XXmoreXX)',$returnValue);
    }


    /**
     * providerTestSearchType
     * This is the provider function for testFilterSearchType
     *
     */
    public function providerTestSearchType()
    {
        return array(
              array('phone', '777', true),
              array('phone', '(777)', true),
              array('phone', '%777', true),
              array('phone', '77', false),
              array('phone', '%77) 7', false),
              array('phone', '88-88-88', false),
              array('int', '1', true),
              array('int', '1.0', true),
              array('int', '.1', true),
              array('int', 'a', false),
              array('decimal', '1.0', true),
              array('decimal', '1', true),
              array('decimal', '1,000', true),
              array('decimal', 'aaaaa', false),
              array('float', '1.0', true),
              array('float', '1', true),
              array('float', '1,000', true),
              array('float', 'aaaaa', false),
              array('id', '1', false),
              array('datetime', '2011-01-01 10:10:10', false),
              array('date', '2011-01-01', false),
              array('bool', true, false),
              array('bool', false, false),
              array('foo', 'foo', true),
        );
    }

    /**
     * testFilterSearchType
     * This function uses a provider to test the filter search type
     * @dataProvider providerTestSearchType
     */
    public function testFilterSearchType($type, $query, $expected)
    {
        $sugarSpot = new Bug50484SugarSpotMock();
        $this->assertEquals($expected, $sugarSpot->filterSearchType($type, $query),
            ('SugarSpot->filterSearchType expected type ' . $type . ' with value ' . $query . ' to return ' . $expected ? 'true' : false));
    }

}


class Bug50484SugarSpotMock extends SugarSpot
{
    public function filterSearchType($type, $query)
    {
        return parent::filterSearchType($type, $query);
    }
}
