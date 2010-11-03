<?php

function create_guid()
{
        $microTime = microtime();
        list($a_dec, $a_sec) = explode(" ", $microTime);

        $dec_hex = dechex($a_dec* 1000000);
        $sec_hex = dechex($a_sec);

        ensure_length($dec_hex, 5);
        ensure_length($sec_hex, 6);

        $guid = "";
        $guid .= $dec_hex;
        $guid .= create_guid_section(3);
        $guid .= '-';
        $guid .= create_guid_section(4);
        $guid .= '-';
        $guid .= create_guid_section(4);
        $guid .= '-';
        $guid .= create_guid_section(4);
        $guid .= '-';
        $guid .= $sec_hex;
        $guid .= create_guid_section(6);

        return $guid;

}

function create_guid_section($characters)
{
        $return = "";
        for($i=0; $i<$characters; $i++)
        {
                $return .= dechex(mt_rand(0,15));
        }
        return $return;
}

function ensure_length(&$string, $length)
{
        $strlen = strlen($string);
        if($strlen < $length)
        {
                $string = str_pad($string,$length,"0");
        }
        else if($strlen > $length)
        {
                $string = substr($string, 0, $length);
        }
}


function get_related($portal_name) {
	global $sidb;
	
	$records = array();
	$records['user_id'] = '';
	$records['contact_id'] = '';
	$records['account_id'] = '';
	
	$sql = sprintf("SELECT id,assigned_user_id FROM contacts WHERE portal_name='%s' LIMIT 0,1", $portal_name);
	$result = mysql_query($sql, $sidb);
	if(mysql_num_rows($result) > 0) {
		$row = mysql_fetch_array($result);
		$records['contact_id'] = $row['id'];
		$records['user_id'] = $row['assigned_user_id'];
	} else {
		return $records;
	}
	
	$sql = sprintf("SELECT account_id FROM accounts_contacts WHERE contact_id='%s' LIMIT 0,1", $records['contact_id']);
	$result = mysql_query($sql, $sidb);
	if(mysql_num_rows($result) > 0) {
		$row = mysql_fetch_array($result);
		$records['account_id'] = $row['account_id'];
	} else {
		return $records;
	}
	
	return $records;	
	
}

function get_status($code) {
	$codes = array('C'=>'Completed','D'=>'Declined','F'=>'Declined','I'=>'pending_po','Q'=>'pending_contract',''=>'pending_salesops');
	
	return $codes[$code];
}

function get_payment_method($code) {
	if($code == 'Purchase Order')
		return 'purchase_order';
	return 'credit_card';
}


$sidb = mysql_connect('si-db1','sugarinternal','rI3pSTukiD6D');
mysql_select_db('sugarinternal', $sidb);

$xcdb = mysql_connect('online-comdb1','sugarcrm_com','08yag81g9Ag91');
mysql_select_db('sugarcrm_com',$xcdb);

$sql = "SELECT * FROM xcart_orders";
$orders = mysql_query($sql, $xcdb);

while($order = mysql_fetch_array($orders)) {
	$rels = get_related($order['login']);
	
	$record = array();
	$guid = create_guid();
	$record['id'] = $guid;
	$record['name'] = $order['orderid'];
	$record['date_entered'] = date("Y-m-d g:i:s", $order['date']);
	$record['date_modified'] = date("Y-m-d g:i:s", $order['date']);
	$record['modified_user_id'] = '1';
	$record['created_by'] = '1';
	$record['description'] = '';
	$record['deleted'] = 0;
	$record['team_id'] = 1;
	$record['team_set_id'] = 1;
	$record['assigned_user_id'] = $rels['user_id'];
	$record['order_id'] = $order['orderid'];
	$record['user_id'] = addslashes($order['user_id']);
	$record['username'] = addslashes($order['login']);
	$record['total'] = $order['total'];
	$record['subtotal'] = $order['subtotal'];
	$record['discount_code'] = '';
	$record['discount'] = $order['discount'];
	$record['tax'] = $order['tax'];
	$record['status'] = get_status($order['status']);
	$record['payment_method'] = get_payment_method($order['payment_method']);
	$record['notes'] = '';
	$record['title'] = addslashes($order['title']);
	$record['first_name'] = addslashes($order['firstname']);
	$record['last_name'] = addslashes($order['lastname']);
	$record['company_name'] = addslashes($order['company']);
	$record['phone'] = addslashes($order['phone']);
	$record['fax'] = addslashes($order['fax']);
	$record['email'] = addslashes($order['email']);
	$record['billing_title'] = addslashes($order['b_title']);
	$record['billing_first_name'] = addslashes($order['b_firstname']);
	$record['billing_last_name'] = addslashes($order['b_lastname']);
	$record['billing_address_city'] = '';
	$record['billing_address_state'] = '';
	$record['billing_address_postalcode'] = '';
	$record['billing_address_country'] = '';
	$record['billing_address'] = addslashes($order['b_address']);
	$record['billing_city'] = addslashes($order['b_city']);
	$record['billing_state'] = addslashes($order['b_state']);
	$record['billing_country'] = addslashes($order['b_country']);
	$record['billing_zip_code'] = addslashes($order['b_zipcode']);
	$record['shipping_title'] = addslashes($order['s_title']);
	$record['shipping_first_name'] = addslashes($order['s_firstname']);
	$record['shipping_last_name'] = addslashes($order['s_lastname']);
	$record['shipping_address_city'] = '';
	$record['shipping_address_state'] = '';
	$record['shipping_address_postalcode'] = '';
	$record['shipping_address_country'] = '';
	$record['shipping_address'] = addslashes($order['s_address']);
	$record['shipping_city'] = addslashes($order['s_city']);
	$record['shipping_state'] = addslashes($order['s_state']);
	$record['shipping_country'] = addslashes($order['s_country']);
	$record['shipping_zip_code'] = addslashes($order['s_zipcode']);
	
	$query = "INSERT INTO orders(";
	$query_values = "VALUES (";
	
	foreach($record as $k => $v) {
		$query .= sprintf("%s,", $k);
		$query_values .= sprintf("'%s',", $v);
	}
	$query = substr($query, 0, -1);
	$query_values = substr($query_values, 0, -1);
	
	$query .= ") ";
	$query_values .= ");";
	printf("%s %s\n", $query, $query_values);
	
	
	$rc = array();
	$rc['id_c'] = $guid;
	$rc['exclude_salesops_c'] = 'yes';
	$rc['copy_address_c'] = '';
	$rc['account_id_c'] = $rels['account_id'];
	$rc['contact_id_c'] = $rels['contact_id'];
	$rc['contact_id1_c'] = '';
	$rc['ondemand_instance_name_c'] = '';
	$rc['ondemand_datacenter_c'] = '';
	$rc['blue_bird_c'] = '';
	$rc['workload_c'] = '';
	$rc['cart_action_c'] = '';
	$rc['black_bird_c'] = '';
	$rc['in_netsuite_c'] = '1';
	$rc['partner_margin_c'] = '';
	
	$query = "INSERT INTO orders_cstm(";
	$query_values = "VALUES (";
	
	foreach($rc as $k => $v) {
		$query .= sprintf("%s,", $k);
		$query_values .= sprintf("'%s',", $v);
	}
	$query = substr($query, 0, -1);
	$query_values = substr($query_values, 0, -1);
	$query .= ") ";
	$query_values .= ");";
	printf("%s %s\n", $query, $query_values);
	
}
