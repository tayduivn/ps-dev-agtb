<?php

if ( function_exists('register_sidebars') )
	register_sidebar();

/*Custom Header Image*/
function flickrock_menu() {
		add_submenu_page('themes.php', 'Theme Option', 'Theme Option', 8, 'theme-option', 'flickrock');
}

function flickrock(){

	if ($_POST['action'] == 'update'){
		$flickr_tag = wp_specialchars($_POST['flickr_tag']);
		update_option('flickrock',$flickr_tag);
		echo '<div class="updated fade"><p>Updated!</p></div>';
	}

	if ($_POST['action'] == 'default'){
		delete_option('flickrock',$flickr_tag);
		echo '<div class="updated fade"><p>Updated!</p></div>';
	}

?>
<div class="wrap">
<h2>Flickrock Option</h2>
<p>Here you can change your header image with flickr tag.</p>
<form method="POST" action="" />
<label for="upload">flickr tag: eg. flowers</label><br />
<input type="text" id="flickr_tag" name="flickr_tag" value="<?php $flickr_tag = get_option('flickrock',$flickr_tag); echo $flickr_tag; ?>"/>
<input type="hidden" name="action" value="update" />
<p class="submit"><input type="submit" value="Update Options &raquo;" /></p>
</form>
<form method="POST" action="" />
<input type="hidden" name="action" value="default" />
<p class="submit"><input type="submit" value="Restore Default &raquo;" /></p>
</form>
<p>Theme Author: Flickrock Wordpress II <a href="http://patrick.bloggles.info">Patrick Chia</a></p>
</div>
<?php
}
	add_action('admin_menu', 'flickrock_menu');
?>