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

/**
 * @ticket 48484
 */
class Bug48484Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Existing module name used to perform the test
     *
     * @var string
     */
    protected $moduleName = 'Accounts';

    /**
     * Custom field name that is tested to be considered
     *
     * @var string
     */
    protected $customFieldName = 'bug48484test_c';

    /**
     * Stub of the mass update object being tested.
     * @var
     */
    protected $massUpdate;

    /**
     * Basic range used to perform the test
     *
     * @var string
     */
    protected $range = 'this_year';

    public function setUp()
    {
         $this->massUpdate = new MassUpdateStub($this->customFieldName);
    }
    /**
     * Verify whether custom field values are considered during mass update
     */
    public function testModuleCustomFieldsAreConsidered()
    {
        // create search query
        $query = array(
            'searchFormTab'                                => 'basic_search',
            $this->customFieldName . '_basic_range_choice' => $this->range,
            'range_' . $this->customFieldName . '_basic'   => '[' . $this->range . ']',
        );

        // encode the query as the MassUpdate::generateSearchWhere requires
        $query = base64_encode(serialize($query));

        // generate SQL where clause
        $this->massUpdate->generateSearchWhere($this->moduleName, $query);

        // ensure that field name is contained in SQL where clause
        $this->assertContains($this->customFieldName, $this->massUpdate->where_clauses);
    }


}

require_once 'include/MassUpdate.php';

class MassUpdateStub extends MassUpdate
{
    protected $customFieldName = 'bug48484test_c';

    public function __construct($customFieldName)
    {
        $this->customFieldName = $customFieldName;
    }

    protected function getSearchDefs($module, $metafiles = array())
    {
        return array($module => array(
            'layout' => array(
                'basic_search' => array(
                    $this->customFieldName => array(
                        'type' => 'date',
                        'name' => $this->customFieldName,
                    ),
                ),
            ),
        ));
    }

    protected function getSearchFields($module, $metafiles = array())
    {
         $customFields = array(
            'range_'       . $this->customFieldName,
            'start_range_' . $this->customFieldName,
            'end_range_'   . $this->customFieldName,
        );

        $searchFields = array();
        foreach ($customFields as $field)
        {
            $searchFields[$field] = array(
                'query_type'          => 'default',
                'enable_range_search' => true,
                'is_date_field'       => true,
            );
        }
        return array($module => $searchFields);
    }
}
