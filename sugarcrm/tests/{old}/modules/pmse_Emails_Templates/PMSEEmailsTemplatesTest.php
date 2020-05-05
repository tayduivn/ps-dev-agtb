<?php
//FILE SUGARCRM flav=ent ONLY
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

use PHPUnit\Framework\TestCase;

class PMSEEmailsTemplatesTest extends TestCase
{
    /**
     * @var PMSEEmailsTemplates
     */
    protected $object;

    /**
     * @covers PMSECrmDataWrapper::retrieveFields
     */
    public function testRetrieveFields()
    {
        $this->object = $this->getMockBuilder('PMSEEmailsTemplates')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $GLOBALS['app_list_strings']['moduleList'] = [];
        $this->object->beanList = ['Emails' => 'Email'];

        $output = $this->object->retrieveFields('Emails', null, 20, 0, 'Emails');
        $fields = $this->getOutputFields($output['records']);
        $this->assertCount(9, $fields);
        $this->assertContains("direction", $fields, "direction should be a supported field in ET.");
        $this->assertNotContains("type", $fields, "type should not be a supported field in ET.");
    }

    /**
     * Get output fields
     * @params array
     * @return array
     */
    protected function getOutputFields($result)
    {
        $fields = [];
        if (!empty($result)) {
            foreach ($result as $field) {
                $fields[] = $field['id'];
            }
        }
        return $fields;
    }
}
