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
namespace Sugarcrm\SugarcrmTestsUnit\modules\ModuleBuilder\parsers\views;

use PHPUnit\Framework\TestCase;
use SugarTestReflection;

/**
 * @coversDefaultClass \DeployedMetaDataImplementation
 */
class DeployedMetaDataImplementationTest extends TestCase
{
    /**
     * @covers ::getPreviewDefsFromRecord
     */
    public function testGetPreviewDefsFromRecord()
    {
        $fields = array(
            'name',
            array(
                'name' => 'desc',
                'type' => 'name',
            ),
        );
        $defs['base']['view']['record'] = array(
            'panels' => array(
                array(
                    'fields' => array(
                        'name',
                        array(
                            'name' => 'favorite',
                            'type' => 'favorite',
                        ),
                        array(
                            'name' => 'desc',
                            'type' => 'name',
                        ),
                    ),
                ),
            ),
        );
        $impl = $this->getMockBuilder('DeployedMetaDataImplementation')
        ->disableOriginalConstructor()->setMethods(null)->getMock();
        $defs = SugarTestReflection::callProtectedMethod($impl, 'getPreviewDefsFromRecord', array($defs));
        $this->assertArrayHasKey('preview', $defs['base']['view']);
        $this->assertArrayHasKey('templateMeta', $defs['base']['view']['preview']);
        $this->assertArrayHasKey('maxColumns', $defs['base']['view']['preview']['templateMeta']);
        $this->assertEquals(1, $defs['base']['view']['preview']['templateMeta']['maxColumns']);
        $this->assertArrayHasKey('panels', $defs['base']['view']['preview']);
        $this->assertArrayHasKey('fields', $defs['base']['view']['preview']['panels'][0]);
        $this->assertEquals($fields, array_values($defs['base']['view']['preview']['panels'][0]['fields']));
    }
}
