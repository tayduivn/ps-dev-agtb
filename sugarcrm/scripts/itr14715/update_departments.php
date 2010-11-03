<?php

chdir('../..');
define('sugarEntry', true);

require_once('include/entryPoint.php');

global $current_user;
$current_user = new User();
$current_user->getSystemUser();

$arrItUsers = array(
    '1da41340-a225-6b35-3f65-4a0c9d905873',
    '745c0a32-f513-2dd4-bfc2-4b562daca291',
    '2607211a-ec6d-1d4a-7b28-4856ce37b7a4',
    '1b850a81-fefd-da09-007d-462d2e7a6e96',
    '83edccce-6fed-2d37-f61b-47cef7d11f94',
    'd72a6542-b901-7d28-a9ba-47b3757579ae',
    '5fd690b9-bb2b-d463-74a8-4701540e651c',
    '59032718-2e2a-3d6f-34c1-498cae341119',
    '4ec24c05-a913-6717-1241-46aef51489fb',
    'c57bc6bf-0ae3-2659-6c92-4bfeb89f11ec',
    '94e570ab-089d-0b10-45b6-4c0831c3b0b7',
);

$arrSiUsers = array(
    '9f0a0d88-aac9-aedf-a588-44481726f89d',
    'c2d60c7e-90e3-4cc7-f169-4bf5a26810cb',
    '82ab35b7-655e-e3e5-1318-447dffe09d54',
    '4bc5df9e-2c6c-17f7-8531-48b304a90ad4',
    '290dddad-c592-87ad-a837-414790743628',
    'e6c7d4e5-dd51-9b70-daeb-4b4cf394a683',
    '3493d761-517d-6bea-134f-4147907cf605',
    '3627d5b3-d7d6-bafb-add6-48f6641a90c4',
    '1a06f6e0-9afc-50d3-8c58-433835ff0552',
);

$arrOdUsers = array(
    '5e892a55-4449-b534-9be4-477bf9935333',
    'd624760c-78cb-1a37-3172-421f56e40d98',
    'bc693473-5e13-b0b9-8fb7-48d7dae94277',
    '284e2c41-d1fa-0e27-c86c-4b90502f2cba',
    '225711f0-48e8-4683-ae69-4b742ec5e062',
);


$itrequests = $GLOBALS['db']->query("SELECT id, itrequest_number, assigned_user_id, id_c
    FROM itrequests
    LEFT JOIN itrequests_cstm ON id_c = id
    WHERE deleted = 0
    ORDER BY itrequest_number ASC LIMIT 8000 OFFSET 16000"
);

$totals = array(
    'it' => 0,
    'ia' => 0,
    'od' => 0,
    'sk' => 0
);

while ($itr = $GLOBALS['db']->fetchByAssoc($itrequests)) {
    // test for it user
    echo "Starting ITR: #" . $itr['itrequest_number'] . '...';
    /*if(empty($itr['id_c'])) {
        // no custom row so lets create one
        $GLOBALS['db']->query("INSERT INTO itrequests_cstm (id_c) VALUES ('" . $itr['id'] . "')");
    }*/
    
    if (in_array($itr['assigned_user_id'], $arrItUsers, true)) {
        echo "Assigned to an IT user...";
        $itRequest = new ITRequest();
        $itRequest->retrieve($itr['id']);
        $itRequest->department_c = 'it';


        switch ($itRequest->subcategory) {
            case "Email":
                $itRequest->department_category_c = "IT_Email";
                break;
            case "Applications":
            case "Operating Systems":
                $itRequest->department_category_c = "IT_Software";
                break;
            case "Hardware":
                $itRequest->department_category_c = "IT_Computer";
                break;
            case "Printers":
                $itRequest->department_category_c = "IT_Printing";
                break;
            case "Phones":
                $itRequest->department_category_c = "IT_Phone";
                break;
            case "Network":
                $itRequest->department_category_c = "IT_Network";
                break;
            default:
                $itRequest->department_category_c = 'IT_Other';
                break;

        }

        echo "Saving...";
        $totals['it']++;
        $itRequest->save(false);
        // we have the it user
        // map everything to IT_Other
    } else if (in_array($itr['assigned_user_id'], $arrSiUsers, true)) {
        $itRequest = new ITRequest();
        $itRequest->retrieve($itr['id']);
        echo "Assigned to an Internal Apps User...";
        // si user
        $itRequest->department_c = 'internal';
        if ($itRequest->category == 'Online Services' || $itRequest->subcategory == 'CMS') {
            $itRequest->department_category_c = 'IS_DotCom';
        } else {
            // default everything that is not Online Services to SugarIntenral
            $itRequest->department_category_c = 'IS_SugarInternal';
        }

        switch ($itRequest->subcategory) {
            case "Go_Green_Project":
                $itRequest->project_c = 'SI_Proj_GoGreen';
                break;
            case "Partner_Lead_Conflict":
                $itRequest->project_c = 'SI_Proj_PLC';
                break;
            case "MoofCart":
                $itRequest->project_c = 'SI_Proj_MoofCart';
                break;
        }
        echo "Saving...";
        $totals['ia']++;
        $itRequest->save(false);
    } else if (in_array($itr['assigned_user_id'], $arrOdUsers, true)) {
        $itRequest = new ITRequest();
        $itRequest->retrieve($itr['id']);
        // od user
        echo "Assigned to an Ondemand User user...";
        $itRequest->department_c = 'operations';

        /**
         * CK's Rules
         *
         * nothing will map to System/Service access
         * Existing* as well as Accoutn Restore From Backup map to Existing
         * and the rest can probably all go to System Support
         */
        switch ($itRequest->subcategory) {
            case "Existing Account Upgrades":
            case "Existing Account Troubleshooting":
            case "Existing Account Customer Service":
            case "Existing Account Licensing":
            case "Account Restore From Backup":
                $itRequest->department_category_c = 'Ops_OnDemandSupport';
                break;
            default:
                $itRequest->department_category_c = 'Ops_SysSupport';
        }

        echo "Saving...";
        $totals['od']++;
        $itRequest->save(false);
    } else {
        // just ignore it.
        $totals['sk']++;
        echo "Skipped...";
    }

    echo PHP_EOL;

    unset($itRequest);
}

echo "Totals: " . PHP_EOL . "  - IT: " . $totals['it'] . PHP_EOL . "  - IA: " . $totals['ia'] . PHP_EOL . "  - OD: " . $totals['od'] . PHP_EOL . "  - SK: " . $totals['sk'] . PHP_EOL;
