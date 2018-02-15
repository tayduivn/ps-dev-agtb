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

use Sugarcrm\Sugarcrm\Util\Uuid;

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

/**
 * @coversDefaultClass Importer
 */
class SugarEmailAddressImportTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $importData = array();
    private $configOptoutBackUp = null;
    private $fileName = 'upload://import_email_properties.csv';

    protected function setUp()
    {
        if (is_null($this->configOptoutBackUp) && isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        unlink($this->fileName);

        $id = Uuid::uuid1();
        $this->importData = array(
            'id' => $id,
            'first_name' => 'ContactFirstName',
            'last_name' => 'ContactLastName',
            'email' => "Contact_{$id}@test.net",
        );
    }

    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        unset($GLOBALS['current_user']);
        $this->importData = array();
        unlink($this->fileName);
        if (!is_null($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        }
    }

    public function optoutDataProvider()
    {
        return array(
            [true],
            [false],
        );
    }

    /**
     * @covers ::import
     * @dataProvider optoutDataProvider
     */
    public function testImportEmailAddress_OptoutSupplied_ImportsCorrectly(bool $optOut)
    {
        $contactId = $this->importData['id'];

        $importValue = intval($optOut);
        $this->importData['email_opt_out'] = "{$importValue}";
        $this->createImportFile($this->importData);
        $bean = BeanFactory::newBean('Contacts');
        $importSource = new ImportFile($this->fileName, ',', '"');
        $importer = new Importer($importSource, $bean);
        $importer->import();

        $contact = $this->retrieveAndDeleteImportedContact($contactId);
        $this->assertNotEmpty($contact, 'Contact record was not created on Import');

        $this->assertEquals($contact->first_name, $this->importData['first_name'], 'Contact first_name not imported');
        $this->assertEquals($contact->last_name, $this->importData['last_name'], 'Contact last_name not imported');
        $this->assertEquals(
            $this->importData['email'],
            $contact->email[0]['email_address'],
            'Contact email address not created on import'
        );

        $this->assertEquals(
            $this->importData['email_opt_out'],
            $contact->email[0]['opt_out'],
            'Opt_out value on created Email Address Value does not match Opt-Out Value Supplied in Import Record'
        );
    }

    /**
     * @covers ::import
     * @dataProvider optoutDataProvider
     */
    public function testImportEmailAddress_OptoutNotMapped_DefaultsToConfigValue(bool $defaultOptout)
    {
        $this->setConfigOptout($defaultOptout);

        $contactId = $this->importData['id'];

        $this->createImportFile($this->importData);
        $bean = BeanFactory::newBean('Contacts');
        $importSource = new ImportFile($this->fileName, ',', '"');
        $importer = new Importer($importSource, $bean);
        $importer->import();

        $contact = $this->retrieveAndDeleteImportedContact($contactId);
        $this->assertNotEmpty($contact, 'Contact record was not created on Import');

        $this->assertEquals($contact->first_name, $this->importData['first_name'], 'Contact first_name not imported');
        $this->assertEquals($contact->last_name, $this->importData['last_name'], 'Contact last_name not imported');
        $this->assertEquals(
            $this->importData['email'],
            $contact->email[0]['email_address'],
            'Contact email address not created on import'
        );
        $this->assertEquals(
            $defaultOptout,
            ($contact->email[0]['opt_out'] == '1'),
            'Opt_out value on created Email Address Value does not match the system-configured default opt-out value'
        );
    }

    private function createImportFile($importData)
    {
        $fields = array_keys($importData);
        $fieldCount = count($fields);

        $_REQUEST['columncount'] = $fieldCount;
        $_REQUEST['import_module'] = 'Contacts';

        for ($i = 0; $i < $fieldCount; $i++) {
            $_REQUEST["colnum_{$i}"] = $fields[$i];
        }

        $values = array_values($importData);
        $data = "\"" . implode('","', $values) . "\"\n";
        file_put_contents($this->fileName, $data);
    }

    private function retrieveAndDeleteImportedContact($contactId)
    {
        $contact = BeanFactory::retrieveBean('Contacts', $contactId);
        if (!empty($contact)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '" . $contactId . "'");
        }
        return $contact;
    }

    private function setConfigOptout(bool $optOut)
    {
        $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = "{$optOut}";
    }
}
