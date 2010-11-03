<?php

  // BEGIN SUGARINTERNAL CUSTOMIZATION - Sadek - get team_membership functions
function get_team_in_clause($user_id = ''){
    if(empty($user_id)){
        global $current_user;
        $user_id = $current_user->id;
    }

    $team_id_list = get_team_membership_array($user_id);

    $team_id_string = '';
    if(!empty($team_id_list)){
        $team_id_string = implode("','", $team_id_list);
        $team_id_string = "('$team_id_string')";
    }

    return $team_id_string;
}

function get_team_membership_array($user_id){
    $query = "select team_id from team_memberships where team_memberships.user_id = '$user_id' AND team_memberships.deleted=0";
    $res = $GLOBALS['db']->query($query);

    $team_id_list = array();

    while($row = $GLOBALS['db']->fetchByAssoc($res)){
        $team_id_list[] = $row['team_id'];
    }

    return $team_id_list;
}
// END SUGAR INTERNAL CUSTOMIZATION

function getSugarInternalClosedStages($format = 'array'){
	$closed_stages = array(
		'Closed Won',
		'Sales Ops Closed',
		'Finance Closed',
	);
	
	$return_data = false;

	switch($format){
		case 'array':
			$return_data = $closed_stages;
			break;
		case 'in_clause':
			$return_data = "('" . implode("','", $closed_stages) . "')";
			break;
		default:
			$return_data = false;
			break;
	}
	
	return $return_data;
}

function getSugarInternalSubscriptionCustomerTypes($format = 'array', $names = false){
	$cust_sub_types = array(
		'6ff357cb-c92f-f984-82ef-46e1d3ad50bb' => 'OnDemand_Outlook_Plugin',  
		'4f487a8f-8809-ecaa-5385-46e1d3719860' => 'OnDemand_Sugar_Enterprise',
		'9f2dcf49-c631-3497-b5f5-481ba0038f1e' => 'SugarDCE',								 
		'6d8b74a7-05b3-1912-6cdf-46e1d32494a4' => 'SugarEnterprise',				  
		'4521e4d5-025f-4325-4e5f-46e1d3e39da3' => 'SugarEnterpriseInstaller', 
		'c3f0daa9-4139-f19a-d4a0-46e1d39e37b1' => 'SugarPartner',						 
		'6429d839-f494-2624-ca95-46e1d3b57a82' => 'SugarPro',								 
		'e5c53d84-5cb4-0f9e-db4d-46e1d3585a6a' => 'SugarProInstaller',				
		'2c62574d-7d59-dd78-0d75-46e1d3f0f37c' => 'WebExOnDemand',						
	);
	
	if(!$names){
		$cust_sub_types = array_flip($cust_sub_types);
	}
	
	$return_data = false;

	switch($format){
		case 'array':
			$return_data = $cust_sub_types;
			break;
		case 'in_clause':
			$return_data = "('" . implode("','", $cust_sub_types) . "')";
			break;
		default:
			$return_data = false;
			break;
	}
	
	return $return_data;
}

function getSugarInternalOpenCaseStatuses($format = 'array'){
    $open_statuses = array(
        'New',
        'Assigned',
        'Pending Input',
        'Pending Internal Input',
        'Re-Opened',
        'Closed Pending',
    );

    $return_data = false;

    switch($format){
        case 'array':
            $return_data = $open_statuses;
            break;
        case 'in_clause':
            $return_data = "('" . implode("','", $open_statuses) . "')";
            break;
        default:
            $return_data = false;
            break;
    }

    return $return_data;
}

function getSugarInternalLeadSystemStatuses($format = 'array'){
	$system_statuses = array(
		'Auto Welcome',
				 'Converted',
		'Assigned - Existing',
		'Assigned - Overflow',
		'Converted - Existing',
	);
	
	$return_data = false;

	switch($format){
		case 'array':
			$return_data = $system_statuses;
			break;
		case 'in_clause':
			$return_data = "('" . implode("','", $system_statuses) . "')";
			break;
		default:
			$return_data = false;
			break;
	}
	
	return $return_data;
}

function getBugClosedStatuses($format = 'array'){
	$bug_statuses = array(
		'Closed',
		'Rejected',
	);
	
	$return_data = false;
	
	switch($format){
		case 'array':
			$return_data = $bug_statuses;
			break;
		case 'in_clause':
			$return_data = "('" . implode("','", $bug_statuses) . "')";
			break;
		default:
			$return_data = false;
			break;
	}
	
	return $return_data;
}

function inDomainExclusionList($email, $full_email_address = false){
	if($full_email_address){
		// String manipulation to convert the full email address to just the domain
		$email_domain = substr_replace($email, '', 0, strpos($email, '@'));
		$email = substr($email_domain, 1);
	}
	
	$domains = getDomainExclusionList();
	return in_array($email, $domains);
}

function getDomainExclusionList(){
	$domains = array(
		'111mail.com',
		'123iran.com',
		'1-usa.com',
		'2die4.com',
		'37.com',
		'420email.com',
		'4degreez.com',
		'4-music-today.com',
		'5.am',
		'5005.lv',
		'8.am',
		'a.org.ua',
		'abha.cc',
		'accountant.com',
		'actingbiz.com',
		'adexec.com',
		'africamail.com',
		'agadir.cc',
		'ahsa.ws',
		'ajman.cc',
		'ajman.us',
		'ajman.ws',
		'albaha.cc',
		'alex4all.com',
		'alexandria.cc',
		'algerie.cc',
		'allergist.com',
		'allhiphop.com',
		'alriyadh.cc',
		'alumnidirector.com',
		'amman.cc',
		'anatomicrock.com',
		'animeone.com',
		'anjungcafe.com',
		'aqaba.cc',
		'arar.ws',
		'archaeologist.com',
		'arcticmail.com',
		'artlover.com',
		'asia.com',
		'asiancutes.com',
		'aswan.cc',
		'a-teens.net',
		'ausi.com',
		'australiamail.com',
		'autoindia.com',
		'autopm.com',
		'baalbeck.cc',
		'bahraini.cc',
		'banha.cc',
		'barriolife.com',
		'b-boy.com',
		'beautifulboy.com',
		'berlin.com',
		'bicycledata.com',
		'bicycling.com',
		'bigmailbox.net',
		'bikerheaven.net',
		'bikerider.com',
		'bikermail.com',
		'billssite.com',
		'bizerte.cc',
		'bk.ru',
		'blida.info',
		'bmx.lv',
		'bmxtrix.com',
		'boarderzone.com',
		'boatnerd.com',
		'bolbox.com',
		'bowl.com',
		'buraydah.cc',
		'byke.com',
		'calle22.com',
		'cameroon.cc',
		'catlover.com',
		'catlovers.com',
		'championboxing.com',
		'chatway.com',
		'cheerful.com',
		'chemist.com',
		'chillymail.com',
		'classprod.com',
		'clerk.com',
		'cliffhanger.com',
		'columnist.com',
		'comic.com',
		'company.org.ua',
		'congiu.net',
		'consultant.com',
		'coolmail.ru',
		'corpusmail.com',
		'counsellor.com',
		'cycledata.com',
		'darkforces.com',
		'deliveryman.com',
		'dhahran.cc',
		'dhofar.cc',
		'dino.lv',
		'diplomats.com',
		'dirtythird.com',
		'djibouti.cc',
		'doctor.com',
		'doglover.com',
		'dominican.cc',
		'dopefiends.com',
		'dr.com',
		'draac.com',
		'drakmail.net',
		'dreamstop.com',
		'dublin.com',
		'earthling.net',
		'eclub.lv',
		'egypt.net',
		'e-mail.am',
		'email.com',
		'e-mail.ru',
		'emailfast.com',
		'emails.ru',
		'e-mails.ru',
		'envirocitizen.com',
		'eritrea.cc',
		'eritrea.cc',
		'escapeartist.com',
		'europe.com',
		'execs.com',
		'ezsweeps.com',
		'falasteen.cc',
		'famous.as',
		'financier.com',
		'firemyst.com',
		'fit.lv',
		'freeonline.com',
		'fromru.com',
		'front.ru',
		'fudge.com',
		'fujairah.cc',
		'fujairah.us',
		'fujairah.ws',
		'funkytimes.com',
		'gabes.cc',
		'gafsa.cc',
		'gala.net',
		'gamerssolution.com',
		'gardener.com',
		'gawab.com',
		'gazabo.net',
		'geologist.com',
		'giza.cc',
		'gmail.com',
		'gmx.de',
		'goatrance.com',
		'goddess.com',
		'gohip.com',
		'goldenmail.ru',
		'goldmail.ru',
		'googlemail.com',
		'gospelcity.com',
		'grapemail.net',
		'graphic-designer.com',
		'greatautos.org',
		'guinea.cc',
		'guy.com',
		'hacker.am',
		'hairdresser.net',
		'haitisurf.com',
		'hamra.cc',
		'happyhippo.com',
		'hasakah.com',
		'hateinthebox.com',
		'hebron.tv',
		'homs.cc',
		'hotbox.ru',
		'hotmail.es',
		'hotmail.fr',
		'hotmail.com',
		'hotmail.ru',
		'houseofhorrors.com',
		'hullnumber.com',
		'human.lv',
		'ibra.cc',
		'ihatenetscape.com',
		'iname.com',
		'inbox.ru',
		'inorbit.com',
		'insurer.com',
		'intimatefire.com',
		'iphon.biz',
		'irbid.ws',
		'irow.com',
		'ismailia.cc',
		'jadida.cc',
		'jadida.org',
		'japan.com',
		'jazzemail.com',
		'jerash.cc',
		'jizan.cc',
		'jouf.cc',
		'journalist.com',
		'juanitabynum.com',
		'kairouan.cc',
		'kanoodle.com',
		'karak.cc',
		'khaimah.cc',
		'khartoum.cc',
		'khobar.cc',
		'kickboxing.com',
		'kidrock.com',
		'kinkyemail.com',
		'kool-things.com',
		'krovatka.net',
		'kuwaiti.tv',
		'kyrgyzstan.cc',
		'land.ru',
		'latakia.cc',
		'latchess.com',
		'lawyer.com',
		'lebanese.cc',
		'leesville.com',
		'legislator.com',
		'live.com',
		'list.ru',
		'live.in',
		'lobbyist.com',
		'london.com',
		'lowrider.com',
		'lubnan.cc',
		'lubnan.ws',
		'lucky7lotto.net',
		'lv-inter.net',
		'mad.scientist.com',
		'madinah.cc',
		'madrid.com',
		'maghreb.cc',
		'mail.com',
		'mail.ru',
		'mail15.com',
		'mail333.com',
		'mailbomb.com',
		'manama.cc',
		'mansoura.tv',
		'marillion.net',
		'marrakesh.cc',
		'mascara.ws',
		'meknes.cc',
		'mesra.net',
		'mindless.com',
		'minister.com',
		'mofa.com',
		'moscowmail.com',
		'motley.com',
		'munich.com',
		'muscat.tv',
		'muscat.ws',
		'music.com',
		'musician.net',
		'musician.org',
		'musicsites.com',
		'myself.com',
		'nabeul.cc',
		'nabeul.info',
		'nablus.cc',
		'nador.cc',
		'najaf.cc',
		'narod.ru',
		'netbroadcaster.com',
		'netfingers.com',
		'net-surf.com',
		'nettaxi.com',
		'newmail.ru',
		'nm.ru',
		'nocharge.com',
		'nycmail.com',
		'omani.ws',
		'omdurman.cc',
		'operationivy.com',
		'optician.com',
		'oran.cc',
		'oued.info',
		'oued.org',
		'oujda.biz',
		'oujda.cc',
		'pakistani.ws',
		'palmyra.cc',
		'palmyra.ws',
		'pcbee.com',
		'pediatrician.com',
		'persian.com',
		'petrofind.com',
		'pikaguam.com',
		'pisem.net',
		'pitbullmail.com',
		'planetsmeg.com',
		'pochta.ru',
		'pochtamt.ru',
		'poetic.com',
		'pookmail.com',
		'poormail.com',
		'pop3.ru',
		'popstar.com',
		'portsaid.cc',
		'post.com',
		'presidency.com',
		'priest.com',
		'primetap.com',
		'programmer.net',
		'project420.com',
		'prolife.net',
		'publicist.com',
		'puertoricowow.com',
		'puppetweb.com',
		'qassem.cc',
		'quds.cc',
		'rabat.cc',
		'rafah.cc',
		'ramallah.cc',
		'rambler.ru',
		'rastamall.com',
		'ravermail.com',
		'rbcmail.ru',
		'realtyagent.com',
		'registerednurses.com',
		'relapsecult.com',
		'remixer.com',
		'repairman.com',
		'representative.com',
		'rescueteam.com',
		'rockeros.com',
		'rome.com',
		'safat.biz',
		'safat.info',
		'safat.us',
		'safat.ws',
		'saintly.com',
		'salalah.cc',
		'salmiya.biz',
		'samerica.com',
		'sanaa.cc',
		'sanfranmail.com',
		'scientist.com',
		'seeb.cc',
		'sfax.ws',
		'sharm.cc',
		'sinai.cc',
		'singalongcenter.com',
		'singapore.com',
		'siria.cc',
		'smartstocks.com',
		'smtp.ru',
		'sociologist.com',
		'sok.lv',
		'soon.com',
		'sousse.cc',
		'spam.lv',
		'specialoperations.com',
		'speedymail.net',
		'spells.com',
		'streetracing.com',
		'subspacemail.com',
		'sudanese.cc',
		'suez.cc',
		'superbikeclub.com',
		'superintendents.net',
		'supermail.ru',
		'surfguiden.com',
		'tabouk.cc',
		'tajikistan.cc',
		'tangiers.cc',
		'tanta.cc',
		'tayef.cc',
		'teamster.net',
		'techie.com',
		'technologist.com',
		'teenchatnow.com',
		'tetouan.cc',
		'the5thquarter.com',
		'timor.cc',
		'tiscali.co.uk',
		'tokyo.com',
		'tombstone.ws',
		'troamail.org',
		'tunisian.cc',
		'tunisian.cc',
		'tut.by',
		'tx.am',
		'ua.fm',
		'uaix.info',
		'umpire.com',
		'urdun.cc',
		'vipmail.ru',
		'vitalogy.org',
		'whatisthis.com',
		'whoever.com',
		'winning.com',
		'witty.com',
		'writeme.com',
		'yahoo.com',
		'yahoo.co.in',
		'yanbo.cc',
		'yandex.ru',
		'yemeni.cc',
		'yogaelements.com',
		'yours.com',
		'yunus.cc',
		'zabor.lv',
		'zagazig.cc',
		'zambia.cc',
		'zarqa.cc',
		'zerogravityclub.com',
		'juno.com',
		'me.com',
		'mac.com',
		'gmx.ch',
		'liberot.it',
		'live.dk',
		'optusnet.com.au',
		'bellsouth.net',
		'aol.in',
		'yahoo.fr',
		'163.com',
		'126.com',
		'laposte.net',
		'yahoo.cn',
		'earthlink.com',
		'aol.com',
		'roadrunner.com',
		'live.nl',
		'cox.net',
		'msn.com',
		'hotmail.it',
		'rocketmail.com',
		'mailinator.com',
		'web.de',
		'btconnect.com',
		'yahoo.ca',
		'cantv.net',
		'att.net',
		'wanadoo.fr',
		'cogeco.com',
		'iinet.net.au',
		'T-online.de',
		'nurfuerspam.de',
		'gmx.at',
		'compuserve.com',
		'comcast.net',
		'earthlink.com',
		'yahoo.co.uk',
		'hiwaay.net',
		'freemail.it ',
		'free.fr',
		'lycos.com',
		'yahoo.co.in',
		'ameritech.net',
		'bluewin.ch',
		'netzero.net',
		'live.cn',
		'terra.es',
		'qq.com',
		'ymail.colm',
		'yahoo.de',
		'yahoo.in',
		'yahoo.hk',
		'seznam.cz',
		'rogers.com',
		'aol.it',
		'yahoo.es',
		'uol.com.br',
		'pop.com.br',
		'singnet.com.sg',
		'sify.com',
		'myrealbox.com',
		'hotmail.ca',
		'chello.hu',
		'shaw.ca',
		'terra.com',
		'dodo.com.au',
		'time.net.my',
		'arcor.de',
		'yahoo.gr',
		'in.com',
		'tampabay.rr.com',
		'yahoo.com.au',
		'fibertel.com.ar',
	);
	
	return $domains;
}

function getLeadGroupFromValues($potential_users, $annual_revenue, $lead_group_value, $assigned_user_id){
    $inside_revenue_values = array("under 10M", "10 - 25M", "25 - 99M");
    $inside_potential_values = array("1 - 4", "5 - 10", "11 - 20", "21 - 49", "50 - 75");
    $channel_revenue_values = array("100M - 249M", "250 - 499M", "500M - 1B", "more than 1B");
    $channel_potential_values = array("75 - 100", "101 - 174", "175 - 499", "500 - 999", "more than 1000");

    if($assigned_user_id == '2c780a1f-1f07-23fd-3a49-434d94d78ae5'){
        $lead_group_value = 'Partner';
    }
    else{
        if($lead_group_value == 'Unknown' && $annual_revenue == 'Unknown' && $potential_users == 'Unknown'){
            $lead_group_value = 'Unknown';
        }
        if(in_array($annual_revenue, $inside_revenue_values) || in_array($potential_users, $inside_potential_values)){
            $lead_group_value = 'Inside';
        }
        if(in_array($annual_revenue, $channel_revenue_values) || in_array($potential_users, $channel_potential_values)){
            $lead_group_value = 'Partner';
        }
    }

	return $lead_group_value;
}

function siGetSalesAssignmentMap(&$tp, $scrub = ""){
	$return_values = array();
	//DEE CUSTOMIZATION - LEAD ROUTING TO CHANNEL REPS AND INSIDE SALES REPS BASED ON LEAD GROUP
	if($tp->assigned_user_id == 'c15afb6d-a403-b92a-f388-4342a492003e' || $tp->assigned_user_id == 'bf6f1e6b-f6bf-01e5-69e3-4a833bf57cfd') {
			//IF LEAD GROUP = INSIDE THEN ASSIGNED TO USER = INSIDE SALES
			if((isSixtyMinuteOpp($tp) || $scrub == 'Manual') && ($tp->lead_group_c == 'Inside' || $tp->lead_group_c == 'Unknown')) {		
					$tp->assigned_user_id = 'ee815bc4-5279-a3a1-3ba5-443bdb6c6e94';
					$tp->assigned_user_name = 'Inside_Sales';
			}
			//IF LEAD GROUP = PARTNER THEN TRIGGER NORTH AMERICA AND CANADA CHANNEL ROUTING
			if((isSixtyMinuteOpp($tp) || $scrub == 'Manual') && $tp->lead_group_c == 'Partner' && !empty($tp->primary_address_country)) {
					require('custom/si_custom_files/meta/leadRoutingMeta.php');
					$lr_lead_group_c = $tp->lead_group_c;
					$lr_primary_address_country = $tp->primary_address_country;
					$lr_primary_address_state = $tp->primary_address_state;
					if(!empty($lr_primary_address_state) && !empty($leadBreakdownMap[$lr_lead_group_c][$lr_primary_address_country][$lr_primary_address_state])){
							$tp->assigned_user_id = $leadBreakdownMap[$lr_lead_group_c][$lr_primary_address_country][$lr_primary_address_state];
					}
			}
			$return_values['assigned_user_id'] = $tp->assigned_user_id;
	}
	//END DEE CUSTOMIZATION
	
	// BEGIN SADEK SUGARINTERNAL CUSTOMIZATION - IT REQUEST 11092 - Add Channel Routing based on Leads_Partner assignment
	if($tp->assigned_user_id == '2c780a1f-1f07-23fd-3a49-434d94d78ae5' && !empty($tp->primary_address_country) && !empty($tp->primary_address_state)){
		require('custom/si_custom_files/meta/leadRoutingMeta.php');
		$lr_lead_group_c = 'Partner';
		$lr_primary_address_country = $tp->primary_address_country;
		$lr_primary_address_state = $tp->primary_address_state;
		if(!empty($leadBreakdownMap[$lr_lead_group_c][$lr_primary_address_country][$lr_primary_address_state])){
			$tp->assigned_user_id = $leadBreakdownMap[$lr_lead_group_c][$lr_primary_address_country][$lr_primary_address_state];
		}
	
		$return_values['assigned_user_id'] = $tp->assigned_user_id;
	}
	// END SADEK SUGARINTERNAL CUSTOMIZATION - IT REQUEST 11092 - Add Channel Routing based on Leads_Partner assignment
	
	return $return_values;
}

//Checks for sixty min campaigns during auto scrub
function isSixtyMinuteOpp($touchpoint){
	$campaign_ids_for_opp_create = getCampaignIdsForSixtyMinOpp();
	$result = (in_array($touchpoint->campaign_id, $campaign_ids_for_opp_create) || (isset($touchpoint->call_back_c) && $touchpoint->call_back_c));	
	return $result;
}

//Checks for sixty min campaigns during manual scrub
/*
** DEE 05/06/2010
** ITREQUEST: 15862
** CUSTOMIZATION: Flag opportunity as a 60 min opp if ce_user_profile_c = 'Evaluating Sugar for purchase'
*/
function isSixtyMinuteOpp_manual($touchpoint){
        $campaign_ids_for_opp_create = getCampaignIdsForSixtyMinOpp_manual();
        $result = (in_array($touchpoint->campaign_id, $campaign_ids_for_opp_create)
		|| (isset($touchpoint->ce_user_profile_c) && !empty($touchpoint->ce_user_profile_c) && $touchpoint->ce_user_profile_c == 'Evaluating Sugar for purchase')
	);
        return $result;
}

//Get all 60 min campaigns for conversion during auto scrub
function getCampaignIdsForSixtyMinOpp(){
	return array(
		'27c5bb36-a021-0835-7d82-43742c76164d', //Call Me Form
		'5e433111-4d25-0eda-548e-4321b4c0c8c8', //Call me form leads
	);
}

//Get all 60 min campaigns for conversion during manual scrub
function getCampaignIdsForSixtyMinOpp_manual() {
        return array(
                '44b2f86d-c179-ff06-26b3-49d525446fed',
                'a39fb927-37f4-3540-fd58-4535096a7d52',
                'ae723076-d7d5-ab15-c993-456c82cef418',
                '783d02f2-6b5c-cc0d-67ed-456c95dc3fa4',
                '2fe432fd-da0b-4c03-cdc7-45ad7d389f0e',
                '24a002ed-2996-3d38-4d64-46d30b20dbbc',
                '96285954-0c90-5e95-c969-46815abdb7d0',
                '7294e4f7-4c33-af2a-754e-4681443f7957',
                '3e477a24-fa6b-1df0-83de-468159dba1ce',
                '96da0526-9ca5-9d07-93c8-4681581f2659',
                'd820ec45-a51e-c624-187e-46815919504d',
                'a7fa21ca-4211-8ff7-36d0-46951528bc1a',
                'f2a997bd-c87b-e635-6b40-46c34983b8d0',
                'cfca160f-4565-c003-5084-469513d5a052',
                'd3b2c580-e158-77e3-b8ad-46951525296c',
                '39ba1609-3d54-186c-9296-46c4d0740763',
                '6cf48db9-7009-2b8d-28e7-469508c7cf02',
                '5d660fdd-2eea-c664-428a-481654d715b5',
                'af2bc4b4-24cd-3a43-5a3d-469513915e4b',
                '688f73a3-ef2d-1d80-b030-468165f20c41',
                'ace22528-7188-573b-3175-488dff664771',
                'c94b708c-0455-3150-94e2-46813e351d2e',
		'71b9cc9c-68ad-ee70-29ed-4afdac6678b8', //Smokescreen form: Data migration contact us
		'65a283ea-4c75-88b1-bd61-4b01fe68fb74', //7 Day Trials
		'c8f969df-56e5-1fc4-f890-4be13cfe70cf', //Sugar6 OnlinePreview
        	'd737c41a-b852-70ff-1d24-4c5b18c3a24e', //Focus HQL Program Q3 2010
		'cb4f416e-b6a3-7837-9f62-4ccb259521c3', //7 Day Trials - Mobile App
	);
}

function createOppTaskFromTouchpoint($touchpoint_id, $leadcontact_id){
	require_once('modules/LeadContacts/LeadContact.php');
	$lc = new LeadContact();
	$lc->disable_row_level_security = true;
	$lc->retrieve($leadcontact_id);
	if(!empty($lc->id) && !empty($lc->leadaccount_id)){
		require_once('modules/LeadAccounts/LeadAccount.php');
		$la = new LeadAccount();
		$la->disable_row_level_security = true;
		$la->retrieve($lc->leadaccount_id);
		if(!empty($la->id) && !empty($la->opportunity_id)){
			// Before actually doing the save, we check if the task has already been created
			$check_query = "select id from tasks where name = 'Touchpoint requests rep call' and parent_type = 'Opportunities' and parent_id = '{$la->opportunity_id}' and status != 'Complete' and deleted = 0";
			$check_res = $GLOBALS['db']->query($check_query);
			if($check_res){
				$check_row = $GLOBALS['db']->fetchByAssoc($check_res);
				if(!empty($check_row) && !empty($check_row['id'])){
					return false; // We return since we don't need to create a tasks, it already exists
				}
			}
			
			require_once('modules/Tasks/Task.php');
			$task = new Task();
			$task->assigned_user_id = $la->assigned_user_id;
			$task->team_id = '1';
			$task->name = 'Touchpoint requests rep call';
			$task->status = 'Not Started';
			$task->parent_type = 'Opportunities';
			$task->parent_id = $la->opportunity_id;
			$task->priority = 'High';
			global $sugar_config;
			$task->description = $sugar_config['site_url']."/index.php?module=Touchpoints&action=DetailView&record=".$touchpoint_id;
			$task_id = $task->save();
			
			return $task_id; // return the task id
		}
	}
	
	return false; // we didn't return in the inner block, so we didn't create a task
}
