<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$sprites_config['themes/default/images'] = array(

	// exclude images
	'exclude' => array(
		// animated gifs
		'bar_loader.gif',
		'img_loading.gif',
	),

	// repeatable sprites
	'repeat' => array(
		array(
			'width' => 1,
			'height' => 10,
			'direction' => 'horizontal',
		),
	),

);

?>
