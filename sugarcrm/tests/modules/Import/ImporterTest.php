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

 
require_once 'modules/Import/Importer.php';
require_once 'modules/Import/sources/ImportFile.php';

class ImporterTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_importModule;
    private $_importObject;

    // date_entered and last_name
    private static $CsvContent = array (
        0 => "\"3/26/2011 10:02am\",\"Doe\"",
        1 => "\"2011-3-26 10:2 am\",\"Doe\"",
        2 => "\"3.26.2011 10.02\",\"Doe\"",
    );

    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_importModule = 'Contacts';
        $this->_importObject = 'Contact';
    }
    
    public function tearDown() 
    {
        $GLOBALS['db']->query("DELETE FROM contacts where created_by='{$GLOBALS['current_user']->id}'");

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
    
    public function providerCsvData()
    {
        return array(
            array(0, '2011-03-26 10:02:00', 'm/d/Y', 'h:ia'),
            array(1, '2011-03-26 10:02:00', 'Y-m-d', 'h:ia'),
            array(2, '2011-03-26 10:02:00', 'm.d.Y', 'H.i'),
            );
    }

    /**
     * @dataProvider providerCsvData
     */
    public function testDateTimeImport($content_idx, $expected_datetime, $date_format, $time_format)
    {
        $file = $GLOBALS['sugar_config']['upload_dir'].'test.csv';
        $ret = file_put_contents($file, self::$CsvContent[$content_idx]);
        $this->assertGreaterThan(0, $ret, 'Failed to write to '.$file .' for content '.$content_idx);

        $importSource = new ImportFile($file, ',', '"');

        $bean = loadBean($this->_importModule);

        $_REQUEST['columncount'] = 2;
        $_REQUEST['colnum_0'] = 'date_entered';
        $_REQUEST['colnum_1'] = 'last_name';
        $_REQUEST['import_module'] = 'Contacts';
        $_REQUEST['importlocale_charset'] = 'UTF-8';
        $_REQUEST['importlocale_dateformat'] = $date_format;
        $_REQUEST['importlocale_timeformat'] = $time_format;
        $_REQUEST['importlocale_timezone'] = 'GMT';
        $_REQUEST['importlocale_default_currency_significant_digits'] = '2';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_dec_sep'] = '.';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_default_locale_name_format'] = 's f l';
        $_REQUEST['importlocale_num_grp_sep'] = ',';

        $importer = new Importer($importSource, $bean);
        $importer->import();

        $query = "SELECT date_entered from contacts where created_by='{$GLOBALS['current_user']->id}'";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($expected_datetime, $GLOBALS['db']->fromConvert($row['date_entered'], 'datetime'), 'Got incorrect date_entered.');

    }
}
    
