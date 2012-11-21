<?php
require_once('include/entryPoint.php');
require_once('data/SugarBean.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Opportunities/Opportunity.php');
function getOffsetDate($offset)
{
    $oneday = 24 * 3600;
    $time   = time() + $oneday * $offset;
    return date('Y-m-d', $time);

}
class Phase3Demo
{
    protected $userId = '';
    protected $demodata;

    public function __construct($user_id)
    {
        $this->userId   = $user_id;
        $this->demodata = array(
            array(
                'user_id' => $this->userId,
                'se_id' => 'seed_max_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_dell',
                        'name' => 'Dell',
                        'phone_office' => ' (512) 338-4400',
                        'website' => 'www.dell.com',
                        'billing_address_street' => '1 Dell Way',
                        'billing_address_city' => 'Round Rock',
                        'billing_address_state' => 'TX',
                        'billing_address_postalcode' => '78682',
                        'billing_address_country' => 'USA',
                        'account_type' => 'Customer',
                        'industry' => 'Technology'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_mdell',
                        'first_name' => 'Michael',
                        'last_name' => 'Dell',
                        'phone_office' => ' (512) 338-4400',
                        'email1' => 'mdell@example.com',
                        'title' => 'Chairman and CEO'
                    ),
                    array(
                        'id' => 'seed_ss_dell',
                        'first_name' => 'Stephen',
                        'phone_office' => ' (512) 338-4400',
                        'last_name' => 'Schuckenbrock',
                        'email1' => 'sschuckenbrock@example.com',
                        'title' => 'CIO'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_dell_op1',
                        'name' => '150 Liceneses',
                        'amount' => '15000',
                        'date_closed' => 20,
                        'sales_stage' => 'Qualification',
                        'probability' => 90
                    ),
                    array(
                        'id' => 'seed_dell_op2',
                        'name' => 'Training',
                        'amount' => '5000',
                        'sales_stage' => 'Qualification',
                        'probability' => 90,
                        'date_closed' => 20
                    ),
                    array(
                        'id' => 'seed_dell_op3',
                        'name' => 'Jump Start',
                        'amount' => '5000',
                        'sales_stage' => 'Qualification',
                        'probability' => 90,
                        'date_closed' => 20
                    )
                ),

                'Meetings' => array(
                    array(
                        'id' => 'seed_dellmeeting1',
                        'name' => 'Initial Call',
                        'type' => 'Call'
                    ),

                    array(
                        'id' => 'seed_dellmeeting3',
                        'name' => 'In Person Meeting',
                        'type' => 'Meeting'
                    )
                ),

                'Calls' => array(
                    array(
                        'id' => 'seed_dellmeeting4',
                        'name' => 'Meeting Recap',
                        'type' => 'Call'
                    ),

                    array(
                        'id' => 'seed_dellmeeting5',
                        'name' => 'Discuss Training Options',
                        'type' => 'Call'
                    ),
                    array(
                        'id' => 'seed_dellmeeting2',
                        'name' => 'Followup Call',
                        'type' => 'Call'
                    )

                )

            ),

            array(
                'user_id' => $this->userId,
                'se_id' => 'seed_max_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_chase',
                        'name' => 'JP Morgan Chase',
                        'phone_office' => ' (555) 555-4400',
                        'website' => 'www.jpmorgan.com',
                        'billing_address_street' => '1111 Polaris Parkway',
                        'billing_address_city' => 'Chicago',
                        'billing_address_state' => 'Ohio',
                        'billing_address_postalcode' => '43240',
                        'billing_address_country' => 'USA',
                        'account_type' => 'Customer',
                        'industry' => 'Banking'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_jlee',
                        'first_name' => 'Jimmy',
                        'last_name' => 'Lee',
                        'phone_office' => ' (555) 555-4400',
                        'email1' => 'jlee@example.com',
                        'title' => 'Vice Chairman'
                    ),
                    array(
                        'id' => 'seed_ikhan',
                        'first_name' => 'Imran',
                        'phone_office' => ' (555) 555-4400',
                        'last_name' => 'Khan',
                        'email1' => 'ikhan@example.com',
                        'title' => 'Managing Director'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_chase_op1',
                        'name' => '450 Liceneses',
                        'amount' => '45000',
                        'date_closed' => 20,
                        'sales_stage' => 'Qualification',
                        'probability' => 80
                    ),
                    array(
                        'id' => 'seed_chase_op2',
                        'name' => 'Training',
                        'amount' => '5000',
                        'sales_stage' => 'Qualification',
                        'probability' => 80,
                        'date_closed' => 20
                    ),
                    array(
                        'id' => 'seed_chase_op3',
                        'name' => '5 user Pilot',
                        'amount' => '500',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100,
                        'date_closed' => 20
                    )
                ),

                'Meetings' => array(

                    array(
                        'id' => 'seed_chasemeeting3',
                        'name' => 'Land and Expand',
                        'type' => 'Meeting'
                    )



                ),
                'Calls' => array(
                    array(
                        'id' => 'seed_chasemeeting1',
                        'name' => 'Initial Call',
                        'type' => 'Call'
                    ),

                    array(
                        'id' => 'seed_chasemeeting2',
                        'name' => 'Discuss The Pilot',
                        'type' => 'Call'
                    ),

                    array(
                        'id' => 'seed_chasemeeting4',
                        'name' => 'Global Deployment',
                        'type' => 'Call'
                    )
                )
            ),


            array(
                'user_id' => $this->userId,
                'se_id' => 'seed_sally_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_hp',
                        'name' => 'HP',
                        'phone_office' => ' (555) 555-4400',
                        'website' => 'www.hp.com',
                        'billing_address_street' => '1501 Page Mill Rd',
                        'billing_address_city' => 'Palo Alto',
                        'billing_address_state' => 'CA',
                        'billing_address_postalcode' => '94304',
                        'billing_address_country' => 'USA',
                        'account_type' => 'Customer',
                        'industry' => 'Technology'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_mwhitman',
                        'first_name' => 'Meg',
                        'last_name' => 'Whitman',
                        'phone_office' => ' (555) 555-4400',
                        'email1' => 'mwhitman@example.com',
                        'title' => 'CEO'
                    ),
                    array(
                        'id' => 'seed_rland',
                        'first_name' => 'Ray',
                        'phone_office' => ' (555) 555-4400',
                        'last_name' => 'Lang',
                        'email1' => 'rlane@example.com',
                        'title' => 'Non-Executive Chairman'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_hp_op1',
                        'name' => '25 Liceneses',
                        'amount' => '2500',
                        'date_closed' => 20,
                        'sales_stage' => 'Qualification',
                        'probability' => 10
                    ),
                    array(
                        'id' => 'seed_hp_op2',
                        'name' => 'Training',
                        'amount' => '5000',
                        'sales_stage' => 'Qualification',
                        'probability' => 10,
                        'date_closed' => 20
                    )
                ),

                'Calls' => array(
                    array(
                        'id' => 'seed_hpmeeting1',
                        'name' => 'Initial Call',
                        'type' => 'Call'
                    )


                )
            ),




            array(
                'user_id' => $this->userId,
                'Accounts' => array(
                    array(
                        'id' => 'seed_barclays',
                        'name' => 'Barclays',
                        'phone_office' => ' +44 (0) 20 7623 2323',
                        'website' => 'www.barclays.com',
                        'billing_address_street' => '5 The North Colonnade',
                        'billing_address_city' => 'Canary Wharf',
                        'billing_address_state' => 'London',
                        'billing_address_postalcode' => 'E14 4BB',
                        'billing_address_country' => 'United Kingdom',
                        'account_type' => 'Customer',
                        'industry' => 'Banking'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_rmaurice',
                        'first_name' => 'Robert',
                        'last_name' => 'Maurice',
                        'email1' => 'rmaurice@example.com',
                        'title' => 'Chairman and Chief Executive'
                    ),
                    array(
                        'id' => 'seed_rdiamond',
                        'first_name' => 'Robert',
                        'last_name' => 'Diamond',
                        'email1' => 'rdiamond@example.com',
                        'title' => 'President of Barclays'
                    )

                ),
                'Stream' => array(
                    array(
                        'activity_data' => array(
                            'value' => '@[Contacts:seed_rmaurice] downloaded a white paper titled "Our Cloud". @[Opportunities:seed_barclays_op1] @[Opportunities:seed_barclays_op2] '
                        ),
                        'target_module' => 'Accounts',
                        'target_id' => 'seed_barclays',
                        'activity_type' => 'posted',
                        'id' => 'seed_barclays_str1'
                    )
                ),
                'Opportunities' => array(
                    array(
                        'id' => 'seed_barclays_op1',
                        'name' => '800 Liceneses',
                        'amount' => '80000',
                        'date_closed' => 20,
                        'sales_stage' => 'Qualification',
                        'probability' => 30
                    ),
                    array(
                        'id' => 'seed_barclays_op2',
                        'name' => 'On Site Training',
                        'amount' => '20000',
                        'sales_stage' => 'Qualification',
                        'probability' => 30,
                        'date_closed' => 20
                    )
                ),

                'Meetings' => array(


                    array(
                        'id' => 'seed_meeting3',
                        'name' => 'In Person Meeting',
                        'type' => 'Meeting'
                    )


                ),

                'Calls' => array(
                    array(
                        'id' => 'seed_meeting1',
                        'name' => 'Initial Call',
                        'type' => 'Call'
                    ),

                    array(
                        'id' => 'seed_meeting2',
                        'name' => 'Followup Call',
                        'type' => 'Call'
                    ),
                    array(
                        'id' => 'seed_meeting4',
                        'name' => 'Meeting Recap',
                        'type' => 'Call'
                    )

                )
            ),

            array(
                'user_id' => 'seed_sarah_id',
                'se_id' => 'rmt-0cf6224d795dcd82bbc6e719d2a4f595',
                'Accounts' => array(
                    array(
                        'id' => 'seed_rbs',
                        'name' => 'Royal Bank of Scotland',
                        'phone_office' => ' +44 (0) 20 7623 2323',
                        'website' => 'www.rbs.com',
                        'billing_address_street' => '36 St Andrew Square',
                        'billing_address_city' => 'Edinburgh',
                        'billing_address_state' => 'Scotland',
                        'billing_address_postalcode' => 'EH2 2YB',
                        'billing_address_country' => 'United Kingdom',
                        'account_type' => 'Customer',
                        'industry' => 'Banking'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_shester',
                        'first_name' => 'Stephen',
                        'last_name' => 'Hester',
                        'phone_office' => ' +44 (0) 20 7623 2323',
                        'email1' => 'shester@example.com',
                        'title' => 'Group Chief Executive'
                    ),
                    array(
                        'id' => 'seed_csullivan',
                        'first_name' => 'Chris',
                        'last_name' => 'Sullivan',
                        'phone_office' => ' +44 (0) 20 7623 2323',
                        'email1' => 'csullivan@example.com',
                        'title' => 'Chief Executive, UK Corp'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_rbs_op1',
                        'name' => '100 Liceneses',
                        'amount' => '100000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100,
                        'date_closed' => 20
                    ),
                    array(
                        'id' => 'seed_rbs_op2',
                        'name' => 'On Site Training',
                        'amount' => '20000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100,
                        'date_closed' => 20
                    ),

                    array(
                        'id' => 'seed_rbs_op3',
                        'name' => 'Advanced Analytics Engine',
                        'amount' => '50000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100,
                        'date_closed' => 20
                    )
                ),


                'Meetings' => array(
                    array(


                        array(
                            'id' => 'seed_meeting_rbs3',
                            'name' => 'In Person Meeting',
                            'type' => 'Meeting'
                        ),



                        array(
                            'id' => 'seed_meeting_rbs5',
                            'name' => 'Demo Analytics Engine',
                            'type' => 'Meeting'
                        ),
                        array(
                            'id' => 'seed_meeting_rbs6',
                            'name' => 'Demo Basic Functionality',
                            'type' => 'Meeting'
                        )

                    ),

                    'Calls' => array(
                        'id' => 'seed_meeting_rbs1',
                        'name' => 'Initial Call',
                        'type' => 'Call'
                    ),

                    array(
                        'id' => 'seed_meeting_rbs2',
                        'name' => 'Followup Call',
                        'type' => 'Call'
                    ),
                    array(
                        'id' => 'seed_meeting_rbs4',
                        'name' => 'Meeting Recap',
                        'type' => 'Call'
                    ),
                    array(
                        'id' => 'seed_meeting_rbs8',
                        'name' => 'Call to Discuss Analytic Needs',
                        'type' => 'Call'
                    )

                )



            ),

            array(
                'user_id' => 'seed_sarah_id',
                'se_id' => 'seed_max_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_sugarcrm',
                        'name' => 'SugarCRM',
                        'phone_office' => '1-408-454-6900',
                        'website' => 'www.sugarcrm.com',
                        'billing_address_street' => '10050 North Wolfe Road, SW2-130',
                        'billing_address_city' => 'Cupertino',
                        'billing_address_state' => 'CA',
                        'billing_address_postalcode' => '95014',
                        'billing_address_country' => 'USA',
                        'account_type' => 'Customer',
                        'industry' => 'Technology'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_laugustin',
                        'first_name' => 'Larry',
                        'last_name' => 'Augustin',
                        'email1' => 'laugustin@example.com',
                        'linkedin' => 'larryaugustin',
                        'twitter' => 'lmaugustin',
                        'title' => 'CEO',
                        'picture' => 'http://www.sugarcrm.com/crm/images/management/Larry_Augustin.jpg'
                    ),
                    array(
                        'id' => 'seed_coram',
                        'first_name' => 'Clint',
                        'last_name' => 'Oram',
                        'linkedin' => 'clintoram',
                        'twitter' => 'sugarclint',
                        'email1' => 'coram@example.com',
                        'title' => 'CTO',
                        'picture' => 'http://www.sugarcrm.com/crm/images/management/Clint_Oram.jpg'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_cubes',
                        'name' => '50 Liceneses',
                        'amount' => '50000',
                        'sales_stage' => 'Closed Lost',
                        'probability' => 0
                    ),
                    array(
                        'id' => 'seed_servers',
                        'name' => '150 Liceneses',
                        'amount' => '150000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100
                    )
                )



            ),
            array(
                'user_id' => 'seed_sarah_id',
                'se_id' => 'seed_sally_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_google',
                        'name' => 'Google',
                        'phone_office' => '1-650-253-0000',
                        'website' => 'www.google.com',
                        'billing_address_street' => '1600 Amphitheatre Parkway',
                        'billing_address_city' => 'Mountain View',
                        'billing_address_state' => 'CA',
                        'billing_address_postalcode' => '94043',
                        'billing_address_country' => 'USA',
                        'account_type' => 'Customer',
                        'industry' => 'Technology'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_sbrin',
                        'first_name' => 'Sergey',
                        'last_name' => 'Brin',
                        'email1' => 'sbrin@example.com',
                        'linkedin' => 'sergey-brin',
                        'title' => 'Co-Founder',
                        'picture' => 'http://lh5.ggpht.com/_7ZYqYi4xigk/SaXJbI77n-I/AAAAAAAAEeA/DeV-BvKsa0c/s288/sergey_brin.jpg'
                    ),
                    array(
                        'id' => 'seed_lpage',
                        'first_name' => 'Larry',
                        'last_name' => 'Page',
                        'linkedin' => 'tlytle',
                        'email1' => 'lpage@example.com',
                        'title' => 'CEO',
                        'picture' => 'http://lh5.ggpht.com/_7ZYqYi4xigk/SaXJcOm_aLI/AAAAAAAAEdY/uR-9UDfKqKU/s288/larry_page.jpg'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_internets2',
                        'name' => '5 Licensed User Trial',
                        'amount' => '5000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100
                    ),
                    array(
                        'id' => 'seed_internets',
                        'name' => '100 Licensed Users',
                        'amount' => '100000',
                        'sales_stage' => 'Qualification',
                        'probability' => 60
                    ),
                    array(
                        'id' => 'seed_bots',
                        'name' => 'Training',
                        'amount' => '5000',
                        'sales_stage' => 'Qualification',
                        'probability' => 60
                    )
                )

            ),
            array(
                'user_id' => 'seed_jim_id',
                'se_id' => 'seed_sally_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_apple',
                        'name' => 'Apple',
                        'phone_office' => '1-408-996-1010',
                        'website' => 'www.apple.com',
                        'billing_address_street' => '1 Infinite Loop',
                        'billing_address_city' => 'Cupertino',
                        'billing_address_state' => 'CA',
                        'billing_address_postalcode' => '95014',
                        'billing_address_country' => 'USA',
                        'account_type' => 'Customer',
                        'industry' => 'Technology'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_tcook',
                        'first_name' => 'Tim',
                        'last_name' => 'Cook',
                        'email1' => 'tcook@example.com',
                        'title' => 'Chief Executive Officer',
                        'picture' => 'http://images.apple.com/pr/bios/images/cook_hero20110204.png'
                    ),
                    array(
                        'id' => 'seed_po',
                        'first_name' => 'Peter',
                        'last_name' => 'Oppenheimer',
                        'email1' => 'poppenheimer@example.com',
                        'title' => 'CFO',
                        'picture' => 'http://images.apple.com/pr/bios/images/oppenheimer_hero20110204.png'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_floppy',
                        'name' => '50 User Licenses',
                        'amount' => '50000',
                        'sales_stage' => 'Closed Lost',
                        'probability' => 0
                    ),
                    array(
                        'id' => 'seed_iCats',
                        'name' => '5 User Trial',
                        'amount' => '5000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100
                    )
                )

            ),

            array(
                'user_id' => 'seed_chris_id',
                'se_id' => 'rmt-0cf6224d795dcd82bbc6e719d2a4f595',
                'Accounts' => array(
                    array(
                        'id' => 'seed_alcatel_lucent',
                        'name' => 'Alcatel-Lucent',
                        'phone_office' => '33 (0)1 40 76 10 10',
                        'website' => 'www.alcatel-lucent.com',
                        'billing_address_street' => '3 av. Octave Gréard',
                        'billing_address_city' => 'Paris',
                        'billing_address_state' => 'Île-de-France',
                        'billing_address_postalcode' => '75007',
                        'billing_address_country' => 'France',
                        'account_type' => 'Customer',
                        'industry' => 'Communications'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_alcatel_lucent_ct1',
                        'first_name' => 'Ben',
                        'last_name' => 'Verwaayen',
                        'email1' => 'bverwaayen@example.com',
                        'title' => 'CEO',
                        'picture' => 'http://www.alcatel-lucent.com/wps/PA_1_A_1OB/images/Photos/Portrait_Photos/verwaayen_70x92.jpg'
                    ),
                    array(
                        'id' => 'seed_alcatel_lucent_ct2',
                        'first_name' => 'Michel',
                        'last_name' => 'Emelianoff',
                        'email1' => 'memlianoff@example.com',
                        'title' => 'Executive Vice President',
                        'picture' => 'http://www.alcatel-lucent.com/wps/PA_1_A_1OB/images/Photos/Portrait_Photos/Michel_Emelianoff_152x200.jpg'
                    )
                ),
                'Opportunities' => array(
                    array(
                        'id' => 'seed_alcatel_lucent_op1',
                        'name' => '1000 User Licenses',
                        'amount' => '100000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100
                    ),
                    array(
                        'id' => 'seed_alcatel_lucent_op2',
                        'name' => '50 User Training',
                        'amount' => '20000',
                        'sales_stage' => 'Closed Lost',
                        'probability' => 0
                    )
                )
            ),

            array(
                'user_id' => 'seed_jim_id',
                'se_id' => $this->userId,
                'Accounts' => array(
                    array(
                        'id' => 'seed_mubadala',
                        'name' => 'Mubadala',
                        'phone_office' => '+971 2 413 0000',
                        'website' => 'www.mubadala.ae',
                        'billing_address_street' => 'PO Box 45005',
                        'billing_address_city' => 'Abu Dhabi',
                        'billing_address_state' => 'Abu Dhabi',
                        'billing_address_postalcode' => '45005',
                        'billing_address_country' => 'United Arab Emirates',
                        'account_type' => 'Customer',
                        'industry' => 'Government'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_mubadala_ct1',
                        'first_name' => 'Ahmed Yahia',
                        'last_name' => 'Al Idrissi',
                        'email1' => 'aidrissi@example.com',
                        'title' => 'Executive Director',
                        'picture' => 'http://mubadala.ae/images/sized/images/upload_images/ahmed-yahia-al-idrissi-150x150.jpg'
                    ),
                    array(
                        'id' => 'seed_mubadala_ct2',
                        'first_name' => 'Ajit',
                        'last_name' => 'Naidu',
                        'email1' => 'anaidu@example.com',
                        'title' => 'Chief Information Officer',
                        'picture' => 'http://mubadala.ae/images/sized/images/upload_images/ajit-naidu-150x150.jpg'
                    )
                ),
                'Opportunities' => array(
                    array(
                        'id' => 'seed_mubadala_op1',
                        'name' => '500 User Licenses',
                        'amount' => '50000',
                        'sales_stage' => 'Closed Lost',
                        'probability' => "0"
                    ),
                    array(
                        'id' => 'seed_mubadala_op2',
                        'name' => '50 User Trial',
                        'amount' => '3000',
                        'sales_stage' => 'Qualification',
                        'probability' => 40
                    )
                )
            ),


            array(
                'user_id' => 'seed_chris_id',
                'se_id' => 'seed_max_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_vw',
                        'name' => 'Volkswagen',
                        'phone_office' => '+49 5361 90',
                        'website' => 'www.vw.com',
                        'billing_address_street' => 'Berliner Ring 2',
                        'billing_address_city' => 'Wolfsburg',
                        'billing_address_state' => '',
                        'billing_address_postalcode' => '38436',
                        'billing_address_country' => 'Germany',
                        'account_type' => 'Customer',
                        'industry' => 'Transportation'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_mwinterkorn',
                        'first_name' => 'Martin',
                        'last_name' => 'Winterkorn',
                        'email1' => 'mwinterkorn@example.com',
                        'twitter' => 'VW',
                        'title' => 'Chief Executive Officer',
                        'picture' => 'http://www.autoevolution.com/images/news/martin-winterkorn-to-head-vw-until-2016-27291_1.jpeg'
                    ),
                    array(
                        'id' => 'seed_hdp',
                        'first_name' => 'Hans',
                        'last_name' => 'Dieter Pötsch',
                        'email1' => 'hdp@example.com',
                        'title' => 'CFO',
                        'picture' => 'http://www.austriantimes.at/thumbnails/s/saczuk28_large.jpg'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_bugs',
                        'name' => '50 Licenses',
                        'amount' => '50000',
                        'sales_stage' => 'Closed Lost',
                        'probability' => 0
                    ),
                    array(
                        'id' => 'seed_wheels',
                        'name' => '100 Licenses',
                        'amount' => '100000',
                        'sales_stage' => 'Qualification',
                        'probability' => 40
                    )

                )
            ),
            array(
                'user_id' => 'seed_chris_id',
                'se_id' => 'seed_max_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_bp',
                        'name' => 'British Petroleum',
                        'phone_office' => '+44 (0)20 7496 4000',
                        'website' => 'www.bp.com',
                        'billing_address_street' => '1 St. James Sq.',
                        'billing_address_city' => 'London',
                        'billing_address_state' => '',
                        'billing_address_postalcode' => 'SW1Y 4PD',
                        'billing_address_country' => 'Britain',
                        'account_type' => 'Customer',
                        'industry' => 'Chemicals'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_rdudley',
                        'first_name' => 'Robert',
                        'last_name' => 'Dudley',
                        'email1' => 'rdudley@example.com',
                        'twitter' => 'BP_America',
                        'title' => 'Chief executive',
                        'picture' => 'http://www.bp.com/liveassets/bp_internet/globalbp/STAGING/global_assets/images/people/board_and_directors/bob_dudley_106x85.jpg'
                    ),
                    array(
                        'id' => 'seed_mdaly',
                        'first_name' => 'Mike',
                        'last_name' => 'Daly',
                        'email1' => 'mdaly@example.com',
                        'title' => 'Executive Vice President, Exploration',
                        'picture' => 'http://www.bp.com/liveassets/bp_internet/globalbp/STAGING/global_assets/images/people/board_and_directors/mike_daly_106x85.jpg'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_water',
                        'name' => '3000 Licenses',
                        'amount' => '300000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100
                    ),
                    array(
                        'id' => 'seed_sodas',
                        'name' => 'On Site Training',
                        'amount' => '20000',
                        'sales_stage' => 'Qualification',
                        'probability' => 80
                    )
                )

            ),
            array(
                'user_id' => 'seed_chris_id',
                'se_id' => 'seed_max_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_rds',
                        'name' => 'Royal Dutch Shell',
                        'phone_office' => '+31 70 377 9111',
                        'website' => 'www.shell.com',
                        'billing_address_street' => 'Carel van Bylandtlaan 30',
                        'billing_address_city' => 'Den Haag',
                        'billing_address_state' => '',
                        'billing_address_postalcode' => '2596',
                        'billing_address_country' => 'The Netherlands',
                        'account_type' => 'Customer',
                        'industry' => 'Chemicals'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_shenry',
                        'first_name' => 'Simon',
                        'twitter' => 'shell',
                        'last_name' => 'Henry',
                        'email1' => 'shenry@example.com',
                        'title' => 'Chief Financial Officer'
                    ),
                    array(
                        'id' => 'seed_pvoser',
                        'first_name' => 'Peter',
                        'last_name' => 'Voser',
                        'email1' => 'pvoser@example.com',
                        'title' => 'Chief Executive Officer '
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_mops',
                        'name' => '700 Licensed Users',
                        'amount' => '700000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100
                    ),
                    array(
                        'id' => 'seed_tshirts',
                        'name' => '200 T-Shirts',
                        'amount' => '1000',
                        'sales_stage' => 'Closed Lost',
                        'probability' => "0"
                    )

                )
            ),

            array(
                'user_id' => 'seed_will_id',
                'se_id' => 'seed_sally_id',
                'Accounts' => array(
                    array(
                        'id' => 'seed_walmart',
                        'name' => 'Wal-Mart',
                        'phone_office' => '1-800-925-6278',
                        'website' => 'www.walmart.com',
                        'billing_address_street' => '702 S.W. Eighth St.',
                        'billing_address_city' => 'Bentonville',
                        'billing_address_state' => 'Arkansas',
                        'billing_address_postalcode' => '72716',
                        'billing_address_country' => 'USA',
                        'account_type' => 'Customer',
                        'industry' => 'Retail'
                    )
                ),
                'Contacts' => array(
                    array(
                        'id' => 'seed_johnaden',
                        'first_name' => 'John',
                        'last_name' => 'Aden',
                        'email1' => 'jaden@example.com',
                        'title' => 'Executive Vice President, General Merchandise'
                    ),
                    array(
                        'id' => 'seed_willsimon',
                        'first_name' => 'William',
                        'last_name' => 'Simon',
                        'twitter' => 'Walmart',
                        'email1' => 'wsimon@example.com',
                        'title' => 'President and CEO'
                    )

                ),

                'Opportunities' => array(
                    array(
                        'id' => 'seed_wombats',
                        'name' => '100 Licenses',
                        'amount' => '100000',
                        'sales_stage' => 'Closed Won',
                        'probability' => 100
                    ),
                    array(
                        'id' => 'seed_zebras',
                        'name' => 'On Site Training',
                        'amount' => '20000',
                        'sales_stage' => 'Qualification',
                        'probability' => 50
                    )

                )
            )

        );
    }



    function install()
    {
        $GLOBALS['db']->query('TRUNCATE contacts');
        $GLOBALS['db']->query('TRUNCATE opportunities');
        $GLOBALS['db']->query('TRUNCATE meetings');
        $GLOBALS['db']->query('TRUNCATE accounts');
        $GLOBALS['db']->query('TRUNCATE activity_stream');
        $GLOBALS['db']->query('TRUNCATE calls');
        $GLOBALS['db']->query('DELETE FROM users WHERE id="rmt-0cf6224d795dcd82bbc6e719d2a4f595"');
        $GLOBALS['db']->query("INSERT INTO `users` VALUES ('rmt-0cf6224d795dcd82bbc6e719d2a4f595','lheynike@sugarcrm.com',NULL,0,NULL,'https://accounts.google.com/117553501220792602619',1,NULL,'Lorna','Heynike',0,1,0,NULL,'2012-10-17 00:41:36','2012-10-17 00:48:40',NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,NULL,NULL,'1','1',0,0,1,NULL,NULL,NULL,'',0,'en_us')");
        $GLOBALS['db']->query("INSERT INTO `users` set id='Web Campaign', first_name='Web' , last_name='Campaign', user_name='Web Campaign'");
        DBManager::setQueryLimit(0);
        $actual_user_id = $this->userId;

        foreach ($this->demodata as $data) {
            $user_id = $this->userId;
            if (!isset($GLOBALS['current_user']))
                $GLOBALS['current_user'] = new User();
            $GLOBALS['current_user']->retrieve($user_id);
            if (!empty($data['user_id'])) {
                $user_id = $data['user_id'];
            }

            $primary_account  = null;
            $primary_contacts = array();
            $primary_opps     = array();
            foreach ($data['Accounts'] as $account) {
                $primary_account = new Account();
                foreach ($account as $k => $v) {
                    $primary_account->$k = $v;
                }
                $primary_account->new_with_id      = true;
                $primary_account->team_set_id      = 1;
                $primary_account->team_id          = 1;
                $primary_account->assigned_user_id = $user_id;
                $primary_account->modified_user_id = $user_id;
                $primary_account->created_by       = $user_id;
                $primary_account->set_created_by   = false;
                $primary_account->save();
                foreach ($data['Contacts'] as $contact) {
                    $obj = new Contact();
                    foreach ($contact as $k => $v) {
                        $obj->$k = $v;
                    }
                    $obj->new_with_id                = true;
                    $obj->primary_address_city       = $primary_account->billing_address_city;
                    $obj->primary_address_state      = $primary_account->billing_address_state;
                    $obj->primary_address_postalcode = $primary_account->billing_address_postalcode;
                    $obj->primary_address_country    = $primary_account->billing_address_country;
                    $obj->primary_address_street     = $primary_account->billing_address_street;
                    $obj->assigned_user_id           = $user_id;
                    $obj->modified_user_id           = $user_id;
                    $obj->created_by                 = $user_id;
                    $obj->account_id                 = $primary_account->id;
                    $obj->phone_mobile               = $primary_account->phone_office;
                    $obj->team_set_id                = 1;
                    $obj->team_id                    = 1;
                    $obj->set_created_by             = false;
                    $obj->save();
                    $primary_account->contacts->add($obj);
                    $primary_contacts[] = $obj;
                }

                foreach ($data['Opportunities'] as $opp) {
                    $obj = new Opportunity();
                    foreach ($opp as $k => $v) {
                        $obj->$k = $v;
                    }
                    $obj->name             = $primary_account->name . ' - ' . $obj->name;
                    $obj->new_with_id      = true;
                    $obj->assigned_user_id = $user_id;
                    $obj->modified_user_id = $user_id;
                    $obj->created_by       = $user_id;
                    $obj->account_id       = $primary_account->id;
                    $obj->contact_id       = $primary_contacts[0]->id;
                    $obj->date_closed      = $this->randomDate();
                    $obj->team_set_id      = 1;
                    $obj->team_id          = 1;
                    $obj->set_created_by   = false;
                    $obj->save();
                    $primary_account->opportunities->add($obj);
                    $primary_opps[] = $obj;
                }
                if (isset($data['Meetings'])) {
                    foreach ($data['Meetings'] as $meet) {
                        $obj = new Meeting();
                        foreach ($meet as $k => $v) {
                            $obj->$k = $v;
                        }
                        $obj->new_with_id      = true;
                        $obj->assigned_user_id = $user_id;
                        $obj->modified_user_id = $user_id;
                        $obj->created_by       = $user_id;
                        $obj->parent_id        = $primary_account->id;
                        $obj->parent_type      = 'Accounts';
                        $obj->team_set_id      = 1;
                        $obj->team_id          = 1;
                        $obj->set_created_by   = false;
                        $obj->save();
                        $primary_account->load_relationship('meetings');
                        $primary_account->meetings->add($obj);
                        $obj->load_relationship('contacts');
                        foreach ($primary_contacts as $primary_contact) {
                            $obj->contacts->add($primary_contact);
                        }
                        if (!empty($data['se_id'])) {
                            $se = new User();
                            $se->retrieve($data['se_id']);
                            $obj->load_relationship('users');
                            $obj->users->add($se);
                        }



                    }


                }

                if (isset($data['Calls'])) {
                    foreach ($data['Calls'] as $call) {
                        $obj = new Call();
                        foreach ($call as $k => $v) {
                            $obj->$k = $v;
                        }
                        $obj->new_with_id      = true;
                        $obj->assigned_user_id = $user_id;
                        $obj->modified_user_id = $user_id;
                        $obj->created_by       = $user_id;
                        $obj->parent_id        = $primary_account->id;
                        $obj->parent_type      = 'Accounts';
                        $obj->team_set_id      = 1;
                        $obj->team_id          = 1;
                        $obj->set_created_by   = false;
                        $obj->save();
                        $primary_account->load_relationship('calls');
                        $primary_account->calls->add($obj);
                        $obj->load_relationship('contacts');
                        foreach ($primary_contacts as $primary_contact) {
                            $obj->contacts->add($primary_contact);
                        }
                        if (!empty($data['se_id'])) {
                            $se = new User();
                            $se->retrieve($data['se_id']);
                            $obj->load_relationship('users');
                            $obj->users->add($se);
                        }



                    }


                }


                if (isset($data['Stream'])) {
                    foreach ($data['Stream'] as $stream) {
                        $obj = new ActivityStream();
                        foreach ($stream as $k => $v) {
                            $obj->$k = $v;
                        }
                        if (isset($obj->activity_data))
                            $obj->activity_data = json_encode($obj->activity_data);
                        $obj->new_with_id      = true;
                        $obj->assigned_user_id = $user_id;
                        $obj->modified_user_id = $user_id;
                        $obj->created_by       = $user_id;
                        $obj->team_set_id      = 1;
                        $obj->team_id          = 1;
                        $obj->created_by       = 'Web Campaign';
                        $obj->set_created_by   = false;
                        $obj->date_created     = date('Y-m-d H:i:s', time() + 60);
                        $obj->save();

                    }
                }

            }
        }


        if (!isset($GLOBALS['current_user']))
            $GLOBALS['current_user'] = new User();
        $GLOBALS['current_user']->retrieve($actual_user_id);

        // Insert Lorna's record + make Max, Sally and Lorna SEs.
        $GLOBALS['db']->query("UPDATE users SET title='Sales Engineer' WHERE id IN ('seed_max_id', 'seed_sally_id', 'rmt-0cf6224d795dcd82bbc6e719d2a4f595')");

        $GLOBALS['db']->query("DELETE FROM quotas WHERE user_id='" . $actual_user_id . "'");

        // Set quota for current user to $200k.
        $timeperiod_id                    = TimePeriod::getCurrentId();
        $quota_bean                       = BeanFactory::getBean('Quotas');
        $quota_bean->user_id              = $actual_user_id;
        $quota_bean->amount               = 200000;
        $quota_bean->amount_base_currency = 200000;
        $quota_bean->committed            = 1;
        $quota_bean->quota_type           = 'Direct';
        $quota_bean->timeperiod_id        = $timeperiod_id;
        $quota_bean->save();
    }

    public function uninstall()
    {
        $GLOBALS['db']->query('UPDATE contacts SET deleted=1 WHERE id LIKE \'seed%\'');
        $GLOBALS['db']->query('UPDATE accounts SET deleted=1 WHERE id LIKE \'seed%\'');
        $GLOBALS['db']->query('UPDATE opportunities SET deleted=1 WHERE id LIKE \'seed%\'');
        $GLOBALS['db']->query('UPDATE sugarfeed SET deleted=1 WHERE id LIKE \'seed%\' OR related_id LIKE \'seed%\' or child_id LIKE \'seed%\'');

    }

    public function reinstall()
    {
        $GLOBALS['db']->query('UPDATE contacts SET deleted=0 WHERE id LIKE \'seed%\'');
        $GLOBALS['db']->query('UPDATE accounts SET deleted=0 WHERE id LIKE \'seed%\'');
        $GLOBALS['db']->query('UPDATE opportunities SET deleted=0 WHERE id LIKE \'seed%\'');
        $GLOBALS['db']->query('UPDATE sugarfeed SET deleted=0 WHERE id LIKE \'seed%\' OR related_id LIKE \'seed%\' or child_id LIKE \'seed%\'');

    }


    protected function randomDate()
    {
        $oneday = 24 * 3600;
        $days   = mt_rand(0, 60);
        $before = mt_rand(0, 1);
        if ($before == 1) {
        }
        $time = ($before == 1) ? time() - $oneday * $days : time() + $oneday * $days;
        return date('Y-m-d', $time);

    }

    protected function getOffsetDate($offset)
    {
        $oneday = 24 * 3600;
        $time   = time() + $oneday * $offset;
        return date('Y-m-d', $time);


    }


}

$demo = new Phase3Demo($_SESSION['authenticated_user_id']);
$demo->install();
