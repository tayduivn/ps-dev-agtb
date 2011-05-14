<?php

define('sugarEntry', true);
// official location if builder is included in the product itself
//include('include/SugarTheme/SugarSpriteBuilder.php');
include('SugarSpriteBuilder.php');

$sb = new SugarSpriteBuilder();

// false for html output
$sb->silentRun = true;

// add common image directories
$sb->addDirectory('default', 'include/images');
$sb->addDirectory('default', 'themes/default/images');
$sb->addDirectory('default', 'themes/default/images/SugarLogic');

// add all theme image directories
if($dh = opendir('themes')) {
	while (($dir = readdir($dh)) !== false) {
		if ($dir != "." && $dir != ".." && $dir != 'default' && is_dir('themes/'.$dir)) {
			$sb->addDirectory($dir, "themes/$dir/images");
		}
	}
	closedir($dh);
}

// generate the sprite goodies
// everything is saved into cache/sprites
$sb->createSprites();

// after generation, send png files to Lam to recompress them with Photoshop

?>
