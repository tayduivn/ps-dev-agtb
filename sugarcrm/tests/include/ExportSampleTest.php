<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once 'include/utils.php';
require_once 'include/export_utils.php';

class ExportSampleTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $exportModule = 'Accounts';
    private $exportBeanType = 'Account';
    private $expContent = '';
    private $expArr =  array();

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $_REQUEST['module'] = $this->exportModule;
        $_REQUEST['action'] = 'index';
        $_REQUEST['all'] = '1';
        $_REQUEST['sample'] = 'true';
        $this->expContent = export($this->exportModule,null, null, 1);
        $this->expArr =  explode("\r\n", trim($this->expContent));


    }

    public function tearDown()
    {

        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['all']);
        unset($_REQUEST['sample']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($this->expContent);
        unset($this->expArr);

    }

    //verify that the export actually exported some data
    public function testVerifyAcquiredSampleData()
    {
        //make sure export returned something
        $this->assertFalse(empty($this->expContent), 'sample export returned an empty string, it should return a header row with 1 to 5 records of sample data');

        //make sure it returned at least 1 row.
        $this->assertTrue(count($this->expArr)>1, 'sample export returned only one record, which should be the header row.  It should return a header row with between 1 to 5 records of sample data');
    }

    //make sure that the export is using the label names and not the db names
    public function testVerifyColumLabelNames()
    {
        //create a new bean
        $acc = new $this->exportBeanType();
        //make sure that the db name is NOT used.  Lets pick a field we know will have a different label, like account_type
        $this->assertNotContains( 'account_type', $this->expArr[0], ' the header list contained the field account_type, it should have translated it to a proper field label');
        //now let's make sure that the label IS used.  Lets search for the translated field label value
        $this->assertContains( translateForExport('account_type',$acc), $this->expArr[0], ' the label for account_type field was not found in the header list.  The field label translation process is broken for exports');
	 }

    //if the first field returned is id, the file can be confused with a SYLK file, and will not open in Excel.  Lets make sure ID is not first
    public function testNotSylkFormattedFile()
    {
        //lets make an array of the first header row
        $headerRow = $this->returnHeaderFieldsAsArray();
        $this->assertFalse(strtolower($headerRow[0]) == 'id', 'first column is id, this will cause the file to be confused with a SYLK file, and will not open up correctly in Excel.  ID should not be first column returned');
	 }

    //the returned fields need to match a specified order
    public function testOrderOfExportedFields()
    {
        //lets make an array of the first header row for comparison
        $headerRow = $this->returnHeaderFieldsAsArray();

        //now lets get the list of fields that this bean type will expect to export
        $acc = new $this->exportBeanType();
        $where=$orderby = '';
        $query = $acc->create_export_query($orderby,$where);
        $result = $acc->db->limitQuery($query, 0, 5, true, '');
        $fields_array = $acc->db->getFieldsArray($result,true);

        //set up the order on the fields array
        $orderedHeaderRow = get_field_order_mapping($this->exportModule, $fields_array,true);

        //set up the translated labels to be used for the header row
        foreach($orderedHeaderRow as $dbname){
            $orderedHeaderRow[$dbname] = translateForExport($dbname,$acc);
        }

        //now lets drop the key values to properly compare versus the header row
        $orderedHeaderRow = array_values($orderedHeaderRow);

        $this->assertEquals($orderedHeaderRow, $headerRow, 'The exported header row was not ordered according to the specified order of field columns');

	 }

    //Some fields are to be excluded from export, as specified in export_utils.
    public function testExclusionOfFields()
    {
        //first lets grab the expected bean export fields WITH the exclude flag (default)
        $fieldsWithExcluded = $this->returnOrderedBeanHeaderFieldsArray(true);

        //now lets grab the expected fields from the export, WITHOUT the exclusion flag
        $fieldsWithoutExcluded = $this->returnOrderedBeanHeaderFieldsArray(false);

        //there should be one extra field in the non excluded array
        $this->assertTrue(count($fieldsWithoutExcluded) > count($fieldsWithExcluded),' Fields are not being stripped out (excluded) properly during order mapping in export_utils.php ');
	 }

    //helper function to create an array from exported file header row
    public function returnHeaderFieldsAsArray(){
        return explode(',', str_replace('"','',$this->expArr[0]));

    }

    //helper function to create an array of the expected header row for a given bean
    public function returnOrderedBeanHeaderFieldsArray($exclude=true){

        //now lets get the list of fields that this bean type will expect to export
        $acc = new $this->exportBeanType();
        $where=$orderby = '';
        $query = $acc->create_export_query($orderby,$where);
        $result = $acc->db->limitQuery($query, 0, 5, true, '');
        $fields_array = $acc->db->getFieldsArray($result,true);

        //set up the order on the fields array
        $orderedHeaderRow = get_field_order_mapping($this->exportModule, $fields_array,$exclude);

        //set up the translated labels to be used for the header row
        foreach($orderedHeaderRow as $dbname){
            $orderedHeaderRow[$dbname] = translateForExport($dbname,$acc);
        }
        return $orderedHeaderRow;

    }

}

