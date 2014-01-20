<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/ExpressionEngine/clients/base/api/RelatedValueApi.php';

/**
 * Tests RelatedValueApi.
 */
class RS148Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var SugarBean
     */
    protected $account;

    /**
     * @var SugarBean
     */
    protected $contact;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        $this->api = new RelatedValueApi();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact('', array('first_name' => 'RS148Test_FName'));
        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact);
        $this->account->save();
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @param array $fields
     * @param mixed $expected
     * @param bool $setRelatedId
     * @dataProvider relatedProvider
     */
    public function testRelatedValue($fields, $expected, $setRelatedId)
    {
        if ($setRelatedId) {
            $fields['contacts']['relId'] = $this->contact->id;
            $expected['contacts']['relId'] = $this->contact->id;
        }
        $encoded_fields = json_encode($fields);
        $result = $this->api->getRelatedValues(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'fields' => $encoded_fields, 'record' => $this->account->id)
        );
        $this->assertEquals($expected, $result);
    }

    public function relatedProvider()
    {
        return array(
            'empty' => array(
                array(),
                array(),
                false,
            ),
            'related' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'related',
                        'relate' => 'first_name',
                    )
                ),
                array(
                    'contacts' => array(
                        'related' => array(
                            'first_name' => 'RS148Test_FName'
                        )
                    )
                ),
                true,
            ),
            'count' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'count',
                        'relate' => 'first_name',
                    )
                ),
                array(
                    'contacts' => array(
                        'count' => 1
                    )
                ),
                false,
            ),
            'rollupMin' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'rollupMin',
                        'relate' => 'first_name',
                    )
                ),
                array(
                    'contacts' => array(
                        'rollupMin' => array(
                            'first_name' => ''
                        )
                    )
                ),
                false,
            ),
            'rollupCurrencySum' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'rollupCurrencySum',
                        'relate' => 'first_name',
                    )
                ),
                array(
                    'contacts' => array(
                        'rollupCurrencySum' => array(
                            'first_name' => 0
                        )
                    )
                ),
                false,
            )
        );
    }
}
