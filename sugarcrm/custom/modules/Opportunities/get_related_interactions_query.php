<?php

	//this function is used by interaction subpanel off of opportunity and OppQ forms to populate subpanel.
	function get_related_interactions_query($param_arr){
		if(is_array($param_arr) && isset($param_arr['bean_id'])){

			$query = "(
			SELECT interactions.* from opportunities o 
    			LEFT JOIN accounts_opportunities ao on ao.opportunity_id = o.id
    			LEFT JOIN accounts_contacts accon on accon.account_id = ao.account_id
    			INNER JOIN interactions on interactions.parent_id = accon.contact_id && interactions.deleted = 0
			WHERE o.id = '{$param_arr['bean_id']}')";

		}else{
			$query = '';
		}

		return $query;
	}

	//this function will take in the opportunity id and return the maximum score of all related contacts and leadcontacts
	function get_max_score($bean_id){
		//preset score to 0
		$score = 0;
		//process if bean id is passed in
		if (!empty($bean_id)){
/*			$query = "(select max(i.score) score from interactions i
			left join leadcontacts lc on lc.id = i.parent_id
			left join leadaccounts la on la.id = lc.leadaccount_id
			left join opportunities o on o.id = la.opportunity_id
			where o.id = '$bean_id') UNION ALL (select max(i.score) score from interactions i
			left join contacts con on con.id = i.parent_id
			left join accounts_contacts accon on accon.contact_id = con.id
			left join accounts_opportunities ao on ao.account_id = accon.account_id
			left join opportunities o on o.id = ao.opportunity_id
			where o.id = '$bean_id')";
*/
			//query related leadaccouts and contacts for the max score
			$query = "(select max(lc.score) score from leadcontacts lc 
			left join leadaccounts la on la.id = lc.leadaccount_id
			left join opportunities o on o.id = la.opportunity_id
			where o.id = '$bean_id') UNION ALL (select max(con_c.score_c) score from contacts con 
			left join contacts_cstm con_c on con_c.id_c = con.id
			left join accounts_contacts accon on accon.contact_id = con.id
			left join accounts_opportunities ao on ao.account_id = accon.account_id
			left join opportunities o on o.id = ao.opportunity_id
			where o.id = '$bean_id')";

			//execute query and process results
			$result =$GLOBALS['db']->query($query);
			
			while ($row = $GLOBALS['db']->fetchByAssoc($result)){

				//skip if empty
				if(empty($row['score'])) continue;
				//$score = $score + $row['score'];
				//compare and keep the max
				if($score<$row['score']) $score = $row['score'];
			}
		}
		return $score;

	}

?>
