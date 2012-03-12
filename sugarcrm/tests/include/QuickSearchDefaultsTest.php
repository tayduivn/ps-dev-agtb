<?php
require_once('include/QuickSearchDefaults.php');

class QuickSearchDefaultsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testGetQuickSearchDefaults()
    {
        $qsd = QuickSearchDefaults::getQuickSearchDefaults();
        $this->assertInstanceOf('QuickSearchDefaults', $qsd, 'Object retrieved');
    }
}