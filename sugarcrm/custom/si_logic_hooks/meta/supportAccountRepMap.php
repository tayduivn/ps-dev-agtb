<?php

/*
+--------------------------------------+-----------+
| id                                   | user_name |
+--------------------------------------+-----------+
| 1203b541-ef1a-a089-aa25-43a5f267e783 | mfleeger  | 
| ec9482ab-4e2e-765b-526e-465f4dbeb443 | jtichenor | 
| 2696fab0-bfe1-da9d-2818-46a14aff712d | dreverri  | 
| dd3773f7-031d-4180-2c21-46ccf271b4bb | nithya    | 
| 4d3fabcd-f98f-64b4-ec4e-42828344f2e4 | support   | 
| 1541dae9-d87f-45f9-75f8-461d776df963 | kbrill    | 
| 55cf4e63-0d32-83f2-4e30-42dc5314c316 | matt      | 
| 19572b2b-0b88-44cb-4e43-4689489fde84 | craffle   | 
| 78337945-6b7c-5786-9381-44313895da01 | sharon    | 
| 8984c5a0-8229-ef61-9908-42518864b933 | jason     | 
| 929412a0-6d51-429e-a484-466479c74311 | ase-team  | 
| b7d892aa-2f3f-3672-f565-448d759760f4 | lori      | 
| b666e0e9-5a96-3e3e-c8aa-45e717f638cf | dkosy     | 
| ef79b2ea-f7b9-6942-fb1a-45481a914260 | tbrennan  | 
| e1491ad1-92b8-4adb-23ba-470fa249c625 | bgomes    |
| 9683e668-4804-4b62-0f0c-4b0d6e8de1fe | Matt B. | 
| 2696fab0-bfe1-da9d-2818-46a14aff712d | Reverri | 
| a5da4c1c-fb25-118d-6150-4947f5b2bb07 | Christian | 
| b666e0e9-5a96-3e3e-c8aa-45e717f638cf | Kosy | 
| 4ccb4931-7dda-6a0d-ddac-4ac680b3379a | Freddy | 
| d3087151-5278-974a-d07f-47755a4b5ccc | Erin | 
| 9f8c28a1-bb99-d8bd-830b-49f212110d1e | Geno | 
| 5e0686ad-af6e-335d-0463-47f11e474e75 | Bill | 
 
+--------------------------------------+-----------+
*/

$repToAccountMap = array(
// PAST MAPPINGS
//	'c115e8cb-6cbc-504d-ac6c-45ca59724e44' => '1541dae9-d87f-45f9-75f8-461d776df963', // Carrier Access -> Ken Brill
//      '277c83af-2bdf-ccbc-67db-460378aa3784' => '19572b2b-0b88-44cb-4e43-4689489fde84', // GM Nameplate -> Chris Raffle
//      'bc166418-7b38-37bb-9ffd-4738fad207c2' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Practice One -> Jeff Tichenor
//      'afadabd0-55d6-9ee3-ef0c-461e5218ba62' => '19572b2b-0b88-44cb-4e43-4689489fde84', // FitLinxx -> Chris Raffle
//      '689cab44-1081-b7ac-ff7a-44e0b6d33f2a' => '1541dae9-d87f-45f9-75f8-461d776df963', // Maginet -> Ken Brill
//      '9f9a3b00-e6eb-6813-80bd-45c00a5d5d87' => 'e1491ad1-92b8-4adb-23ba-470fa249c625', // IONA Technologies - Breno Gomes
//      '7f700c30-ef35-da5d-f815-43e0f0cdc77f' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // PKF -> Jeff Tichenor
//      'ab6f4b2e-55fd-5fc1-c8fa-453e526fdda1' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Root Consulting (dba Aaxis) -> Jeff Tichenor
//      '6f9f02c0-1785-330e-6a53-41659fb9db0d' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Pride Industries -> Jeff Tichenor
//      '4fc70b57-ece8-8a8a-6263-4579da4c2fa6' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // NameMedia -> Jeff Tichenor
//      'd5f089bd-576a-deb7-5abf-470cf00eec9b' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Jupiterimages -> Jeff Tichenor
//      '1dcdf29f-172c-4682-1a2e-45df5cfd9c9f' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Ullink -> Jeff Tichenor 
//      '2b1d1eb1-2367-1f73-7d51-447e0a4f360f' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Interbay -> Jeff Tichenor
//	'69086720-ba9c-5f33-147e-455cd393de9f' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Bayview Financial -> Jeff Tichenor
//	'80508dba-b4ae-2baf-d9a3-47148af1c062' => 'e1491ad1-92b8-4adb-23ba-470fa249c625', // Interbay UK -> Breno Gomes
//	'587cc34d-b16b-9606-be58-46d7449f12da' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Armstrong & Associates -> Jeff Tichenor
//'257a7aae-5bab-8619-71d2-45c1154d0417' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Acusis Software -> Jeff Tichenor
//'6ab1ba75-793d-dd3e-ee01-475671bd06ba' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // APLUS Inc. (Shinsei) -> Jeff Tichenor
//'9918a213-edd1-4009-0bcf-4891daac009f' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Apple Inc. -> Jeff Tichenor
//'6df0e24f-50d7-05aa-ca58-44b3e5002424' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Ascent Media -> Jeff Tichenor
//'a4698789-3ffc-2c84-3c5f-432b50bf5a7f' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Bristol Myers Squibb -> Jeff Tichenor
//'e98831f4-e677-7b03-d9a6-46d0b16050b2' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Convatec UK -> Jeff Tichenor
//'ae3ba7f3-993a-e81b-004d-434cb6bad0a0' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Convatec/BMS -> Jeff Tichenor
//'60bf0790-e545-a3f1-6b95-47c70b89ab78' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // DNI Open Source Center -> Jeff Tichenor
//'ed108233-1c7d-b2ff-5c43-46a7701bb30c' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Exigen Services -> Jeff Tichenor
//'49005cf6-b835-d664-cd52-4713dedb510d' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Florida Penninsula Managers -> Jeff Tichenor
//'4e78ec11-823d-6b3a-a769-46cf63591fcc' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // HealthEquity -> Jeff Tichenor
//'4555e651-136e-5f0e-0d54-452e466b3633' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // IBM (CJ) -> Jeff Tichenor 
//'7ead848b-8fc6-418b-a83c-469fd2490b30' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Intercontinental Hotels Group -> Jeff Tichenor
//'eb59ca98-0935-19b7-e90d-46f4207100d4' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Paragon Computer -> Jeff Tichenor
//'f1afbbc4-310f-275b-81ce-45949bc22dee' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Shinsei IT -> Jeff Tichenor
//'bee81954-91e3-5064-8f4a-45cb4fd8937b' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // SoftCom Technology -> Jeff Tichenor
//'3a13018a-a958-b09c-0207-4489bcd124d5' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // White Springs -> Jeff Tichenor
//'28dffadd-a512-b161-216f-451c02ca7d87' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Zenoss -> Jeff Tichenor
//'e016dd5c-4cba-9b1e-7038-496539d68546' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Family Search -> Jeff Tichenor
//'b499d5ea-939f-0efa-500c-496cc57fee16' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // 1ShoppingCart.com -> Jeff Tichenor
//'287aaf79-9477-72eb-babe-4a6655196a3d' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Australian Society of Plastic Surgeons Incorporated -> Jeff Tichenor
//'9e7cbdff-9c41-a21b-c0d4-49e8a2480826' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // HomCastle Mortgage -> Jeff Tichenor
//'26dd03c0-65a8-22f1-e6ba-46fbf61bb5d7' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Bright Idea  -> Jeff Tichenor
//
// SPECIAL HANDLING, NOT PREMIUM, will be removed as soon as account is deactivated
//'27acd5d6-7344-3b86-dce1-463627ffec55' => '8984c5a0-8229-ef61-9908-42518864b933', // Santa Clarita Web Services -> Jason Nassi
//'82c224ec-d3ac-d940-a253-4ba8fe4fb9e8' => '19572b2b-0b88-44cb-4e43-4689489fde84', //Plumchoice.com -> Chris Raffle
//'b17e29d7-73ab-b178-c166-4b7425c0ff34' => 'a5da4c1c-fb25-118d-6150-4947f5b2bb07', //New England Energy Management, Inc -> Christian Weaver
//
//
//
//
// CURRENT MAPPINGS FOR PREMIUM ACCOUNTS
'257a7aae-5bab-8619-71d2-45c1154d0417' => 'b666e0e9-5a96-3e3e-c8aa-45e717f638cf', // Acusis Software -> David Kosy
'6ab1ba75-793d-dd3e-ee01-475671bd06ba' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // APLUS Inc. (Shinsei) -> Jeff Tichenor
'9918a213-edd1-4009-0bcf-4891daac009f' => '4ccb4931-7dda-6a0d-ddac-4ac680b3379a', // Apple Inc. -> Freddy F.
'6df0e24f-50d7-05aa-ca58-44b3e5002424' => '4ccb4931-7dda-6a0d-ddac-4ac680b3379a', // Ascent Media -> Freddy F.
'a4698789-3ffc-2c84-3c5f-432b50bf5a7f' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Bristol Myers Squibb -> Jeff Tichenor
'e98831f4-e677-7b03-d9a6-46d0b16050b2' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Convatec UK -> Jeff Tichenor
'ae3ba7f3-993a-e81b-004d-434cb6bad0a0' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Convatec/BMS -> Jeff Tichenor
'c3700f94-99a6-bc6d-81d7-45afc6e4bdb0' => '1541dae9-d87f-45f9-75f8-461d776df963', // Deakin University -> Ken Brill
'60bf0790-e545-a3f1-6b95-47c70b89ab78' => '1541dae9-d87f-45f9-75f8-461d776df963', // DNI Open Source Center -> Ken
'ed108233-1c7d-b2ff-5c43-46a7701bb30c' => '1541dae9-d87f-45f9-75f8-461d776df963', // Exigen Services -> Ken
'49005cf6-b835-d664-cd52-4713dedb510d' => '9f8c28a1-bb99-d8bd-830b-49f212110d1e', // Florida Penninsula Managers -> Geno
//'4e78ec11-823d-6b3a-a769-46cf63591fcc' => '9683e668-4804-4b62-0f0c-4b0d6e8de1fe', // HealthEquity -> Matt B.
'4555e651-136e-5f0e-0d54-452e466b3633' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // IBM (CJ) -> Jeff Tichenor 
'7ead848b-8fc6-418b-a83c-469fd2490b30' => '1541dae9-d87f-45f9-75f8-461d776df963', // Intercontinental Hotels Group -> Ken
'eb59ca98-0935-19b7-e90d-46f4207100d4' => '1541dae9-d87f-45f9-75f8-461d776df963', // Paragon Computer -> Ken
'f1afbbc4-310f-275b-81ce-45949bc22dee' => 'ec9482ab-4e2e-765b-526e-465f4dbeb443', // Shinsei IT -> Jeff Tichenor
'bee81954-91e3-5064-8f4a-45cb4fd8937b' => 'a5da4c1c-fb25-118d-6150-4947f5b2bb07', // SoftCom Technology -> Christian Weaver
'dccf3f81-da1b-ad3a-67a7-439dbc7dbcb9' => '19572b2b-0b88-44cb-4e43-4689489fde84', // Tiscali International Network BV -> Chris Raffle
'555b9b15-0fa4-4d16-956b-452d86ea6da4' => '19572b2b-0b88-44cb-4e43-4689489fde84', // Vodafone -> Chris Raffle
'3a13018a-a958-b09c-0207-4489bcd124d5' => 'a5da4c1c-fb25-118d-6150-4947f5b2bb07', // White Springs -> Christian Weaver
'2261ab55-07ef-3c7e-e787-4b1d65d2c63b' => 'a5da4c1c-fb25-118d-6150-4947f5b2bb07', // Zenoss -> Christian Weaver
'e016dd5c-4cba-9b1e-7038-496539d68546' => 'a5da4c1c-fb25-118d-6150-4947f5b2bb07', // Family Search -> Christian Weaver
'b499d5ea-939f-0efa-500c-496cc57fee16' => '9683e668-4804-4b62-0f0c-4b0d6e8de1fe', // 1ShoppingCart.com -> Matt B.
'287aaf79-9477-72eb-babe-4a6655196a3d' => 'b666e0e9-5a96-3e3e-c8aa-45e717f638cf', // Australian Society of Plastic Surgeons Incorporated -> David Kosy
'25447429-66ce-5d78-4dee-46c36dbdc74d' => 'b666e0e9-5a96-3e3e-c8aa-45e717f638cf', // Medicity -> David Kosy
'9e7cbdff-9c41-a21b-c0d4-49e8a2480826' => '9f8c28a1-bb99-d8bd-830b-49f212110d1e', // HomCastle Mortgage -> Geno
'26dd03c0-65a8-22f1-e6ba-46fbf61bb5d7' => '9f8c28a1-bb99-d8bd-830b-49f212110d1e', // Bright Idea  -> Geno
'c1bdc2b9-e115-40d7-0248-434d83b08e7a' => '4ccb4931-7dda-6a0d-ddac-4ac680b3379a', // MedSphere Systems Corporation -> Freddy Feliciano
'9a8cdd59-48f2-49f3-015d-4b229c54f5ca' => '4ccb4931-7dda-6a0d-ddac-4ac680b3379a', // SIMOS Insourcing Solutions -> Freddy Feliciano
'26dd03c0-65a8-22f1-e6ba-46fbf61bb5d7' => '5ced86a1-0f21-f3ee-6af1-4c098ee3cc39', // Bright Idea  -> William Feleciano
'91a1c61a-f667-4026-fa03-4a5e4265e6c2' => '19572b2b-0b88-44cb-4e43-4689489fde84', // Etrali Singapore Pte Ltd -> Chris Raffle
'31e50cd3-bee3-7bec-0181-4b040c95d4b9' => '257b8af7-0e06-6bfc-7e3e-48078c2b6d41', // FSR Group  -> Dan Godwin
'956d6a04-bb4e-c0a7-6aef-4b623718da5e' => '4ccb4931-7dda-6a0d-ddac-4ac680b3379a', // IP Centrics Corporation -> Freddy Feliciano
'61f0ecfc-07c3-d804-9379-498a13c22051' => '1541dae9-d87f-45f9-75f8-461d776df963', // Fox Interactive Media  -> Ken Brill
'2d35326c-8eaf-1fb1-f8ac-4203fc319057' => '5ced86a1-0f21-f3ee-6af1-4c098ee3cc39', // DFJ -> William Feleciano
'bf1131fe-3e5f-7c68-9d8b-47ed2eea2d61' => '1541dae9-d87f-45f9-75f8-461d776df963', // : Linux Foundation -> Ken Brill
'9ab25a46-85a4-c0f7-146c-4afaec6db55f' => '19572b2b-0b88-44cb-4e43-4689489fde84', // Gerber Life Insurance Company -> Chris Raffle
'8e51a965-e508-5080-36b5-4be0480ffd85' => '5614e973-b6bc-72c2-6076-4c098a3d2e04', //safaricom -> Weatherford Knowles
'9f03718e-7967-c535-68f6-4c5055fa23f2' => '5ced86a1-0f21-f3ee-6af1-4c098ee3cc39', //Jaccard -> William Feleciano
'd6d0937d-09f7-8741-ead0-43702a16cb6d' => '5ced86a1-0f21-f3ee-6af1-4c098ee3cc39', //Alfresco Software -> William Feleciano
'44807972-88f6-34b0-7d3a-4ba7be6f31a8' => '4ccb4931-7dda-6a0d-ddac-4ac680b3379a', // Firehole Technologies -> Freddy Feliciano
'f3143372-e49f-ea8c-323f-4a083ebfb7b3' => '4ccb4931-7dda-6a0d-ddac-4ac680b3379a', // Alpari -> Freddy Feliciano
'8e51a965-e508-5080-36b5-4be0480ffd85' => '5614e973-b6bc-72c2-6076-4c098a3d2e04', //safaricom -> Weatherford Knowles
);

?>
