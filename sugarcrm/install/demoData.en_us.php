<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

// $Id: demoData.en_us.php 36879 2008-06-19 20:12:26Z Collin Lee $

//users demodata
//VP
global $sugar_demodata;
$sugar_demodata['users'][0] = array(
  'id' => 'seed_jim_id',	
  'last_name' => 'Brennan',
  'first_name' => 'Jim',
  'user_name' => 'jim',
  'title'	=> 'VP Sales',
  'is_admin' => false,
  'reports_to' => null,
  'reports_to_name' => null,
  'email' => 'jim@example.com'
);

//west team
$sugar_demodata['users'][] = array(
  'id' => 'seed_sarah_id',	
  'last_name' => 'Smith',
  'first_name' => 'Sarah',
  'user_name' => 'sarah',
  'title'	=> 'Sales Manager West',
  'is_admin' => false,
  'reports_to' => 'seed_jim_id',
  'reports_to_name' => 'Brennan, Jim',
  'email' => 'sarah@example.com'
);

$sugar_demodata['users'][] = array(
  'id' => 'seed_sally_id',	
  'last_name' => 'Bronsen',
  'first_name' => 'Sally',
  'user_name' => 'sally',
  'title'	=> 'Senior Account Rep',
  'is_admin' => false,
  'reports_to' => 'seed_sarah_id',
  'reports_to_name' => 'Smith, Sarah',
  'email' => 'sally@example.com'
);

$sugar_demodata['users'][] = array(
  'id' => 'seed_max_id',	
  'last_name' => 'Jensen',
  'first_name' => 'Max',
  'user_name' => 'max',
  'title'	=> 'Account Rep',
  'is_admin' => false,
  'reports_to' => 'seed_sarah_id',
  'reports_to_name' => 'Smith, Sarah',
  'email' => 'tom@example.com'
);

//east team
$sugar_demodata['users'][] = array(
  'id' => 'seed_will_id',	
  'last_name' => 'Westin',
  'first_name' => 'Will',
  'user_name' => 'will',
  'title'	=> 'Sales Manager East',
  'is_admin' => false,
  'reports_to' => 'seed_jim_id',
  'reports_to_name' => 'Brennan, Jim',
  'email' => 'will@example.com'
);

$sugar_demodata['users'][] = array(
  'id' => 'seed_chris_id',	
  'last_name' => 'Olliver',
  'first_name' => 'Chris',
  'user_name' => 'chris',
  'title'	=> 'Senior Account Rep',
  'is_admin' => false,
  'reports_to' => 'seed_will_id',
  'reports_to_name' => 'Westin, Will',
  'email' => 'chris@example.com'
);

//teams demodata
$sugar_demodata['teams'][] = array(
  'name' => 'East',	
  'description' => 'This is the team for the East',
  'team_id' => 'East',
);

$sugar_demodata['teams'][] = array(
  'name' => 'West',	
  'description' => 'This is the team for the West',
  'team_id' => 'West',
);

//contacts accounts
$sugar_demodata['last_name_array'] = array(
    'Posey',
    'Beringer',
    'Lemasters',
    'Abney',
    'Guilbault',
    'Ellenberger',
    'Faul',
    'Tooker',
    'Gerrish',
    'Hilton',
    'Mccasland',
    'Mickle',
    'Emmett',
    'Vanhoose',
    'Bruno',
    'Kelton',
    'Mcnitt',
    'Trautman',
    'Schramm',
    'Mccrady',
    'Ormond',
    'Hoch',
    'Knupp',
    'Rayes',
    'Tennison',
    'Tung',
    'Trisler',
    'Limones',
    'Timmons',
    'Moreno',
    'Breit',
    'Muhammad',
    'Gaskell',
    'Lambros',
    'March',
    'Slattery',
    'Morita',
    'Mccoin',
    'Sebastian',
    'Sloan',
    'Rodes',
    'Cabot',
    'Wydra',
    'Seabury',
    'Channell',
    'Jen',
    'File',
    'Waddell',
    'Doshier',
    'Kohls',
    'Kinchen',
    'Parkinson',
    'Gould',
    'Sosa',
    'Vogan',
    'Roig',
    'Swarthout',
    'Alameda',
    'Dibiase',
    'Wedeking',
    'Gildea',
    'Elmore',
    'Barish',
    'Dulmage',
    'Kuykendall',
    'Saez',
    'Piller',
    'Mathews',
    'Haun',
    'Krzeminski',
    'Eller',
    'Halvorsen',
    'Stultz',
    'Schuessler',
    'Dagley',
    'Person',
    'Hallberg',
    'Roland',
    'Delamora',
    'Havens',
    'Cutshaw',
    'Parkerson',
    'Brann',
    'Harvell',
    'Schurg',
    'Jimerson',
    'Sones',
    'Kostka',
    'Treaster',
    'Puryear',
    'Bellini',
    'Terrio',
    'Longoria',
    'Canales',
    'Cassel',
    'Janda',
    'Bourgault',
    'Motz',
    'Homan',
    'Ruple',
    'Docherty',
    'Lui',
    'Swayne',
    'Zirbel',
    'Pelley',
    'Caffee',
    'Barfoot',
    'Peskin',
    'Shanley',
    'Josey',
    'Cooke',
    'Whitmire',
    'Coffelt',
    'Xavier',
    'Ludlow',
    'Guizar',
    'Marcoux',
    'Sroka',
    'Richard',
    'Rolon',
    'Cesar',
    'Liao',
    'Bortz',
    'Pierson',
    'Gorsuch',
    'Cotter',
    'Esparza',
    'Ahmad',
    'Schutz',
    'Wen',
    'Mcghee',
    'Perrotti',
    'Wurster',
    'Carter',
    'Myron',
    'Lessley',
    'Allen',
    'Fawcett',
    'Ehlers',
    'Valentino',
    'Lema',
    'Baumann',
    'Agin',
    'Lucarelli',
    'Mickens',
    'Filler',
    'Schneiderman',
    'Orenstein',
    'Mcniel',
    'Theus',
    'Anaya',
    'Magallanes',
    'Verrier',
    'Wohlwend',
    'Bittle',
    'Brenneman',
    'Molder',
    'Griffen',
    'Parra',
    'Muszynski',
    'Fragoso',
    'Sharp',
    'Santiago',
    'Crosier',
    'Calder',
    'Edberg',
    'Wages',
    'Bradbury',
    'Maroney',
    'Siguenza',
    'Abram',
    'Hammaker',
    'Goldberger',
    'Ahn',
    'Mohl',
    'Bandy',
    'Wooster',
    'Principe',
    'Prosperie',
    'Ryckman',
    'Jeffreys',
    'Florey',
    'Auclair',
    'Krauth',
    'Livengood',
    'Rux',
    'Lechner',
    'Forsyth',
    'Roberds',
    'Harig',
    'Goffney',
    'Seelye',
    'Libby',
    'Mccraw',
    'Torbett',
    'Wahlen',
    'Tien',
    'Greenan',
    'Ogan',
    'Katz',
);

$sugar_demodata['first_name_array'] = array(
    'Jolene',
    'Ivonne',
    'Hayden',
    'Mahalia',
    'Alisa',
    'Brittanie',
    'Conception',
    'Raylene',
    'Piedad',
    'Jenette',
    'Rolland',
    'Enid',
    'Amado',
    'Juliette',
    'Rich',
    'Debera',
    'Ramona',
    'Chere',
    'Corazon',
    'Rosanne',
    'Shelli',
    'Angela',
    'Stephine',
    'Agnus',
    'Pedro',
    'Rupert',
    'Darnell',
    'Dagmar',
    'Isa',
    'Velda',
    'Annamarie',
    'Shanita',
    'Jude',
    'Elyse',
    'Adriene',
    'Hannelore',
    'Eliana',
    'Yuette',
    'Malinda',
    'Cindi',
    'Claude',
    'Tyesha',
    'Eleni',
    'Twila',
    'Darell',
    'Myrtice',
    'Aliza',
    'Johana',
    'Dominica',
    'Roderick',
    'Herlinda',
    'Parthenia',
    'Manda',
    'Beryl',
    'Fran',
    'Sheila',
    'Lizabeth',
    'Ingeborg',
    'Alden',
    'Maryanne',
    'Andra',
    'Marcelina',
    'Demetra',
    'Dixie',
    'Candelaria',
    'Emerita',
    'Eusebia',
    'Heather',
    'Douglass',
    'Adrien',
    'Jutta',
    'Hugh',
    'Isaura',
    'Joe',
    'Therese',
    'Shani',
    'Ellyn',
    'Muriel',
    'Bryce',
    'Liza',
    'Tiana',
    'Elenore',
    'Wonda',
    'Ollie',
    'Cleora',
    'Ronnie',
    'Krystal',
    'Liana',
    'Ema',
    'Alene',
    'Antonia',
    'Evan',
    'Luba',
    'Carolyne',
    'Glennie',
    'Taunya',
    'Marilee',
    'Bethann',
    'Van',
    'Candis',
    'Lekisha',
    'Christena',
    'Yolonda',
    'Kendal',
    'Carey',
    'Guadalupe',
    'Shari',
    'Afton',
    'Emmaline',
    'Gala',
    'Dewey',
    'Burt',
    'Gladys',
    'Kristi',
    'Marlys',
    'Rocco',
    'Kaycee',
    'Aurelio',
    'Nadine',
    'Marc',
    'Contessa',
    'Cliff',
    'Robin',
    'Stanley',
    'Adelaida',
    'Devora',
    'Ida',
    'Yee',
    'Angella',
    'Hal',
    'Arnoldo',
    'Exie',
    'Chanda',
    'Florene',
    'Manie',
    'Milly',
    'Nella',
    'Dwana',
    'Millie',
    'Cheryle',
    'Caryn',
    'Garret',
    'Tatiana',
    'Britney',
    'Ute',
    'Janie',
    'Remona',
    'Tyesha',
    'Sondra',
    'Ronda',
    'Molly',
    'Eric',
    'Karlene',
    'Sanda',
    'Ofelia',
    'Carline',
    'Les',
    'Shiloh',
    'Marian',
    'Angla',
    'Florencia',
    'Kelvin',
    'Jaime',
    'Edith',
    'Billie',
    'Nathan',
    'Coretta',
    'Temika',
    'Carlos',
    'Simone',
    'Joana',
    'Saul',
    'Quintin',
    'Tamica',
    'Ronna',
    'Lowell',
    'Darren',
    'Aleen',
    'Maura',
    'Otis',
    'Stanley',
    'Tyrell',
    'Bonita',
    'Floy',
    'Mandy',
    'Darin',
    'Elise',
    'Lanny',
    'Tosha',
    'Ernestina',
    'Kimbra',
    'Alona',
    'Queenie',
    'Clarine',
    'Venetta',
    'Roman',
    'Ona',
    'Matilda',
    'Vennie',
    'Doretha',
);

$sugar_demodata['company_name_array'] = array(
	"24/7 Couriers",
	"Sandeon Consolidation Corp",
	"360 Vacations",
	"Powell Funding",
	"5D Investments",
	"Aim Capital Inc",
	"AB Drivers Limited",
	"Hammer Group Inc",
	"Arts & Crafts Inc",
	"A.D. Importing Company Inc",
	"A.G. Parr PLC",
	"AtoZ Co Ltd",
	"Avery Software Co",
	"Airline Maintenance Co",
	"Air Safety Inc",
	"Anytime Air Support Inc",
	"Coolview Net Ltd",
	"Rubble Group Inc",
	"B.C. Investing International",
	"Sunyvale Reporting Ltd",
	"B.H. Edwards Inc",
	"Insight Marketing Inc",
	"Bay Funding Co",
	"Nelson Inc",
	"Calm Sailing Inc",
	"Chandler Logistics Inc",
	"Cloud Cover Trust",
	"Complete Holding",
	"Constrata Trust LLC",
	"Cumberland Trails Inc",
	"DD Furniture Inc",
	"Dirt Mining Ltd",
	"Hollywood Diner Ltd",
	"Davenport Investing",
	"Draft Diversified Energy Inc",
	"First National S/B",
	"Gifted Holdings AG",
	"Green Tractor Group Limited",
	"Grow-Fast Inc",
	"Income Free Investing LP",
	"International Art Inc",
	"Ink Conglomerate Inc",
	"J.K.M. Corp (HA)",
	"JAB Funds Ltd.",
	"JBC Banking Inc",
	"JJ Resources Inc",
	"Jungle Systems Inc",
	"Kringle Bell Inc".
	"K.A. Tower & Co",
	"Kaos Trading Ltd",
	"Kings Royalty Trust",
	"King Software Inc",
	"Smith & Sons",
	"Lexington Shores Corp",
	"Max Holdings Ltd",
	"Mississippi Bank Group",
	"MMM Mortuary Corp",
	"MTM Investment Bank F S B",
	"Nimble Technologies Inc",
	"NW Bridge Construction",
	"NW Capital Corp",
	"OTC Holdings",
	"Overhead & Underfoot Ltd.",
	"P Piper & Sons",
	"Powder Suppliers",
	"Pullman Cart Company",
	"Q.R.&E. Corp",
	"Q3 ARVRO III PR",
	"Rhyme & Reason Inc",
	"Riviera Hotels",
	"RR. Talker Co",
	"RRR Advertising Inc.",
	"S Cane Sweeteners Ltd",
	"Sea Region Inc",
	"Slender Broadband Inc",
	"Smallville Resources Inc",
	"South Sea Plumbing Products",
	"Southern Realty",
	"Spend Thrift Inc",
	"Spindle Broadcast Corp.",
	"Start Over Trust",
	"Super Star Holdings Inc",
	"SuperG Tech",
	"T-Cat Media Group Inc",
	"TJ O'Rourke Inc",
	"Tortoise Corp",
	"Tracker Com LP",
	"Trait Institute Inc",
	"Tri-State Medical Corp",
	"T-Squared Techs",
	"EEE Endowments LTD",
	"Underwater Mining Inc.",
	"Union Bank",
	"Waverly Trading House",
	"White Cross Co",
	"X-Sell Holdings",
	"XY&Z Funding Inc",
);

$sugar_demodata['street_address_array'] = array(
	 "123 Anywhere Street",
	 "345 Sugar Blvd.",
	 "1715 Scott Dr",
	 "999 Baker Way",
	 "67321 West Siam St.",
	 "48920 San Carlos Ave",
	 "777 West Filmore Ln",
	 "9 IBM Path",
	 "111 Silicon Valley Road",
	 "321 University Ave.",
 );

$sugar_demodata['city_array'] = array(
	 "San Jose",
	 "San Francisco",
	 "Sunnyvale",
	 "San Mateo",
	 "Cupertino",
	 "Los Angeles",
	 "Santa Monica",
	 "Denver",
	 "St. Petersburg",
	 "Santa Fe",
	 "Ohio",
	 "Salt Lake City",
	 "Persistance",
	 "Alabama",
	 "Kansas City",
);

//cases demodata
$sugar_demodata['case_seed_names'] = array(
	'Having trouble adding new items',
	'System not responding',
	'Need assistance with large customization',
	'Need to purchase additional licenses',
	'Warning message when using the wrong browser'
);

//bugs demodata
$sugar_demodata['bug_seed_names'] = array(
	'Error occurs while running count query',
	'Warning is displayed in file after exporting',
	'Fatal error during installation',
	'Broken image appears in home page',
	'Syntax error appears when running old reports'
);

$sugar_demodata['note_seed_names_and_Descriptions'] = array(
	array('More Account Information','This account could turn into a 3,000-user opportunity.'),
	array('Call Information','The initial sales call went well. Will follow up with contact in 3 days.'),
	array('Met at SugarCon 2010','Contact expressed interest in becoming a partner.'),
	array('Attended CRM Exceleration Event in Asia','Contact\'s event registration fee was comped.')
);

$sugar_demodata['call_seed_data_names'] = array(
	'Get more information on the proposed deal',
	'Left a message',
	'Bad time, will call back',
	'Discuss review process'
);

//titles
$sugar_demodata['titles'] = array(
	"President",
	"VP Operations",
	"VP Sales",
	"Director Operations",
	"Director Sales",
	"Mgr Operations",
	"IT Developer",
	"Senior Product Manager"
);

//tasks
$sugar_demodata['task_seed_data_names'] = array(
	'Assemble catalogs', 
	'Make travel arrangements', 
	'Send a letter', 
	'Send contract', 
	'Send fax', 
	'Send a follow-up letter', 
	'Send literature', 
	'Send proposal', 
	'Send quote', 
	'Call to schedule meeting', 
	'Setup evaluation', 
	'Get demo feedback', 
	'Arrange introduction', 
	'Escalate support request', 
	'Close out support request', 
	'Ship product', 
	'Arrange reference call', 
	'Schedule training', 
	'Send local user group information', 
	'Add to mailing list',
);

//meetings
$sugar_demodata['meeting_seed_data_names'] = array(
	'Follow-up on proposal', 
	'Initial discussion', 
	'Review needs', 
	'Discuss pricing', 
	'Demo', 
	'Introduce all players',
);
$sugar_demodata['meeting_seed_data_descriptions'] = 'Meeting to discuss project plan and hash out the details of implementation';

//emails
$sugar_demodata['email_seed_data_subjects'] = array(
	'Follow-up on proposal', 
	'Initial discussion', 
	'Review needs', 
	'Discuss pricing', 
	'Demo', 
	'Introduce all players', 
);
$sugar_demodata['email_seed_data_descriptions'] = 'Meeting to discuss project plan and hash out the details of implementation';
$sugar_demodata['email_seed_data_types'] = array(
    'inbound',
    'draft',
    'out',
);

//leads
$sugar_demodata['primary_address_state'] = 'CA';
$sugar_demodata['billing_address_state']['east'] = 'NY';
$sugar_demodata['billing_address_state']['west'] = 'CA';
$sugar_demodata['primary_address_country'] = 'USA';

//manufacturers
$sugar_demodata['manufacturer_seed_data_names'] = array(
	'TekWare Inc.', 
	'Acme Suppliers'
);

//Shippers
$sugar_demodata['shipper_seed_data_names'] = array(
	'FedEx', 
	'USPS Ground'
);

//productcategories
$sugar_demodata['category_ext_name'] = ' Widgets';
$sugar_demodata['product_ext_name'] = ' Gadget';
$sugar_demodata['productcategory_seed_data_names'] = array(
	'Desktops', 
	'Laptops', 
	'Stationary Device', 
	'Modular Device'
);

//producttype
$sugar_demodata['producttype_seed_data_names']= array(
	'Devices', 
	'Hardware', 
	'Support Contract'
);

//taxrate
$sugar_demodata['taxrate_seed_data'][] = array(
	'name' => '8.25 - Cupertino, CA',
	'value' => '8.25',
);

$sugar_demodata['currency_seed_data'][] = array(
	'name' => 'Euro',
	'conversion_rate' => 0.9,
	'iso4217' => 'EUR',
	'symbol' => '€',
);

//producttemplate
$sugar_demodata['producttemplate_seed_data'][] = array(
	'name' => 'TK 1000 Desktop',
	'tax_class' => 'Taxable',
	'cost_price' => 500.00,
	'cost_usdollar' => 500.00,
	'list_price' => 800.00,
	'list_usdollar' => 800.00,
	'discount_price' => 800.00,
	'discount_usdollar' => 800.00,
	'pricing_formula' => 'IsList',
	'mft_part_num' => 'XYZ7890122222',
	'pricing_factor' => '1',
	'status' => 'Available',
	'weight' => 20.0,
	'date_available' => '2009-10-15',
	'qty_in_stock' => '72',
); 

$sugar_demodata['producttemplate_seed_data'][] = array(
	'name' => 'TK 1000 Desktop',
	'tax_class' => 'Taxable',
	'cost_price' => 600.00,
	'cost_usdollar' => 600.00,
	'list_price' => 900.00,
	'list_usdollar' => 900.00,
	'discount_price' => 900.00,
	'discount_usdollar' => 900.00,
	'pricing_formula' => 'IsList',
	'mft_part_num' => 'XYZ7890123456',
	'pricing_factor' => '1',
	'status' => 'Available',
	'weight' => 20.0,
	'date_available' => '2009-10-15',
	'qty_in_stock' => '65',
); 

$sugar_demodata['producttemplate_seed_data'][] = array(
	'name' => 'TK m30 Desktop',
	'tax_class' => 'Taxable',
	'cost_price' => 1300.00,
	'cost_usdollar' => 1300.00,
	'list_price' => 1700.00,
	'list_usdollar' => 1700.00,
	'discount_price' => 1625.00,
	'discount_usdollar' => 1625.00,
	'pricing_formula' => 'ProfitMargin',
	'mft_part_num' => 'ABCD123456890',
	'pricing_factor' => '20',
	'status' => 'Available',
	'weight' => 5.0,
	'date_available' => '2009-10-15',
	'qty_in_stock' => '12',
); 

$sugar_demodata['producttemplate_seed_data'][] = array(
	'name' => 'Reflective Mirror Widget',
	'tax_class' => 'Taxable',
	'cost_price' => 200.00,
	'cost_usdollar' => 200.00,
	'list_price' => 325.00,
	'list_usdollar' => 325.00,
	'discount_price' => 266.50,
	'discount_usdollar' => 266.50,
	'pricing_formula' => 'PercentageDiscount',
	'mft_part_num' => '2.0',
	'pricing_factor' => '20',
	'status' => 'Available',
	'weight' => 20.0,
	'date_available' => '2009-10-15',
	'qty_in_stock' => '65',
); 

//BEGIN SUGARCRM flav=pro ONLY
// KBDocuments
$sugar_demodata['kbdocument_seed_data_kbtags'] = array(
    'OS and Interface',
    'Hardware',
    'WiFi, Bluetooth, and Networking',
    'Tools',
    'Basic Usage',
    );

$sugar_demodata['kbdocument_seed_data'][] = array(
    'name' => 'Connecting to the Internet',
    'start_date' => '2010-01-01',
    'exp_date' => '2015-12-31',
    'body' => '<p>To connect your device to the Internet, use any application that accesses the Internet. You can connect using either Wi-Fi or Bluetooth.</p>',
    'tags' => array(
        'WiFi, Bluetooth, and Networking',
        ),
    );

$sugar_demodata['kbdocument_seed_data'][] = array(
    'name' => 'Charging the battery',
    'start_date' => '2010-01-01',
    'exp_date' => '2015-12-31',
    'body' => '<p>To charge the battery, try the following:</p>
   <ul><li>Connect device to a power outlet using the included cable and the USB power adapter.</li>
    <li>Connect to a high-power USB 2.0 port using the included cable.</li></ul>',
    'tags' => array(
        'Basic Usage',
        'Hardware',
        ),
    );

$sugar_demodata['kbdocument_seed_data'][] = array(
    'name' => 'How to print',
    'start_date' => '2010-01-01',
    'exp_date' => '2015-12-31',
    'body' => '<p>In order to print, you first need to send your file to your computer. Access and print the file from your computer.</p>',
    'tags' => array(
        'Basic Usage',
        'Tools',
        ),
    );

$sugar_demodata['kbdocument_seed_data'][] = array(
    'name' => 'How to change the language',
    'start_date' => '2010-01-01',
    'exp_date' => '2015-12-31',
    'body' => '<p>If your device is not set to your preferred language, please make sure you have completed the setup. In the Settings screen, select Languages. Select the language you prefer.</p>',
    'tags' => array(
        'Basic Usage',
        'OS and Interface',
        ),
    );

$sugar_demodata['kbdocument_seed_data'][] = array(
    'name' => 'Resetting the device',
    'start_date' => '2010-01-01',
    'exp_date' => '2010-12-31',
    'body' => '<p>When things are not working as expected, try resetting the device. Hold the Start button until the dialog box displays.  Select the Reset option.</p>',
    'tags' => array(
        'Basic Usage',
        'Hardware',
        ),
); 
//END SUGARCRM flav=pro ONLY

$sugar_demodata['contract_seed_data'][] = array(
	'name' => 'IT Tech Support for UK Datacenter',
	'reference_code' => 'EMP-9802',
	'total_contract_value' => '500600.01',
	'start_date' => '2010-05-15',
	'end_date' => '2020-05-15',
	'company_signed_date' => '2010-03-15',
	'customer_signed_date' => '2010-03-16',
	'description' => 'This is a sub-contract for a very large project.',
); 

$sugar_demodata['contract_seed_data'][] = array(
	'name' => 'Ion Engines for Auto Plant',
	'reference_code' => 'EMP-7277',
	'total_contract_value' => '333444.34',
	'start_date' => '2010-05-15',
	'end_date' => '2020-05-15',
	'company_signed_date' => '2010-03-15',
	'customer_signed_date' => '2010-03-16',
	'description' => 'In competition with Sienar Fleet Systems for this one.',
);

//BEGIN Quotes demo data
$sugar_demodata['quotes_seed_data']['quotes'][0] = array(
	'name' => 'Computers for [account name]',
	'quote_stage' => 'Draft',
	'date_quote_expected_closed' => '2012-04-30',
    'description' => '',
    'purcahse_order_num' => '6011842',
    'payment_terms' => 'Net 30',

    'bundle_data' => array(
		0 => array (
		    'bundle_name' => 'Computers',
		    'bundle_stage' => 'Draft',
		    'comment' => 'TK Desktop Computers',
		    'products' => array (
				1 => array('name'=>'TK 1000 Desktop', 'quantity'=>'1'),
				2 => array('name'=>'TK m30 Desktop', 'quantity'=>'2'),
			),
		),
	),
);


$sugar_demodata['quotes_seed_data']['quotes'][1] = array(
	'name' => 'Mirrors for [account name]',
	'quote_stage' => 'Negotiation',
	'date_quote_expected_closed' => '2012-04-30',
    'description' => '',
 	'purcahse_order_num' => '3940021',
    'payment_terms' => 'Net 15',
         

    'bundle_data' => array(
		0 => array (
		    'bundle_name' => 'Mirrors',
		    'bundle_stage' => 'Draft',
		    'comment' => 'Reflective Mirrors',
		    'products' => array (
				1 => array('name'=>'Reflective Mirror Widget', 'quantity'=>'2'),
			),
		),
	),
);
//END Quotes demo data

//BEGIN Opportunities demo data
$sugar_demodata['opportunities_seed_data']['opportunities'][1] = array(

    'bundle_data' => array(
		0 => array (
		    'bundle_name' => 'Mirrors',
		    'bundle_stage' => 'Draft',
		    'comment' => 'Reflective Mirrors',
		    'products' => array (
				1 => array('name'=>'Reflective Mirror Widget', 'quantity'=>'2'),
			),
		),


        0 => array (
      		    'bundle_name' => 'Mirrors',
      		    'bundle_stage' => 'Draft',
      		    'comment' => 'Reflective Mirrors',
      		    'products' => array (
      				1 => array('name'=>'Reflective Mirror Widget', 'quantity'=>'2'),
      			),
      	),

	),
);
//END Opportunities demo data
?>
