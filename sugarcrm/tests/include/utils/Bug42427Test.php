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
 
/**
 * @ticket 42427
 */
class Bug42427Test extends Sugar_PHPUnit_Framework_TestCase
{    
    public function setUp()
    {
        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
        sugar_cache_clear('app_list_strings.de_test');
        
        if ( isset($sugar_config['default_language']) ) {
            $this->_backup_default_language = $sugar_config['default_language'];
        }
    }
    
    public function tearDown()
    {
        unlink('include/language/fr_test.lang.php');
        unlink('include/language/de_test.lang.php');
        
        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
        sugar_cache_clear('app_list_strings.de_test');
        
        if ( isset($this->_backup_default_language) ) {
            $sugar_config['default_language'] = $this->_backup_default_language;
        }
    }
    
    public function testWillLoadEnUsStringIfDefaultLanguageIsNotEnUs()
    {
        file_put_contents('include/language/fr_test.lang.php', '<?php $app_list_strings = array(); ?>');
        file_put_contents('include/language/de_test.lang.php', '<?php $app_list_strings = array(); ?>');
        
        $sugar_config['default_language'] = 'fr_test';
        
        $strings = return_app_list_strings_language('de_test');
        
        $this->assertArrayHasKey('lead_source_default_key',$strings);
    }
}
