<?php

$xcartdbserver = "online-comdb1";
$xcartdbuser   = "sugarcrm_com";
$xcartdbpass   = "08yag81g9Ag91";
$xcartdbname   = "sugarcrm_com";

$internalserver= 'si-db1';
//$internalport  = '1983';
$internaluser  = 'subscriptions';
$internalpass  = 'Reb0bObRus';
$internalname  = '';
$sidatabase = "sugarinternal";
$log = "portal name, first name, last name, email\n";
$noportalmatch ="";

echo getcwd() . "\n";
$connection = mysql_connect($xcartdbserver, $xcartdbuser, $xcartdbpass);
if(!$connection){
  echo "Connect to online db failed -> mysql $xcartdbname -h $xcartdbserver -u $xcartdbuser -p *********";
  die();
} else {
  echo "successfully connected to xcart db\n";
}

echo "starting xcart get of portal names tied to dl keys\n";
mysql_select_db($xcartdbname);
$query = "select distinct xcart_customers.login, xcart_customers.firstname, xcart_customers.lastname, xcart_customers.company, xcart_customers.email from xcart_customers inner join xcart_product_download_assoc on xcart_customers.userid = xcart_product_download_assoc.user_id inner join drupal_users on drupal_users.uid=xcart_customers.userid where drupal_users.status = 1 order by xcart_customers.login";

$res = mysql_query($query);
$data = array();
echo "query complete\n";
$n = 0;
$ucount=0;

while($row = mysql_fetch_assoc($res)){
  $data[]=array('login'=>$row['login'],'firstname'=>$row['firstname'],'lastname'=>$row['lastname'],'company'=>$row['company'],'email'=>$row['email']);
  $n++;
}
mysql_close($connection);
echo "total records from xcart:" . $n . "\n";
$connectionsi = mysql_connect($internalserver, $internaluser, $internalpass);
if(!$connectionsi){
  echo "Connect to online db failed -> mysql $xcartdbname -h $xcartdbserver -u $xcartdbuser -p *********";
  die();
} else {
  echo "successfully connected to si db\n";
}
$curCount = 0;
$percent_threshold = 0;
$pcount=0;
$ecount=0;
mysql_select_db($sidatabase);
echo "looping over logins and looking for portal active contacts to update\n";
foreach($data as $user) {
  $curCount++;

  $siquery ="select distinct id from contacts where deleted = 0 and portal_name ='" . $user['login'] . "'";
  $res = mysql_query($siquery);
  $row = mysql_fetch_assoc($res);
    if (isset($row['id'])) {
      $ucount++;
      $pcount++;
      $update_query = "UPDATE contacts_cstm SET download_software_c = 1 WHERE id_c ='" . $row['id'] ."'";
      $log .= $update_query." PORTAL MATCH\n";
      //////////// NEEED QUERY TO DO LIVE UPDATES //////////
      $res = mysql_query($update_query);
    } else {
      $siquery ="select distinct id from contacts where deleted = 0 and email1 ='" . $user['email'] . "'";
      $res = mysql_query($siquery);
      $row = mysql_fetch_assoc($res);
      if (isset($row['id'])) {
	$ucount++;
	$ecount++;
	$update_query = "UPDATE contacts_cstm SET download_software_c = 1 WHERE id_c ='" . $row['id'] ."'";
	$log .= $update_query.", EMAIL MATCH\n";
      //////////// NEEED QUERY TO DO LIVE UPDATES //////////
      $res = mysql_query($update_query);
      } else {
	$noportalmatch .= $user['login'].", ".$user['firstname'].", ".$user['lastname'].", ".$user['company'].", ".$user['email'].", "."\n";
      }    
    }
    if ($curCount/$n > $percentthreshold) {
      echo "Commpleted processing " .  $curCount." of " .$n . " with ". $pcount . " portal update hits ". $ecount . " email update hits " . $ucount . " total update hits\n";
      $percentthreshold += .01;
    }
}

mysql_close($connectionsi);
$nm =$n-$ucount;
echo "number no match:" . $nm . "\n";
echo "number updated:" . $ucount . "\n";
echo "no match log\n";
//var_dump($noportalmatch);
echo "match log\n";
//var_dump($log);
// write log
$ulog='dlAccessSyncUpdateLog.txt';
$elog ='dlAccessSyncMismatchLog.txt';
$fp = fopen($ulog, "wb");
fwrite($fp, $log);
fclose($fp);
$fp = fopen($elog, "wb");
fwrite($fp, $noportalmatch);
fclose($fp);
?>
