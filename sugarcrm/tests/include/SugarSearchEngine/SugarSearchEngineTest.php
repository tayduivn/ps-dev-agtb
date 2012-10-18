<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'include/SugarSearchEngine/SugarSearchEngineFactory.php';
require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');


class SugarSearchEngineTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider factoryProvider
     * @param string $engineName
     * @param string $expectedClass
     */
    public function testFactoryMethod($engineName, $expectedClass)
    {
        $instance = SugarSearchEngineFactory::getInstance($engineName);
        $this->assertEquals(get_class($instance), $expectedClass);
    }

    public function factoryProvider()
    {
        return array(
            array('','SugarSearchEngine'),
            array('Elastic','SugarSearchEngineElastic'),
            //Fallback to default.
            array('BadClassName','SugarSearchEngine')
        );
    }

    public function testSearchEngineDown()
    {
        SugarSearchEngineAbstractBase::markSearchEngineStatus(true);
        $isDown = SugarSearchEngineAbstractBase::isSearchEngineDown();
        $this->assertTrue($isDown, 'Incorrect status');
    }

    public function testSearchEngineUp()
    {
        SugarSearchEngineAbstractBase::markSearchEngineStatus(false);
        $isDown = SugarSearchEngineAbstractBase::isSearchEngineDown();
        $this->assertFalse($isDown, 'Incorrect status');
    }
}

class SugarSearchEngineTestStub extends SugarSearchEngineAbstractBase
{
    public function connect() {}

    public function indexBean($bean, $batched = TRUE){}

    public function flush() {}

    public function delete(SugarBean $bean){}

    public function search($query, $offset = 0, $limit = 20) {}

    public function bulkInsert(array $docs){}

    public function getServerStatus(){}

    public function createIndex($recreate = false) {}

    public function createIndexDocument($bean, $searchFields = null){}

}
