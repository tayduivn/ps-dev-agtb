<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Security\InputValidation;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\InputValidation\Serialized
 *
 */
class SerializedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::unserialize
     * @dataProvider providerTestUnserialize
     */
    public function testUnserialize($value, $expected)
    {
        $this->assertEquals(
            $expected, 
            \Sugarcrm\Sugarcrm\Security\InputValidation\Serialized::unserialize($value)
        );
    }

    public function providerTestUnserialize()
    {
        return array(
            array(
                'b:0;',
                false,
            ),
            array(
                'b:1;',
                true,
            ),
            array(
                'i:10;',
                10,
            ),
            array(
                'd:12.199999999999999;',
                12.2,
            ),
            array(
                's:6:"String";',
                'String',
            ),
            array(
                'a:1:{s:3:"foo";s:3:"bar";}',
                array('foo' => 'bar'),
            ),
            array(
                'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}',
                false,
            ),
            array(
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                false,
            ),
            array(
                'O:8:',
                false,
            ),
        );
    }
}
