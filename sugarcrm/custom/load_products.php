<?php

/**
 * jvink - temp file to import the products_array.php file into the database.
 * 		may be removed for production
 */

// db connect ... plz change as required
mysql_connect('itr21796.cytxbuhjqkpu.us-west-1.rds.amazonaws.com','root','7vjLFb7K');
mysql_select_db('ibmpilotdev') or die ('i made a booboo ...');

// load product array
include('products_array.php');

// empty table
$table = 'ibm_revenuelineitems_products';
mysql_query('TRUNCATE TABLE '.$table);

foreach($products as $product) {

	// catch product ids not matching id column length
	if(strlen($product['id']) > 32) {
		echo "\nID ERROR -> ".$product['id']."\n";
	} else {

	// escape name
	// replace spaces by '' to be able to full text search whole strings --> to be tested !!!
	//$name = addslashes(str_replace(' ',"''",$product['name']));
	$name = addslashes($product['name']);

	// escape description
	$desc = addslashes($product['desc']);

	// level needs only integer
	$level = substr($product['level'], $product['level'] - 2, 2);
	$insert = 'INSERT INTO '.$table.' (id,name,level,parent_id,code,'.$table.'.desc)
					VALUES ("'.$product['id'].'","'.$name.'", '.$level.', "'.$product['parent_id'].'", 
					"'.$product['code'].'", "'.$desc.'")';
	if(mysql_query($insert)) {
		echo "-";
	} else {
		echo $insert."\n";
	}	

	}

}
echo "\n";

// enable full text search on this table
//mysql_query('ALTER TABLE '.$table.' ADD FULLTEXT(name)');

// repair table to rebuild full text search
mysql_query('REPAIR TABLE '.$table);

?>
