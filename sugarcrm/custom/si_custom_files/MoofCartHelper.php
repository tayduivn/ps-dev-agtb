<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class MoofCartHelper
{

    private static $cart_url = 'https://www.sugarcrm.com/sugarshop/cart.php';

    private static $mambo_dbconfig = array(
        'host' => 'online-comdb1',
        'user' => 'sugarcrm_com',
        'pass' => '08yag81g9Ag91',
        'dbname' => 'sugarcrm_com',
    );

    // jostrow

    // a list of sales stages that indicate an Opportunity is "open" (i.e. not Closed Won/Closed Lost)
    public static $openOpportunitySalesStages = array(
        'Initial_Opportunity',
        'Interested_Prospect',
        'Discovery',
        'Solution',
        'Negotiation',
        'Verbal',
        'Contract',
    );

    // a mapping of 'cart_actions' from MoofCart to their respective Opportunities::Revenue Type
    public static $cartActionToRevenueType = array(
        'renew' => 'Renewal',
        'add_users' => 'Additional',
        'upgrade_enterprise' => 'Additional',
        'add_support' => 'Additional',
    );

    /**
     * @author Jim Bartek
     * @project Moofcart
     * @tasknum 51
     * static variable for the access code for the netsuite scripts
     */

    public static $netsuiteAccessCode = 'kFG5sXw486xfdNs37';

    /**
     * @author Jim Bartek
     * @project moofcart
     * @tasknum 5
     * static variable of the salesop id for the orders dashlet Sales Op Pending
     */

    public static $salesop_id = 'bc5bc161-c4f1-422f-d76d-45bab06c211b';

    /**
     * @author Jim Bartek
     * @project moofcart
     * @tasknum 43
     * static variable of the carep id for the updateCARep logic hook
     */

    public static $carep_id = '3c210f1c-1e7a-3c74-2f7b-4a0c3a33a094';

    /**
     * @author jwhitcraft
     * @project moofcart
     * @tasknum 83
     * static variable number of allowed users for each support type
     */

    public static $supportTypeMaxUsers = array(
        'premium' => 4,
        'extended' => 2,
        'standard' => 2
    );

   // partner email no customer list ITR20055 jbartek
	public static $partnerNoSendCustomerEmail = array(
		'd402e9ef-7c80-2305-bd8e-41b0d29a9054', //Synolia
		'2afa3dac-516c-d95f-195d-449050b5b4ac', //Carrenet
	);

    /**
     * @author Jim Bartek
     * @project moofcart
     * @tasknum 66
     * Static variables for the renewal emails
     */

    public static $customer_subject = 'Your SugarCRM Account Renewal';

    public static $partner_subject = 'SugarCRM Account Renewal';

    public static $customer_template_discount = "
	
	A discount has been applied to your order. Please see your sales representive for terms and conditions of this discount. Discounts may expire before the attached quote.
	
	";

    public static $missing_partner_notice = '
	We could not find a Primary Business Contact for this Account (PARTNER_ACCOUNT_NAME).
	
	Please forward this e-mail to the proper Contact at PARTNER_ACCOUNT_NAME. To avoid this message in the future, please update the Account and choose at least one Contact as the Primary Business Contact.
	
	LINK_TO_PARTNER_ACCOUNT
	';


    public static $ca_manager_name = 'Christine Sharma';


    /**
     * @author Jim Bartek
     * @project moofcart
     * @tasknum 36,37
     * Arrays for linking products to opportunities, account types, and support users
     */

    public static $directOpportunityTypes = array(
        'sugar_pro_converge',
        'sugar_ent_converge',
        'Sugar Enterprise',
        'Sugar Professional',
        'Sugar Enterprise On-Demand',
        'Sugar OnDemand',
    );

    public static $productToAccountType = array(
        'cc912daf-5470-22b2-7c55-4c4ee8a7e7a7' => 'Partner', //Partnership Fee - Bronze
        'c6bc510c-da55-529d-5920-4c4ee8f9564b' => 'Partner', //Partnership Fee - Gold
        'cf74f502-4349-3794-5d47-4c4ee8d39589' => 'Partner', //Partnership Fee - OEM
        'c9a39e81-800d-d937-4fba-4c4ee81c321a' => 'Partner', //Partnership Fee - Silver
        '1c398a2d-8ac3-3140-fb4a-4c4ee8d82e8d' => 'SugarExchange Partner: Standard', //Sugar Exchange Partnership Fee
        'b4fbe830-ceb1-6c7b-c4b2-4c4ee885864f' => 'Customer',
        'b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae' => 'Customer-Ent',

    );

    public static $partnerToPartnerType = array(
        'cc912daf-5470-22b2-7c55-4c4ee8a7e7a7' => array('Partner_Type_c' => 'Bronze', 'resell_discount' => '10'),
        'c6bc510c-da55-529d-5920-4c4ee8f9564b' => array('Partner_Type_c' => 'Gold', 'resell_discount' => '25'),
        'cf74f502-4349-3794-5d47-4c4ee8d39589' => array('Partner_Type_c' => 'OEM', 'resell_discount' => '0'),
        'c9a39e81-800d-d937-4fba-4c4ee81c321a' => array('Partner_Type_c' => 'Silver', 'resell_discount' => '15'),
    );


    public static $product_template_categories = array(
        '8d1d91f5-1a13-5810-ca93-4c4ee3de5b90' => 'Subscriptions',
        '52f85b76-a4d0-a38e-3903-4c4ee39439ce' => 'Support',
        '1a1b02b2-1bd9-9d0c-2c33-4c4ee8b440ec' => 'SugarExchange',
        '5e7d4214-7b04-067a-6b13-4c4ee36863c0' => 'Professional Services',
        '1d363cb4-980e-ab1e-7955-4c4ee3737da1' => 'Training',
        'c4a8f7ed-4c37-b552-d87d-4c4ee8607e67' => 'Partnerships',
    );


    public static $productToOpportunityPriority = array(
        'b4fbe830-ceb1-6c7b-c4b2-4c4ee885864f', //Sugar Professional subscription
        'b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae', //Sugar Enterprise subscription
        '1c398a2d-8ac3-3140-fb4a-4c4ee8d82e8d', //Sugar Exchange Partnership Fee
        'cc912daf-5470-22b2-7c55-4c4ee8a7e7a7', //Partnership Fee - Bronze
        'c6bc510c-da55-529d-5920-4c4ee8f9564b', //Partnership Fee - Gold
        'cf74f502-4349-3794-5d47-4c4ee8d39589', //Partnership Fee - OEM
        'c9a39e81-800d-d937-4fba-4c4ee81c321a', //Partnership Fee - Silver
        'e3db85ff-898a-df21-7428-4c4ee846d0f3', //1-day Remote Jump Start
        '608c06fc-f4a3-73b5-b5ae-4c4ee8abc305', //1-time Support Case
        'bf370f92-ff60-21b9-7a9a-4c4ee891f094', //1-time Support Case:  10-pack
        '8f6f060d-7f9c-bbfa-ac9e-4c4ee8bd826b', //1-time Support Case:  5-pack
        'e6c75b54-6c78-f3c5-dfe8-4c4ee88e122c', //2-day Remote Jump Start
		'4ed81929-88c4-02c6-71a6-4ccb242e66b4', // Train Your Team
        '17eab64e-af57-67b3-9f9c-4c4ee8fcf24d', //Admin Fundamentals Online Training Course
        '258e001b-7d56-c4f8-d0ff-4c5201355d49', //asdtest
        'c27deed6-2b5f-96b8-a152-4c4ee8ccb5eb', //Backup Recovery Service - Yearly
        '308003c3-45bd-1bef-0523-4c4ee80c71a6', //Campaign Setup and Training
        'f279d2a2-b681-8f52-f320-4c4ee8a09f0e', //Custom End-User Training
        'd845552f-a2c1-d722-ce6f-4c4ee83e2469', //Data Migration
        '14ef45a6-8824-6679-6e25-4c4ee875ac48', //End User Train-The-Trainer Manual Kit
        'bd5bc400-042b-7373-3ef3-4c4ee8392337', //Extended Support - 90 day
        '554ceb22-8c4e-f225-b62c-4c4eef6ff9b0', //Extended Support - annual
        'eca6fbca-0c9d-cceb-5a28-4c4ee825218f', //Leads Package
        'db28d218-7773-aa7b-ec1f-4c4ee83520e4', //Light Data Migration
        '5922bad2-a760-3456-9a7c-4c4eef5c3957', //Premium support
        'd25d80e9-15b5-fb7d-2cbb-4c4ee8c4081b', //PS Consultant - Hourly Rate
        'd561ba0a-9555-893d-1b9f-4c4ee8a3afb6', //PS Senior Consultant - Hourly Rate
        'ef88cc53-ec56-7369-c505-4c4ee8639a59', //Quote PDF
        'ef020ddb-eaab-1238-eebb-4c4ee898cb00', //Sugar Administrator Fundamentals Learning Kit
        'de0a9495-ac18-3970-3ff6-4c4ee85f5c89', //Sugar Community Edition to Sugar Pro/Ent Migration
        '11f50fd8-29be-31a4-c3bf-4c4ee81b0e0e', //Sugar End-User Learning Kit
        'e9b73fcf-3a6a-0000-48fb-4c4ee825f839', //Sugar Guidance
        'e0f785c1-9edd-6c1f-6252-4c4ee8ebdc74', //Sugar Pro/Ent MySQL to Microsoft SQL Server Migrat

    );

    public static $productToSupportUsers = array(
        'bd5bc400-042b-7373-3ef3-4c4ee8392337' => 'extended', //Extended Support - 90 day
        '554ceb22-8c4e-f225-b62c-4c4eef6ff9b0' => 'extended', //Extended Support - annual
        '5922bad2-a760-3456-9a7c-4c4eef5c3957' => 'premium', //Premium support
    );

    public static $productToOpportunityType = array(
        'e3db85ff-898a-df21-7428-4c4ee846d0f3' => 'Training', //1-day Remote Jump Start
        '608c06fc-f4a3-73b5-b5ae-4c4ee8abc305' => 'Training', //1-time Support Case
        'bf370f92-ff60-21b9-7a9a-4c4ee891f094' => 'Support Services', //1-time Support Case:  10-pack
        '8f6f060d-7f9c-bbfa-ac9e-4c4ee8bd826b' => 'Support Services', //1-time Support Case:  5-pack
        'e6c75b54-6c78-f3c5-dfe8-4c4ee88e122c' => 'Training', //2-day Remote Jump Start
		'4ed81929-88c4-02c6-71a6-4ccb242e66b4' => 'Training', // Train Your Team
        '17eab64e-af57-67b3-9f9c-4c4ee8fcf24d' => 'Training', //Admin Fundamentals Online Training Course
        '258e001b-7d56-c4f8-d0ff-4c5201355d49' => 'Undecided', //asdtest
        'c27deed6-2b5f-96b8-a152-4c4ee8ccb5eb' => 'Support Services', //Backup Recovery Service - Yearly
        '308003c3-45bd-1bef-0523-4c4ee80c71a6' => 'Training', //Campaign Setup and Training
        'f279d2a2-b681-8f52-f320-4c4ee8a09f0e' => 'Training', //Custom End-User Training
        'd845552f-a2c1-d722-ce6f-4c4ee83e2469' => 'Support Services', //Data Migration
        '14ef45a6-8824-6679-6e25-4c4ee875ac48' => 'Training', //End User Train-The-Trainer Manual Kit
        'bd5bc400-042b-7373-3ef3-4c4ee8392337' => 'Support Services', //Extended Support - 90 day
        '554ceb22-8c4e-f225-b62c-4c4eef6ff9b0' => 'Support Services', //Extended Support - annual
        'eca6fbca-0c9d-cceb-5a28-4c4ee825218f' => 'Support Services', //Leads Package
        'db28d218-7773-aa7b-ec1f-4c4ee83520e4' => 'Support Services', //Light Data Migration
        'cc912daf-5470-22b2-7c55-4c4ee8a7e7a7' => 'Partner Fees', //Partnership Fee - Bronze
        'c6bc510c-da55-529d-5920-4c4ee8f9564b' => 'Partner Fees', //Partnership Fee - Gold
        'cf74f502-4349-3794-5d47-4c4ee8d39589' => 'Partner Fees', //Partnership Fee - OEM
        'c9a39e81-800d-d937-4fba-4c4ee81c321a' => 'Partner Fees', //Partnership Fee - Silver
        '5922bad2-a760-3456-9a7c-4c4eef5c3957' => 'Support Services', //Premium support
        'd25d80e9-15b5-fb7d-2cbb-4c4ee8c4081b' => 'Professional Services', //PS Consultant - Hourly Rate
        'd561ba0a-9555-893d-1b9f-4c4ee8a3afb6' => 'Professional Services', //PS Senior Consultant - Hourly Rate
        'ef88cc53-ec56-7369-c505-4c4ee8639a59' => 'Support Services', //Quote PDF
        'ef020ddb-eaab-1238-eebb-4c4ee898cb00' => 'Training', //Sugar Administrator Fundamentals Learning Kit
        'de0a9495-ac18-3970-3ff6-4c4ee85f5c89' => 'Support Services', //Sugar Community Edition to Sugar Pro/Ent Migration
        '11f50fd8-29be-31a4-c3bf-4c4ee81b0e0e' => 'Training', //Sugar End-User Learning Kit
        'b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae' => 'sugar_ent_coverage', //Sugar Enterprise subscription
        '1c398a2d-8ac3-3140-fb4a-4c4ee8d82e8d' => 'Partner Fees', //Sugar Exchange Partnership Fee
        'e9b73fcf-3a6a-0000-48fb-4c4ee825f839' => 'Training', //Sugar Guidance
        'e0f785c1-9edd-6c1f-6252-4c4ee8ebdc74' => 'Support Services', //Sugar Pro/Ent MySQL to Microsoft SQL Server Migrat
        'b4fbe830-ceb1-6c7b-c4b2-4c4ee885864f' => 'sugar_pro_converge', //Sugar Professional subscription
        'b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae' => 'sugar_ent_converge', //Sugar Enterprise subscription
    );

    /***** end jbartek customization ********/

    /**
     * 59c2978c-729c-d825-503d-423134b1ae9b // jeff campbell
     * 912da741-09eb-bcf8-9329-45d9f7520350 // vince randazzo
     * ec91f0de-f3c5-22bd-2116-426d380065d2 // andy dreisch
     * eeb78eea-d2fa-e3d1-60fa-462cd8842b44 // christine charma
     * 300a6f81-d21a-2cc1-e848-44a88c5eaa19 // Liz Smith
     * b23a40e4-ba90-d4df-2302-4bb61cc1ad89 // Tom Schuster
     * b7d892aa-2f3f-3672-f565-448d759760f4 // Lori Arce
     * c11865d2-1d7a-28d2-55ec-475db31018bf // Genevieve So
     *
     * @var array
     */
    public static $taskApprovalChain = array(
        'is_subscription' => array('912da741-09eb-bcf8-9329-45d9f7520350', '59c2978c-729c-d825-503d-423134b1ae9b'),
        'ca_subscription' => array('eeb78eea-d2fa-e3d1-60fa-462cd8842b44', 'ec91f0de-f3c5-22bd-2116-426d380065d2'),
        'cs_d_na_subscription' => array('59c2978c-729c-d825-503d-423134b1ae9b'),
        'cs_d_emea_subscription' => array('300a6f81-d21a-2cc1-e848-44a88c5eaa19', 'b23a40e4-ba90-d4df-2302-4bb61cc1ad89'),
        'cs_ps_emea_subscription' => array('b23a40e4-ba90-d4df-2302-4bb61cc1ad89'),
        'cs_ps_world_subscription' => array('59c2978c-729c-d825-503d-423134b1ae9b'),
        'cs_d_apac_subscription' => array('59c2978c-729c-d825-503d-423134b1ae9b'),
        'cs_ps_apac_subscription' => array('59c2978c-729c-d825-503d-423134b1ae9b'),
        'support' => array('b7d892aa-2f3f-3672-f565-448d759760f4', 'ec91f0de-f3c5-22bd-2116-426d380065d2'),
        'professional_services' => array('c11865d2-1d7a-28d2-55ec-475db31018bf', 'ec91f0de-f3c5-22bd-2116-426d380065d2'),
    );

    // GUID for the 'moofcart' user in Sugar Internal
    public static $moof_cart_user_id = '6b18835a-a4ce-bad5-cf75-4aa72bc8257b';

    public static $xcart_order_statuses = array(
        'B' => 'Backordered',
        'C' => 'Complete',
        'D' => 'Declined',
        'F' => 'Failed',
        'I' => 'Not Finished',
        'Q' => 'Queued',
    );

    // See ITRequest #10528
    // When we automatically create a renewal Opportunity, we need to set the Opportunity Type to a 'Converge' type if the original Opportunity
    // ... was a specific On-Site or On-Demand product; all renewals going-forward should be Converge instead
    public static $opp_type_renewal_type_map = array(
        'Sugar Enterprise' => 'sugar_ent_converge',
        'Sugar Enterprise On-Demand' => 'sugar_ent_converge',
        'Sugar Professional' => 'sugar_pro_converge',
        'Sugar OnDemand' => 'sugar_pro_converge',
    );

    // BEGIN jostrow customization
    // See ITRequest #10856
    // When somebody renews and we update their Subscription, we need to verify that the DistributionGroup linked to the Subscription
    // ... matches their Opportunity Type (especially in cases where they were specifically OnSite/OnDemand and are now Converge)
    // We will only be performing this check if the Opportunity that just closed was a Converge Opportunity, hence this mapping

    public static $converge_opportunity_types = array(
        'sugar_pro_converge',
        'sugar_ent_converge'
    );

    // END jostrow customization

    // If somebody's Subscription has an old On-Site/On-Demand DistributionGroup associated with it, we need to change that DistributionGroup to
    // ... a new 'Converge' type DistributionGroup.  This array serves as a mapping between old and new.
    public static $distgroup_converge_map = array(
        // SugarEnterprise => ConvergeEnterprise
        '6d8b74a7-05b3-1912-6cdf-46e1d32494a4' => '3adc1f77-b23d-403e-f324-49f88fb37370',

        // SugarEnterpriseInstaller => ConvergeEnterpriseInstaller
        '4521e4d5-025f-4325-4e5f-46e1d3e39da3' => '1025006e-cdca-16fa-0da4-49f895ad97df',

        // OnDemand_Sugar_Enterprise => ConvergeEnterprise
        '4f487a8f-8809-ecaa-5385-46e1d3719860' => '3adc1f77-b23d-403e-f324-49f88fb37370',

        // SugarPro => ConvergePro
        '6429d839-f494-2624-ca95-46e1d3b57a82' => 'd089c80f-2ccb-24ec-0f05-49f82eee06e5',

        // SugarProInstaller => ConvergeProInstaller
        'e5c53d84-5cb4-0f9e-db4d-46e1d3585a6a' => 'a933583c-d6b5-3736-4e84-49f87c669d09',

        // OnDemand_Outlook_Plugin => ConvergePro
        '6ff357cb-c92f-f984-82ef-46e1d3ad50bb' => 'd089c80f-2ccb-24ec-0f05-49f82eee06e5',
    );
	//ITR20164 :: jbartek
    private static $opp_type_product_map = array(
        'sugar_ent_converge' => 'b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae', // 'Sugar Enterprise'
        'sugar_pro_converge' => 'b4fbe830-ceb1-6c7b-c4b2-4c4ee885864f', // 'Sugar Professional- Yearly Renewal'
        
        /*** NOT IN USE ANYMORE
        'Sugar Express' => array(
            'sugar_internal' => 'eb6042fe-9cb6-6893-7889-49efef1117f7', // Renewal Sugar Express 1-5 users
            'cart' => 637, // Renewal Sugar Express 1-5 users
        ),
        *************************/
        
        'Sugar Enterprise' => '5e7c5f2d-9620-cacd-191d-4a942faeb467', // 'Sugar Enterprise - On-Site Renewal'
        'Sugar Professional' => 'aaaa9093-d7fe-a6ce-2624-4a942d8a8bf4', // 'Sugar Professional - On-Site Renewal'
        'Sugar Enterprise On-Demand' => 'f2bd4669-d4c5-0a10-5e31-4a9430b4c4e3', // 'Sugar Enterprise On-Demand Yearly Renewal'
        'Sugar OnDemand' => '7b67161f-e201-9052-b1c3-4a942eb6c644', // 'Sugar On-Demand - Renewal (Yearly)'
    );


    public static $product_to_distgroup = array(
        'cf74f502-4349-3794-5d47-4c4ee8d39589' => 'c3f0daa9-4139-f19a-d4a0-46e1d39e37b1', // OEM Partner -> SugarPartner
        'c6bc510c-da55-529d-5920-4c4ee8f9564b' => 'c3f0daa9-4139-f19a-d4a0-46e1d39e37b1', // Gold Partner -> SugarPartner
        'c9a39e81-800d-d937-4fba-4c4ee81c321a' => 'c3f0daa9-4139-f19a-d4a0-46e1d39e37b1', // Silver Partner -> SugarPartner
        'cc912daf-5470-22b2-7c55-4c4ee8a7e7a7' => 'c3f0daa9-4139-f19a-d4a0-46e1d39e37b1', // Bronze Partner -> SugarPartner
        'b4fbe830-ceb1-6c7b-c4b2-4c4ee885864f' => 'd089c80f-2ccb-24ec-0f05-49f82eee06e5', // Sugar Professional -> ConvergePro
        'b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae' => '3adc1f77-b23d-403e-f324-49f88fb37370', // Sugar Enterprise -> ConvergeEnterprise
        '1c398a2d-8ac3-3140-fb4a-4c4ee8d82e8d' => '771179b8-0798-56ad-e9d6-4baa99e41794', // Sugar Exchange Developer -> Exchange Dev
    );
    public static $product_to_od_type = array(
        'cf74f502-4349-3794-5d47-4c4ee8d39589' => 'ent', // OEM Partner -> SugarPartner
        'c6bc510c-da55-529d-5920-4c4ee8f9564b' => 'ent', // Gold Partner -> SugarPartner
        'c9a39e81-800d-d937-4fba-4c4ee81c321a' => 'ent', // Silver Partner -> SugarPartner
        'cc912daf-5470-22b2-7c55-4c4ee8a7e7a7' => 'ent', // Bronze Partner -> SugarPartner
        'b4fbe830-ceb1-6c7b-c4b2-4c4ee885864f' => 'pro', // Sugar Professional -> ConvergePro
        'b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae' => 'ent', // Sugar Enterprise -> ConvergeEnterprise
    );

    public static $partner_product_to_quantity = array(
        'cf74f502-4349-3794-5d47-4c4ee8d39589' => '50', // OEM Partner -> SugarPartner
        'c6bc510c-da55-529d-5920-4c4ee8f9564b' => '20', // Gold Partner -> SugarPartner
        'c9a39e81-800d-d937-4fba-4c4ee81c321a' => '10', // Silver Partner -> SugarPartner
        'cc912daf-5470-22b2-7c55-4c4ee8a7e7a7' => '5', // Bronze Partner -> SugarPartner
    );

    // stores a list of products, and the (possibly multiple) agreements that are required for each product
    public static $products_to_agreements = array(
        '17eab64e-af57-67b3-9f9c-4c4ee8fcf24d' => array( // Admin Fundamentals Online Training Course
            'proserve_agreement',
        ),

        '308003c3-45bd-1bef-0523-4c4ee80c71a6' => array( // Campaign Setup and Training
            'proserve_agreement',
            'sow_campaign_setup',
        ),

        'f279d2a2-b681-8f52-f320-4c4ee8a09f0e' => array( // Custom Super-User Training
            'proserve_agreement',
            'sow_super_user_training',
        ),

        'd845552f-a2c1-d722-ce6f-4c4ee83e2469' => array( // Data Migration
            'proserve_agreement',
            'sow_data_migration',
        ),

        '14ef45a6-8824-6679-6e25-4c4ee875ac48' => array( // End User Train-The-Trainer Manual Kit
            'proserve_agreement',
        ),

        'e6c75b54-6c78-f3c5-dfe8-4c4ee88e122c' => array( // Extended Remote Jump Start
            'proserve_agreement',
            'sow_extended_remote_jumpstart',
        ),

		'4ed81929-88c4-02c6-71a6-4ccb242e66b4' => array( // Train Your Team
			'proserve_agreement',
			'sow_train_your_team',
		),

        '554ceb22-8c4e-f225-b62c-4c4eef6ff9b0' => array( // Extended Support
            'support_services_agreement',
        ),

        'bd5bc400-042b-7373-3ef3-4c4ee8392337' => array( // Extended Support (90 day)
            'support_services_agreement',
        ),

        'eca6fbca-0c9d-cceb-5a28-4c4ee825218f' => array( // Leads Package
            'proserve_agreement',
            'sow_leads_package',
        ),

        'cc912daf-5470-22b2-7c55-4c4ee8a7e7a7' => array( // Partnership - Bronze
            'reseller_partner_agreement',
        ),

        'c6bc510c-da55-529d-5920-4c4ee8f9564b' => array( // Partnership - Gold
            'reseller_partner_agreement',
        ),

        'cf74f502-4349-3794-5d47-4c4ee8d39589' => array( // Partnership - OEM
            'oem_partner_agreement',
        ),

        'c9a39e81-800d-d937-4fba-4c4ee81c321a' => array( // Partnership - Silver
            'reseller_partner_agreement',
        ),

        '5922bad2-a760-3456-9a7c-4c4eef5c3957' => array( // Premium Support
            'support_services_agreement',
        ),

        'e0f785c1-9edd-6c1f-6252-4c4ee8ebdc74' => array( // Pro/Ent MySQL to Microsoft SQL Server Migration
            'proserve_agreement',
            'sow_mysql_to_microsoft_sql_migration',
        ),

        'ef88cc53-ec56-7369-c505-4c4ee8639a59' => array( // Quote PDF
            'proserve_agreement',
            'sow_quote_pdf',
        ),

        '14c6a9c3-095b-9d1a-2a57-4cb7a6f6948a' => array( // Remote Tune Up
            'proserve_agreement',
            'sow_remote_tuneup',
        ),

        'e3db85ff-898a-df21-7428-4c4ee846d0f3' => array( // Standard Remote Jump Start
            'proserve_agreement',
            'sow_standard_remote_jumpstart',
        ),

        'de0a9495-ac18-3970-3ff6-4c4ee85f5c89' => array( // Sugar Community Edition to Sugar Pro/Ent Migration
            'proserve_agreement',
            'sow_ce_to_pro_or_ent_migration',
        ),

        '11f50fd8-29be-31a4-c3bf-4c4ee81b0e0e' => array( // Sugar End-User Learning Kit
            'proserve_agreement',
        ),

        'b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae' => array( // Sugar Enterprise
            'master_subscription_agreement',
        ),

        '1c398a2d-8ac3-3140-fb4a-4c4ee8d82e8d' => array( // Sugar Exchange Partnership Fee
            'sugar_exchange_partnership_agreement',
        ),

        'e9b73fcf-3a6a-0000-48fb-4c4ee825f839' => array( // Sugar Guidance (1 Hour)
            'proserve_agreement',
            'sow_sugar_guidance_1hour',
        ),

        '88dc0b1e-e5ec-715e-47fe-4cb7a518f63b' => array( // Sugar Guidance (10 Hours)
            'proserve_agreement',
            'sow_sugar_guidance_10hours',
        ),

        '70a46662-5537-a499-5737-4cb7a5da02a1' => array( // Sugar Guidance (25 Hours)
            'proserve_agreement',
            'sow_sugar_guidance_25hours',
        ),

        '2a38fb30-4ba9-2748-25e4-4cb7a5507c77' => array( // Sugar Guidance (5 Hours)
            'proserve_agreement',
            'sow_sugar_guidance_5hours',
        ),

        'b4fbe830-ceb1-6c7b-c4b2-4c4ee885864f' => array( // Sugar Professional
            'master_subscription_agreement',
        ),
    );


	// ITR20168 --> this is the gearman worker name for moof
	private static $moofOrderWorker = 'SendOrder';

    /**
     * @author Jim Bartek
     * @project moofcart
     * @tasknum 33
     * Function and variable to get all users that report to you down the entire line
     */

    public $my_downline = array();

    public function generate_password()
    {
        srand(rand(time(), 1));

        $sid = '';
        $sets = "ABCDEFGHJKMNPQRSTWXYZ";
        $sets .= "acefghjkmnorstwxyz";
        $sets .= "23456789";

        for ($index = 0; $index < 10; $index++)
        {
            $sid .= substr($sets, (rand() % (strlen($sets))), 1);
        }

        return $sid;
    }

    public function retrieve_downline($user_id)
    {
        $db = &DBManagerFactory::getInstance();
        //find the logged in user's downline
        $query = "SELECT id FROM users WHERE reports_to_id = '$user_id'";
        $result = $db->query($query, true, " Error fetching user's reporting hierarchy: ");
        while (($row = $db->fetchByAssoc($result)) != null) {
            $this->my_downline[] = $row['id'];
            $this->retrieve_downline($row['id']);
        }
        return true;
    }

	// jostrow
	public static function sortByOrderNumberDesc($a, $b) {
		if ($a['order_number'] == $b['order_number']) {
			return 0;
		}

		return ($a['order_number'] < $b['order_number']) ? 1 : -1;
	}

    // See ITRequest #7809
    // Perform a 1-to-1 mapping of sugarcrm.com usernames to Contact/LeadPerson/Touchpoint portal names, based on e-mail address
    public static function syncPortalUsersToPersonRecords()
    {
        $GLOBALS['log']->info("syncPortalUsersToPersonRecords(): running syncPortalUsersToPersonRecords()...");

        $db = DBManagerFactory::getInstance();

        // TODO: add error handling here
        $mambo_db = mysql_connect(self::$mambo_dbconfig['host'], self::$mambo_dbconfig['user'], self::$mambo_dbconfig['pass']);
        mysql_select_db(self::$mambo_dbconfig['dbname'], $mambo_db);

        // NOTE: you'll need to add to the switch() statements below if you add a module
        $modules_to_process = array(
            'Contacts',
            'LeadContacts',
            'Touchpoints',
        );

        foreach ($modules_to_process as $module) {
            $GLOBALS['log']->info("syncPortalUsersToPersonRecords(): -- processing {$module}");

            // Retrieve a list of distinct -mail addresses where the portal name is empty, and the e-mail address is unique
            switch ($module) {
                case 'Contacts':
                    $distinct_query = "
						SELECT DISTINCT
							email_addresses.email_address
						FROM
							contacts
							INNER JOIN email_addr_bean_rel AS rel
								ON contacts.id = rel.bean_id
							INNER JOIN email_addresses
								ON rel.email_address_id = email_addresses.id
						WHERE
							contacts.deleted <> 1
							AND ( contacts.portal_name IS NULL OR contacts.portal_name = '' )
							AND rel.deleted <> 1
							AND rel.bean_module = 'Contacts'
							AND email_addresses.deleted <> 1
						GROUP BY
							email_addresses.email_address
						HAVING
							count(contacts.id) = 1
					";

                    break;

                case 'LeadContacts':
                    $distinct_query = "
						SELECT DISTINCT
							email_addresses.email_address
						FROM
							leadcontacts
							INNER JOIN email_addr_bean_rel AS rel
								ON leadcontacts.id = rel.bean_id
							INNER JOIN email_addresses
								ON rel.email_address_id = email_addresses.id
						WHERE
							leadcontacts.deleted <> 1
							AND ( leadcontacts.portal_name IS NULL OR leadcontacts.portal_name = '' )
							AND rel.deleted <> 1
							AND rel.bean_module = 'LeadContacts'
							AND email_addresses.deleted <> 1
						GROUP BY
							email_addresses.email_address
						HAVING
							count(leadcontacts.id) = 1
					";

                    break;

                case 'Touchpoints':
                    $distinct_query = "
						SELECT DISTINCT
							touchpoints.email1 AS email_address
						FROM
							touchpoints
						WHERE
							touchpoints.deleted <> 1
							AND touchpoints.email1 <> ''
							AND ( touchpoints.portal_name IS NULL OR touchpoints.portal_name = '' )
					";

                    break;

                default:
                    // TODO: ADD ERROR CASE HERE

            }

            /* Get the list from the database */
            // TODO: handle query error here
            $distinct_res = $db->query($distinct_query);
            $emails = array();
            while ($row = $db->fetchByAssoc($distinct_res)) {
                $emails[] = $row['email_address'];
            }

            $GLOBALS['log']->info("syncPortalUsersToPersonRecords(): -- retrieved " . count($emails) . " distinct e-mail addresses");

            $email_list = "'" . join("','", $emails) . "'";
            $matching_query = "
				SELECT
					`drupal`.`name` AS `username`,
					`drupal`.`mail` AS `email`
				FROM
					`drupal_users` as `drupal`
				WHERE
					`drupal`.`mail` IN ($email_list)
					AND `drupal`.`name` IS NOT NULL
					AND `drupal`.`name` <> ''
			";

            $emails = null;
            $email_list = null;

            /* TODO unified db access */
            //$usernames = $mambo_db->getRows($matching_query);

            // TODO: add error handling here
            $r = mysql_query($matching_query, $mambo_db);

            $usernames = array();
            while ($row = mysql_fetch_assoc($r)) {
                $usernames[] = $row;
            }

            $GLOBALS['log']->info("syncPortalUsersToPersonRecords(): -- found " . count($usernames) . " in Drupal");

            $raw_usernames = array();
            $updated_count = 0;
            $failure_count = 0;
            foreach ($usernames as $row) {
                if (2 == count($row)) {
                    $username = $row['username'];
                    $email = $row['email'];
                }

                $raw_usernames[] = $username;

                switch ($module) {
                    case 'Contacts':
                        $query = "
							UPDATE
								`contacts`
							INNER JOIN
								`email_addr_bean_rel` AS `rel`
								ON `contacts`.`id` = `rel`.`bean_id`
							INNER JOIN
								`email_addresses`
								ON `rel`.`email_address_id` = `email_addresses`.`id`
							SET
								`contacts`.`portal_name` = '$username'
							WHERE
								`email_addresses`.`email_address` = '$email'
								AND `email_addresses`.`deleted` <> 1
								AND `rel`.`deleted` <> 1
								AND `contacts`.`deleted` <> 1
								AND (
								   `contacts`.`portal_name` IS NULL
								   OR `contacts`.`portal_name` = ''
								)
						";

                        break;

                    case 'LeadContacts':
                        $query = "
							UPDATE
								`leadcontacts`
							INNER JOIN
								`email_addr_bean_rel` AS `rel`
								ON `leadcontacts`.`id` = `rel`.`bean_id`
							INNER JOIN
								`email_addresses`
								ON `rel`.`email_address_id` = `email_addresses`.`id`
							SET
								`leadcontacts`.`portal_name` = '$username'
							WHERE
								`email_addresses`.`email_address` = '$email'
								AND `email_addresses`.`deleted` <> 1
								AND `rel`.`deleted` <> 1
								AND `leadcontacts`.`deleted` <> 1
								AND (
								   `leadcontacts`.`portal_name` IS NULL
								   OR `leadcontacts`.`portal_name` = ''
								)
						";

                        break;

                    case 'Touchpoints':
                        $query = "
							UPDATE
								`touchpoints`
							SET
								`touchpoints`.`portal_name` = '$username'
							WHERE
								`touchpoints`.`email1` = '$email'
								AND `touchpoints`.`deleted` <> 1
								AND (
								   `touchpoints`.`portal_name` IS NULL
								   OR `touchpoints`.`portal_name` = ''
								)
						";

                        break;

                    default:
                        //  ADD ERROR CASE  HERE
                }


                // $GLOBALS['log']->fatal("----->syncPortalUsersAndContacts update email '$email' to '$username'");
                // $GLOBALS['log']->fatal("----->syncPortalUsersAndContacts $query");
                // echo '$db_master ' . join(' ', array_slice(explode("\n", trim($query)), 0, 2)) . "\n";
                // $db_master->exec($query);

                // todo: add error handling here
                $r = $db->query($query);
                if ($r) {
                    $updated_count++;
                } else {
                    $failure_count++;
                }

            }

            $GLOBALS['log']->info("syncPortalUsersToPersonRecords(): -- updated {$updated_count} records, with {$failure_count} failures");

            $usernames = null;
            $portal_name_list = "'" . join("','", $raw_usernames) . "'";

            switch ($module) {
                case 'Contacts':
                    $query = "
						SELECT
							`id`,
							`portal_name`
						FROM
							`contacts`
						WHERE
							`portal_name` IS NOT NULL
							AND `portal_name` <> ''
							AND `portal_name` IN ($portal_name_list)
							AND `contacts`.`deleted` <> 1
					";

                    break;

                case 'LeadContacts':
                    $query = "
						SELECT
							`id`,
							`portal_name`
						FROM
							`leadcontacts`
						WHERE
							`portal_name` IS NOT NULL
							AND `portal_name` <> ''
							AND `portal_name` IN ($portal_name_list)
							AND `leadcontacts`.`deleted` <> 1
					";

                    break;

                case 'Touchpoints':
                    $query = "
						SELECT
							`id`,
							`portal_name`
						FROM
							`touchpoints`
						WHERE
							`portal_name` IS NOT NULL
							AND `portal_name` <> ''
							AND `portal_name` IN ($portal_name_list)
							AND `touchpoints`.`deleted` <> 1
					";

                    break;

                default:
                    // TODO: ADD ERROR CASE HERE

            }

            $raw_usernames = null;
            $portal_name_list = null;

            // TODO: add error handling here
            $r = $db->query($query);
            $ids_to_usernames = array();
            while ($row = $db->fetchByAssoc($r)) {
                $ids_to_usernames[] = $row;
            }

            // echo '$db ' . join(' ', array_slice(explode("\n", trim($query)), 0, 2)) . "\n";
            //$ids_to_usernames = $db->getRows($query);
            // echo count($ids_to_usernames) . " contacts with new portal names found\n";

            $GLOBALS['log']->info("syncPortalUsersToPersonRecords(): -- found " . count($ids_to_usernames) . " {$module} with new portal names");

            $audit_count = 0;
            foreach ($ids_to_usernames as $row) {
                $id = $row['id'];
                $username = $row['portal_name'];
                $new_guid = create_guid();

                // we could just have one query here and replace in {$module}, but let's keep it consistent with the rest of the method,
                //    in case we need special handling later
                switch ($module) {
                    case 'Contacts':
                        $query = "
							INSERT INTO `contacts_audit`
							(
								`id`,
								`parent_id`,
								`date_created`,
								`created_by`,
								`field_name`,
								`data_type`,
								`before_value_string`,
								`after_value_string`
							)
							VALUES
							(
								'$new_guid',
								'$id',
								now(),
								'1',
								'portal_name',
								'varchar',
								'',
								'$username'
							)
						";

                        break;

                    case 'LeadContacts':
                        $query = "
							INSERT INTO `leadcontacts_audit`
							(
								`id`,
								`parent_id`,
								`date_created`,
								`created_by`,
								`field_name`,
								`data_type`,
								`before_value_string`,
								`after_value_string`
							)
							VALUES
							(
								'$new_guid',
								'$id',
								now(),
								'1',
								'portal_name',
								'varchar',
								'',
								'$username'
							)
						";

                        break;

                    case 'Touchpoints':
                        $query = "
							INSERT INTO `touchpoints_audit`
							(
								`id`,
								`parent_id`,
								`date_created`,
								`created_by`,
								`field_name`,
								`data_type`,
								`before_value_string`,
								`after_value_string`
							)
							VALUES
							(
								'$new_guid',
								'$id',
								now(),
								'1',
								'portal_name',
								'varchar',
								'',
								'$username'
							)
						";

                        break;

                    default:
                        //  ADD ERROR CASE HERE

                }

                // TODO: add error handling here
                $r = $db->query($query);
                $audit_count++;
            }

            $GLOBALS['log']->info("syncPortalUsersToPersonRecords(): -- done with {$module}");
        }

        mysql_close($mambo_db);

        $GLOBALS['log']->info("syncPortalUsersToPersonRecords(): DONE");

        return TRUE;
    }

    // This method returns the number of users to be renewed-- used to generate the Quote and the shopping cart
    public static function getRenewalNumberOfUsers($opportunity)
    {
        $opp_users = $opportunity->users; // the 'Subscriptions' field in Opportunities

        $db = DBManagerFactory::getInstance();

        // query for SugarInstallations records; we want 'Active' SugarInstallations first, then 'Live' SugarInstallations, if any exist (ORDER BY status ASC)
        // ...then sort them by last_touch, most recent first
        // if the SugarInstallations::users value ('Active Users') is greater than the Opportunities::users ('Subscriptions') value, then return that instead

        // JOSTROW: temporarily commenting out the SugarInstallations logic
        // See ITRequest #10820

        /*
          $res = $db->query("SELECT users FROM sugar_installations WHERE account_id = '{$opportunity->account_id}' ORDER BY status ASC, last_touch DESC LIMIT 1");

          if ($db->getRowCount($res) > 0) {
              $row = $db->fetchByAssoc($res);

              if ($row['users'] > $opp_users) {
                  return $row['users'];
              }
          }
          */

        return $opp_users;
    }
	// ITR20164 :: jbartek -> Rewriting this to get the "best" product id.
    public static function getRenewalProductIdsFromOppType($opportunity_focus)
    {
        $opportunity_type = $opportunity_focus->opportunity_type;

        // BEGIN jostrow customization
        // See ITRequest #13843
        // When sending out Quotes (and performing a number of other functions), override any OnSite/OnDemand-type Opportunities
        //    by returning information for the applicable Converge-type product.
        // In other words, even if the Opportunity Type is "Enterprise On-Site," send information for "Enterprise Converge"
        // A number of other methods rely on this method, so the change should be made across the board by adding the lines of code below

        if (isset(self::$opp_type_renewal_type_map[$opportunity_type])) {
            $opportunity_type = self::$opp_type_renewal_type_map[$opportunity_type];
        }

        // END jostrow customization

        //$opportunity_sale_means = !empty($opportunity_focus->partner_assigned_to_c) ? 'partner' : 'direct';
		
		return self::$opp_type_product_map[$opportunity_type];
		/*
        if (isset(self::$opp_type_product_map[$opportunity_sale_means][$opportunity_type][$system])) {
            return self::$opp_type_product_map[$opportunity_sale_means][$opportunity_type][$system];
        }
        else {
            return '';
        }
        */
    }

    // See IT Request 10089
    // You pass in the opportunity for the quote, and an emtpy seed quote, which I populate the data in
    public static function createQuoteForRenewal(&$opportunity, &$quote)
    {
        require_once('modules/Accounts/Account.php');
        require_once('modules/ProductBundles/ProductBundle.php');
        require_once('include/TimeDate.php');
        $timedate = new TimeDate();

        $account = new Account();
        $account->disable_row_level_security = true;
        $account->retrieve($opportunity->account_id);
        if (empty($account->id)) {
            //echo "createQuoteForRenewal :: bad account<BR>";
            return false;
        }
		// ITR20164 :: jbartek
        $product_id = self::getRenewalProductIdsFromOppType($opportunity);
        if (empty($product_id)) {
            //echo "createQuoteForRenewal :: bad product_id<BR>";
            return false;
        }

        $product_template = new ProductTemplate();
        $product_template->disable_row_level_security = true;
        $product_template->retrieve($product_id);
        
        if (empty($product_template->id)) {
            //echo "createQuoteForRenewal :: bad product template<BR>";
            return false;
        }
        $product_category = new ProductCategory();
        $product_category->disable_row_level_security = true;
        $product_category->retrieve($product_template->category_id);
        if (empty($product_category->id)) {
            //echo "createQuoteForRenewal :: bad product category<BR>";
            return false;
        }

        $subscription_data = self::getLastExpirationDate($opportunity->account_id);
        if (empty($subscription_data)) {
            //echo "createQuoteForRenewal :: getLastExpirationDate returned empty value<BR>";
            return false;
        }
        $date_quote_expected_closed = $subscription_data['expiration_date'];

        // Set ID first so we can associate with product bundles
        $quote->id = create_guid();
        $quote->new_with_id = true;

        $quote->name = "SugarCRM {$product_category->name} Renewal for {$account->name}";
        $quote->opportunity_name = '';
        $quote->opportunity_id = '';
        $quote->quote_stage = 'Delivered';
        $quote->date_quote_expected_closed = $timedate->to_display_date($date_quote_expected_closed, false);
        $quote->assigned_user_id = $opportunity->assigned_user_id;
        $quote->team_id = '1'; // This may have to change. Verify
        $quote->billing_account_name = $account->name;
        $quote->billing_account_id = $account->id;
        $quote->billing_address_street = $account->billing_address_street;
        $quote->billing_address_city = $account->billing_address_city;
        $quote->billing_address_state = $account->billing_address_state;
        $quote->billing_address_postalcode = $account->billing_address_postalcode;
        $quote->billing_address_country = $account->billing_address_country;
        $quote->shipping_address_street = $account->shipping_address_street;
        $quote->shipping_address_city = $account->shipping_address_city;
        $quote->shipping_address_state = $account->shipping_address_state;
        $quote->shipping_address_postalcode = $account->shipping_address_postalcode;
        $quote->shipping_address_country = $account->shipping_address_country;
        $quote->taxrate_id = 'b03534a7-f284-17ef-6d06-474dafff92d3';
        $quote->taxrate_value = 0;

        $renewal_users = MoofCartHelper::getRenewalNumberOfUsers($opportunity);

        $pb = new ProductBundle();
        $pb->tax = 0;
        $pb->shipping = 0;
        $pb->subtotal = $product_template->cost_price * $renewal_users;
        $pb->deal_tot = 0;
        $pb->new_sub = $product_template->cost_price * $renewal_users;
        $pb->total = $product_template->cost_price * $renewal_users;
        $pb->currency_id = '-99';
        $pb->bundle_stage = 'Delivered';
        $pb->team_id = '1';
        $pb_id = $pb->save(false);
        $pb->set_productbundle_quote_relationship($quote->id, $pb->id, 0);

        $product = new Product();
        $product->name = $product_template->name;
        $product->bundle_stage = 'Delivered';
        $product->tax_class = 'Taxable';
        $product->parent_group = 'group_0';
        $product->parent_group_index = '1';
        $product->parent_group_position = '1';
        $product->quantity = $renewal_users;
        $product->description = $product_template->description;
        $product->mft_part_num = $product_template->mft_part_num;
        $product->pricing_factor = '1';
        $product->tax_class_select_name = 'Non-Taxable';
        $product->tax_class_name = 'Taxable';
        $product->cost_price = $product_template->cost_price;
        $product->list_price = $product_template->list_price;
        $product->discount_price = $product_template->discount_price;
        $product->discount_amount = '0';
        $product->product_template_id = $product_template->id;
        $product->type_id = 'aeb79a56-8b58-23bf-d82a-436aadf35f1d'; // "n/a" product_types
        $product->status = 'Quotes';
        $product->currency_id = $pb->currency_id;
        $product->team_id = '1';
        $product->quote_id = $quote->id;
        $product->account_id = $quote->billing_account_id;
        $product->contact_id = $quote->billing_contact_id;
        $product->status = $quote->quote_type;
        $product->unformat_all_fields();
        $product->save(false);

        $pb->set_productbundle_product_relationship($product->id, $product->parent_group_position, $pb_id);

        $quote->subtotal = $pb->subtotal;
        $quote->subtotal_usdollar = $pb->subtotal;
        $quote->shipping = 0;
        $quote->shipping_usdollar = 0;
        $quote->tax = 0;
        $quote->tax_usdollar = 0;
        $quote->total = $pb->subtotal;
        $quote->total_usdollar = $pb->subtotal;
        $quote->save(false);

        return true;
    }

    public static function getLastExpirationDate($account_id)
    {
        $subscription_query =
                "select subscriptions.* \n" .
                        "from subscriptions \n" .
                        "where subscriptions.account_id = '{$account_id}' \n" .
                        "  and subscriptions.status = 'enabled' \n" .
                        "  and subscriptions.deleted = 0 \n" .
                        "order by subscriptions.expiration_date desc";

        $subscription_res = $GLOBALS['db']->query($subscription_query);

        $subscription_row = $GLOBALS['db']->fetchByAssoc($subscription_res);

        if ($subscription_row) {
            return $subscription_row;
        }
        else {
            return '';
        }
    }

    public static function getRenewableOpportunityTypes($return_type = 'array')
    {
        $renewable_opp_types = array(
            'sugar_ent_converge',
            'sugar_pro_converge',
            'Sugar Express',
            'Sugar Enterprise',
            'Sugar Professional',
            'Sugar Enterprise On-Demand',
            'Sugar OnDemand',
            'Partner Fees',
        );

        if ($return_type == 'in_clause') {
            return "('" . implode("','", $renewable_opp_types) . "')";
        }
        else {
            return $renewable_opp_types;
        }
    }

    public static function getRenewableDistributionGroups($return_type = 'array')
    {
        $renewable_dist_groups = array(
            '3adc1f77-b23d-403e-f324-49f88fb37370', // ConvergeEnterprise
            '1025006e-cdca-16fa-0da4-49f895ad97df', // ConvergeEnterpriseInstaller
            'd089c80f-2ccb-24ec-0f05-49f82eee06e5', // ConvergePro
            'a933583c-d6b5-3736-4e84-49f87c669d09', // ConvergeProInstaller
            '6ff357cb-c92f-f984-82ef-46e1d3ad50bb', // OnDemand_Outlook_Plugin
            '4f487a8f-8809-ecaa-5385-46e1d3719860', // OnDemand_Sugar_Enterprise
            '9c0c7571-7a68-6fdc-91a7-49b99f314e67', // SugarCEOnDemand
            '6d8b74a7-05b3-1912-6cdf-46e1d32494a4', // SugarEnterprise
            '4521e4d5-025f-4325-4e5f-46e1d3e39da3', // SugarEnterpriseInstaller
            '8e9e6e95-6bf6-2fe3-7005-49f7bd5f04a7', // SugarExpress
            '6429d839-f494-2624-ca95-46e1d3b57a82', // SugarPro
            'e5c53d84-5cb4-0f9e-db4d-46e1d3585a6a', // SugarProInstaller
            '57fa69d8-a5c8-3cdc-0a14-46e1d3f22246', // SugarProOffline
        );

        if ($return_type == 'in_clause') {
            return "('" . implode("','", $renewable_dist_groups) . "')";
        }
        else {
            return $renewable_dist_groups;
        }
    }

    // return the current List Price for a given product (pulling from Sugar Internal's Product Catalog)
    public static function getAmountFromOpportunityType($opportunity, $subscriptions)
    {
        global $current_user;

        require_once('modules/ProductTemplates/ProductTemplate.php');

        // retrieve the correct product ID
        // ITR20164:: jbartek
        $product_id = self::getRenewalProductIdsFromOppType($opportunity);

        if (empty($product_id)) {
        	exit('b');
            $GLOBALS['log']->fatal("getAmountFromOpportunityType(): could not retrieve valid product_id for Opportunity {$opportunity->id}");
            return 0.00;
        }

        // we need to make sure that no Roles/Team Security get in the way of this operation; temporarily using the system user
        $old_current_user = $current_user;
        unset($current_user);

        $current_user = new User();
        $current_user->getSystemUser();

        // load the ProductTemplate so we can retrieve its List Price
        $product = new ProductTemplate();
        $product->retrieve($product_id);

        // restore the previous user session
        unset($current_user);
        $GLOBALS['current_user'] = $old_current_user;

        // BEGIN jostrow customization
        // See ITRequest #10769
        // We need to calculate the Opportunity Amount differently for 'Sugar Express' type Opportunities

        if ($opportunity->opportunity_type == 'Sugar Express') {
            return $product->list_price;
        }
        else {
            return $product->list_price * $subscriptions;
        }

        // END jostrow customization
    }


    public static function automationLog($text)
    {
        $return = '(' . date('Y-m-d H:i:s') . ')';
        $return .= $text;
        return $return;
    }

    public static function getWelcomeMeetingLinks($feed, $parse_as_html = false)
    {

        $contents = MoofCartHelper::rss_to_array($feed);

        if (empty($contents)) {
            if ($parse_as_html) {
                return '<h3>Support</h3><br /><a href="http://www.sugarcrm.com/support">http://www.sugarcrm.com/support</a>';
            }
            else {
                return 'http://www.sugarcrm.com/support';
            }
        }

        if ($parse_as_html) {
            $html = '';
            foreach ($contents AS $item) {
                $html .= "<a href='{$item['link']}'>{$item['title']}</a><br/>";
            }
            return $html;
        }
        else {
            return $contents;
        }
    }

    public static function rss_to_array($feed = false)
    {
        if ($feed === false) {
            return array();
        }

        $return = array();

        $rss = simplexml_load_file($feed);

        $x = 0;

        foreach ($rss->channel->item AS $item) {
            foreach ($item AS $key => $val) {
                $return[$x][$key] = (string) $val;
            }
            $x++;
        }

        return $return;
    }


    /**
     * @author Jim Bartek
     * @project moofcart
     * @tasknum 66
     * Static function to get the renewal email body
     */
    public static function getRenewalEmail($replacements, $type)
    {
        switch ($type) {
            case 'partner':
                $body = 'partner_renewal.tpl';
                break;
            case 'html':
                $body = 'contact_renewal.tpl';
                break;
            default:
                $body = 'contact_renewal.tpl';
                break;
        }

        require_once('XTemplate/xtpl.php');
        $tpl = new XTemplate('custom/si_custom_files/tpls/moofcart_emails/' . $body);


        if ($body===false) {
            return false;
        }

        foreach ($replacements as $search => $replace) {
            $tpl->assign($search, $replace);
        }
        $tpl->parse('main');

        return $tpl->text('main');
    }

    /**
     * @author Jim Bartek
     * @project moofcart
     * @tasknum 44
     * static method to call to complete an order, pass in the order bean and if you really want to run it..let the magic happen
     */
    public static function completeOrder(&$order, $run = false, $display = true)
    {
	// log why are we running this?
	// ITR 19941 :: Add logging to track down why this may be running twice
	if($order->status == 'Completed') {
	    $msg = 'jbartek :: running complete order for an already completed order :: ' . $order->order_id . ' :: ' . $order->id;
            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
	}

        //ini_set('display_errors',1);
        //creating/updating subscription data
        $subs = $order->get_linked_beans('orders_subscriptions', 'Subscription', array(), 0, -1, 0);
        $cons = $order->get_linked_beans('contacts_orders', 'Contact', array(), 0, -1, 0);
        $opp = reset($order->get_linked_beans('orders_opportunities', 'Opportunity', array(), 0, -1, 0));
        $con = reset($order->get_linked_beans('orders_contracts', 'Contract', array(), 0, -1, 0));
        $acc = reset($order->get_linked_beans('accounts_orders', 'Account', array(), 0, 1));
        $products = $order->get_linked_beans('orders_products', 'Product', array(), 0, -1, 0);

        $revenue_type = $opp->Revenue_Type_c;

        $return = array();

        //update the order status
        $msg = "Updating Order";
        if ($display) {
            $custom = array();
            $custom[] = array('field' => 'Status',
                'before' => ucwords(str_replace('_', ' ', $order->status)),
                'after' => 'Completed',
            );

            $return['messages'][] = array('msg' => $msg, 'custom' => $custom);
        }
        if ($run) {
            //update the order status
            $msg = 'Setting the Order to Complete';
            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
            $order->status = 'Completed';
            $order->save();
        }

        foreach ($cons AS $contact) {
            if ($contact->portal_active == 0 || $contact->download_software_c == 0 || $contact->manage_employees_c == 0) {
                //update portal
                $msg = 'Activating Contacts Portal';
                if ($run) {
                    $contact->portal_active = 1;
                    if ($order->blue_bird_c == 1) {
                        $contact->download_software_c = 1;
                        $contact->manage_employees_c = 1;
                    }
                    //ITR20138 :: if it was purchased on behalf of a customer make sure download software and support authorized are turned on
		    if(!empty($order->account_id_c)) {
			$contact->support_authorized_c = 1;
			$contact->download_software_c = 1;
		    }
                    $contact->save();
                }
                if ($display) {
                    $custom = array();
                    $custom[] = array('field' => 'Portal Active',
                        'before' => 'Off',
                        'after' => 'On',
                    );
                    if ($order->blue_bird_c == 1) {
                        $custom[] = array('field' => 'Download Software', 'before' => 'Off', 'after' => 'On');
                        $custom[] = array('field' => 'Manage Employees', 'before' => 'Off', 'after' => 'On');
                    }
		    //ITR20138 :: if it was purchased on behalf of a customer make sure download software and support authorized are turned on
		    if(!empty($order->account_id_c)) {
                        $custom[] = array('field' => 'Download Software', 'before' => 'Off', 'after' => 'On');
                        $custom[] = array('field' => 'Support Authorized', 'before' => 'Off', 'after' => 'On');
		    }

                    $return['messages'][] = array('msg' => $msg,
                        'custom' => $custom,
                    );
                }
                $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
            }
        }
	if($order->cart_action_c != 'add_support') {
        if (empty($subs)) {
            if ($results = MoofCartHelper::getSubscriptionDistGroup($products, $order)) {
                $msg = 'Creating a subscription';
                if ($run) {
                    // do function call
                    $sub = new Subscription;
                    $sub->subscription_id = md5($acc->name . date('Y-m-d H:i:s'));
                    $sub->expiration_date = $results['expiration_date'];
					$sub->term_end_date_c = $results['expiration_date'];
                    $sub->account_id = $acc->id;
                    $sub->status = 'enabled';
                    $sub->assigned_user_id = $acc->assigned_user_id;
	   	    // ITR 19968 :: Subs are being correctly linked but weren't being set global so people couldn't see them.
		    $sub->team_id = 1;
		    $sub->team_set_id = 1;
                    $sub->audited = 1;
		    // ITR 19983
		    $sub->portal_users = $results['portal_users'];
		    $sub->save();

                    $sub->load_relationship('distgroups');
                    $sub->load_relationship('orders');

                    $order->orders_subb9eaiptions_idb = $sub->id;
                    $order->save();

                    $sub->distgroups->add($results['distgroup_id'], array('quantity' => $results['quantity']));

                }
                if ($display) {
                    $return['messages'][] = array('msg' => $msg);
                }
                $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
            }
        }
        else {
            $msg = 'Updating a subscription';
            if ($run) {
                $results = MoofCartHelper::getSubscriptionDistGroup($products, $order);
                // do function call
                foreach ($subs AS $sub) {
                    $distgroup = reset($sub->get_linked_beans('distgroups', 'DistGroup', array(), 0, -1, 0));
                    
                    if (!empty($results['distgroup_id']) && $results['distgroup_id'] == $distgroup->id) {
                        if ($order->cart_action_c == 'add_users') {
                            // get the quantity
                            // update the quantity
                            $GLOBALS['db']->query("UPDATE subscriptions_distgroups SET quantity = quantity + {$results['quantity']} WHERE subscription_id = '{$sub->id}' AND distgroup_id = '{$distgroup->id}'");
                            $msg = 'Updating Subscription Distgroups with new quantity since we are adding users';
                            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
                        }
                        else {
                            $GLOBALS['db']->query("UPDATE subscriptions_distgroups SET quantity = {$results['quantity']} WHERE subscription_id = '{$sub->id}' AND distgroup_id = '{$distgroup->id}'");
                            $msg = 'Updating Subscription Distgroups with new quantity since we are ' . $order->cart_action_c;
                            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
                        }
                    }
					elseif(!empty($results['distgroup_id']) && $results['distgroup_id'] != $distgroup->id) {
                        if ($order->cart_action_c == 'add_users') {
                            // get the quantity
                            // update the quantity
                            $GLOBALS['db']->query("UPDATE subscriptions_distgroups SET quantity = quantity + {$results['quantity']}, distgroup_id = '{$results['distgroup_id']}' WHERE subscription_id = '{$sub->id}' AND distgroup_id = '{$distgroup->id}'");
                            $msg = 'Updating Subscription Distgroups with new quantity since we are adding users';
                            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
                        }
                        else {
                            $GLOBALS['db']->query("UPDATE subscriptions_distgroups SET quantity = {$results['quantity']}, distgroup_id = '{$results['distgroup_id']}' WHERE subscription_id = '{$sub->id}' AND distgroup_id = '{$distgroup->id}'");
                            $msg = 'Updating Subscription Distgroups with new quantity since we are ' . $order->cart_action_c;
                            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
                        }
		
					}
                    $sub->status = 'enabled';
				    if($results['expiration_date'] != false) {
						$sub->expiration_date = $results['expiration_date'];
						$sub->term_end_date_c = $results['expiration_date'];
				    }
                    $sub->save();
                }
            }
            if ($display) {
                $custom = array();
                $results = MoofCartHelper::getSubscriptionDistGroup($products, $order);
                foreach ($subs AS $sub) {
                    $distgroups = $sub->get_linked_beans('distgroups', 'DistGroup', array(), 0, -1, 0);
                    foreach ($distgroups AS $distgroup) {
                        $msg .= "<br /><a href='/index.php?module=Subscriptions&action=DetailView&record={$sub->id}' target='_blank'>{$distgroup->name} ({$sub->subscription_id})</a>";
                        $custom[] = array('field' => 'Expiration Date',
                            'before' => $sub->expiration_date,
                            'after' => $results['expiration_date'],
                        );


                        $return['messages'][] = array('msg' => $msg, 'custom' => $custom);
                        $msg = 'Updating a subscription';
                    }
                }
            }
            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
        }
	}
        // update opportunity
        if (!empty($opp)) {
            $msg = 'Updating an Opportunity';
            if ($run) {
		$opp->date_closed = date('Y-m-d');
                $opp->sales_stage = "Sales Ops Closed";
                $opp->save();
            }
            if ($display) {
                $msg .= "<br/><a href='/index.php?module=Opportunities&action=DetailView&record={$opp->id}' target='_blank'>{$opp->name}</a>";
                $custom = array();
                $custom[] = array('field' => 'Sales Stage',
                    'before' => ucwords(str_replace('_', ' ', $opp->sales_stage)),
                    'after' => 'Sales Ops Closed',
                );
		$custom[] = array( 'field' => 'Date Closed', 
			'before' => $opp->date_closed,
			'after' => date('Y-m-d'),
			);
                $return['messages'][] = array('msg' => $msg,
                    'custom' => $custom,
                );
            }
            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
        }
        //ITR 19435 dtam Make sure OD instances created
        global $current_user;
	global $timedate;

	// ITR 19941 :: Added the field ondemand_instance_created_c to stop creating two ondemand instances
        if (isset($acc->id) && !empty($acc->id) && $order->ondemand_instance_name_c != "" && $order->ondemand_datacenter_c != '' && $run && $order->ondemand_instance_created_c == 0) {
            $pw = MoofCartHelper::generate_password();
            $admin_pw = MoofCartHelper::generate_password();
            $lower_name = strtolower($order->ondemand_instance_name_c);
            $deploy_edition = 'pro';
            foreach ($products AS $product) {
                if (array_key_exists($product->product_template_id, MoofCartHelper::$product_to_od_type)) {
                    $deploy_edition = MoofCartHelper::$product_to_od_type[$product->product_template_id];
                }
            }
            $download_key = $sub->subscription_id;
            $expires = $timedate->to_db_date($con->end_date);
            $quantity = $results['quantity'];
            if ($order->ondemand_datacenter_c == 'us') {
                $server = 7;
            } else {
                $server = 10; //emea
            }
            $pu_email = $order->email;
            $pu_fname = $order->first_name;
            $portal_user = $order->username;
            $order_id = $order->name;
            $msg = "Creating On Demand Instance";
            if ($display) {
                $return['messages'][] = array('msg' => $msg);
            }
                $iondb = mysql_connect("admin4", "ion3", "flip5!m00");
                mysql_select_db("ion3", $iondb);
                $sql = sprintf("INSERT INTO log_instances(instance,password,admin_password, license_users, license_expire, license_key, internal_record, edition, server, user_id, owner, first_name, done, autogen, portal_user, date_created, order_id) VALUES('%s', '%s','%s','%s','%s','%s','%s','%s',%d,'%s','%s','%s',%d,%d, '%s', NOW(), '%s')",
                    $lower_name,
                    $pw,
                    $admin_pw,
                    $quantity,
                    $expires,
                    $download_key,
                    $acc->id,
                    $deploy_edition, $server, $pu_email,
                    'ion@sugarcrm.com', $pu_fname, 0, 1, $portal_user, $order_id);
                syslog(LOG_DEBUG, "joey livedebug {$thisOrderID}: insert sql " . $sql);
                mysql_query($sql, $iondb);
                $sql = sprintf("INSERT INTO accounts(instance, account_type, account_name, internal_id) VALUES ('%s','%s','%s','%s')", $lower_name, 'account', addslashes($acc->name), $acc->id);
                syslog(LOG_DEBUG, "joey livedebug {$thisOrderID}: insert sql " . $sql);
                mysql_query($sql, $iondb);
                mysql_close($iondb);
		// when we create an OD instance se the created to 1
		// ITR 19941
		$order->ondemand_instance_created_c = 1;
		$order->save();
        }
	// if it wasn't a partner purchased order OR it was but wasn't in the list of partners who don't want customer emails sent then send it to everyone ELSE send it just to the partner
	// ITR20055 - jbartek
	if(empty($order->account_id_c) || !in_array($order->account_id_c, MoofCartHelper::$partnerNoSendCustomerEmail)) {
	        foreach ($cons AS $contact) {
        	    $email = reset($contact->get_linked_beans('email_addresses_primary', 'EmailAddress', array(), 0, -1, 0));

	            if (!empty($email->email_address) && $order->email != $email->email_address) {
        	        $to[] = array('name' => $contact->first_name . ' ' . $contact->last_name,
                	    'email' => $email->email_address,
    	                'id' => $email->id,
        	        );
            	   }
		}
	        $to[] = array('name' => $order->billing_first_name . ' ' . $order->billing_last_name,
        	    'email' => $order->email
       		 );
	}
	else {
		$contact = new Contact();
		$contact->retrieve($order->contact_id_c);
                $email = reset($contact->get_linked_beans('email_addresses_primary', 'EmailAddress', array(), 0, -1, 0));

                $to[] = array('name' => $contact->first_name . ' ' . $contact->last_name,
                             'email' => $email->email_address,
   	                     'id' => $email->id,
                        );

	}
        require_once('modules/Administration/Administration.php');
        require_once('include/SugarPHPMailer.php');

        // CREATE WELCOME EMAIL AND ARCHIVE IT
        /*
          if(!empty($order->ondemand_instance_name_c)) {
              $welcome_body = MoofCartHelper::getWelcomeEmail($user);
              if($run) {

                  $mail = new SugarPHPMailer();
                  $admin = new Administration();

                  $admin->retrieveSettings();
                  if ($admin->settings['mail_sendtype'] == "SMTP") {
                      $mail->Host = $admin->settings['mail_smtpserver'];
                      $mail->Port = $admin->settings['mail_smtpport'];
                      if ($admin->settings['mail_smtpauth_req']) {
                          $mail->SMTPAuth = TRUE;
                          $mail->Username = $admin->settings['mail_smtpuser'];
                          $mail->Password = $admin->settings['mail_smtppass'];
                      }
                      $mail->Mailer   = "smtp";
                      $mail->SMTPKeepAlive = true;
                  }
                  else {
                      $mail->mailer = 'sendmail';
                  }
                  $from = array();
                  $from['email'] = $mail->From = 'orders@sugarcrm.com';
                  $from['name'] = $mail->FromName = 'orders@sugarcrm.com';
                  $mail->ContentType = "text/html"; //"text/plain"

                  $subject = $mail->Subject = "Welcome to Sugar!";

                  global $sugar_config;

                  $mail->Body = $welcome_body;

                  foreach($to AS $t) {
                      $mail->AddAddress($t['email'], $t['name']);
                  }

                  MoofCartHelper::archiveEmail($to,$from,$subject,$welcome_body,'Orders',$order->id, $order->assigned_user_id,$acc->id);


                  if (!$mail->send()) {
                      $GLOBALS['log']->info("Mailer error: " . $mail->ErrorInfo);
                      return false;
                  }
              }
          }
          */
        $complete_body = MoofCartHelper::getOrderCompleteEmail($order, $user, $admin_pw);
        $msg = 'Emailing complete emails.';

        if ($run) {
            require_once('modules/Administration/Administration.php');
            require_once('include/SugarPHPMailer.php');
            $mail = new SugarPHPMailer();
            $admin = new Administration();

            $admin->retrieveSettings();
            if ($admin->settings['mail_sendtype'] == "SMTP") {
                $mail->Host = $admin->settings['mail_smtpserver'];
                $mail->Port = $admin->settings['mail_smtpport'];
                if ($admin->settings['mail_smtpauth_req']) {
                    $mail->SMTPAuth = TRUE;
                    $mail->Username = $admin->settings['mail_smtpuser'];
                    $mail->Password = $admin->settings['mail_smtppass'];
                }
                $mail->Mailer = "smtp";
                $mail->SMTPKeepAlive = true;
            }
            else {
                $mail->mailer = 'sendmail';
            }

            $from['email'] = $mail->From = 'orders@sugarcrm.com';
            $from['name'] = $mail->FromName = 'orders@sugarcrm.com';

            $subject = $mail->Subject = "SugarCRM Inc.: Order #{$order->order_id} Confirmation";

            global $sugar_config;

	    $mail->ContentType = "text/html"; //"text/plain"


            $mail->Body = $complete_body;

            foreach ($to AS $t) {
                $mail->AddAddress($t['email'], $t['name']);
            }

            MoofCartHelper::archiveEmail($to, $from, $subject, $complete_body, 'Orders', $order->id, $order->assigned_user_id, $acc->id);
            MoofCartHelper::archiveEmail($to, $from, $subject, $welcome_body, 'Opportunities', $opp->id, $order->assigned_user_id, $acc->id);

	        $mail->AddBCC("orders-mailbox@sugarcrm.com", "orders-mailbox@sugarcrm.com");
    
            if (!$mail->send()) {
		// ITR 19941
		$msg = 'jbartek :: send failed for order completion';
	        $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
                $GLOBALS['log']->info("Mailer error: " . $mail->ErrorInfo);
                return false;
            }

        }
        if ($display) {
            $return['messages'][] = array('msg' => $msg);
            // make to string
            if (!empty($to)) {
                $string = '<ul>';
                foreach ($to AS $email) {
                    $string .= "<li>{$email['name']} - {$email['email']}</li>";
                }
                $string .= "</ul>";
            }
            /*
               if(!empty($order->ondemand_instance_name_c)) {
                   $return['emails'][] = array(
                                           'to' => $string,
                                           'subject' => 'Welcome To Sugar!',
                                           'email' => $welcome_body,
                                           'name' => 'welcome_email',
                                           'name_link' => 'welcome_email_link',
                                           );
               }
               */
            $return['emails'][] = array(
                'to' => $string,
                'subject' => "SugarCRM Inc.: Order #{$order->order_id} Confirmation",
                'email' => $complete_body,
                'name' => 'complete_email',
                'name_link' => 'complete_email_link',
            );
        }
        $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);

        if ($display) {
            return $return;
        }

        return true;
    }

    public static function getWelcomeEmail(&$user)
    {
        require_once('XTemplate/xtpl.php');
        $tpl = new XTemplate('custom/si_custom_files/tpls/moofcart_emails/welcome_email.tpl');
        $signature = $user->first_name . ' ' . $user->last_name;
        if (!empty($user->title)) {
            $signature .= "<br />
{$user->title}";
        }
        if (!empty($user->phone_work)) {
            $signature .= "<br />
Work: {$user->phone_work}";
        }
        if (!empty($user->phone_mobile)) {
            $signature .= "<br />
Mobile: {$user->phone_mobile}";
        }

        // GET GoToMeeting Links
        $getting_started = '';
        $getting_started = MoofCartHelper::getWelcomeMeetingLinks('http://www.sugarcrm.com/crm/category/newsfeeds/welcome-webinars-new-customers/getting-started-sugar/feed', true);

        $welcome_links = '';
        $welcome_links = MoofCartHelper::getWelcomeMeetingLinks('http://www.sugarcrm.com/crm/category/newsfeeds/welcome-webinars-new-customers/feed', true);

        if (empty($welcome_links)) {
            $welcome_links = "<a href='http://www.sugarcrm.com/support'>Future Schedule</a>";
        }

        if (empty($getting_started)) {
            $getting_started = "<a href='http://www.sugarcrm.com/support'>Future Schedule</a>";
        }

        $tpl->assign('getting_started', $getting_started);
        $tpl->assign('welcome_links', $welcome_links);
        $tpl->assign('signature', $signature);
        $tpl->assign('email_address', $user->emailAddress->addresses[0]['email_address']);
        $tpl->parse('main');
        return $tpl->text('main');
    }

    public static function getOrderCompleteEmail(&$order, &$user, $password = '')
    {
        require_once('XTemplate/xtpl.php');
        if (!empty($order->ondemand_instance_name_c)) {
            $tpl = new XTemplate('custom/si_custom_files/tpls/moofcart_emails/order_complete_new.tpl');
	    if($order->ondemand_datacenter_c == 'us') {
		$tpl->assign('od_link', 'https://' . $order->ondemand_instance_name_c . '.sugarondemand.com');
	    }
	    elseif($order->ondemand_datacenter_c = 'emea') {
	    	$tpl->assign('od_link', 'https://' . $order->ondemand_instance_name_c . '.sugaropencloud.eu');
	    }
	    else {
		$tpl->assign('od_link', '');
	    }
        }
        else {
            $tpl = new XTemplate('custom/si_custom_files/tpls/moofcart_emails/order_complete_existing.tpl');
        }


        $sub = reset($order->get_linked_beans('orders_subscriptions', 'Subscription', array(), 0, -1, 0));

        $cart_action = (empty($order->cart_action_c)) ? 'empty' : $order->cart_action_c;

	$tpl->assign('download_key', $sub->subscription_id);

        $tpl->parse('main.' . $cart_action);

        $tpl->assign('password', $password);

        $discount_msg = MoofCartHelper::checkIfUnusedDiscount($order);

        $tpl->parse('discount_msg', $discount_msg);

        $contact = reset($order->get_linked_beans('contacts_orders', 'Contact', array(), 0, -1, 0));

        $products = $order->get_linked_beans('orders_products', 'Product', array(), 0, -1, 0);

		$contracts = $order->get_linked_beans('orders_contracts', 'Contract', array(), 0, -1, 0);

        foreach ($products AS $product) {
        	$pfr = $product->fetched_row;
			
			$pt_id = $product->product_template_id;
			
			$agreements = MoofCartHelper::$products_to_agreements[$pt_id];

			foreach($contracts AS $contract) {
				$type = $contract->moofcart_agreement_type_c;

				if(in_array($type,$agreements)) {
					$pfr['expiration_date'] = $contract->end_date;
					break;
				}
			}
			
					
            $tpl->assign('product', $pfr);
            if (!empty($pfr['expiration_date'])) {
                $tpl->parse('main.products.expiration');
            }
            if (!empty($product->quantity)) {
                $tpl->parse('main.products.quantity');
            }
            $tpl->parse('main.products');
        }

        $tpl->assign('contact', $contact->fetched_row);

        $tpl->parse('main');

        return $tpl->text('main');
    }

    public static function checkIfUnusedDiscount(&$order)
    {
        $a = reset($order->get_linked_beans('accounts_orders', 'Account', array(), 0, 1));


        $show_discount_message = false;

		$today = strtotime(date('Y-m-d'));
		
		if(empty($a->discount_approved_c)) {
			return false;
		}
		
		if(empty($a->discount_percent_c)) {
			return false;
		}

		if(MoofCartHelper::$product_template_categories[$a->producttemplate_id_c] != 'Subscriptions' && MoofCartHelper::$product_template_categories[$a->producttemplate_id1_c] != 'Subscriptions')
		{
			return false;
		}
		
		if((!empty($a->discount_valid_from_c) && (strtotime($a->discount_valid_from_c) > $today)) || $a->discount_no_expiration_c == 1) {
			return MoofCartHelper::getDiscountAvailableEmail($a->discount_percent_c);
		}
		else {
			return false;
		}

		$today = strtotime(date('Y-m-d'));
		
		if(empty($a->discount_approved_c)) {
			return false;
		}
		
		if(empty($a->discount_percent_c)) {
			return false;
		}
		
		if((!empty($a->discount_valid_from_c) && (strtotime($a->discount_valid_from_c) > $today)) || $a->discount_no_expiration_c == 1) {
			return MoofCartHelper::getDiscountAvailableEmail($a->discount_percent_c);
		}
		else {
			return false;
		}        return false;
    }

    // add some fluff to this
    public static function getDiscountAvailableEmail($percent)
    {
    	$msg = null;
        $msg = "After the initial purchase, Company may purchase additional Subscription Users for a fee equal to SugarCRM's then current subscription fee minus {$percent}%.";
        return $msg;
    }

    /**
     * @author Jim Bartek
     * @project moofcart
     * @tasknum 47
     * static method to call to sync an order with NetSuite when the Opp for the order reaches 99% SalesOps Closed
     */

    public static function syncNetSuiteOrder(&$bean, &$order)
    {
        if (empty($bean) || empty($order)) {
            // nothing to do
            return false;
        }

        $workload = array();
        $workload['order_id'] = $order->id;
        $workload['assigned_user_id'] = $order->assigned_user_id;
        $task = '';

        $netsuite_status = 'Pending Finance Review';
        switch ($bean->object_name) {
            case 'Account':
                // account billing address changed [changes = account bean]
                $workload['account_id'] = $bean->id;
                $task = 'NSUpdateCustomerAddresses';
                break;
            case 'Contract':
                // a new contract was uploaded [changes = contracts bean]
                /**
                 * @var $document Documents
                 */
                $document = reset($bean->get_linked_beans('contracts_documents', 'Document', array(), 0, -1, 0));
                $workload['doc_id'] = $document->id;
                $task = 'NSSyncDocument';
                break;
            case 'Document':
                // a new PO was uploaded [changes = documents bean]
                $workload['doc_id'] = $bean->id;
                $task = 'NSSyncDocument';
                break;
            case 'Opportunity':
                // opportunity amount changed [changes = opportunity bean]
                $workload['opp_amount'] = $bean->amount;
                $task = 'NSOppAmountChange';
                break;
            default:
                // lets return false just to make sure nothign happens.
                return false;
                break;
        }

        $client = new GearmanClient();
        $client->addServers();
        $client->doBackground($task, serialize($workload));
        $client->doBackground('NSSetPendingSalesOps', serialize(array('order_id' => $order->id, 'flag' => false)));

        return true;

    }

	public static function getSubscriptionDistGroup($products, $order)
	{
		// there aren't any products
		if (empty($products)) {
		    return false;
		}

		// initialize the filters array
		$filters = array();

		// get the priorities and flip'em so its easier to deal with
		$priority = array_flip(MoofCartHelper::$productToOpportunityPriority);

		// initialize the current priority
		$current_priority = array();

		$data = array();

		// loop over the products

		$sub = reset($order->get_linked_beans('orders_subscriptions', 'Subscription', array(), 0, -1, 0));

		if(count($products) > 0 ) {
			foreach ($products AS $product) {
				if (!empty($product->product_template_id)) {

					$current_priority[$product->product_template_id] = $priority[$product->product_template_id];
					$users = (int) $product->quantity;
					
					$data[$product->product_template_id] = array(
						'users' => $users,
						'Term_c' => $product->term_c,
					);

					// jostrow
					// we only need to change the expiration date if this is a new or renewal Order
					if (empty($order->cart_action_c) || $order->cart_action_c == 'renew') {
						// set the priority
						$contract = reset($product->get_linked_beans('products_contracts', 'Contract', array(), 0, -1, 0));

						// ITR20046 :: jbartek :: verifying that a subscription always has an expiration date
						if(empty($contract->end_date)) {
							if(empty($product->term_c)) {
								$term = 1;
							}
							else {
								$term = $product->term_c;
							}

							if (!empty($sub) && !empty($sub->term_end_date_c)) {
								$start_date = $sub->term_end_date_c;
							}
							else {
								$start_date = date('Y-m-d');
							}

							$end_date = date('Y-m-d', strtotime("{$start_date} + {$term} Years"));
						}
						else {
							$end_date = $contract->end_date;
						}

					}
					else {
						// we still need to return something for the expiration date, so let's return the current date
						// but -- we will first try to normalize the expiration date to the "Term End Date," since that's the most accurate date, and
						// ...sometimes SalesOps temporarily moves the Expiration Date

						if (!empty($sub->term_end_date_c)) {
							$end_date = $sub->term_end_date_c;
						}
						else {
							$end_date = $sub->expiration_date;
						}
					}

					$data[$product->product_template_id]['end_date'] = $end_date;

				}
			}
		}
		else {
			$product = reset($products);

			// set the priority
			// ITR20046 :: jbartek :: verifying that a subscription always has an expiration date
			if(empty($contract->end_date)) {
				if(empty($product->term_c)) {
					$term = 1;
				}
				else {
					$term = $product->term_c;
				}

				$end_date = date('Y-m-d', strtotime("+ {$term} Years"));
			}
			else {
				$end_date = $contract->end_date;
			}

			$contract = reset($product->get_linked_beans('products_contracts', 'Contract', array(), 0, -1, 0));
			$current_priority[$product->product_template_id] = $priority[$product->product_template_id];
			$users = (int) $product->quantity;

			$data[$product->product_template_id] = array(
				'users' => $users,
				'Term_c' => $product->term_c,
				'end_date' => $end_date,
			);
		}

		// sort the array maintaining the keys
		asort($current_priority);

		// get the first one
		reset($current_priority);

		$GLOBALS['log']->info('jbartek :::: ' . __FILE__.'::'.__FUNCTION__. ' - ' . key($current_priority));
		$GLOBALS['log']->info('jbartek :::: ' . __FILE__.'::'.__FUNCTION__. ' - ' . key($current_priority));

		$GLOBALS['log']->info('jbartek :::: ' . __FILE__.'::'.__FUNCTION__. ' - ' . key($current_priority));

		$GLOBALS['log']->info('jbartek :::: ' . __FILE__.'::'.__FUNCTION__. ' - ' . key($current_priority));

		$return['expiration_date'] = $data[key($current_priority)]['end_date'];

		// get the opp type from the first ones key
		$return['distgroup_id'] = MoofCartHelper::$product_to_distgroup[key($current_priority)];


		if (empty($return['distgroup_id'])) {
			return false;
		}


		//ITR 19983
		$return['portal_users'] = 0;

		// if the distgroup is SugarPartner or Enterprise set the portal_users to 50
		if($return['distgroup_id'] == 'c3f0daa9-4139-f19a-d4a0-46e1d39e37b1' || $return['distgroup_id'] == '3adc1f77-b23d-403e-f324-49f88fb37370') {
			$return['portal_users'] = 50;
		}

		// dtam ITR 19376 set quantity when creating Subscription for new partners
		if (array_key_exists(key($current_priority), MoofCartHelper::$partner_product_to_quantity)) {
			$return['quantity'] = MoofCartHelper::$partner_product_to_quantity[key($current_priority)];
		}
		else {
			$return['quantity'] = $data[key($current_priority)]['users'];
		}
		
		return $return;
	}

    public static function sendOrderReceipt(&$order)
    {
        $products = $order->get_linked_beans('orders_products', 'Product', array(), 0, -1, 0);
        $contact = reset($order->get_linked_beans('contacts_orders', 'Contact', array(), 0, -1, 0));
        $cons = $order->get_linked_beans('contacts_orders', 'Contact', array(), 0, -1, 0);

		// if its a partner purchase send the receipt only to the partner because it has margin in it.
		if(!empty($order->account_id_c)) {
			$contact = new Contact;
			$contact->retrieve($order->contact_id_c);
			$cons = array();
		}

        $acc = reset($order->get_linked_beans('accounts_orders', 'Account', array(), 0, 1));
        //$opp = reset($order->get_linked_beans('orders_opportunities', 'Opportunity', array(), 0, -1, 0));

        require_once('XTemplate/xtpl.php');
        $tpl = new XTemplate('custom/si_custom_files/tpls/moofcart_emails/order_receipt.tpl');

        $tpl->assign('order', $order->fetched_row);

		// if margin isn't blank and its a partner purchase
		if(!empty($order->partner_margin_c) && !empty($order->account_id_c)) {
			$tpl->parse('main.partner_margin');
		}
		
		if(!empty($order->tax) && $order->tax > 0) {
			$tpl->parse('main.tax');
		}
		
		if(!empty($order->discount) && $order->discount > 0) {
			$tpl->parse('main.discount');
		}

        $products = $order->get_linked_beans('orders_products', 'Product', array(), 0, -1, 0);

        foreach ($products AS $product) {
        	$pfr = $product->fetched_row;
			$quantity = (int) $pfr['quantity'];
        	$pfr['quantity'] = ($quantity == 0 ) ? 1 : $quantity;
			
		$price = ($pfr['list_price'] <= 0) ? $pfr['discount_price'] : $pfr['list_price'];

       		$pfr['total_price'] = $pfr['quantity'] * $price;

            $tpl->assign('product', $pfr);

            $tpl->parse('main.products.quantity');

            $tpl->parse('main.products');
        }

        $tpl->assign('contact', $contact->fetched_row);
	// ITR 20015 changed the Dear line to be account name and not contact name
	$tpl->assign('account_name', $acc->name);


        $tpl->parse('main');

        $html = $tpl->text('main');


        $user = new User();
        $user->retrieve($order->assigned_user_id);
        if(empty($order->account_id_c)) {
	        foreach ($cons AS $contact) {
	            $email = reset($contact->get_linked_beans('email_addresses_primary', 'EmailAddress', array(), 0, -1, 0));
	            if (!empty($email->email_address) && $order->email != $email->email_address) {
	                $to[] = array('name' => $contact->first_name . ' ' . $contact->last_name,
	                    'email' => $email->email_address,
	                    'id' => $email->id,
	                );
	            }
	        }
	        $to[] = array('name' => $order->billing_first_name . ' ' . $order->billing_last_name,
	            'email' => $order->email
	        );
		}
		else {
	       	$email = reset($contact->get_linked_beans('email_addresses_primary', 'EmailAddress', array(), 0, -1, 0));
            $to[] = array('name' => $contact->first_name . ' ' . $contact->last_name,
                'email' => $email->email_address,
                'id' => $email->id,
            );
		}
        require_once('modules/Administration/Administration.php');
        require_once('include/SugarPHPMailer.php');
        $mail = new SugarPHPMailer();
        $admin = new Administration();

        $admin->retrieveSettings();
        if ($admin->settings['mail_sendtype'] == "SMTP") {
            $mail->Host = $admin->settings['mail_smtpserver'];
            $mail->Port = $admin->settings['mail_smtpport'];
            if ($admin->settings['mail_smtpauth_req']) {
                $mail->SMTPAuth = TRUE;
                $mail->Username = $admin->settings['mail_smtpuser'];
                $mail->Password = $admin->settings['mail_smtppass'];
            }
            $mail->Mailer = "smtp";
            $mail->SMTPKeepAlive = true;
        }
        else {
            $mail->mailer = 'sendmail';
        }

        $from['email'] = $mail->From = 'orders@sugarcrm.com';
        $from['name'] = $mail->FromName = 'orders@sugarcrm.com';
        
        $mail->ContentType = "text/html"; //"text/plain"

        $subject = $mail->Subject = "SugarCRM Inc.: Order #{$order->order_id} Receipt";

        global $sugar_config;

        $mail->Body = $html;

		$GLOBALS['log']->info(__FILE__.'::'.__FUNCTION__. ' - Calling archiveEmail');

        MoofCartHelper::archiveEmail($to, $from, $subject, $html, 'Orders', $order->id, $order->assigned_user_id, $acc->id);


        foreach ($to AS $t) {
            $mail->AddAddress($t['email'], $t['name']);
        }

        $mail->AddBCC("orders-mailbox@sugarcrm.com", "orders-mailbox@sugarcrm.com");

        if (!$mail->send()) {
            $GLOBALS['log']->info("Mailer error: " . $mail->ErrorInfo);
            return false;
        }



//        MoofCartHelper::archiveEmail($to, $from, $subject, $html, 'Opportunities', $opp->id, $order->assigned_user_id, $acc->id);
        return true;

    }


    public static function checkOrderContracts($order_id)
    {
        $o = new Orders();
        $o->retrieve($order_id);

        if (empty($o->fetched_row)) {
            $msg = "No Order Found with ID: {$order_id}";
            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
            return array();
        }

        $contracts = $o->get_linked_beans('orders_contracts', 'Contract', array(), 0, -1, 0);

        $already_signed = array();
        foreach ($contracts AS $contract) {
            if (!empty($contract->moofcart_agreement_type_c)) {
                $already_signed[$contract->moofcart_agreement_type_c] = $contract->moofcart_agreement_type_c;
            }
        }


        sort($already_signed);

        // Get Products
        $products = $o->get_linked_beans('orders_products', 'Product', array(), 0, -1, 0);
        $needed = array();
        foreach ($products AS $product) {
            $pt_id = $product->product_template_id;

            $pt = new ProductTemplate();
            $pt->retrieve($pt_id);
            $category = MoofCartHelper::$product_template_categories[$pt->category_id];

			if (!empty($o->partner_id_c)) {
				$partner_acc = new Account();
				$partner_acc->disable_row_level_security = TRUE;
				$partner_acc->retrieve($o->partner_id_c);

				if (!empty($partner_acc) && !empty($partner_acc->customer_msa_not_required_c) && $category == 'Subscriptions') {
					$skip_msa = TRUE;
				}
			}

            if (isset(MoofCartHelper::$products_to_agreements[$pt_id])) {
		// ITR19866 AND ITR19966 jbartek -> added renew as an action to skip agreements for
                if (($o->cart_action_c == 'renew' || $o->cart_action_c == 'add_users' || $o->cart_action_c == 'upgrade_enterprise') && ($category == 'Subscriptions' || $category == 'Partnerships')) {
                    continue;
                }
				elseif (isset($skip_msa) && $skip_msa === TRUE) {
					unset($partner_acc);
					unset($skip_msa);

					continue;
				}
                else {
                    $needed = array_merge($needed, MoofCartHelper::$products_to_agreements[$pt_id]);
                }
            }
        }

        $needed = array_unique($needed);

        if (!empty($needed) && !empty($already_signed)) {
            foreach ($needed AS $doc) {
                if (!in_array($doc, $already_signed)) {
                    $needed_agreements[] = $doc;
                }
            }
        }
        elseif (!empty($needed) && empty($already_signed)) {
            $needed_agreements = $needed;
        }
        else {
            $needed_agreements = array();
        }

        return $needed_agreements;
    }

/* 
	TODO :: FINISH THIS
   public static function checkProductContracts($product_id) {
	$product = new Product();
	$product->retrieve($product_id);

        if(empty($product->fetched_row)) {
                $msg = "No Product Found with ID: {$product_id}";
                $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
                return array();
        }



	$needed_agreements = array();

	$pt_id = $product->product_template_id;
                // see if the product needs an agreement        
                if(isset(MoofCartHelper::$products_to_agreements[$pt_id])) {
                        // if it does get the contracts for that product
                        $contracts = $product->get_linked_beans('products_contracts', 'Contract', array(), 0, 1, 0);
                        // if there are contracts
                        if(!empty($contracts)) {
                                // loop over them
                                $agreements_received = array();
                                foreach($contracts AS $contract) {
                                        // get all the current agreement types
                                        $agreements_received[] = $contract->moofcart_agreement_type_c;
                                }

                                $needed_agreements = array_intersect(MoofCartHelper::$products_to_agreements[$pt_id], $agreements_received);

                        }
                        // if contracts are empty we need all of'em 
                        else {
                                $needed_agreements = MoofCartHelper::$products_to_agreements[$pt_id];
                        }
                }

	return $needed_agreements;
	
   }
   */


    public static function archiveEmail($to, $from, $subject, $body, $parent_type, $parent_id, $assigned_user_id, $account_id)
    {
        if (empty($to) || empty($parent_type) || empty($parent_id)) {
            return false;
        }

        $email = new Email();

        $email->assigned_user_id = $assigned_user_id;

        $email->account_id = $account_id;

        $email->status = $email->type = 'archived';
        $email->account_id = $account_id;
        $email->parent_type = $parent_type;
        $email->parent_id = $parent_id;

        $email->from_addr = $from['email'];
        $email->from_name = $from['name'];

        $email->name = $email->Subject = $subject;

        foreach ($to AS $t) {
            $email->to_addrs_arr[] = array('email' => $t['email'],
                'name' => $t['name']);

            $string[] = $t['email'];
            $names[] = $t['name'];
            $ids[] = $t['id'];

        }

        //$email_bean->to_addrs = to_html(implode(',', $to));

        $email->to_addrs = implode(',', $string);

        $email->to_addrs_emails = implode(',', $string);
        $email->to_addrs_names = implode(',', $names);
        $email->to_addrs_ids = implode(',', $ids);

        $email->description_html = $body;
        $email->save();

        unset($email);

        return true;
    }


    public static function determineOrderStatus(&$o)
    {

        if (!($o instanceof Orders)) {
            $msg = "Object not instance of Orders";
            $GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
            return false;
        }

        $msg = "Setting Status For: Order {$o->order_id}";
        $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);

        if ($o->payment_method == 'purchase_order') {
            $has_po = false;

            // does it need PO
            $documents = $o->get_linked_beans('orders_documents', 'Document', array(), 0, -1, 0);
            foreach ($documents AS $document) {
                if ($document->category_id == 'po') {
                    $has_po = true;
                }
            }
            if ($has_po == false) {
                $msg = "Setting Status To pending_po For: Order {$o->order_id} - Either no documents - OR - document without category_id of po";
                $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
                return 'pending_po';
            }
        }

        $msg = "Calling MoofCartHelper::checkOrderContracts -- To figure out if pending_contract for Order: {$o->order_id}";
        $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);


        $needed_agreements = MoofCartHelper::checkOrderContracts($o->id);

        if (empty($needed_agreements)) {
            // no agreements retruned
            $msg = "No Agreements needed for Order:{$o->order_id} setting status to pending_salesops.";
            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);

            return 'pending_salesops';
        }

        $contracts = $o->get_linked_beans('orders_contracts', 'Contract', array(), 0, -1, 0);
        /*
                $clickthru_or_echosign = array();
                $other_agreements = array();
                foreach($contracts AS $c) {
                    if($c->signing_method_c == 'clickthru' || $c->signing_method_c == 'echosign') {
                        $clickthru_or_echosign[] = $c->id;
                    }
                    else {
                        $other_agreements[] = $c->id;
                    }
                }
                // if its credit card and all the agreements
                if($o->payment_method == 'credit_card' && (!empty($clickthru_or_echosign) && empty($other_agreements)) {
                    $msg = "No Agreements needed for CC for Order:{$o->order_id} setting status to pending_salesops.";
                    $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);

                    return 'pending_salesops';
                }
        */
        if (!empty($needed_agreements)) {
            $msg = "Agreements have been found for Order: {$o->order_id} setting status pending_contract";
            $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
            return 'pending_contract';
        }

        $msg = "No Agreements needed and No PO needed for Order:{$o->order_id} setting status to pending_salesops.";
        $GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);

        return 'pending_salesops';
    }

    public static function getGearmanServers()
    {
        $path = dirname(__FILE__);

        if (strpos($path, 'moofcart-si.sjc.sugarcrm.pvt')) {
            $env = "development";
        } else if (strpos($path, 'stage.sugarinternal.sugarondemand.com')) {
            $env = "staging";
        } else {
            $env = "production";
        }

        $ret = parse_ini_file($path . '/gearman_config.ini', true);
        return $ret[$env]['servers'];
    }
	// ITR20168 :: jbartek
	public static function isOrderInSI($order_id) {
		$order_id = (int) $order_id;
		if($order_id <= 0) {
			return false;
		}

		$query = "SELECT * FROM orders LEFT JOIN orders_cstm ON orders.id = orders_cstm.id_c WHERE order_id = {$order_id}";
	
		$qry = $GLOBALS['db']->query($query);
		
		$row = $GLOBALS['db']->fetchByAssoc($qry);
		if(empty($row)) { return false; }

		return true;
	}

	// ITR20168 :: jbartek
	public function processMoofWorkload($workload) {
		$gc = new GearmanClient();
		$server = self::getGearmanServers();
		$gearman_worker = self::$moofOrderWorker;
		//add the servers here!
		$gc->addServers($server);
		$gc->doBackground($gearman_worker, serialize($workload));
	}

	// send an email
	// ITR19203 jbartek
	public function sendAnEmail($to,$from,$subject,$body) {
            require_once('modules/Administration/Administration.php');
            require_once('include/SugarPHPMailer.php');
            $mail = new SugarPHPMailer();
            $admin = new Administration();

            $admin->retrieveSettings();
            if ($admin->settings['mail_sendtype'] == "SMTP") {
                $mail->Host = $admin->settings['mail_smtpserver'];
                $mail->Port = $admin->settings['mail_smtpport'];
                if ($admin->settings['mail_smtpauth_req']) {
                    $mail->SMTPAuth = TRUE;
                    $mail->Username = $admin->settings['mail_smtpuser'];
                    $mail->Password = $admin->settings['mail_smtppass'];
                }
                $mail->Mailer = "smtp";
                $mail->SMTPKeepAlive = true;
            }
            else {
                $mail->mailer = 'sendmail';
            }

            $mail->From = $from['email'];
            $mail->FromName = $from['name'];

            $mail->Subject = $subject;

            global $sugar_config;

        $mail->ContentType = "text/html"; //"text/plain"


            $mail->Body = $body;

            foreach ($to AS $t) {
                $mail->AddAddress($t['email'], $t['name']);
            }


            if (!$mail->send()) {
                $GLOBALS['log']->info("Mailer error: " . $mail->ErrorInfo);
                return false;
            }


	}

}
