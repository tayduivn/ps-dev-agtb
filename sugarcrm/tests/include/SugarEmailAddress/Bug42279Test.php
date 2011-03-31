<?php
require_once 'include/SugarEmailAddress/SugarEmailAddress.php';
require_once 'SugarTestContactUtilities.php';


/**
 * 
 * Bug 42279
 *
 */

class Bug42279Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $contact;

    public function setUp() {
        $this->contact = SugarTestContactUtilities::createContact();
    }

    public function tearDown() {
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * @group bug42279
     */
    public function testEmailAddressInFetchedRow() {
        $sea = new SugarEmailAddress();

        // this will populate contact->email1
        $sea->populateLegacyFields($this->contact);
        $email1 = $this->contact->email1;

        // this should set fetched_row['email1'] to contatc->email1
        $sea->handleLegacyRetrieve($this->contact);

        $this->assertEquals($email1, $this->contact->fetched_row['email1']);
    }
}
